<?php

$data['data'] = [];
$read = new \Conn\Read();
$read->exeRead("relatorios_card");
if($read->getResult()) {

    $setor = !empty($_SESSION['userlogin']) ? $_SESSION['userlogin']['setor'] : "0";

    foreach ($read->getResult() as $item) {

        $usuarios = empty($item['usuarios']) ? [] : json_decode($item['usuarios'], !0);
        if (empty($usuarios) || (is_array($usuarios) && in_array($setor, $usuarios))) {

            $report = new \Report\Report($item, 1, $variaveis[0] ?? 0);
            $entidadeIcon = $item['entidade'];
            $dic = \Entity\Metadados::getDicionario($item['entidade']);
            $format = "";

            $valor = ($item['ordem'] === "total" ? $report->getTotal() : (!empty($report->getResult()) && !empty($report->getResult()[0][$item['ordem']]) ? $report->getResult()[0][$item['ordem']] : ""));

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

            if(is_numeric($valor) && !is_int($valor))
                $valor = number_format($valor, 2,',', '.');

            $data['data'][] = ['id' => $item['id'], 'style' => $item['classes'], 'data' => $valor, 'titulo' => $item['nome'], 'format' => $format, "icon" => $item['icone'], "cor_de_fundo" => $item['cor_de_fundo'], "cor_do_texto" => $item['cor_do_texto']];
        }
    }
}