<?php

/**
 * @param string $dir
 * @return bool
 */
function checkFolder(string $dir): bool
{
    if (file_exists($dir)) {
        require_once $dir;
        return true;
    }
    return false;
}

$inc = false;

//Dashboard Personalizado
$inc = checkFolder(PATH_HOME . "public/dash/{$_SESSION['userlogin']['setor']}/panel.php");
if (!$inc) {
    foreach (\Helpers\Helper::listFolder(PATH_HOME . VENDOR) as $lib) {
        if (!$inc)
            $inc = checkFolder(PATH_HOME . VENDOR . "{$lib}/public/dash/{$_SESSION['userlogin']['setor']}/panel.php");
    }

    if (!$inc) {
        //Dashboard Personalizado Genérico
        $inc = checkFolder(PATH_HOME . "public/dash/panel.php");
        if (!$inc) {
            foreach (\Helpers\Helper::listFolder(PATH_HOME . VENDOR) as $lib) {
                if (!$inc)
                    $inc = checkFolder(PATH_HOME . VENDOR . "{$lib}/public/dash/panel.php");
            }

            //Nenhum panel.php encontrado
            if (!$inc) {

                /**
                 * @param string $incMenu
                 * @param array $lista
                 * @return array
                 */
                function addMenuJson(string $incMenu, array $lista): array
                {
                    $incMenu = json_decode(file_get_contents($incMenu), true);
                    if (!empty($incMenu)) {
                        foreach ($incMenu as $menu) {
                            $lista[Check::name(trim(strip_tags($menu['title'])))] = [
                                'lib' => Check::words(trim(strip_tags($menu['lib'])), 1),
                                'file' => Check::words(trim(strip_tags($menu['file'])), 1),
                                "table" => $menu['table'] ?? true,
                                "form" => $menu['form'] ?? false,
                                "page" => $menu['page'] ?? false,
                                "link" => $menu['link'] ?? false,
                                'title' => ucwords(Check::words(trim(strip_tags($menu['title'])), 3)),
                                'icon' => Check::words(trim(strip_tags($menu['icon'])), 1),
                                "indice" => $menu['indice'] ?? (count($lista) + 1)
                            ];
                        }
                    }

                    return $lista;
                }

                $lista = [];
                if (file_exists(PATH_HOME . "public/dash/panel.json"))
                    $lista = addMenuJson(PATH_HOME . "public/dash/panel.json", $lista);

                if (file_exists(PATH_HOME . "public/dash/{$_SESSION['userlogin']['setor']}/panel.json"))
                    $lista = addMenuJson(PATH_HOME . "public/dash/{$_SESSION['userlogin']['setor']}/panel.json", $lista);

                foreach (Helper::listFolder(PATH_HOME . VENDOR) as $lib) {
                    if (file_exists(PATH_HOME . VENDOR . "{$lib}/public/dash/panel.json"))
                        $lista = addMenuJson(PATH_HOME . VENDOR . "{$lib}/public/dash/panel.json", $lista);
                    if (file_exists(PATH_HOME . VENDOR . "{$lib}/public/dash/{$_SESSION['userlogin']['setor']}/panel.json"))
                        $lista = addMenuJson(PATH_HOME . VENDOR . "{$lib}/public/dash/{$_SESSION['userlogin']['setor']}/panel.json", $lista);
                }
            }
        }
    }
}