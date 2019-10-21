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
//        $this->listRelationContent();
        $this->custom();
    }

    /*private function listRelationContent()
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
    }*/

    /*
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
    }*/

    /**
     * Verifica por Menus Extras para adicionar
     */
    private function custom()
    {
        $setor = !empty($_SESSION['userlogin']) ? $_SESSION['userlogin']['setor'] : "0";

        if (!empty($setor) && file_exists(PATH_HOME . "public/dash/{$setor}/menu.json"))
            $this->addMenuJson(PATH_HOME . "public/dash/{$setor}/menu.json");
        elseif (file_exists(PATH_HOME . "public/dash/menu.json"))
            $this->addMenuJson(PATH_HOME . "public/dash/menu.json");

        foreach (Helper::listFolder(PATH_HOME . VENDOR) as $lib) {
            if (file_exists(PATH_HOME . VENDOR . "{$lib}/public/dash/menu.json"))
                $this->addMenuJson(PATH_HOME . VENDOR . "{$lib}/public/dash/menu.json");
            if (!empty($setor) && file_exists(PATH_HOME . VENDOR . "{$lib}/public/dash/{$setor}/menu.json"))
                $this->addMenuJson(PATH_HOME . VENDOR . "{$lib}/public/dash/{$setor}/menu.json");
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
            foreach ($incMenu as $i => $menu) {
                $mount = [
                    "funcao" => null,
                    "href" => null,
                    "attr" => "",
                    "class" => "",
                    "style" => "",
                    "html" => "",
                    "li" => [
                        "attr" => "",
                        "class" => "",
                        "style" => ""
                    ],
                    "indice" => $i
                ];

                //action / function
                if (!empty($menu['onclick']))
                    $mount['funcao'] = $menu['onclick'];
                elseif (!empty($menu['click']))
                    $mount['funcao'] = $menu['click'];
                elseif (!empty($menu['function']))
                    $mount['funcao'] = $menu['function'];
                elseif (!empty($menu['funcao']))
                    $mount['funcao'] = $menu['funcao'];

                if (!empty($menu['href']))
                    $mount['href'] = $menu['href'];
                elseif (!empty($menu['link']))
                    $mount['href'] = $menu['link'];
                elseif (!empty($menu['a']))
                    $mount['href'] = $menu['a'];
                elseif (!empty($menu['url']))
                    $mount['href'] = $menu['url'];

                //ATRIBUTOS DA LI
                if(!empty($menu['li']['class'])) {
                    if(is_array($menu['li']['class'])) {
                        $mount['li']['class'] = implode(' ', $menu['li']['class']);
                    } elseif(is_string($menu['li']['class'])) {
                        $mount['li']['class'] = $menu['li']['class'];
                    }
                }

                if(!empty($menu['li']['style'])) {
                    if(is_array($menu['li']['style'])) {
                        if(is_object($menu['li']['style'][0])) {
                            foreach ($menu['li']['style'] as $style => $value)
                                $mount['li']['style'] .= "{$style}: {$value};";

                        } elseif(is_string($menu['li']['style'][0])) {
                            $mount['li']['style'] = implode(';', $menu['li']['style']);
                        }

                    } elseif(is_object($menu['li']['style'])) {
                        foreach ($menu['li']['style'] as $style => $value)
                            $mount['li']['style'] .= "{$style}: {$value};";
                    } elseif(is_string($menu['li']['style'])) {
                        $mount['li']['style'] = $menu['li']['style'];
                    }
                }

                if(!empty($menu['li']['attr'])) {
                    if(is_array($menu['li']['attr'])) {
                        if(is_object($menu['li']['attr'][0])) {
                            foreach ($menu['li']['attr'] as $attr => $value)
                                $mount['li']['attr'] .= "{$attr}='{$value}' ";

                        } elseif(is_string($menu['li']['attr'][0])) {
                            $mount['li']['attr'] = implode(' ', $menu['li']['attr']);
                        }
                    } elseif(is_object($menu['li']['attr'])) {
                        foreach ($menu['li']['attr'] as $attr => $value)
                            $mount['li']['attr'] .= "{$attr}='{$value}' ";
                    } elseif(is_string($menu['li']['attr'])) {
                        $mount['li']['attr'] = $menu['li']['attr'];
                    }
                }

                //ATTRIBUTOS DO ALVO
                if(!empty($menu['class'])) {
                    if(is_array($menu['class'])) {
                        $mount['class'] = implode(' ', $menu['class']);
                    } elseif(is_string($menu['class'])) {
                        $mount['class'] = $menu['class'];
                    }
                }

                if(!empty($menu['style'])) {
                    if(is_array($menu['style'])) {
                        if(is_object($menu['style'][0])) {
                            foreach ($menu['style'] as $style => $value)
                                $mount['style'] .= "{$style}: {$value};";

                        } elseif(is_string($menu['style'][0])) {
                            $mount['style'] = implode(';', $menu['style']);
                        }

                    } elseif(is_object($menu['style'])) {
                        foreach ($menu['style'] as $style => $value)
                            $mount['style'] .= "{$style}: {$value};";
                    } elseif(is_string($menu['style'])) {
                        $mount['style'] = $menu['style'];
                    }
                }

                if(!empty($menu['attr'])) {
                    if(is_array($menu['attr'])) {
                        if(is_object($menu['attr'][0])) {
                            foreach ($menu['attr'] as $attr => $value)
                                $mount['attr'] .= "{$attr}='{$value}' ";

                        } elseif(is_string($menu['attr'][0])) {
                            $mount['attr'] = implode(' ', $menu['attr']);
                        }
                    } elseif(is_object($menu['attr'])) {
                        foreach ($menu['attr'] as $attr => $value)
                            $mount['attr'] .= "{$attr}='{$value}' ";
                    } elseif(is_string($menu['attr'])) {
                        $mount['attr'] = $menu['attr'];
                    }
                }

                if(!empty($menu['html']))
                    $mount['html'] = $menu['html'];
                elseif(!empty($menu['text']))
                    $mount['html'] = $menu['text'];
                elseif(!empty($menu['title']))
                    $mount['html'] = $menu['title'];

                if(empty($menu['html']) && !empty($menu['icon']))
                    $mount['html'] = (preg_match('/^</i', $menu['icon']) ? $menu['icon'] : "<i class='material-icons'>{$menu['icon']}</i>") . $mount['html'];

                if(!empty($menu['indice']))
                    $mount['indice'] = $menu['indice'];
                elseif(!empty($menu['index']))
                    $mount['indice'] = $menu['index'];


                $this->menu[] = $mount;
            }
        }
    }
}