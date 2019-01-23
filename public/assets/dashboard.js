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
    view(file, function (data) {
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
    if (typeof (content) === "string")
        $("#dashboard").html(content === "no-network" ? "Ops! Conexão Perdida" : content)
}

$(function () {
    $("body").off("click", ".btn-editLogin").on("click", ".btn-editLogin", function () {
        closeSidebar();
        $(this).panel(themeDashboard("Editar Perfil", {lib: 'dashboard', file: 'edit/perfil'}, function (idOntab) {
            data = formGetData($("#" + idOntab).find(".ontab-content").find(".form-crud"));
            post('dashboard', 'edit/session', {dados: data}, function () {
                location.reload()
            })
        }))
    })
    $("#core-content, #core-applications").off("click", ".menu-li").on("click", ".menu-li", function () {
        let action = $(this).attr("data-action");

        if (action === "table") {
            $("#dashboard").html("").grid($(this).attr("data-entity"));
        } else if (action === 'form') {
            $("#dashboard").html("").form($(this).attr("data-entity"));
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
        view("dashboardPages/panel", function (data) {
            $("#dashboard").html(data.content);
            spaceHeader()
        })
    }, 300)
})