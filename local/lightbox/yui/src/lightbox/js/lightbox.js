/**
 * Lightbox with iframe for all external links
 *
 * User: Urs Hunkler
 * Date: 2014-07-16
 *
 * Only used with bigger screen size
 */
/*global M:true */
var LIGHTBOX = 'Lightbox',
    LIGHTBOXNAME = 'moodle-local_lightbox-lightbox',
    CSS = {
        MOODLEDIALOGBASE: '.moodle-dialogue-base',
        MOODLEDIALOGCLASS: 'moodle-dialogue',
        MOODLEDIALOGWRAPCLASS: 'moodle-dialogue-wrap',
        MOODLEDIALOGHDCLASS: 'moodle-dialogue-hd'
    },
    SELECTORS = {
        MDLBODY: 'body',
        MDLPAGE: '#page-content',
        MDLCUSTOMMENU: '.navbar-fixed-top',
        EXTLINKS: 'a[rel="lightbox"]',
        IFRAME: '#external-links'
    },
    VALUES = {
        LBPADDINGBOTTOM: 5,
        LBIFRAMEGBOTTOM: 7,
        MINWINDOWWIDTH: 960,
        MINWINDOWHEIGHT: 800
    },
    NS = Y.namespace('Moodle.local_lightbox'),
    MNS;

/**
 * The lightbox classes
 *
 * LIGHTBOX
 */
NS[LIGHTBOX] = Y.Base.create(LIGHTBOXNAME, Y.Panel, [], {
    theframe: null,
    firstload: true,
    clickdelegate: null,
    clickoutside: null,
    hasbeenshown: false,

    initializer: function () {
        var that = this;
//            custommenu;

        // handle lightbox links in the content area
        this.clickdelegate = Y.one(SELECTORS.MDLPAGE).delegate('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            that.handleExtLinkClick(e.currentTarget);
        }, SELECTORS.EXTLINKS);

        // handle lightbox links in the custom menu
//        custommenu = Y.one(SELECTORS.MDLCUSTOMMENU);
//        if (custommenu) {
//            this.clickdelegate = custommenu.delegate('click', function (e) {
//                e.preventDefault();
//                e.stopPropagation();
//                that.handleExtLinkClick(e.currentTarget);
//            }, SELECTORS.EXTLINKS);
//        }
    },
    destructor: function () {
        this.set('bodyContent', '');
        this.get('contentBox').detach('clickoutside');
        this.clickdelegate.detach();
    },
    /**
     * Show the dialogue
     *
     * Set the mask darker, resize the dialog content and center the dialogue.
     * Activate the click outside handler and set the focus on the close button.
     */
    display: function () {
//        this.centered(); // The widget centered attribute scrolls the page - it's not usable
        this.show();
        this.get('maskNode').setStyle('opacity', 0.8);
        this.resizeBody();
        this.centerDialogue();
        this.clickoutside = this.get('contentBox').on('clickoutside', this.handleClickoutside, this);
        this.get('buttons').header[0].focus();
    },
    hideLightBox: function () {
        Y.log('hideLightBox');
        this.hide();
        this.set('src', '');
        this.clickoutside.detach();
    },
    handleVisibleChange: function () {
        Y.log('handleVisibleChange');

        if (!this.get('visible')) {
            this.hideLightBox();
        }
    },
    handleClickoutside: function () {
        Y.log('handleClickoutside');

        this.hideLightBox();
    },
    handleExtLinkClick: function (ele) {
        var src = ele.getAttribute('href');

        // Modify the link url to load a modified SCORM view page
        src = src.replace('mod/scorm', 'local/scorm_lightbox');

        if (src !== '') {
            this.set('src', src);
            this.display();
//            this.set('frameloaded', false);
        }
    },
    handleIFrameLoaded: function () {
        if (this.firstload) {
            this.firstload = false;
            return;
        }
        if (this.get('frameloaded')) {
            Y.log('iframe loaded true');
            this.display();
        } else {
            Y.log('iframe loaded false');
        }
    },
    handleResizeChange: function () {
        Y.log('handleResizeChange: ' + this.get('resized'));
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
        Y.log(PbodyHeight);
    },
    renderUI: function () {
        this.set('bodyContent', '<iframe id="external-links"/>');
        this.set('headerContent', '');
        this.theframe = this.get('contentBox').one(SELECTORS.IFRAME);
        this.get('boundingBox').addClass(CSS.MOODLEDIALOGCLASS);
        this.get('contentBox').addClass(CSS.MOODLEDIALOGWRAPCLASS);
        this.plug(Y.Plugin.Drag, {handles: ['.yui3-moodle-local_lightbox-lightbox .moodle-dialogue-hd']});
        this.getStdModNode(Y.WidgetStdMod.HEADER, true).addClass(CSS.MOODLEDIALOGHDCLASS);
    },
    bindUI: function () {
        var that = this;

        // show panel after the document is loaded into the iframe
//        this.theframe.on('load', function () { that.set('frameloaded', true); }, this);
//        this.after('frameloadedChange', this.handleIFrameLoaded, this);
        Y.on("windowresize", function () { that.set('resized', Y.one('window').get('winHeight')); });
        this.after('resizedChange', this.handleResizeChange, this);
        this.after('visibleChange', this.handleVisibleChange, this);
        this.after('srcChange', this.syncUI, this);
    },
    syncUI: function () {
        this.theframe.setAttribute('src', this.get('src'));
    }
}, {
    ATTRS: {
        param1: {
            value: 'Test plugin default text'
        },
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
MNS = M.local_lightbox.lightbox = {};

MNS.init_lightbox = function (config) {
    var dialogwrapper,
        lb,
        winWidth;

    if (window.getComputedStyle !== undefined) {
        winWidth = window.getComputedStyle(document.body, ':after')
            .getPropertyValue('content');
    } else { //http://adactio.com/journal/5429/
        winWidth = Y.one('body').get('offsetWidth') >= VALUES.MINWINDOWWIDTH ? 'widescreen' : '';
    }

    if (winWidth.indexOf('widescreen') !== -1) {
        if (!Y.one(SELECTORS.EXTLINKS)) {
            return;
        }

        dialogwrapper = Y.one(CSS.MOODLEDIALOGBASE);

        if (!dialogwrapper) {
            dialogwrapper = Y.Node.create('<div class="moodle-dialogue-base"></div>');
            Y.one(SELECTORS.MDLBODY).append(dialogwrapper);
        }

        YUI().use('event-resize');

        lb = new NS[LIGHTBOX](config);

        if (window.screen.height <= VALUES.MINWINDOWHEIGHT) {
            lb.set('height', '97%');
        }
        lb.render(dialogwrapper);
    }
};
