<?php

use \Config\Config;

$data['data'] = [];
$read = new \Conn\Read();
$read->exeRead("relatorios_card");
if($read->getResult()) {

    $setor = !empty($_SESSION['userlogin']) ? $_SESSION['userlogin']['setor'] : "0";

    foreach ($read->getResult() as $item) {

        $usuarios = empty($item['usuarios']) ? [] : json_decode($item['usuarios'], !0);
        if (empty($usuarios) || (is_array($usuarios) && in_array($setor, $item['usuarios']))) {

            $report = new \Report\Report($item, 1, $link->getVariaveis()[0] ?? 0);
            $entidadeIcon = $item['entidade'];
            $dic = \Entity\Metadados::getDicionario($item['entidade']);
            $format = "";
            $valor = $report->getResult()[0][$item['ordem']];

            foreach($dic as $d) {
                if($d['column'] === $item['ordem']) {
                    if($d['key'] === "relation") {

                        /**
                         * Se for um campo relacional, então busca valor do campo relacional
                         */
                        $entidadeIcon = $d['relation'];
                        $dic = new \Entity\Dicionario($d['relation']);
                        $relevant = $dic->getRelevant()->getColumn();

                        $result = [];
                        if($d['type'] === "json" && $d['group'] === "one"){
                            $result = json_decode($report->getResult()[0][$item['ordem']], !0);

                        } elseif ($d['type'] === "int"){
                            $read->exeRead($d['relation'], "WHERE id = :id", "id={$report->getResult()[0][$item['ordem']]}");
                            $result = ($read->getResult() ? $read->getResult()[0] : []);
                        }

                        $valor = $result[$relevant];
                    }

                    /**
                     * Obtém o format do campo para aplicar mask no valor
                     */
                    $format = $d['format'];
                    break;
                }
            }

            /**
             * Icone
             */
            $info = \Entity\Metadados::getInfo($entidadeIcon);
            $icon = !empty($info['icon']) ? $info['icon'] : "show_chart";

            $data['data'][] = ['data' => $valor, 'titulo' => $item['nome'], 'format' => $format, "icon" => $icon];
        }
    }
}