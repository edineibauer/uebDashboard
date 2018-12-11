<?php
$tpl = new \Helpers\Template("dashboard");
$dados['dominio'] = VENDOR . "dashboard/";
$dados['version'] = VERSION;
$dados['dashboard'] = "";

if(!empty($_SESSION['userlogin']['setor'])) {

    //SETOR
    if (file_exists(PATH_HOME . "public/dash/{$_SESSION['userlogin']['setor']}/panel.php")) {
        ob_start();
        include_once PATH_HOME . "public/dash/{$_SESSION['userlogin']['setor']}/panel.php";
        $dados['dashboard'] = ob_get_contents();
        ob_end_clean();
    }

    //SETOR LIBS
    if(empty($dados['dashboard'])) {
        foreach (\Helpers\Helper::listFolder(PATH_HOME . VENDOR) as $lib) {
            if (file_exists(PATH_HOME . VENDOR . "{$lib}/public/dash/{$_SESSION['userlogin']['setor']}/panel.php")) {
                ob_start();
                include_once PATH_HOME . VENDOR . "{$lib}/public/dash/{$_SESSION['userlogin']['setor']}/panel.php";
                $dados['dashboard'] = ob_get_contents();
                ob_end_clean();
                break;
            }
        }

        //GENÉRICO
        if(empty($dados['dashboard'])) {
            if (file_exists(PATH_HOME . "public/dash/panel.php")) {
                ob_start();
                include_once PATH_HOME . "public/dash/panel.php";
                $dados['dashboard'] = ob_get_contents();
                ob_end_clean();
            }

            //GENÉRICO LIBS
            if(empty($dados['dashboard'])) {
                foreach (\Helpers\Helper::listFolder(PATH_HOME . VENDOR) as $lib) {
                    if (file_exists(PATH_HOME . VENDOR . "{$lib}/public/dash/panel.php")) {
                        ob_start();
                        include_once PATH_HOME . VENDOR . "{$lib}/public/dash/panel.php";
                        $dados['dashboard'] = ob_get_contents();
                        ob_end_clean();
                        break;
                    }
                }
            }
        }
    }

    //Notificações
    $read = new \ConnCrud\Read();
    $read->exeRead("dashboard_note", "WHERE autor = :a ORDER BY id LIMIT 16", "a={$_SESSION['userlogin']['id']}");
    $dados['note'] = $read->getResult() ?? [];
    if (!empty($dados['note'])) {
        $dataTime = new \Helpers\DateTime();
        foreach ($dados['note'] as $i => $item)
            $dados['note'][$i]['data'] = $dataTime->getDateTime($item['data'], 'd \d\e M \d\e Y');
    }
}

$data['data'] = $tpl->getShow('dashboard', $dados);