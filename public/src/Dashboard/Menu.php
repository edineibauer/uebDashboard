<?php

namespace Dashboard;

use Conn\Read;
use Entity\Metadados;
use Helpers\Check;
use Helpers\Helper;
use Helpers\Template;
use Config\Config;

class Menu
{
    private $menu;

    public function __construct()
    {
        $this->menu = [];
        $this->start();
    }

    /**
     * @return array
     */
    public function getMenu(): array
    {
        return $this->menu;
    }

    public function showMenu()
    {
        echo $this->getMenu();
    }

    private function start()
    {
        $this->listRelationContent();
        $this->custom();
    }

    private function listRelationContent()
    {
        foreach (Helper::listFolder(PATH_HOME . "entity/cache") as $item) {
            if (preg_match('/\.json$/i', $item) && $item !== "login_attempt.json") {
                $entity = str_replace('.json', '', $item);
                $metadados = Metadados::getDicionario($entity);
                foreach ($metadados as $id => $dic) {
                    if ($dic['relation'] === "usuarios" && in_array($dic['format'], ['extend', 'list', 'selecao'])) {
                        $this->getMenuListRelationContent($entity, $metadados, $id);
                        break;
                    }
                }
            }
        }
    }

    private function getMenuListRelationContent(string $entity, array $metadados, int $id)
    {
        $read = new Read();
        $read->exeRead($entity, "WHERE {$metadados[$id]['column']} = :ui", "ui={$_SESSION['userlogin']['id']}");
        if ($read->getResult()) {
            //            $idU = $read->getResult()[0]['id'];
            if ($metadados[$id]['format'] === "extend") {
                // único linkamento, é parte desta entidade (busca seus dados relacionados)

                foreach ($metadados as $metadado) {
                    if ($metadado['format'] === 'extend_mult') {
                        //table owner (exibe tabela com os registros linkados apenas)
                        $this->menu[$metadado['relation']] = [
                            "icon" => "storage",
                            "title" => $metadado['nome'],
                            "table" => true,
                            "form" => false,
                            "page" => false,
                            "link" => false,
                            "file" => "",
                            "lib" => "",
                            "entity" => $metadado['relation'],
                            "indice" => (count($this->menu) + 1)
                        ];

                    } elseif ($metadado['format'] === 'list_mult') {
                        //table publisher (exibe tabela com todos os registros, mas só permite editar os linkados)
                        $this->menu[$metadado['relation']] = [
                            "icon" => "storage",
                            "title" => $metadado['nome'],
                            "table" => true,
                            "form" => false,
                            "page" => false,
                            "link" => false,
                            "file" => "",
                            "lib" => "",
                            "entity" => $metadado['relation'],
                            "indice" => (count($this->menu) + 1)
                        ];

                    } elseif ($metadado['format'] === 'selecao_mult') {
                        //form para ediçaõ das seleções apenas
                        $this->menu[$metadado['relation']] = [
                            "icon" => "storage",
                            "title" => $metadado['nome'],
                            "table" => true,
                            "form" => false,
                            "page" => false,
                            "link" => false,
                            "file" => "",
                            "lib" => "",
                            "entity" => $metadado['relation'],
                            "indice" => (count($this->menu) + 1)
                        ];

                    } elseif ($metadado['format'] === 'extend') {
                        //form para edição do registro único (endereço por exemplo)

                    } elseif ($metadado['format'] === 'list') {
                    }
                }

            } else {
                // multiplos linkamentos, se relaciona ocm a entidade (pode ser autor)

            }
        }
    }

    /**
     * Verifica por Menus Extras para adicionar
     */
    private function custom()
    {
        if (file_exists(PATH_HOME . "public/dash/menu.json"))
            $this->addMenuJson(PATH_HOME . "public/dash/menu.json");

        if (file_exists(PATH_HOME . "public/dash/{$_SESSION['userlogin']['setor']}/menu.json"))
            $this->addMenuJson(PATH_HOME . "public/dash/{$_SESSION['userlogin']['setor']}/menu.json");

        foreach (Helper::listFolder(PATH_HOME . VENDOR) as $lib) {
            if (file_exists(PATH_HOME . VENDOR . "{$lib}/public/dash/menu.json"))
                $this->addMenuJson(PATH_HOME . VENDOR . "{$lib}/public/dash/menu.json");
            if (file_exists(PATH_HOME . VENDOR . "{$lib}/public/dash/{$_SESSION['userlogin']['setor']}/menu.json"))
                $this->addMenuJson(PATH_HOME . VENDOR . "{$lib}/public/dash/{$_SESSION['userlogin']['setor']}/menu.json");
        }
    }

    /**
     * Mostra Menu
     * @param string $incMenu
     */
    private function addMenuJson(string $incMenu)
    {
        $incMenu = json_decode(file_get_contents($incMenu), true);
        if (!empty($incMenu)) {
            foreach ($incMenu as $menu) {
                $name = Check::name(trim(strip_tags($menu['title'])));
                $this->menu[$name] = [
                    'lib' => Check::words(trim(strip_tags($menu['lib'])), 1),
                    'file' => Check::words(trim(strip_tags($menu['file'])), 1),
                    "table" => $menu['table'] ?? true,
                    "form" => $menu['form'] ?? false,
                    "page" => $menu['page'] ?? false,
                    "link" => $menu['link'] ?? false,
                    'title' => ucwords(Check::words(trim(strip_tags($menu['title'])), 3)),
                    'icon' => Check::words(trim(strip_tags($menu['icon'])), 1),
                    "indice" => $menu['indice'] ?? (count($this->menu) + 1)
                ];
            }
        }
    }
}