YUI.add('moodle-local_lightbox-lightbox', function (Y, NAME) {

/**
 * Lightbox with iframe for special defined links.
 *
 * Only used with wider screens - the minimum screen width is defined in the
 * JS VALUES object: MINWINDOWWIDTH and in CSS as a media query which sets the
 * value for the minimum screen width.
 *
 * The link types which trigger the lightbox are defined in the LINKSOURCE object.
 *
 * User: Urs Hunkler
 * Date: 2014-07-16
 */
/*global M:true */
var LIGHTBOX = 'Lightbox',
    LIGHTBOXNAME = 'moodle-local_lightbox-lightbox',
    CSS = {
        MOODLEDIALOGBASE: '.moodle-dialogue-base',
        MOODLEDIALOGCLASS: 'moodle-dialogue',
        MOODLEDIALOGWRAPCLASS: 'moodle-dialogue-wrap',
        MOODLEDIALOGHDCLASS: 'moodle-dialogue-hd',
        INLIGHTBOX: 'in-lightbox',
        NOLIGHTBOX: 'no-lightbox'
    },
    SELECTORS = {
        MDLBODY: 'body',
        MDLPAGE: '#page-content',
        MDLCUSTOMMENU: '.navbar-fixed-top',
        IFRAME: '#external-links'
    },
    LINKSOURCE = {
        EXTLINKS: {
            link: 'a[rel="lightbox"]',
            method: 'handleExtLinkClick'
        },
        SCORMLINKS: {
            link: 'a.scorm',
            method: 'handleSCORMLinkClick'
        },
        EXTURL: {
            link: '.urlworkaround a',
            method: 'handleURLLinkClick'
        },
        EXTURLLIGHTBOX: {
            link: '.urlworkaround a.in-lightbox',
            method: 'handleURLLinkClick'
        }
    },
    VALUES = {
        LBPADDINGBOTTOM: 5,
        LBIFRAMEGBOTTOM: 7,
        MINWINDOWWIDTH: 920,
        MINWINDOWHEIGHT: 800
    },
    NS = Y.namespace('Moodle.local_lightbox'),
    MLLNS;

/**
 * The lightbox classes
 *
 * LIGHTBOX
 */
NS[LIGHTBOX] = Y.Base.create(LIGHTBOXNAME, Y.Panel, [], {
    theframe: null,
    firstload: true,
    clickdelegate: [],
    clickoutside: null,
    hasbeenshown: false,
    lectorafw: null,

    initializer: function () {
        var alink,
            mdlpage = Y.one(SELECTORS.MDLPAGE);

        MLLNS.initialized = true;

        // Handle lightbox links in the content area
        // Add the click delegate on the 'page-content' element
        // to handle all clicks centrally.
        for (alink in LINKSOURCE) {
            // Check if the link is an external URL
            // to be able to differentiate between http and https links
            // because http links must not open in the lightbox.
            if (alink === 'EXTURL') {
                this.check_https(LINKSOURCE[alink]);
            } else {
                this.addClickDelegate(LINKSOURCE[alink]);
            }
        }

        mdlpage.all(LINKSOURCE.EXTURL.link).each(function (node) {
            if (node.hasClass(CSS.INLIGHTBOX)) {
                var popup = node.getAttribute('onclick');

                if (popup !== '') {
                    var wmatch = popup.match(/width=(\d*)/),
                        hmatch = popup.match(/height=(\d*)/),
                        width,
                        height;

                    width = wmatch !== null ? wmatch[1] : 0;
                    height = hmatch !== null ? hmatch[1] : 0;

                    node.setAttribute('data-width', '' + width);
                    node.setAttribute('data-height', '' + height);
                    node.setAttribute('onclick', '');
                }
            }
        });
    },

    destructor: function () {
        this.set('bodyContent', '');
        this.get('contentBox').detach('clickoutside');
        this.clickdelegate.forEach(function (clickdele) {
            clickdele.detach();
        });
    },

    /**
     * Show the dialogue
     *
     * Set the mask darker, resize the dialog content and center the dialogue.
     * Activate the click outside handler and set the focus on the close button.
     */
    display: function () {
//        this.centered(); // The widget centered attribute scrolls the page
        this.show();
        this.get('maskNode').setStyle('opacity', 0.8);
        this.resizeBody();
        this.centerDialogue();
        this.clickoutside = this.get('contentBox').on('clickoutside',
            this.handleClickoutside, this);
        this.get('buttons').header[0].focus();
    },

    hideLightBox: function () {
        // A reference to the Lectora content window has been saved if a Lectora
        // SCORM is shown in the lightbox. If the reference is present correct
        // a Lectora 11 issue with webkit/chromium browsers: onbeforeunload is
        // not fired when the lightbox closes - bind the finish function to
        // onunload instead.

        // get the SCORM content window
        var iframeNode = this.get('contentBox').one('#external-links').getDOMNode(),
            iframeDocument = iframeNode.contentDocument,
            scoObject = Y.one(iframeDocument).one('#scorm_object'),
            fw = null;

        if (scoObject) {
            var scoDocument = scoObject.getDOMNode().contentDocument;

            if (scoDocument.all.length &&
                scoDocument.all[0].innerHTML.indexOf('GENERATED BY:  Lectora') !== -1) {
                var cframe = scoDocument.getElementsByName('contentframe')[0];

                if (cframe) {
                    fw = cframe.contentWindow;

                    if (fw) {
                        var $fw = Y.one(fw);
                        $fw.on('unload', function () {
                        });
                        $fw.on('beforeunload', function () {
                        });

                        if ('finish' in fw) {
                            if (fw.is.webkit) {
                                fw.onbeforeunload = null;
                                fw.onunload = fw.finish; // connect the finish method - changed in L 11
                            }
                        }


                        if ('doQuit' in fw) {
                            fw.doQuit_orig = fw.doQuit;
                            fw.doQuit = function doQuit(bForce) {
                                bForce = 0;
                                fw.doQuit_orig(bForce);
                            };
                        } else if (fw.finishCalled !== true) {
                            if ('computeTime' in fw) {
                                fw.computeTime();
                            }
                            if ('LMSCommit' in fw) {
                                fw.LMSCommit();
                            }
                        }
                    }
                }
            }
        }

        this.set('src', '');
        this.clickoutside.detach();
    },

    handleVisibleChange: function () {

        if (!this.get('visible')) {
            this.hideLightBox();
        }
    },

    handleClickoutside: function () {

        this.hide(); // sets 'visible' to false - triggers handleVisibleChange
    },

    /**
     * Add the click delegate.
     *
     * Create the delegate for the given link and set the given method as
     * the click handler. Collect all click delegates to be able to detach them
     * in the deconstruct method.
     *
     * @param {object} alink   The link source object (link and method)
     */
    addClickDelegate: function (alink) {
        var that = this,
            mdlpage = Y.one(SELECTORS.MDLPAGE);

        this.clickdelegate.push(mdlpage.delegate('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            that[alink.method].call(that, e.currentTarget);
        }, alink.link));
    },

    /**
     * Check if the given link source protokoll is HTTPS.
     *
     * If the site is a HTTPS site then open in lightbox
     * _ only internal HTTPS links
     * _ no external HTTPS links
     * _ no HTTPS links
     * else if it is a HTTP site then open in lightbox
     * _ only HTTP links, internal and external
     * _ no HTTPS links
     *
     * @param {object} alink   The link source object (link and method)
     */
    check_https: function (alink) {
        var mdlpage = Y.one(SELECTORS.MDLPAGE),
            links = mdlpage.all(alink.link);

        if (links) {
            links.each(function (el) {
                var href = el.getAttribute('href'),
                    ishttpslink = (href.indexOf('https') !== -1),
                    ishttpssite = (M.cfg.wwwroot.indexOf('https') !== -1);

                if (ishttpslink) {
                    // Only show in lightbox when it's a local link.
                    if (href.indexOf(M.cfg.wwwroot) !== -1) {
                        el.addClass(CSS.INLIGHTBOX);
                    } else {
                        el.addClass(CSS.NOLIGHTBOX);
                    }
                } else {
                    // It's a http link.
                    // Show in lightbox when it's not a https site and it's a local link.
                    if (!ishttpssite && href.indexOf(M.cfg.wwwroot) !== -1) {
                        el.addClass(CSS.INLIGHTBOX);
                    } else {
                        el.addClass(CSS.NOLIGHTBOX);
                    }
                }
            });
        }
    },

    /**
     * Handle links with the rel=lightbox attribute.
     * Get the href from the click target and use it as the src for the iframe
     * to load the page.
     *
     * If a SCORM module is linked change the path to the local scorm_lightbox
     * which opens the SCORM directly.
     *
     * @param {object} ele   The click target
     */
    handleExtLinkClick: function (ele) {
        var src = ele.getAttribute('href');

        // If the url links to a SCORM modify the link url
        // to load a modified SCORM view page
        if (src.indexOf('scorm') !== -1) {
            src = src.replace('mod/scorm', 'local/scorm_lightbox');
        }

        if (src !== '') {
            this.set('src', src);
            this.display();
            // this.set('frameloaded', false);
        }
    },

    /**
     * Handle links to SCORMs.
     *
     * Get the href from the click target and use it as the src for the iframe
     * to load the page. Change the lightbox width and height to the given
     * values.
     *
     * @param {object} ele   The click target
     */
    handleSCORMLinkClick: function (ele) {
        var src = ele.getAttribute('href'),
            width = ele.getAttribute('data-scormwidth'),
            height = ele.getAttribute('data-scormheight');
        //launch =  ele.getAttribute('data-scormlaunch');

        // Change the URL to load the local/scorm_lightbox/player scripts
        // The SCORM parameter "popup" must be changed to load the page into the
        // lightbox and not open a new window. That task seams quite complicated!!!
//        if (src.indexOf('/mod/scorm/') !== -1) {
//            src = src.replace('/mod/scorm/view', '/local/scorm_lightbox/player');
//            src = src.replace('?id=', '?cm=');
//            src += '&scoid=' + launch;
//        }

        // Change the URL to load the local/scorm_lightbox/player scripts
        if (src.indexOf('/mod/scorm/') !== -1) {
            src = src.replace('/mod/scorm/', '/local/scorm_lightbox/');
        }

        console.log(src, width, height);
        if (src !== '') {
            this.set('src', src);

            // If width and height are given set the lightbox size
            if (width && height) {
                this.set('width', width);
                this.set('height', parseInt(height, 10) + 20);
            }

            this.display();
        }
    },

    /**
     * Handle links set by the Moodle URL resource.
     *
     * Get the href from the click target and use it as the src for the iframe
     * to load the page. Change the lightbox width and height to the given
     * values.
     *
     * @param {object} ele   The click target
     */
    handleURLLinkClick: function (ele) {
        var src = ele.getAttribute('href'),
            width = ele.getAttribute('data-width'),
            height = ele.getAttribute('data-height');

        if (src !== '') {
            this.set('src', src);

            // If width and height are given set the lightbox size
            if (width && height) {
                this.set('width', width);
                this.set('height', parseInt(height, 10) + 20);
            }

            this.display();
            // this.set('frameloaded', false);
        }
    },

    handleIFrameLoaded: function () {
        if (this.firstload) {
            this.firstload = false;
            return;
        }
        if (this.get('frameloaded')) {
            this.display();
        } else {
        }
    },

    handleResizeChange: function () {
        this.resizeBody();
        this.centerDialogue();
    },

    /**
     * Center the dialogue
     *
     * Get the boundingBox width and height and the window width and height
     * and calculate the x, y offset for the dialogue.
     *
     * The widget "centered" method does not work reliable. If set without a
     * relating node the dialogue is opened below and beside the page content
     * and the contetn is scrolled out of the view.
     */
    centerDialogue: function () {
        var bb = this.get('boundingBox'),
            w = parseInt(bb.getComputedStyle('width'), 10),
            h = parseInt(bb.getComputedStyle('height'), 10),
            winW = bb.get('winWidth'),
            winH = bb.get('winHeight');
        this.set('xy', [(winW - w) / 2, (winH - h) / 2]);
    },

    resizeBody: function () {
        var Pbox = this.get('contentBox'),
            Pheader = this.getStdModNode(Y.WidgetStdMod.HEADER),
            Pbody = this.getStdModNode(Y.WidgetStdMod.BODY),
            PbodyHeight;

        PbodyHeight = Pbox.get('clientHeight') - Pheader.get('clientHeight');
        this.theframe.setStyle('height', (PbodyHeight - VALUES.LBPADDINGBOTTOM) + 'px');
        Pbody.setStyle('height', (PbodyHeight - VALUES.LBIFRAMEGBOTTOM) + 'px');
    },

    renderUI: function () {
        this.set('bodyContent', '<iframe id="external-links"></iframe>');
        this.set('headerContent', '');
        this.theframe = this.get('contentBox').one(SELECTORS.IFRAME);
        this.get('boundingBox').addClass(CSS.MOODLEDIALOGCLASS);
        this.get('contentBox').addClass(CSS.MOODLEDIALOGWRAPCLASS);
        // Enable Drag&Drop for all browsers except IE because IEs have a JavaScript
        // issue with the page when D&D is activated on the lightbox.
        if (!Y.UA.ie) {
            this.plug(Y.Plugin.Drag, {handles: ['.yui3-widget-hd']});
        } else {
            Y.one('.yui3-moodle-local_lightbox-lightbox .yui3-widget-hd').setStyle('cursor', 'default');
        }
        this.getStdModNode(Y.WidgetStdMod.HEADER, true)
            .addClass(CSS.MOODLEDIALOGHDCLASS);
    },

    bindUI: function () {
        var that = this;

        // show panel after the document is loaded into the iframe
//        this.theframe.on('load', function () { that.set('frameloaded', true); }, this);
//        this.after('frameloadedChange', this.handleIFrameLoaded, this);
        Y.on("windowresize", function () {
            that.set('resized', Y.one('window').get('winHeight'));
        });
        this.after('resizedChange', this.handleResizeChange, this);
        this.after('visibleChange', this.handleVisibleChange, this);
        this.after('srcChange', this.syncUI, this);
    },

    syncUI: function () {
        this.theframe.setAttribute('src', this.get('src'));
    }
}, {
    ATTRS: {
        src: {
            value: ''
        },
        frameloaded: {
            value: false
        },
        resized: {
            value: 0
        },
        modal: {
            value: true
        },
        visible: {
            value: false
        },
        height: {
            value: '80%'
        },
        width: {
            value: '90%'
        },
        zIndex: {
            value: 5000
        }
    }
});

// create the bridge to the global Moodle M namespace if not present
window.M = window.M || {};
M.local_lightbox = M.local_lightbox || {};
MLLNS = M.local_lightbox.lightbox = {};
MLLNS.initialized = false;

MLLNS.init_lightbox = function (config) {
    var dialogwrapper,
        lb,
        winWidth;

    // Avoid double initialization when the module is called from several
    // places in Moodle.
    if (MLLNS.initialized) {
        return;
    }

    // Check if the getComputedStyle method is present, if so get the content
    // of body:after set by a media query handling widths over the defined
    // window width: 'widescreen'. If the method does not exist calculate the
    // width with JavaScript.
    if (window.getComputedStyle !== undefined) {
        winWidth = window.getComputedStyle(document.body, ':after')
            .getPropertyValue('content');
    } else { //http://adactio.com/journal/5429/
        winWidth = Y.one('body').get('offsetWidth') >= VALUES.MINWINDOWWIDTH ?
            'widescreen' : '';
    }

    if (winWidth.indexOf('widescreen') !== -1) {
        // Check if one of the possible lighbox sources is present in the actual
        // page. All possible link sources are collected in the LINKSOURCE object.
        var nolink = true,
            alink;
        for (alink in LINKSOURCE) {
            if (Y.one(LINKSOURCE[alink].link)) {
                nolink = false;
                break;
            }
        }

        if (nolink) {
            return;
        }

        // Create the HTML for the lightbox if not present
        dialogwrapper = Y.one(CSS.MOODLEDIALOGBASE);

        if (!dialogwrapper) {
            dialogwrapper = Y.Node.create('<div class="moodle-dialogue-base"></div>');
            Y.one(SELECTORS.MDLBODY).append(dialogwrapper);
        }

        // Add the event-resize YUI module which is used to adopt the
        // lightbox to screen size changes.
        YUI().use('event-resize');

        // Create a new lightbox object and render the lightbox
        // without showing it.
        lb = new NS[LIGHTBOX](config);

        if (window.screen.height <= VALUES.MINWINDOWHEIGHT) {
            lb.set('height', '97%');
        }

        lb.render(dialogwrapper);
    }
};


}, '0.1.0', {
    "requires": [
        "panel",
        "node",
        "dd-plugin",
        "dd-drag",
        "event-resize"
    ],
    "config": {
        "use": [
            "panel",
            "node",
            "dd-plugin",
            "dd-drag",
            "event-resize"
        ]
    }
});
