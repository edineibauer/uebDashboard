<?php
if (empty($_SESSION['userlogin'])) {
    $data['response'] = 3;
    $data['data'] = HOME;
} else {
    ob_start();
    ?>
    <nav class="sidebar z-depth-2 collapse color-gray-light no-select animate-left dashboard-nav space-header"
         id="mySidebar">
        <div class="container row" style="background-color: #e9e9e9">
            <?php
            if (isset($_SESSION['userlogin']['imagem']) && !empty($_SESSION['userlogin']['imagem'])) {
                echo '<div class="col" style="height: 60px; width: 60px"><img src="' . HOME . 'image/' . str_replace(HOME, '', $_SESSION['userlogin']['imagem']) . '&w=100&h=100" width="72" height="72" style="margin-bottom:0!important; width: 72px;height: 72px" class="card margin-right"></div>';
            } else {
                echo '<div class="col" style="height: 60px; width: 60px"><i class="material-icons font-jumbo">people</i></div>';
            }
            ?>
            <div class="rest padding-left padding-bottom">
                <strong class="col padding-top no-select dashboard-nome"></strong>

                <div class="col">
                    <span class="left pointer menu-li padding-small color-gray-light opacity hover-opacity-off hover-shadow radius"
                          data-action="form" data-entity="usuarios"
                          data-atributo="<?= $_SESSION['userlogin']['id'] ?>">
                        <i class="material-icons left font-large">edit</i>
                    </span>
                </div>
            </div>

        </div>
        <hr style="margin:0 0 10px 0;border-top: solid 1px #ddd;">
        <div class="bar-block" id="dashboard-menu"></div>
    </nav>

    <div class="main dashboard-main">
        <div id="dashboard" class="dashboard-tab container row"></div>
    </div>

    <?php
    if (!defined("KEY") && !preg_match('/^http:\/\/(localhost|127.0.0.1)(\/|:)/i', HOME)) {
        ?>
        <div style="position:fixed; z-index: 99999999; bottom:10px;right: 20px;"
             class="padding-medium color-red opacity z-depth-2 radius">
            <i style="color:black">Segurança <b class="color-text-white">DESATIVADA! </b> Ative o software com <b>Urgência</b></i>
        </div>
        <?php
    }
    $data['data'] = ob_get_contents();
    ob_end_clean();
}