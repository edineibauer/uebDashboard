function hide_sidebar_small() {
    if (screen.width < 993) {
        $("#myOverlay, #mySidebar").css("display", "none")
    }
}

function mainLoading() {
    hide_sidebar_small();
    closeSidebar()
}

function menuDashboard(count) {
    count = typeof count === "undefined" ? 0 : count;
    if (isEmpty(dicionarios)) {
        if (count > 5)
            return;

        setTimeout(function () {
            menuDashboard(count + 1);
        }, 500);
    } else {
        let allow = dbLocal.exeRead("__allow", 1);
        let info = dbLocal.exeRead("__info", 1);
        let templates = dbLocal.exeRead("__template", 1);
        Promise.all([allow, info, templates]).then(r => {
            allow = r[0];
            info = r[1];
            templates = r[2];
            let menu = [];
            let indice = 1;

            $.each(dicionarios, function (entity, meta) {
                if (typeof allow !== "undefined" && typeof allow[entity] !== "undefined" && typeof allow[entity].menu !== "undefined" && allow[entity].menu) {
                    nome = ucFirst(replaceAll(replaceAll(entity, "_", " "), "-", " "));
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
            $("#dashboard-menu").html("");
            let tpl = (menu.length < 4 ? templates['menu-card'] : templates['menu-li']);
            $.each(menu, function (i, m) {
                $("#dashboard-menu").append(Mustache.render(tpl, m))
            });
        })
    }
}

function dashboardSidebarInfo() {
    if (isEmpty(USER.imagem) || USER.imagem === "null") {
        $("#dashboard-sidebar-imagem").html("<i class='material-icons font-jumbo'>people</i>");
    } else {
        let imagem = "";
        if(typeof USER.imagem === "string") {
            if(isJson(USER.imagem))
                imagem = decodeURIComponent(JSON.parse(USER.imagem)[0]['urls'][100]);
            else
                imagem = USER.imagem;
        } else if(typeof USER.imagem === "object" && typeof USER.imagem.url === "string") {
            imagem = USER.imagem.urls[100];
        }
        $("#dashboard-sidebar-imagem").html("<img src='" + imagem + "' title='" + USER.nome + "' alt='imagem do usuário " + USER.nome + "' width='60' height='60' style='width: 60px;height: 60px' />");
    }

    $("#dashboard-sidebar-nome").html(USER.nome);
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

function closeNote(id) {
    $(".dashboard-note[rel='" + id + "']").remove();
    db.exeDelete("notifications", parseInt(id));
}

function dashboardPanelContent() {
    return dbLocal.exeRead('__dicionario', 1).then(d => {

        let syncCheck = [];
        syncCheck.push(dbLocal.exeRead("__allow", 1));
        syncCheck.push(dbLocal.exeRead("__info", 1));
        syncCheck.push(dbLocal.exeRead("__template", 1));
        syncCheck.push(dbLocal.exeRead("__panel", 1));

        return Promise.all(syncCheck).then(r => {
            allow = r[0];
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
                $.each(d, function (entity, meta) {
                    if (typeof allow !== "undefined" && typeof allow[entity] !== "undefined" && typeof allow[entity].menu !== "undefined" && allow[entity].menu) {
                        nome = ucFirst(replaceAll(replaceAll(entity, "_", " "), "-", " "));
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

            return content
        })
    });
}

function dashboardPanel() {
    if($(".panel-name").length)
        $(".panel-name").html(USER.nome);

    dashboardPanelContent().then(content => {
        $(".dashboard-panel").html(content)
    });

    dbLocal.exeRead("notifications").then(n => {
        if(!isEmpty(n)) {
            getTemplates().then(templates => {
                let note = "";
                $.each(n, function (i, e) {
                    if (parseInt(e.usuario) === parseInt(USER.id))
                        note += Mustache.render(templates.note, e)
                });

                $(".dashboard-note").html(note)
            });
        }
    });
}

$(function () {
    dashboardSidebarInfo();
    dashboardPanel();
    menuDashboard();
    $("body").off("click", ".menu-li:not(.not-menu-li)").on("click", ".menu-li:not(.not-menu-li)", function () {
        let action = $(this).attr("data-action");

        clearHeaderScrollPosition();
        checkUpdate();
        mainLoading();

        if (action === "table") {
            pageTransition($(this).attr("data-entity"), 'grid', 'forward', "#dashboard");

        } else if (action === 'form') {
            // let fields = (typeof $(this).attr("data-fields") !== "undefined" ? JSON.parse($(this).attr("data-fields")) : "undefined");
            let id = !isNaN($(this).attr("data-atributo")) && $(this).attr("data-atributo") > 0 ? parseInt($(this).attr("data-atributo")) : null;
            pageTransition($(this).attr("data-entity"), 'form', 'forward', "#dashboard", id);

        } else if (action === 'page') {

            pageTransition($(this).attr("data-atributo"), 'route', 'forward', "#core-content");
        }
    }).off("click", ".btn-edit-perfil").on("click", ".btn-edit-perfil", function () {
        if(history.state.route !== "usuarios" || history.state.type !== "form") {
            let entity = USER.setorData === "" ? "usuarios" : USER.setorData;
            pageTransition(entity, 'form', 'forward', "#dashboard", USER);
        }
    });

    $("#app, #core-applications").off("click", ".close-dashboard-note").on("click", ".close-dashboard-note", function () {
        let $this = $(this);
        post('dashboard', 'dash/delete', {id: $this.attr("id")}, function (data) {
            $this.closest("article").parent().remove()
        })
    })
})