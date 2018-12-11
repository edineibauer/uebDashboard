function hide_sidebar_small() {
    if (screen.width < 993) {
        $("#myOverlay, #mySidebar").css("display", "none")
    }
}

function mainLoading() {
    $(".main").loading();
    hide_sidebar_small();
    closeSidebar();
}

function requestDashboardContent(file) {
    mainLoading();
    get(file, function (data) {
        setDashboardContent(data.content)
    })
}

function requestDashboardEntity(entity) {
    mainLoading();
    post("table", "api", {entity: entity}, function (data) {
        setDashboardContent(data)
    })
}

function setDashboardContent(content) {
    if (typeof(content) === "string")
        $("#dashboard").html(content)
}

$(function () {
    $("#core-content, #core-applications").off("click", ".menu-li").on("click", ".menu-li", function () {
        let action = $(this).attr("data-action");
        if (action === "table") {
            requestDashboardEntity($(this).attr("data-entity"))
        } else if (action === 'form') {
            post("form-crud", "api", {entity: attr}, function (data) {
                setDashboardContent(data)
            })
        } else if (action === 'page') {
            requestDashboardContent($(this).attr("data-atributo"))
        } else if (action === 'link') {
        }
    }).off("click", ".close-dashboard-note").on("click", ".close-dashboard-note", function () {
        let $this = $(this);
        post('dashboard', 'dash/delete', {id: $this.attr("id")}, function (data) {
            $this.closest("article").parent().remove()
        })
    });
    setTimeout(function () {
        get("dashboardPages/panel", function (data) {
            $("#dashboard").html(data.content);
            spaceHeader()
        })
    }, 300)
})