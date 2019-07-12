<?php

namespace Dashboard;

use Conn\Create;
use Conn\Read;

class Note
{
    /**
     * Cria notificações para a Dashboard
     * @param string $titulo
     * @param string $descricao
     * @param int $autor
     */
    public static function create(string $titulo, string $descricao, int $autor) {
        $create = new Create();
        $read = new Read();

        $notify = [
            "titulo" => $titulo,
            "descricao" => $descricao,
            "data" => date("Y-m-d H:i:s"),
            "status" => 1,
            "autor" => $autor
        ];

        $read->exeRead("dashboard_note", "WHERE titulo = '{$titulo}' AND autor = :a", "a={$autor}");
        if(!$read->getResult())
            $create->exeCreate("dashboard_note", $notify);
    }
}