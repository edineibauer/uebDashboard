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
            table: false,
            link: false,
            form: false,
            page: true,
            file: "dashboardPages/panel",
            lib: "dashboard",
            indice: 0
        });

        dbLocal.exeRead("__menu", 1).then(m => {
            $.each(m, function (nome, dados) {
                menu.push(dados);
            });

            //dicionarios
            $.each(dicionarios, function (entity, meta) {
                if (typeof allow !== "undefined" && typeof allow[entity] !== "undefined" && typeof allow[entity]['menu'] !== "undefined" && allow[entity]['menu']) {
                    nome = ucFirst(entity.replaceAll("_", " ").replaceAll("-", " "));
                    menu.push({
                        indice: indice,
                        icon: (info[entity]['icon'] !== "" ? info[entity]['icon'] : "storage"),
                        title: nome,
                        table: true,
                        link: false,
                        form: false,
                        page: false,
                        file: '',
                        lib: '',
                        entity: entity
                    });
                    indice++;
                }
            });
        }).then(() => {

            menu.sort(dynamicSort('indice'));

            //mostra o menu
            $("#dashboard-menu").html("");
            let tpl = (menu.length < 5 ? templates['menu-card'] : templates['menu-li']);
            $.each(menu, function (i, m) {
                $("#dashboard-menu").append(Mustache.render(tpl, m));
            })
            if (getCookie("setor") === "1" && getCookie("nivel") === "1") {
                $("#dashboard-menu").append(Mustache.render(tpl, {
                    "icon": "settings_ethernet",
                    "title": "DEV",
                    "link": true,
                    "table": false,
                    "page": false,
                    "form": false,
                    "lib": "ui-dev",
                    "file": "UIDev",
                    "entity": "",
                    "indice": 100
                }));
            }
        })
    });
}

$(function () {
    $(".dashboard-nome").html(getCookie("nome"));
    menuDashboard();

    $("#core-content, #core-applications").off("click", ".close-dashboard-note").on("click", ".close-dashboard-note", function () {
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