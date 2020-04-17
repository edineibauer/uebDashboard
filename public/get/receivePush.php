<?php

$id = (int)$link->getVariaveis()[0];

if (is_numeric($id)) {
    $read = new \Conn\Read();
    $read->exeRead("notifications_report", "WHERE id = :id", "id={$id}");
    if ($read->getResult()) {
        $notificacao = $read->getResult()[0];

        $up = new \Conn\Update();
        $up->exeUpdate("notifications_report", ["recebeu" => 1], "WHERE id = :id", "id={$id}");

        $sql = new \Conn\SqlCommand();
        $sql->exeCommand("UPDATE " . PRE . "enviar_mensagem SET total_de_entrega = total_de_entrega + 1, taxa_de_entrega = (((total_de_recebimentos+1)*100)/total_de_envios) WHERE id = '" . $notificacao['enviar_mensagem_id'] . "'");
    }
}