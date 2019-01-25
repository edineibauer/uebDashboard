//carrega menu
dbLocal.exeRead("__menu", 1).then(menuLoad => {
    if(isEmpty(menuLoad)) {
        get("menu").then(m => {
            dbLocal.clear("__menu").then(() => {
                dbLocal.exeCreate("__menu", m).then(() => {
                    menuDashboard();
                });
            })
        });
    }
});

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

function menuDashboard() {
    let allow = dbLocal.exeRead("__allow", 1);
    let dicionarios = dbLocal.exeRead("__dicionario", 1);
    let info = dbLocal.exeRead("__info", 1);
    let templates = dbLocal.exeRead("__template", 1);

    Promise.all([allow, dicionarios, info, templates]).then(r => {
        allow = r[0][getCookie('setor')];
        dicionarios = r[1];
        info = r[2];
        templates = r[3];
        let menu = [];
        let indice = 1;
        menu.push({icon: "timeline", title: "Dashboard", table: false, link: false, form: false, page: true, file: "dashboardPages/panel", lib: "dashboard", indice: 0});

        dbLocal.exeRead("__menu", 1).then(m => {
            $.each(m, function (nome, dados) {
                menu.push(dados);
            });

            //dicionarios
            $.each(dicionarios, function (entity, meta) {
                if(typeof allow !== "undefined" && typeof allow[entity] !== "undefined" && typeof allow[entity]['menu'] !== "undefined" && allow[entity]['menu']) {
                    nome = ucFirst(entity.replaceAll("_", " ").replaceAll("-", " "));
                    menu.push({indice: indice, icon: (info[entity]['icon'] !== "" ? info[entity]['icon'] : "storage"), title: nome, table: true, link: false, form: false, page: false, file: '', lib: '',  entity: entity});
                    indice++;
                }
            });
        }).then(() => {

            menu.sort(dynamicSort('indice'));

            //mostra o menu
            $("#dashboard-menu").html("");
            $.each(menu, function (i, m) {
                $("#dashboard-menu").append(Mustache.render(templates['menu-li'], m));
            })
        })
    });
}

$(function () {
    $(".dashboard-nome").html(getCookie("nome"));
    menuDashboard();


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