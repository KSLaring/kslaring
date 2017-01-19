/*global define: false, YUI: false */
define(['jquery', 'core/log'], function ($, log) {
    "use strict";

    // For debugging.
    // window.$ = $;
    log.debug('KSL quiznav AMD');

    return {
        init: function () {
            log.debug('KSL quiznav init');

            YUI().use('anim', 'node-event-simulate', function (Y) {
                var nbl = Y.one("#mod_quiz_navblock"),
                    p = nbl.one("a.thispage"),
                    qbtns = Y.one(".qn_buttons"),
                    allqbtns = qbtns.all(".qnbutton");

                if (p) {
                    var p_id = p.getAttribute('id'),
                        p_no = parseInt(p_id.replace(/\D/g, ''), 10),
                        c = nbl.one("#qn-buttons-wrapper"),
                        l_btn_ar = c.all('a:last-child'),
                        l_btn = l_btn_ar.item(0),
                        l_btn_id = l_btn.getAttribute('id'),
                        l_btn_no = parseInt(l_btn_id.replace(/\D/g, ''), 10),
                        btnp = nbl.one(".prev-btn"),
                        btnn = nbl.one(".next-btn"),
                        ppos = parseInt(p.getX()),
                        cpos = parseInt(c.getX()),
                        poff = ppos - cpos,
                        pl = 0,
                        pr = 0,
                        amount = 0,
                        base = 0;

                    btnp.on('click', function () {
                        if (p_no > 1) {
                            c.one('#quiznavbutton' + (p_no - 1)).simulate('click');
                        }
                    });

                    btnn.on('click', function () {
                        if (p_no < l_btn_no) {
                            c.one('#quiznavbutton' + (p_no + 1)).simulate('click');
                        }
                    });

                    log.debug('ppos: ' + ppos);
                    log.debug('cpos: ' + cpos);
                    log.debug('poff: ' + poff);

                    if (poff > 0) {
                        var ani = new Y.Anim({
                            node: c,
                            to: {
                                scrollLeft: poff
                            }
                        });
                        ani.run();
                    }

                    // Calculate the padding depending on the number of elements
                    // to get the percentage for left and right padding. One button has
                    // left padding, the number, right padding. So padding will reference
                    // roughly 1/3. If the numbers show 1 figure use 40% if there are more than 10
                    // questions numbers have one and two figures - then use 35%.
                    amount = allqbtns.size();
                    base = (amount < 10) ? 45 : 35;
                    // pr = (base / amount) + "%";
                    pr = "0";
                    pl = pr;

                    // log.debug('amount: ' + amount);
                    // log.debug('base: ' + base);
                    // log.debug('pr: ' + pr);

                    allqbtns.setStyles({
                        "padding-right": pr,
                        "padding-left": pl
                    });

                    qbtns.addClass("repaint");

                    log.debug(p_id);
                    log.debug(p_no);
                    log.debug(l_btn_no);
                }
            });
        }
    };
});
