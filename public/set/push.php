<?php

use Conn\Create;
use Conn\Delete;
use Conn\Read;

$d = ["autor" => $_SESSION['userlogin']['id']];
$d['subscription'] = filter_input(INPUT_POST, 'push', FILTER_DEFAULT);
$d['code'] = filter_input(INPUT_POST, 'p1', FILTER_DEFAULT);
$d['code'] .= filter_input(INPUT_POST, 'p2', FILTER_DEFAULT);
$d['code'] .= filter_input(INPUT_POST, 'p3', FILTER_DEFAULT);

$read = new Read();
$read->exeRead("dashboard_push", "WHERE autor = :a AND code = :c", "a={$d['autor']}&c={$d['code']}");
if($read->getResult()) {
    $del = new Delete();
    $del->exeDelete("dashboard_push", "WHERE autor = :a AND code = :c", "a={$d['autor']}&c={$d['code']}");
}

$create = new Create();
$create->exeCreate("dashboard_push", $d);
