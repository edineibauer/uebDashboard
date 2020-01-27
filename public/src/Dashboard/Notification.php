<?php

namespace Dashboard;

use Conn\Create;
use Conn\Read;

class Notification
{
    /**
     * Cria notificações para a Dashboard
     * @param string $titulo
     * @param string $descricao
     * @param int $usuario
     * @param int $copia
     */
    public static function create(string $titulo, string $descricao, int $usuario, int $copia = 0) {
        $create = new Create();
        $read = new Read();

        $notify = [
            "titulo" => $titulo,
            "descricao" => $descricao,
            "data" => date("Y-m-d H:i:s"),
            "status" => 1,
            "usuario" => $usuario
        ];

        $read->exeRead("notifications", "WHERE titulo = '{$titulo}' AND usuario = :a", "a={$usuario}");
        if(!$read->getResult()) {
            $create->exeCreate("notifications", $notify);
            if($copia !== 0) {
                $notify['titulo'] = "[CÓPIA] " . $notify['titulo'];
                $notify['usuario'] = $_SESSION['userlogin']['id'];
                $create->exeCreate("notifications", $notify);
            }
        }
    }
}