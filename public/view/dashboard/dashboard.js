function pageTransitionOnIframe(view, title) {
    $(".sidebar-wrapper > .nav > li").removeClass("active");
    $(".sidebar-wrapper > .nav > li[rel='" + view + "']").addClass("active");
    setTitleDashboard(title);
    iframeDashboard.pageTransition(view);
}

function setTitleDashboard(title) {
    if (typeof title === "undefined") {
        let b = iframeDashboard.app.title;
        let o = setTimeout(function () {
            clearInterval(t);
        }, 1000);
        let t = setInterval(function () {
            if (iframeDashboard.app.title !== b) {
                $("#navbar-brand-title").html(replaceAll(iframeDashboard.app.title, '_', ' '));
                clearInterval(t);
                clearTimeout(o);
            }
        }, 30);
    } else {
        $("#navbar-brand-title").html(replaceAll(title, '_', ' '));
    }
}

var iframeDashboard = null;
$(function () {
    iframeDashboard = $("iframe")[0].contentWindow;
});