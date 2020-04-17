<?php

$id = (int)$link->getVariaveis()[0];

if (is_numeric($id)) {
    $read = new \Conn\Read();
    $read->exeRead("notifications_report", "WHERE id = :id && abriu != 1", "id={$id}");
    if ($read->getResult()) {
        $notificacao = $read->getResult()[0];

        $up = new \Conn\Update();
        $up->exeUpdate("notifications_report", ["abriu" => 1], "WHERE id = :id", "id={$id}");

        $read->exeRead("enviar_mensagem", "WHERE id = :eid", "eid={$notificacao['enviar_mensagem_id']}");
        if($read->getResult()) {
            $m = $read->getResult()[0];
            $nTotal = $m['total_de_conversao'] + 1;
            $up->exeUpdate("enviar_mensagem", ["total_de_conversao" => $nTotal, "taxa_de_conversao" => ((($nTotal * 100) / $m['total_de_entrega'])*100)], "WHERE id = :eid", "eid={$notificacao['enviar_mensagem_id']}");
        }
    }
}