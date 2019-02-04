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
            $.each(m, function (nome, dados) {
                menu.push(dados)
            });
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
        }).then(() => {
            menu.sort(dynamicSort('indice'));
            $("#dashboard-menu").html("");
            let tpl = (menu.length < 5 ? templates['menu-card'] : templates['menu-li']);
            $.each(menu, function (i, m) {
                $("#dashboard-menu").append(Mustache.render(tpl, m))
            })
            if (getCookie("setor") === "1" && getCookie("nivel") === "1") {
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
    });
}

function dashboardPanelContent() {
    let allow = dbLocal.exeRead("__allow", 1);
    let dicionarios = dbLocal.exeRead("__dicionario", 1);
    let info = dbLocal.exeRead("__info", 1);
    let templates = dbLocal.exeRead("__template", 1);
    return Promise.all([allow, dicionarios, info, templates]).then(r => {
        allow = r[0][getCookie('setor')];
        dicionarios = r[1];
        info = r[2];
        templates = r[3];
        let menu = [];
        let indice = 1;
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

        let content = "";
        $.each(menu, function (i, m) {
            content += Mustache.render(templates.card, m);
        });

        return content;
    })
}
function dashboardPanel() {
    document.querySelector(".panel-name").innerHTML = getCookie("nome");
    dashboardPanelContent().then(content => {
        $(".dashboard-panel").html(content);
    });
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
                    $("#dashboard").html(data.content === "no-network" ? "Ops! Conexão Perdida" : data.content);
                    if(viewPage === "panel")
                        dashboardPanel();
                }
            })
        }
    });
    $("#core-content, #core-applications").off("click", ".close-dashboard-note").on("click", ".close-dashboard-note", function () {
        let $this = $(this);
        post('dashboard', 'dash/delete', {id: $this.attr("id")}, function (data) {
            $this.closest("article").parent().remove()
        })
    });
})