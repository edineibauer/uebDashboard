<?php

$id = (int)$variaveis[0];

if (is_numeric($id)) {
    $read = new \Conn\Read();
    $read->exeRead("notifications_report", "WHERE id = :id && (recebeu IS NULL || recebeu = 0)", "id={$id}");
    if ($read->getResult()) {
        $notificacao = $read->getResult()[0];

        $up = new \Conn\Update();
        $up->exeUpdate("notifications_report", ["recebeu" => 1], "WHERE id = :id", "id={$id}");

        $read->exeRead("enviar_mensagem", "WHERE id = :eid", "eid={$notificacao['enviar_mensagem_id']}");
        if($read->getResult()) {
            $m = $read->getResult()[0];
            $nTotal = (!empty($m['total_de_entrega']) && is_numeric($m['total_de_entrega']) && $m['total_de_entrega'] > 0 ? $m['total_de_entrega'] : 0) + 1;
            $up->exeUpdate("enviar_mensagem", ["total_de_entrega" => $nTotal, "taxa_de_entrega" => ((($nTotal * 100) / $m['total_de_envios'])*100)], "WHERE id = :eid", "eid={$notificacao['enviar_mensagem_id']}");
        }
    }
}