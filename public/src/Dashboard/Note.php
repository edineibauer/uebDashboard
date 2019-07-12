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
     * @param int $copia
     */
    public static function create(string $titulo, string $descricao, int $autor, int $copia = 0) {
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
        if(!$read->getResult()) {
            $create->exeCreate("dashboard_note", $notify);
            if($copia !== 0) {
                $notify['titulo'] = "[CÓPIA] " . $notify['titulo'];
                $notify['autor'] = $_SESSION['userlogin']['id'];
                $create->exeCreate("dashboard_note", $notify);
            }
        }
    }
}