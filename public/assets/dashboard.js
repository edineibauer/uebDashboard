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
}(jQuery));

function getInitialCss(options) {
    return {
        "top": options.initial.top || getCenterTopTarget(options.target),
        "left": options.initial.left || getCenterLeftTarget(options.target),
        "width": options.initial.width || 0,
        "height": options.initial.height || 0
    }
}

function ajaxLoad(src, param, callback) {
    var request = $.ajax({type: "POST", url: src, async: !1, data: param, success: callback, dataType: 'json'});
    request.fail(function () {
        toast("Erro com o Destino", "erro", 2500, "left-top")
    })
}

function processaCss(style) {
    if (typeof (style.width) !== "undefined") {
        if (typeof (style.width) === "string") {
            if (style.width.match(/^\d{1,3}%$/g)) style.width = parseInt($(window).width()) * (parseInt(style.width) * 0.01); else if (style.width.match(/^\d{1,3}[a-z]{1,3}$/g)) style.width = parseInt(style.width)
        }
        style.width = (style.width > $(window).width() ? $(window).width() : style.width)
    }
    if (typeof (style.height) !== "undefined") {
        if (typeof (style.height) === "string" && style.height !== "auto") {
            if (style.height.match(/^\d{1,3}%$/g)) style.height = parseInt($(window).height()) * (parseInt(style.height) * 0.01); else if (style.height.match(/^\d{1,3}[a-z]{1,3}$/g)) style.height = parseInt(style.height)
        }
        style.height = (style.height > $(window).height() ? $(window).height() : style.height)
    }
    if (typeof (style.bottom) !== "undefined") {
        if (typeof (style.bottom) === "string" && !style.bottom.match(/^\d{1,3}[a-z]{1,3}$/g)) {
            if (style.bottom.match(/^\d{1,3}%$/g)) style.top = parseInt($(window).height()) - (parseInt($(window).height()) * (parseInt(style.bottom) * 0.01)); else if (style.bottom === "center") style.top = parseInt($(window).height()) * 0.5 - (typeof (style.height) === "number" ? style.height : 100) * 0.5 - 1; else if (style.bottom === "top") style.top = 0; else if (style.bottom === "bottom") style.top = parseInt($(window).height()) - (typeof (style.height) === "number" ? style.height : 100) - 1; else if (style.bottom === "near-top") style.top = 35; else if (style.bottom.match(/near/i)) style.top = parseInt($(window).height()) - (typeof (style.height) === "number" ? style.height : 100) - 15
        } else {
            style.top = parseInt($(window).height()) - parseInt(style.bottom)
        }
        delete style.bottom
    } else if (typeof (style.top) === "string") {
        if (style.top.match(/^\d{1,3}%$/g)) style.top = parseInt($(window).height()) * (parseInt(style.top) * 0.01); else if (style.top.match(/^\d{1,3}[a-z]{1,3}$/g)) style.top = parseInt(style.top); else if (style.top === "center") style.top = parseInt($(window).height()) * 0.5 - (typeof (style.height) === "number" ? style.height : 100) * 0.5 - 1; else if (style.top === "top") style.top = 0; else if (style.top === "bottom") style.top = parseInt($(window).height()) - (typeof (style.height) === "number" ? style.height : 100) - 1; else if (style.top === "near-bottom") style.top = parseInt($(window).height()) - (typeof (style.height) === "number" ? style.height : 100) - 15; else if (style.top.match(/near/i)) style.top = 35
    }
    if (typeof (style.right) !== "undefined" && typeof (style.right) === "string") {
        if (style.right === "left") {
            delete style.right;
            style.left = 0
        } else if (style.right === "near-left") {
            delete style.right;
            style.left = 30
        } else {
            delete style.left;
            if (style.right.match(/^\d{1,3}%$/g)) style.right = parseInt($(window).width()) * (parseInt(style.right) * 0.01); else if (style.right.match(/^\d{1,3}[a-z]{1,3}$/g)) style.right = parseInt(style.right); else if (style.right === "right") style.right = 0; else if (style.right.match(/near/i)) style.right = 30
        }
    } else if (typeof (style.left) === "string") {
        if (style.left === "right") {
            delete style.left;
            style.right = 0
        } else if (style.left === "near-right") {
            delete style.left;
            style.right = 30
        } else {
            if (style.left.match(/^\d{1,3}%$/g)) style.left = parseInt($(window).width()) * (parseInt(style.left) * 0.01); else if (style.left.match(/^\d{1,3}[a-z]{1,3}$/g)) style.left = parseInt(style.left); else if (style.left === "center") style.left = parseInt($(window).width()) * 0.5 - style.width * 0.5; else if (style.left === "left") style.left = 0; else if (style.left.match(/near/i)) style.left = 30
        }
    }
    return style
}

var timeout;

function blur() {
    timeout = setTimeout(function () {
        $("body").children("*:not(script, style, .ontab)").each(function () {
            $(this).addClass('ontab-blur')
        })
    }, 255)
}

function getCenterTopTarget($target) {
    return parseInt($target.offset().top - $(window).scrollTop()) + parseInt($target.height() * 0.5)
}

function getCenterLeftTarget($target) {
    return parseInt($target.offset().left) + parseInt($target.width() * 0.5)
}

function blurOut() {
    clearTimeout(timeout);
    $(".ontab-blur").removeClass('ontab-blur')
}

function closePanel($panel, retorno, blur, onClose) {
    if (onClose) {
        if (onClose(retorno)) {
            return !1
        }
    }
    if ($panel.attr("data-minimize") === "1") {
        $panel.attr("data-minimize", 0);
        reazusteMinimalize()
    }
    $panel.remove();
    setTimeout(function () {
        panelScrool(blur)
    }, 1)
}

function stop(event, M) {
    $(document).off('mousemove mouseup');
    return dragResizeModule(event, M)
}

function dragResizeAction($ontab, $resize, haveResize, onResize, onDrag) {
    $resize.on('mousedown', {e: $ontab}, function (v) {
        $ontab.css({"transition-duration": "0s", "z-index": getLastIndex($ontab)});
        var changeState = 0;
        var mii = {'x': event.pageX, 'y': event.pageY};
        var d = v.data, p = {};
        var windowsTab = d.e;
        if (windowsTab.css('position') !== 'relative') {
            try {
                windowsTab.position(p)
            } catch (e) {
            }
        }
        var M = {
            h: $resize,
            X: p.left || getInt(windowsTab, 'left') || 0,
            Y: p.top || getInt(windowsTab, 'top') || 0,
            W: getInt(windowsTab, 'width') || windowsTab[0].scrollWidth || 0,
            H: getInt(windowsTab, 'height') || windowsTab[0].scrollHeight || 0,
            pX: v.pageX,
            pY: v.pageY,
            o: windowsTab
        };
        $(document).mousemove(function (event) {
            if (changeState < 1) {
                if (event.pageX > mii.x + 12 || event.pageX < mii.x - 12 || event.pageY > mii.y + 12 || event.pageY < mii.y - 12) {
                    changeState = 1
                }
            }
            if ($ontab.attr("data-maximize") === "1" && changeState === 1) {
                $ontab.attr("data-maximize", 0).css({
                    'transition-duration': '0s',
                    'width': parseInt($ontab.attr("data-width")) + 'px',
                    'height': parseInt($ontab.attr("data-height")) + 'px'
                }).find(".ontab-content").css("height", parseInt($ontab.attr("data-height")) - (haveResize ? 45 : 30) + 'px')
            }
            var newPosition = dragResizeModule(event, M);
            windowsTab.css(newPosition)
        }).mouseup(function (event) {
            if (changeState === 1) {
                var newPosition = stop(event, M);
                windowsTab.css(newPosition);
                if (onDrag)
                    onDrag();
                changeState = 0
            } else {
                stop(event, M);
                if ($ontab.attr("data-maximize") === "1") {
                    $ontab.css({'top': -1 + 'px', 'left': 0})
                }
            }
        });
        return !1
    })
}

function minimizePanelOut($panel, speed, blur, resize, onResize, onDrag) {
    $panel.attr("data-minimize", 0).find(".ontab-header").off("click");
    if ($panel.attr("data-maximize") === "1") {
        maximizePanelIn($panel, resize)
    } else {
        $panel.css({
            "top": parseInt($panel.attr("data-top")) + "px",
            "left": parseInt($panel.attr("data-left")) + "px",
            "width": parseInt($panel.attr("data-width")) + "px",
            "height": parseInt($panel.attr("data-height")) + "px"
        });
        panelScrool(blur)
    }
    if ($panel.attr("data-drag") === "1") dragResizeAction($panel, $panel.find(".ontab-header"), 'd', resize, onResize, onDrag);
    reazusteMinimalize();
    setTimeout(function () {
        $panel.css("transition-duration", "0s")
    }, speed * 1000)
}

function minimizePanelIn($panel, speed, blur) {
    if ($panel.attr("data-maximize") === "0") storePosition($panel);
    var left = 0;
    $(".ontab").each(function () {
        if ($(this).attr("data-minimize") === "1") {
            left += parseInt($(this).css("min-width"))
        }
    });
    $panel.attr("data-minimize", 1).css({
        "transition-duration": speed + "s",
        "top": wht - 30 + "px",
        "left": left,
        "width": 0,
        "height": 0
    });
    panelScrool(blur)
}

function maximizePanelOut($panel, speed, haveResize, onResize, onDrag) {
    $panel.attr("data-maximize", 0).css({
        "transition-duration": speed + "s",
        'width': parseInt($panel.attr("data-width")) + 'px',
        'height': parseInt($panel.attr("data-height")) + 'px',
        'top': parseInt($panel.attr("data-top")) + 'px',
        'left': parseInt($panel.attr("data-left")) + 'px'
    }).find(".ontab-content").css("height", parseInt($panel.attr("data-height")) - (haveResize ? 45 : 30) + 'px');
    setTimeout(function () {
        $panel.css("transition-duration", "0s")
    }, speed * 1000)
}

function maximizePanelIn($panel, speed, haveResize) {
    if ($panel.attr("data-minimize") === "1") {
        storePosition($panel)
    }
    $panel.attr("data-maximize", 1).css({
        "transition-duration": speed + "s",
        'width': '100%',
        'height': '100%',
        'top': '-1px',
        'left': '0'
    }).find(".ontab-content").css("height", wht - (haveResize ? 45 : 30) + "px");
    if (haveResize) $panel.find(".ontab-resize").off("mousedown").css("cursor", "initial")
}

function dragResizeModule(v, M) {
    var left = M.X + v.pageX - M.pX;
    var top = M.Y + v.pageY - M.pY;
    left = left < 0 ? 0 : left;
    top = top < -1 ? -1 : top;
    if ((left !== 0 || top > -1) && M.o.attr("data-maximize") === '0') {
        M.o.attr("data-left", left);
        M.o.attr("data-top", top)
    }
    return {left: left, top: top}
}

function storePosition($panel) {
    $panel.attr({
        "data-width": 2 + parseInt($panel.width() + parseInt($panel.css("padding-left")) + parseInt($panel.css("padding-right"))),
        "data-height": 2 + parseInt($panel.height() + parseInt($panel.css("padding-top")) + parseInt($panel.css("padding-bottom"))),
        "data-top": $panel.offset().top - $(window).scrollTop(),
        "data-left": $panel.offset().left
    })
}

function panelScrool(isBlur) {
    var haveSomeOntabOpen = !1;
    if ($(".ontab").length) {
        $(".ontab").each(function () {
            if ($(this).attr("data-minimize") === "0") {
                haveSomeOntabOpen = !0
            }
        })
    }
    if (haveSomeOntabOpen) {
        $("html").scrollBlock();
        if (isBlur) blur()
    } else {
        $("html").scrollBlock(!1);
        if (isBlur) blurOut()
    }
}

function getLastIndex() {
    var zindex = 1000;
    $(".ontab").each(function () {
        zindex = ($(this).attr("data-minimize") === "0" && parseInt($(this).css("z-index")) >= zindex ? parseInt($(this).css("z-index")) + 1 : zindex)
    });
    return zindex
}

function reazusteMinimalize() {
    var i = 0;
    $(".ontab").each(function () {
        if ($(this).attr("data-minimize") === "1") {
            $(this).css("left", i * parseInt($(this).css("min-width")));
            i++
        }
    })
}

function getInt(E, k) {
    return parseInt(E.css(k)) || !1
}

function themes(theme) {
    if (theme.match(/erro/i))
        return {background: '#f44336', color: "#FFFFFF"}; else if (theme.match(/(warn|alert|attem|aten|aviso)/i))
        return {background: '#ff9800', color: "#FFFFFF"}; else return {background: '#8bc34a', color: "#FFFFFF"}
}

function themesIcon(theme) {
    if (theme.match(/(erro|dang)/i))
        return "error"; else if (theme.match(/(warn|alert|attem|aten|aviso)/i))
        return "warning"; else return "done"
}

function themeWindow(title, param, funcao) {
    return {
        header: {html: title},
        body: {ajax: {src: HOME + "set", param: param}, css: {padding: "0 15px"}},
        control: {onClose: funcao}
    }
}

function themeDashboard(title, param, funcao) {
    $("html").css("overflow-y", "hidden");
    let left = $("#mySidebar").length && $("#mySidebar").css("display") !== "none" ? $("#mySidebar").width() + 15 : 0;
    let top = 6;
    let height = $(window).height() - top;
    let width = $(window).width() - left - (left > 0 ? 10 : 0);
    return {
        header: {
            html: "<span class='left upper padding-medium'>" + title + "</span>",
            class: "theme-l1",
            css: {
                "max-height": "45px",
                "height": ($(".header").height() + parseInt($(".header").css("padding-top")) + parseInt($(".header").css("padding-bottom")) + 1 - top) + "px"
            }
        },
        body: {ajax: {src: HOME + "set", param: param}, css: {padding: "0 15px 30px"}},
        css: {"top": top, "left": left, "width": width, "height": height},
        control: {onClose: funcao, blur: !1, drag: !1, resize: !1, minimize: !1, maximize: !1}
    }
};

function hide_sidebar_small() {
    if (screen.width < 993) {
        $("#myOverlay, #mySidebar").css("display", "none")
    }
}

function mainLoading() {
    $(".main").loading();
    hide_sidebar_small();
    closeSidebar()
}

function menuDashboard() {
    let allow = dbLocal.exeRead("__allow", 1);
    let info = dbLocal.exeRead("__info", 1);
    let templates = dbLocal.exeRead("__template", 1);
    Promise.all([allow, info, templates]).then(r => {
        allow = r[0][getCookie('setor')];
        info = r[1];
        templates = r[2];
        let menu = [];
        let indice = 1;
        menu.push({
            icon: "timeline",
            title: "Dashboard",
            table: !1,
            link: !1,
            form: !1,
            page: !0,
            file: "panel",
            lib: "dashboard",
            indice: 0
        });
        dbLocal.exeRead("__menu", 1).then(m => {
            if (typeof m === "string") {
                $("#dashboard-menu").html(m);
            } else {
                if (m.constructor === Array && m.length) {
                    $.each(m, function (nome, dados) {
                        menu.push(dados)
                    });
                }

                $.each(dicionarios, function (entity, meta) {
                    if (typeof allow !== "undefined" && typeof allow[entity] !== "undefined" && typeof allow[entity].menu !== "undefined" && allow[entity].menu) {
                        nome = ucFirst(entity.replaceAll("_", " ").replaceAll("-", " "));
                        menu.push({
                            indice: indice,
                            icon: (info[entity].icon !== "" ? info[entity].icon : "storage"),
                            title: nome,
                            table: !0,
                            link: !1,
                            form: !1,
                            page: !1,
                            file: '',
                            lib: '',
                            entity: entity
                        });
                        indice++
                    }
                })

                menu.sort(dynamicSort('indice'));
                $("#dashboard-menu").html("");
                let tpl = (menu.length < 4 ? templates['menu-card'] : templates['menu-li']);
                $.each(menu, function (i, m) {
                    $("#dashboard-menu").append(Mustache.render(tpl, m))
                })
                if (getCookie("id") === "1") {
                    $("#dashboard-menu").append(Mustache.render(tpl, {
                        "icon": "settings_ethernet",
                        "title": "DEV",
                        "link": !0,
                        "table": !1,
                        "page": !1,
                        "form": !1,
                        "lib": "ui-dev",
                        "file": "UIDev",
                        "entity": "",
                        "indice": 100
                    }))
                }
            }
        })
    })
}

function dashboardSidebarInfo() {
    if (getCookie("imagem") === "") {
        document.querySelector("#dashboard-sidebar-imagem").innerHTML = "<i class='material-icons font-jumbo'>people</i>"
    } else {
        document.querySelector("#dashboard-sidebar-imagem").innerHTML = "<img src='" + decodeURIComponent(getCookie("imagem")) + "&h=80&w=80' height='60' width='60'>"
    }
    $("#dashboard-sidebar-nome").html(getCookie("nome"));
    let $sidebar = $("#core-sidebar-edit");
    $sidebar.removeClass("hide").off("click").on("click", function () {
        if (document.querySelector(".btn-edit-perfil") !== null) {
            document.querySelector(".btn-edit-perfil").click()
        } else {
            mainLoading();
            app.loadView(HOME + "dashboard");
            toast("carregando perfil...", 1300, "toast-success");
            let ee = setInterval(function () {
                if (document.querySelector(".btn-edit-perfil") !== null) {
                    setTimeout(function () {
                        document.querySelector(".btn-edit-perfil").click()
                    }, 1000);
                    clearInterval(ee)
                }
            }, 100)
        }
    })
}

function dashboardPanelContent() {
    let allow = dbLocal.exeRead("__allow", 1);
    let info = dbLocal.exeRead("__info", 1);
    let templates = dbLocal.exeRead("__template", 1);
    let panel = dbLocal.exeRead("__panel", 1);
    let syncCheck = [];
    $.each(dicionarios, function(entity, meta) {
        syncCheck.push(dbLocal.exeRead("sync_" + entity));
    });

    return Promise.all([allow, info, templates, panel].concat(syncCheck)).then(r => {
        allow = r[0][getCookie('setor')];
        info = r[1];
        templates = r[2];
        panel = r[3];
        let menu = [];
        let indice = 1;
        let content = "";
        if (typeof panel === "string" && panel !== "") {
            content = panel
        } else {
            if (panel.constructor === Array && panel.length) {
                $.each(panel, function (nome, dados) {
                    menu.push(dados)
                })
            }
            $.each(dicionarios, function (entity, meta) {
                if (typeof allow !== "undefined" && typeof allow[entity] !== "undefined" && typeof allow[entity].menu !== "undefined" && allow[entity].menu) {
                    nome = ucFirst(entity.replaceAll("_", " ").replaceAll("-", " "));
                    menu.push({
                        indice: indice,
                        icon: (info[entity].icon !== "" ? info[entity].icon : "storage"),
                        title: nome,
                        table: !0,
                        link: !1,
                        form: !1,
                        page: !1,
                        file: '',
                        lib: '',
                        entity: entity
                    });
                    indice++
                }
            });
            menu.sort(dynamicSort('indice'));
            $.each(menu, function (i, m) {
                content += Mustache.render(templates.card, m)
            })
        }

        for(let i = 4; i < 100; i++) {
            if(typeof r[i] !== "undefined" && r[i].length) {
                content += '<button class="col btn padding-large theme radius btn-panel-sync" onclick="syncDataBtn()">sincronizar</button>';
                i = 100;
            }
        }

        return content
    })
}

function dashboardPanel() {
    document.querySelector(".panel-name").innerHTML = getCookie("nome");
    dashboardPanelContent().then(content => {
        $(".dashboard-panel").html(content)
    })
}

$(function () {
    dashboardSidebarInfo();
    dashboardPanel();
    menuDashboard();
    $("body").off("click", ".menu-li").on("click", ".menu-li", function () {
        let action = $(this).attr("data-action");
        mainLoading();
        if (action === "table") {
            $("#dashboard").html("").grid($(this).attr("data-entity"))
        } else if (action === 'form') {
            let id = !isNaN($(this).attr("data-atributo")) && $(this).attr("data-atributo") > 0 ? parseInt($(this).attr("data-atributo")) : null;
            $("#dashboard").html("").form($(this).attr("data-entity"), id, typeof $(this).attr("data-fields") !== "undefined" ? JSON.parse($(this).attr("data-fields")) : "undefined")
        } else if (action === 'page') {
            let viewPage = $(this).attr("data-atributo");
            view(viewPage, function (data) {
                if (typeof (data.content) === "string") {
                    if(data.content === "no-network") {
                        $("#dashboard").html("Ops! Conexão Perdida");
                    } else {
                        $("#dashboard").html(data.content);
                        if (data.js.length)
                            $.cachedScript(data.js);
                        if(data.css.length)
                            $("#core-style").prepend(data.css);
                    }
                    if (viewPage === "panel")
                        dashboardPanel()
                }
            })
        }
    });
    $("#core-content, #core-applications").off("click", ".close-dashboard-note").on("click", ".close-dashboard-note", function () {
        let $this = $(this);
        post('dashboard', 'dash/delete', {id: $this.attr("id")}, function (data) {
            $this.closest("article").parent().remove()
        })
    })
})