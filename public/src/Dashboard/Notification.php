<?php

namespace Dashboard;

use Conn\Create;
use Conn\Read;

class Notification
{
    private $titulo = "";
    private $descricao = "";
    private $url = HOME;
    private $imagem = HOME . "assetsPublic/img/favicon.png?v=" . VERSION;
    private $usuarios = 0;

    /**
     * @param string $titulo
     */
    public function setTitulo(string $titulo)
    {
        $this->titulo = $titulo;
    }

    /**
     * @param string $descricao
     */
    public function setDescricao(string $descricao)
    {
        $this->descricao = $descricao;
    }

    /**
     * @param string $url
     */
    public function setUrl(string $url)
    {
        $this->url = $url;
    }

    /**
     * @param string $imagem
     */
    public function setImagem(string $imagem)
    {
        $this->imagem = $imagem;
    }

    /**
     * @param int|array $usuarios
     */
    public function setUsuarios($usuarios)
    {
        $this->usuarios = $usuarios;
    }

    /**
     * @return string
     */
    public function getTitulo(): string
    {
        return $this->titulo;
    }

    /**
     * @return string
     */
    public function getDescricao(): string
    {
        return $this->descricao;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @return string
     */
    public function getImagem(): string
    {
        return $this->imagem;
    }

    /**
     * @return int|array
     */
    public function getUsuarios()
    {
        return $this->usuarios;
    }

    public function enviar()
    {
        $this->createNotification($this->titulo, $this->descricao, $this->url, $this->imagem);
    }

    /**
     * Função statica e rápida para criar notificações para a Dashboard
     * @param string $titulo
     * @param string $descricao
     * @param int|array $usuarios
     */
    public static function create(string $titulo, string $descricao, $usuarios)
    {
        $notify = [
            "titulo" => $titulo,
            "descricao" => $descricao,
            "data" => date("Y-m-d H:i:s"),
            "status" => 1
        ];

        $create = new Create();
        $read = new Read();

        $note = 0;
        $read->exeRead("notifications", "WHERE titulo = '{$titulo}' AND descricao = :d", "d={$descricao}");
        if (!$read->getResult()) {
            $create->exeCreate("notifications", $notify);
            if ($create->getResult())
                $note = $create->getResult();
        } else {
            $note = $read->getResult()[0]['id'];
        }

        if(is_numeric($note) && $note > 0) {

            /**
             * Single send
             */
            if (is_numeric($usuarios)) {
                $create->exeCreate("notifications_report", [
                    "usuario" => $usuarios,
                    "notificacao" => $note,
                    "data_de_envio" => date("Y-m-d H:i:s")
                ]);

                /**
                 * Mult send
                 */
            } elseif (is_array($usuarios)) {
                foreach ($usuarios as $usuario) {
                    if (is_numeric($usuario)) {
                        $create->exeCreate("notifications_report", [
                            "usuario" => $usuario,
                            "notificacao" => $note,
                            "data_de_envio" => date("Y-m-d H:i:s")
                        ]);
                    }
                }
            }
        }
    }

    /**
     * @param string $titulo
     * @param string $descricao
     * @param string $url
     * @param string $imagem
     */
    private function createNotification(string $titulo, string $descricao, string $url, string $imagem)
    {
        $notify = [
            "titulo" => $titulo,
            "descricao" => $descricao,
            "data" => date("Y-m-d H:i:s"),
            "status" => 1,
            "url" => $url,
            "imagem" => $imagem
        ];

        $read = new Read();
        $read->exeRead("notifications", "WHERE titulo = '{$titulo}' AND descricao = :d", "d={$descricao}");
        if (!$read->getResult()) {
            $create = new Create();
            $create->exeCreate("notifications", $notify);
            if ($create->getResult()) {
                $note = $create->getResult();

                if($this->usuarios !== 0) {
                    /**
                     * Single send
                     */
                    if (is_numeric($this->usuarios)) {
                        $create->exeCreate("notifications_report", [
                            "usuario" => $this->usuarios,
                            "notificacao" => $note,
                            "data_de_envio" => date("Y-m-d H:i:s")
                        ]);

                        /**
                         * Mult send
                         */
                    } elseif (is_array($this->usuarios)) {
                        foreach ($this->usuarios as $usuario) {
                            if (is_numeric($usuario)) {
                                $create->exeCreate("notifications_report", [
                                    "usuario" => $usuario,
                                    "notificacao" => $note,
                                    "data_de_envio" => date("Y-m-d H:i:s")
                                ]);
                            }
                        }
                    }
                }
            }
        }
    }
}