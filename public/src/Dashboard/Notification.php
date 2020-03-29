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
    private $usuario = 0;
    private $copia = !1;

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
     * @return int
     */
    public function getUsuario(): int
    {
        return $this->usuario;
    }

    /**
     * @return bool
     */
    public function getCopia(): bool
    {
        return $this->copia;
    }

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
     * @param int $usuario
     */
    public function setUsuario(int $usuario)
    {
        $this->usuario = $usuario;
    }

    /**
     * @param bool $copia
     */
    public function setCopia(bool $copia)
    {
        $this->copia = $copia;
    }

    public function enviar()
    {
        $this->createNotification($this->titulo, $this->descricao, $this->url, $this->imagem, $this->usuario, $this->copia);
    }

    /**
     * Função statica e rápida para criar notificações para a Dashboard
     * @param string $titulo
     * @param string $descricao
     * @param int $usuario
     * @param int $copia
     */
    public static function create(string $titulo, string $descricao, int $usuario, int $copia = 0)
    {
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
        if (!$read->getResult()) {
            $create->exeCreate("notifications", $notify);
            if ($copia !== 0) {
                $notify['titulo'] = "[CÓPIA] " . $notify['titulo'];
                $notify['usuario'] = $_SESSION['userlogin']['id'];
                $create->exeCreate("notifications", $notify);
            }
        }
    }

    /**
     * @param string $titulo
     * @param string $descricao
     * @param string $url
     * @param string $imagem
     * @param int $usuario
     * @param bool $copia
     */
    private function createNotification(string $titulo, string $descricao, string $url, string $imagem, int $usuario, bool $copia)
    {
        $notify = [
            "titulo" => $titulo,
            "descricao" => $descricao,
            "data" => date("Y-m-d H:i:s"),
            "status" => 1,
            "url" => $url,
            "imagem" => $imagem,
            "usuario" => $usuario
        ];

        $read = new Read();
        $read->exeRead("notifications", "WHERE titulo = '{$titulo}' AND usuario = :a", "a={$usuario}");
        if (!$read->getResult()) {

            $create = new Create();
            $create->exeCreate("notifications", $notify);
            if ($copia) {
                $notify['titulo'] = "[CÓPIA] " . $notify['titulo'];
                $notify['usuario'] = $_SESSION['userlogin']['id'];
                $create->exeCreate("notifications", $notify);
            }
        }
    }
}