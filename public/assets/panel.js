var wwt = parseInt($(window).width());
var wht = parseInt($(window).height());
$(function ($) {
    $.fn.panel = function (options) {
        options = options || {};
        options.target = options.target || this;
        options.initial = options.initial || {};
        var id = options.id || Math.floor((Math.random() * 10000000));

        function _init() {
            var panel = {
                drag: function ($ontab, $drag) {
                    dragResizeAction($ontab, $drag, 'd', control.resize, control.onResize, control.onDrag)
                }, minimize: function ($panel) {
                    if ($panel.attr("data-minimize") === "1") {
                        minimizePanelOut($panel, control.speed, control.blur, control.resize, control.onResize, control.onDrag)
                    } else {
                        minimizePanelIn($panel, control.speed, control.blur);
                        setTimeout(function () {
                            $panel.find(".ontab-header").off("mousedown").on("click", function () {
                                panel.minimize($panel)
                            })
                        }, control.speed * 1000)
                    }
                }, maximize: function ($panel) {
                    if ($panel.attr("data-maximize") === "1") maximizePanelOut($panel, control.speed, control.resize, control.onResize, control.onDrag); else maximizePanelIn($panel, control.speed, control.resize);
                    panelScrool(control.blur)
                }
            };
            var control = {
                drag: !0,
                resize: !0,
                minimize: !0,
                maximize: !0,
                close: !0,
                onClose: !1,
                onMinimize: !1,
                onMaximize: !1,
                onDrag: !1,
                onResize: !1,
                speed: 0.25,
                blur: !0,
                clickOut: !1,
                timeOut: !1,
                closeFunction: !1
            };
            $.extend(control, options.control);
            var css = {
                "top": parseInt(wht * (wwt < 900 ? (wwt < 500 ? 0 : 0.025) : 0.05)),
                "left": parseInt(wwt * (wwt < 900 ? (wwt < 500 ? 0 : 0.04) : 0.075)),
                "width": parseInt(wwt * (wwt < 900 ? (wwt < 500 ? 1 : 0.92) : 0.85)),
                "height": parseInt(wht * (wwt < 900 ? (wwt < 500 ? 1 : 0.95) : 0.9)),
                "z-index": 20,
                "transition-duration": control.speed * 1.3 + "s",
                "border-radius": 0,
                "min-width": 190,
                "max-width": wwt,
                "min-height": 30,
                "max-height": wht
            };
            $.extend(css, options.css);
            css = processaCss(css);
            var attr = {"data-drag": control.drag === !0 ? 1 : 0, "data-top": css.top};
            if (control.minimize) attr['data-minimize'] = 0;
            if (control.maximize) attr['data-maximize'] = 0;
            $.extend(attr, options.attr);
            attr.id = id;
            var header = {html: "", css: {padding: "5px 10px"}, class: ""};
            $.extend(header, options.header);
            header.css = processaCss(header.css);
            header.css.background = header.css.background || "#FFFFFF";
            var body = {html: '', ajax: {src: (typeof HOME !== 'undefined' ? HOME : "./") + "set"}, css: {padding: 0}};
            body = $.extend(!0, {}, body, options.body);
            var $ontab = $("<div />").addClass("ontab");
            if ($("#dashboard").length)
                $ontab.appendTo("#dashboard"); else if ($("#content").length)
                $ontab.appendTo("#content"); else $ontab.appendTo("body");
            var $header = $("<div />").addClass("ontab-header display-container").addClass(header.class).prependTo($ontab);
            var $title = $("<div />").addClass("ontab-title").html(header.html).addClass(header.class).css(header.css).prependTo($header);
            $('<span class="font-large upper font-bold ontab-feedback display-middle"></span>').prependTo($header);
            var headerHeight = parseInt($title.height());
            headerHeight = header.html !== "" && headerHeight < 30 ? 30 : headerHeight + parseInt($title.css("padding-top")) + parseInt($title.css("padding-bottom"));
            $header.css("background", $title.css("background")).css("height", headerHeight);
            var $body = $("<div />").addClass("ontab-content").css(body.css).css("margin-top", headerHeight).html(body.html).prependTo($ontab);
            if (css.width === "auto") {
                $ontab.css("width", "auto");
                css.width = parseInt($ontab.width()) + 20
            }
            if (css.width < 190) css.width = 190;
            if (css.height === "auto") {
                $ontab.css("height", "auto");
                css.height = parseInt($ontab.height())
            }
            if (control.resize) css.height += 14;
            if (typeof (css.right) !== "undefined") {
                css.left = parseInt($(window).width()) - css.right - css.width;
                delete css.right
            }
            if (!$.isEmptyObject(options.initial)) {
                options.initial = processaCss(options.initial);
                options.initial.width = css.width;
                options.initial.height = css.height;
                if (typeof (options.initial.right) !== "undefined") {
                    options.initial.left = parseInt($(window).width()) - css.width - options.initial.right;
                    delete options.initial.right
                }
            }
            var contOntab = $(".ontab").length;
            if (control.drag) {
                css.left += (contOntab * 10) - contOntab;
                css.top -= (contOntab * 3) - contOntab
            }
            $ontab.css(getInitialCss(options));
            attr['data-left'] = css.left;
            attr['data-width'] = css.width;
            attr['data-height'] = css.height;
            $ontab.attr(attr);
            setTimeout(function () {
                delete css['z-index'];
                $ontab.css(css)
            }, 10);
            if (typeof (body.ajax.param) !== 'undefined' || body.ajax.src !== (typeof (HOME) !== 'undefined' ? HOME : "./") + "set") {
                $ontab.loading();
                setTimeout(function () {
                    ajaxLoad(body.ajax.src, body.ajax.param, function (data) {
                        switch (data.response) {
                            case 1:
                                $body.html(data.data);
                                break;
                            case 2:
                                $body.panel(themeNotify(data.error, "warning", 3000));
                                break;
                            default:
                                $body.panel(themeNotify("Erro ao carregar", "error"))
                        }
                    })
                }, 450)
            }
            if (control.resize)
                $ontab.css("resize", "both");
            if (control.minimize) {
                var $mini = $("<div />").addClass("ontab-button btn-ontab-mini").attr("title", "minimizar").text("-").prependTo($header);
                $mini.on("click", function () {
                    if (control.onMinimize) {
                        if (!control.onMinimize()) {
                            panel.minimize($ontab)
                        }
                    } else {
                        panel.minimize($ontab)
                    }
                })
            }
            if (control.maximize) {
                var $maxi = $("<div />").addClass("ontab-button btn-ontab-maxi").attr("title", "maximizar").html("<div class='maxi'></div>").prependTo($header);
                $header.on('dblclick', function () {
                    panel.maximize($ontab)
                });
                $maxi.on("click", function () {
                    if (control.onMaximize) {
                        if (!control.onMaximize()) {
                            panel.maximize($ontab)
                        }
                    } else {
                        panel.maximize($ontab)
                    }
                })
            }
            if (control.close) {
                var $close = $("<div />").addClass("ontab-button btn-ontab-close").attr("title", "fechar").text("x").prependTo($header);
                $close.on("click", function () {
                    closePanel($ontab, id, control.blur, control.onClose)
                })
            }
            if (control.drag) {
                panel.drag($ontab, $header)
            }
            $ontab.off("mousedown").on("mousedown", function () {
                $(this).css("z-index", getLastIndex())
            });
            $ontab.css("z-index", getLastIndex())
            panelScrool(control.blur);
            if (control.clickOut) {
                $(document).on("mousedown", function (e) {
                    if (!$ontab.is(e.target) && $ontab.has(e.target).length === 0) {
                        if (!$ontab.attr("data-minimize") || $ontab.attr("data-minimize") === "0") {
                            if (control.clickOut === "minimize") {
                                panel.minimize($ontab)
                            } else {
                                closePanel($ontab, $body, control.blur, control.onClose)
                            }
                        }
                    }
                })
            }
            if (control.timeOut) {
                setTimeout(function () {
                    $ontab.css("transition-duration", (control.speed * 1.3) + "s");
                    if (control.timeOut.out === "left" || control.timeOut.out === "right") $ontab.css("left", (control.timeOut.out === "left" ? (parseInt($ontab.width()) * -1) : $(window).width())); else $ontab.css("top", (control.timeOut.out === "top" ? parseInt($ontab.height()) * -1 : $(window).height()));
                    setTimeout(function () {
                        closePanel($ontab, $body, control.blur, control.onClose)
                    }, control.speed * 1000)
                }, (typeof (control.timeOut.time) === "number" ? control.timeOut.time : 2500))
            }
            setTimeout(function () {
                $ontab.css("transition-duration", "0s")
            }, control.speed * 1000);
            $ontab.find(".ontab-content").css("height", parseInt($ontab.attr("data-height")) - parseInt($ontab.find(".ontab-header").height()) - 13)
        }

        return this.each(function () {
            _init();
            id
        })
    };
    $.fn.scrollBlock = function (enable) {
        if (typeof (enable) === "undefined" || enable) {
            window.oldScrollPos = $(window).scrollTop();
            $(window).on('scroll.scrolldisabler', function (event) {
                $(window).scrollTop(window.oldScrollPos);
                event.preventDefault()
            })
        } else {
            $(window).off('scroll.scrolldisabler')
        }
    }
    
    dashboardSidebarInfo();
    dashboardPanel();
    menuDashboard();
}(jQuery));