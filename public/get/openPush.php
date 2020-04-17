<?php

$id = (int)$link->getVariaveis()[0];

if (is_numeric($id)) {
    $read = new \Conn\Read();
    $read->exeRead("notifications_report", "WHERE id = :id", "id={$id}");
    if ($read->getResult()) {
        $notificacao = $read->getResult()[0];

        $up = new \Conn\Update();
        $up->exeUpdate("notifications_report", ["abriu" => 1], "WHERE id = :id", "id={$id}");

        $sql = new \Conn\SqlCommand();
        $sql->exeCommand("UPDATE " . PRE . "enviar_mensagem SET total_de_conversao = total_de_conversao + 1 WHERE id = '" . $notificacao['enviar_mensagem_id'] . "'");
    }
}