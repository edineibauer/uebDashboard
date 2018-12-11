<?php
$config = json_decode(file_get_contents(PATH_HOME . "_config/config.json"), true);

foreach ($dados as $column => $value) {
    $column = strtolower(trim(strip_tags($column)));
    $column = str_replace(['nome_do_site', 'subtitulo', 'descricao', 'https'], ['sitename', 'sitesub', 'sitedesc', 'ssl'], $column);
    $value = trim(strip_tags($value));
    if (isset($config[$column])) {
        if($column === "favicon" || $column === "logo") {
            $config[$column] = json_decode($value, true)[0]['url'];
        } else {
            $config[$column] = $value;
        }
    }
}

Config\Config::createConfig($config);

if ((!empty($config['ssl']) && $config['ssl'] !== SSL) || (!empty($config['www']) && $config['www'] !== WWW)) {
    new \Dashboard\UpdateDashboard(['manifest', 'assets', 'lib']);
} elseif ((!empty($config['sitename']) && $config['sitename'] !== SITENAME) || (!empty($config['favicon']) && $config['favicon'] !== FAVICON)) {
    new \Dashboard\UpdateDashboard(['manifest']);
}