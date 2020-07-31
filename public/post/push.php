<?php

use Conn\Create;
use Conn\Delete;
use Conn\Read;

if(!empty($_SESSION['userlogin'])) {
    $d = ["usuario" => $_SESSION['userlogin']['id']];
    $d['subscription'] = filter_input(INPUT_POST, 'push', FILTER_DEFAULT);
    $d['code'] = filter_input(INPUT_POST, 'p1', FILTER_DEFAULT);
    $d['code'] .= filter_input(INPUT_POST, 'p2', FILTER_DEFAULT);
    $d['code'] .= filter_input(INPUT_POST, 'p3', FILTER_DEFAULT);

    $read = new Read();
    $read->exeRead("push_notifications", "WHERE usuario = :a AND code = :c", "a={$d['usuario']}&c={$d['code']}");
    if ($read->getResult()) {
        $del = new Delete();
        $del->exeDelete("push_notifications", "WHERE usuario = :a AND code = :c", "a={$d['usuario']}&c={$d['code']}");
    }

    $create = new Create();
    $create->exeCreate("push_notifications", $d);
}