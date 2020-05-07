<?php

if(empty($dados['ownerpub'])) {
    $up = new \Conn\Update();
    $up->exeUpdate("notifications_report", ["ownerpub" => $dados['usuario']], "WHERE id = :id", "id={$dados['id']}");
}