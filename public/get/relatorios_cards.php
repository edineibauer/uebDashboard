<?php

use \Config\Config;

$data['data'] = [];
$read = new \Conn\Read();
$read->exeRead("relatorios_card");
if($read->getResult()) {

    $setor = !empty($_SESSION['userlogin']) ? $_SESSION['userlogin']['setor'] : "0";
    $permissoes = Config::getPermission();

    foreach ($read->getResult() as $item) {

        $entityIsMySetor = ($setor !== "admin" && (isset($permissoes[$setor][$item['entidade']]['read']) && !$permissoes[$setor][$item['entidade']]['read']) && $setor !== "0" && $item['entidade'] === $setor);
        if ($setor === "admin" || (isset($permissoes[$setor][$item['entidade']]['read']) || $permissoes[$setor][$item['entidade']]['read']) || $entityIsMySetor) {

            $report = new \Report\Report($item, 1, $link->getVariaveis()[0] ?? 0);
            $data['data'][] = ['data' => $report->getResult()[0][$item['ordem']], 'titulo' => $item['nome']];
        }
    }
}