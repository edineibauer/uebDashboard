<?php

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if($id && $id > 0) {
    $del = new \Conn\Delete();
    $del->exeDelete(PRE . "dashboard_note", "WHERE id=:id", "id={$id}");
}