<?php

$id = (int) $link->getVariaveis()[0];

if (is_numeric($id)) {
    $up = new \Conn\Update();
    $up->exeUpdate("notifications_report", ["recebeu" => 1], "WHERE id = :id", "id={$id}");
}