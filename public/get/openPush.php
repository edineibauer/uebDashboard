<?php

$id = (int)$variaveis[0];

if (is_numeric($id)) {
    $read = new \Conn\Read();
    $read->exeRead("notifications_report", "WHERE id = :id && (abriu IS NULL || abriu = 0)", "id={$id}");
    if ($read->getResult()) {
        $notificacao = $read->getResult()[0];

        $up = new \Conn\Update();
        $up->exeUpdate("notifications_report", ["abriu" => 1], "WHERE id = :id", "id={$id}");

        $read->exeRead("enviar_mensagem", "WHERE id = :eid", "eid={$notificacao['enviar_mensagem_id']}");
        if($read->getResult()) {
            $m = $read->getResult()[0];
            $nTotal = (!empty($m['total_de_conversao']) && is_numeric($m['total_de_conversao']) && $m['total_de_conversao'] > 0 ? $m['total_de_conversao'] : 0) + 1;
            $up->exeUpdate("enviar_mensagem", ["total_de_conversao" => $nTotal, "taxa_de_conversao" => ((($nTotal * 100) / $m['total_de_entrega'])*100)], "WHERE id = :eid", "eid={$notificacao['enviar_mensagem_id']}");
        }
    }
}