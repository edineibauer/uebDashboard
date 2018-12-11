<?php
if (empty($_SESSION['userlogin'])) {
    $data['response'] = 3;
    $data['data'] = HOME . "login";
} else {
    ob_start();
    ?>
    <nav class="sidebar z-depth-2 collapse color-gray-light no-select animate-left dashboard-nav space-header" id="mySidebar"><br>
        <div class="container row">
            <?php
            if (isset($_SESSION['userlogin']['imagem']) && !empty($_SESSION['userlogin']['imagem'])) {
                echo '<div class="left"><img src="' . HOME . 'image/' . $_SESSION['userlogin']['imagem'] . '&w=100&h=100" width="72" height="72" style="margin-bottom:0!important; width: 72px;height: 72px" class="card margin-right"></div>';
            } else {
                echo '<div class="left"><i class="material-icons font-jumbo">people</i></div>';
            }
            ?>

            <div class="left">
                <div class="col div-btn-editLogin">
                    <button class="btn-editLogin left color-white z-depth-0 border hover-shadow radius padding-small color-grey-light margin-0">
                        <i class="material-icons prefix padding-0 font-large">edit</i>
                    </button>
                </div>
            </div>
            <strong class="col padding-top no-select"><?= $_SESSION['userlogin']['email'] ?></strong><br>

        </div>
        <hr style="margin: 10px 0 0;border-top: solid 1px #ddd;">
        <div class="bar-block">
            <?php
            require_once 'dashboardPages/menu.php';
            ?>
        </div>
    </nav>

    <div class="main dashboard-main">
        <div id="dashboard" class="dashboard-tab container row"></div>
    </div>

    <?php
    if(!defined("KEY") && !preg_match('/^http:\/\/(localhost|127.0.0.1)(\/|:)/i', HOME)) {
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