<?php

use \Config\Config;

$data['data'] = [];
$read = new \Conn\Read();
$read->exeRead("relatorios_card");
if($read->getResult()) {

    $setor = !empty($_SESSION['userlogin']) ? $_SESSION['userlogin']['setor'] : "0";

    foreach ($read->getResult() as $item) {

        $usuarios = empty($item['usuarios']) ? [] : json_decode($item['usuarios'], !0);
        if (empty($usuarios) || (is_array($usuarios) && in_array($setor, $usuarios))) {

            $report = new \Report\Report($item, 1, $variaveis[0] ?? 0);
            $format = "";
            $valor = ($item['ordem'] === "total" ? $report->getTotal() : (!empty($report->getResult()) && !empty($report->getResult()[0][$item['ordem']]) ? $report->getResult()[0][$item['ordem']] : ""));

            $dic = \Entity\Metadados::getDicionario($item['entidade']);
            foreach($dic as $d) {
                if($d['column'] === $item['ordem']) {
                    if($d['key'] === "relation") {

                        /**
                         * Se for um campo relacional, então busca valor do campo relacional
                         */
                        $dic = new \Entity\Dicionario($d['relation']);
                        $relevant = $dic->getRelevant()->getColumn();

                        $result = [];
                        if($d['type'] === "json" && $d['group'] === "one"){
                            $result = json_decode($report->getResult()[0][$item['ordem']], !0);

                        } elseif ($d['type'] === "int"){
                            $read->exeRead($d['relation'], "WHERE id = :id", "id={$report->getResult()[0][$item['ordem']]}");
                            $result = ($read->getResult() ? $read->getResult()[0] : []);
                        }

                        if(!empty($result[$relevant]))
                            $valor = $result[$relevant];
                    }

                    /**
                     * Obtém o format do campo para aplicar mask no valor
                     */
                    $format = $d['format'];
                    break;
                }
            }
            $data['data'][] = ['id' => $item['id'], 'data' => $valor, 'format' => $format];
        }
    }
}