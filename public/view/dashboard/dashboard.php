<nav class="sidebar z-depth-2 collapse color-gray-light no-select dashboard-nav space-header"
     id="mySidebar">
    <div class="container row padding-4" style="background-color: #e9e9e9">
        <div id="dashboard-sidebar-imagem" class="col" style="height: 60px; width: 60px"></div>
        <div class="rest padding-left padding-bottom">
            <strong class="col padding-top no-select" id="dashboard-sidebar-nome"></strong>

            <div class="col">
                <span class="btn-edit-perfil left pointer padding-small color-gray-light opacity hover-opacity-off hover-shadow radius">
                    <i class="material-icons left font-large">edit</i>
                </span>
            </div>
        </div>
    </div>
    <hr style="margin:0 0 10px 0;border-top: solid 1px #ddd;">
    <div class="bar-block" id="dashboard-menu"></div>
</nav>

<div class="main dashboard-main animate-left">
    <div id="dashboard" class="dashboard-tab container row">
        <div class="col relative">
            <header class="container s-padding-small">
                <h5 class="left padding-32 s-padding-12 s-padding-tiny">
                    <b>
                        <i class="material-icons left padding-right">dashboard</i>
                        <span class="left">Meu Painel</span>
                    </b>
                </h5>
                <div class="right s-hide" style="padding-top: 30px;margin-right: calc(12% - 95px);">
                    <h2 class="col color-text-gray padding-0">
                        <i class="material-icons left padding-small">notification_important</i>
                        <div class="left font-large padding-small" >Suas notificações</div>
                    </h2>
                </div>
            </header>
            <div class="col s12 m9 padding-small dashboard-panel"></div>
            <div class="col s12 m3 padding-small dashboard-note"></div>
        </div>
    </div>
</div>

<?php
/*if (!defined("KEY") && !preg_match('/^http:\/\/(localhost|127.0.0.1)(\/|:)/i', HOME)) {
    */?><!--
    <div style="position:fixed; z-index: 99999999; bottom:10px;right: 20px;"
         class="padding-medium color-red opacity z-depth-2 radius">
        <i style="color:black">Segurança <b class="color-text-white">DESATIVADA! </b> Ative o software com
            <b>Urgência</b></i>
    </div>
    --><?php
/*}*/