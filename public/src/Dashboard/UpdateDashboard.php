<?php

namespace Dashboard;

use EntityUi\EntityCreateEntityDatabase;
use Helpers\Helper;
use Conn\Read;
use Entity\Entity;
use Config\Config;
use MatthiasMullie\Minify;

class UpdateDashboard
{
    private $result;

    /**
     * UpdateDashboard constructor.
     * @param array $custom
     */
    public function __construct(array $custom = [])
    {
        $this->start($custom);
    }

    /**
     * @return mixed
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @param array $custom
     */
    private function start(array $custom)
    {
        if (file_exists(PATH_HOME . "composer.lock")) {
            $this->checkConfigJsonExist();

            if (!empty($custom)) {

                if (in_array('assets', $custom) || in_array('lib', $custom) || in_array('manifest', $custom) || in_array('serviceworker', $custom))
                    $this->updateVersionNumber();

                $this->updateVersion($custom);

            } elseif (file_exists(PATH_HOME . "_config/updates/version.txt")) {
                $keyVersion = file_get_contents(PATH_HOME . "composer.lock");
                $old = file_get_contents(PATH_HOME . "_config/updates/version.txt");
                if ($old !== $keyVersion) {
                    $this->updateVersionNumber();
                    $this->updateVersion($custom);
                }
            } else {

                //check if is the first time in the system to clear database
                if (!file_exists(PATH_HOME . "entity/cache")) {
                    //nenhuma entidade, zera banco
                    $sql = new \Conn\SqlCommand();
                    $sql->exeCommand("SHOW TABLES");
                    if ($sql->getResult()) {
                        $sqlDelete = new \Conn\SqlCommand();
                        foreach ($sql->getResult() as $item) {
                            if (!empty($item['Tables_in_' . DATABASE]))
                                $sqlDelete->exeCommand("DROP TABLE IF EXISTS " . $item['Tables_in_' . DATABASE]);
                        }
                    }
                }

                //Cria Version hash info
                Helper::createFolderIfNoExist(PATH_HOME . "_config/updates");
                $f = fopen(PATH_HOME . "_config/updates/version.txt", "w");
                fwrite($f, file_get_contents(PATH_HOME . "composer.lock"));
                fclose($f);

                $this->updateVersion($custom);
            }
        }
    }

    /**
     * Cria arquivo de configurações json se não existir
     */
    private function checkConfigJsonExist()
    {
        if (!file_exists(PATH_HOME . "_config/config.json")) {
            $conf = file_get_contents(PATH_HOME . "_config/config.php");

            $config = [];
            foreach (explode("define('", $conf) as $i => $item) {
                if ($i > 0) {
                    $d = explode("'", $item);
                    $config[strtolower(trim($d[0]))] = $d[2];
                }
            }

            Config::writeFile("_config/.htaccess", "Deny from all");
            Config::createConfig($config);
        }
    }

    /**
     * Atualiza a Versão do site
     */
    private function updateVersionNumber()
    {
        $dados = json_decode(file_get_contents(PATH_HOME . "_config/config.json"), true);
        $dados['version'] += 0.01;
        Config::createConfig($dados);

        Helper::createFolderIfNoExist(PATH_HOME . "_config/updates");
        $f = fopen(PATH_HOME . "_config/updates/version.txt", "w+");
        fwrite($f, file_get_contents(PATH_HOME . "composer.lock"));
        fclose($f);
    }

    private function checkAdminExist()
    {
        $read = new Read();
        $read->exeRead(PRE . "usuarios", "WHERE setor = 1 ORDER BY id ASC LIMIT 1");
        if (!$read->getResult())
            Entity::add("usuarios", ["nome" => "Admin", "nome_usuario" => "admin", "setor" => 1, "email" => (!defined('EMAIL') ? "contato@ontab.com.br" : EMAIL), "password" => "mudar"]);
    }

    /**
     * @param array $updates
     */
    private function updateVersion(array $updates)
    {
        $dados = json_decode(file_get_contents(PATH_HOME . "_config/config.json"), true);

        if (empty($updates)) {
            $this->updateDependenciesEntity();
            $this->checkAdminExist();
            $this->updateAssets();
            $this->createMinifyAssetsLib();
            $this->createManifest($dados);
            $this->updateServiceWorker($dados);
        } else {

            //atualizações personalizadas

            if (in_array('entity', $updates))
                $this->updateDependenciesEntity();

            if (in_array('admin', $updates))
                $this->checkAdminExist();

            if (in_array('assets', $updates)) {
                $this->updateAssets();
                $this->updateServiceWorker($dados);
            }

            if (in_array('lib', $updates))
                $this->createMinifyAssetsLib();

            if (in_array('manifest', $updates)) {
                $this->createManifest($dados);
                $this->updateServiceWorker($dados);
            }
        }

        $this->result = true;
    }

    private function updateAssets()
    {
        //Remove only core Assets
        if (file_exists(PATH_HOME . "assetsPublic/core.min.js"))
            unlink(PATH_HOME . "assetsPublic/core.min.js");

        if (file_exists(PATH_HOME . "assetsPublic/core.min.css"))
            unlink(PATH_HOME . "assetsPublic/core.min.css");

        if (file_exists(PATH_HOME . "assetsPublic/fonts.min.css"))
            unlink(PATH_HOME . "assetsPublic/fonts.min.css");

        if (file_exists(PATH_HOME . "assetsPublic/view")) {
            foreach (Helper::listFolder(PATH_HOME . "assetsPublic/view") as $item)
                unlink(PATH_HOME . "assetsPublic/view/{$item}");
        }

        if (file_exists(PATH_HOME . "cacheImage")) {
            foreach (Helper::listFolder(PATH_HOME . "cacheImage") as $item)
                unlink(PATH_HOME . "cacheImage/{$item}");
        }

        if (file_exists(PATH_HOME . "templates_c")) {
            foreach (Helper::listFolder(PATH_HOME . "templates_c") as $item)
                unlink(PATH_HOME . "templates_c/{$item}");
        }

        //gera core novamente
        $f = [];
        if (file_exists(PATH_HOME . "_config/param.json"))
            $f = json_decode(file_get_contents(PATH_HOME . "_config/param.json"), true);

        $list = implode('/', array_unique(array_merge($f['js'], $f['css'])));
        $data = json_decode(file_get_contents(REPOSITORIO . "app/library/{$list}"), true);
        if ($data['response'] === 1 && !empty($data['data'])) {
            $this->createCoreJs($f['js'], $data['data'], 'core');
            $this->createCoreCss($f['css'], $data['data'], 'core');
        }

        $this->createCoreFont($f['font'], $f['icon'], 'fonts');

        $this->copyInstallTemplate();
        $this->copyCustomSystem();
    }

    /**
     * Copia os templates para o sistema em caso de atualizações
     */
    private function copyInstallTemplate()
    {
        Config::writeFile("index.php", file_get_contents(PATH_HOME . VENDOR . "config/public/installTemplates/index.txt"));
        Config::writeFile("tim.php", file_get_contents(PATH_HOME . VENDOR . "config/public/installTemplates/tim.txt"));
        Config::writeFile("apiView.php", file_get_contents(PATH_HOME . VENDOR . "config/public/installTemplates/apiView.txt"));
        Config::writeFile("apiGet.php", file_get_contents(PATH_HOME . VENDOR . "config/public/installTemplates/apiGet.txt"));
        Config::writeFile("apiSet.php", file_get_contents(PATH_HOME . VENDOR . "config/public/installTemplates/apiSet.txt"));
        Config::writeFile("apiApi.php", file_get_contents(PATH_HOME . VENDOR . "config/public/installTemplates/apiApi.txt"));

        //Bloqueios por .htaccess
        Config::writeFile("_config/.htaccess", "Deny from all");
        Config::writeFile("entity/.htaccess", "Deny from all");
        Config::writeFile("public/react/.htaccess", "Deny from all");
        Config::writeFile("public/cron/.htaccess", "Deny from all");
        Config::writeFile("public/api/.htaccess", "Deny from all");
        Config::writeFile("vendor/.htaccess", $this->getAccessFile());
        Config::createHtaccess();

        if (!file_exists(PATH_HOME . "_config/entity_not_show.json"))
            Config::writeFile("_config/entity_not_show.json", '{"1":[],"2":[],"3":[],"0":[]}');

        if (!file_exists(PATH_HOME . "_config/menu_not_show.json"))
            Config::writeFile("_config/menu_not_show.json", '{"1":[],"2":[],"3":[],"0":[]}');

        if (!file_exists(PATH_HOME . "entity/general/general_info.json"))
            Config::writeFile("entity/general/general_info.json", "[]");
    }

    /**
     * Copia arquivos personalizados das libs para o sistema,
     * arquivos como tema, cabeçalho e outras personalizações
     */
    private function copyCustomSystem()
    {
        //Para cada biblioteca
        foreach (Helper::listFolder(PATH_HOME . VENDOR) as $lib) {

            // copia tema caso não exista no projeto mas exista nas libs
            if (!file_exists(PATH_HOME . "public/assets/theme.min.css") && file_exists(PATH_HOME . VENDOR . $lib . "/public/assets/theme.min.css"))
                copy(PATH_HOME . VENDOR . $lib . "/public/assets/theme.min.css", PATH_HOME . "public/assets/theme.min.css");

            $libNot = Config::getMenuNotAllow();

            //Remove index caso alguma biblioteca já possua
            if (file_exists(PATH_HOME . VENDOR . $lib . "/public/view/index.php") && file_exists(PATH_HOME . "public/view/index.php")) {
                if (preg_match("/<h1>Parabéns, tudo funcionando de acordo!<\/h1>/i", file_get_contents(PATH_HOME . "public/view/index.php")) && (!isset($libNot) || !in_array($lib, $libNot))) {
                    unlink(PATH_HOME . "public/view/index.php");
                    unlink(PATH_HOME . "public/param/index.json");
                }
            }
        }
    }

    private function getAccessFile()
    {
        return '<Files "*.json">
            Order Deny,Allow
            Deny from all
        </Files>
        <Files "*.php">
            Order Deny,Allow
            Deny from all
        </Files>
        <Files "*.html">
            Order Deny,Allow
            Deny from all
        </Files>
        <Files "*.tpl">
            Order Deny,Allow
            Deny from all
        </Files>';
    }

    /**
     * @param array $jsList
     * @param array $data
     * @param string $name
     */
    private function createCoreJs(array $jsList, array $data, string $name = "core")
    {
        if (!file_exists(PATH_HOME . "assetsPublic/{$name}.min.js")) {
            Helper::createFolderIfNoExist(PATH_HOME . "assetsPublic");
            $minifier = new Minify\JS("");

            foreach ($data as $datum) {
                if (in_array($datum['nome'], $jsList)) {
                    foreach ($datum['arquivos'] as $file) {
                        if ($file['type'] === "text/javascript")
                            $minifier->add($file['content']);
                    }
                }
            }

            $minifier->minify(PATH_HOME . "assetsPublic/{$name}.min.js");
        }
    }

    /**
     * @param array $cssList
     * @param array $data
     * @param string $name
     */
    private function createCoreCss(array $cssList, array $data, string $name = "core")
    {
        if (!file_exists(PATH_HOME . "assetsPublic/{$name}.min.css")) {
            Helper::createFolderIfNoExist(PATH_HOME . "assetsPublic");

            $minifier = new Minify\CSS("");

            foreach ($cssList as $item) {
                $datum = array_values(array_filter(array_map(function ($d) use ($item) {
                    return $d['nome'] === $item ? $d : [];
                }, $data)))[0];

                if (!empty($datum['arquivos'])) {
                    foreach ($datum['arquivos'] as $file) {
                        if ($file['type'] === "text/css")
                            $minifier->add($file['content']);
                    }
                }
            }

            //copia theme padrão para pasta do site
            if (!file_exists(PATH_HOME . "public/assets/theme.min.css") && file_exists(PATH_HOME . VENDOR . "dashboard/public/assets/theme.min.css"))
                copy(PATH_HOME . VENDOR . "dashboard/public/assets/theme.min.css", PATH_HOME . "public/assets/theme.min.css");

            $minifier->add(PATH_HOME . "public/assets/theme.min.css");
            $minifier->minify(PATH_HOME . "assetsPublic/{$name}.min.css");
        }
    }

    /**
     * @param $fontList
     * @param null $iconList
     * @param string $name
     */
    private function createCoreFont($fontList, $iconList = null, string $name = 'fonts')
    {
        if (!file_exists(PATH_HOME . "assetsPublic/{$name}.min.css")) {
            Helper::createFolderIfNoExist(PATH_HOME . "assetsPublic");
            $fonts = "";
            if ($fontList) {
                foreach ($fontList as $item)
                    $fonts .= $this->getFontIcon($item, "font");
            }
            if ($iconList) {
                foreach ($iconList as $item)
                    $fonts .= $this->getFontIcon($item, "icon");
            }

            $m = new Minify\CSS($fonts);
            $m->minify(PATH_HOME . "assetsPublic/{$name}.min.css");
        }
    }

    private function createMinifyAssetsLib()
    {
        //Para cada arquivo css e js presente nas bibliotecas dentro da pasta assets, minifica quando não existe
        foreach (Helper::listFolder(PATH_HOME . VENDOR) as $lib) {
            foreach (Helper::listFolder(PATH_HOME . VENDOR . $lib . "/public/assets") as $file) {
                $ext = pathinfo($file, PATHINFO_EXTENSION);
                $name = pathinfo($file, PATHINFO_FILENAME);
                if (in_array($ext, ['css', 'js']) && !file_exists(PATH_HOME . VENDOR . $lib . "/public/assets/{$name}.min.{$ext}") && !preg_match('/\.min\.(css|js)$/i', $file)) {
                    if ($ext === "js")
                        $minifier = new Minify\JS(file_get_contents(PATH_HOME . VENDOR . $lib . "/public/assets/{$name}.js"));
                    else
                        $minifier = new Minify\CSS(file_get_contents(PATH_HOME . VENDOR . $lib . "/public/assets/{$name}.css"));
                    $minifier->minify(PATH_HOME . VENDOR . $lib . "/public/assets/{$name}.min.{$ext}");
                }
            }
        }
    }

    private function generateInfo(array $metadados): array
    {
        $data = [
            "identifier" => 0, "title" => null, "link" => null, "status" => null, "date" => null, "datetime" => null, "valor" => null, "email" => null, "tel" => null, "cpf" => null, "cnpj" => null, "cep" => null, "time" => null, "week" => null, "month" => null, "year" => null,
            "required" => null, "unique" => null, "publisher" => null, "constant" => null, "extend" => null, "extend_mult" => null, "list" => null, "list_mult" => null, "selecao" => null, "selecao_mult" => null,
            "source" => [
                "image" => null,
                "audio" => null,
                "video" => null,
                "multimidia" => null,
                "compact" => null,
                "document" => null,
                "denveloper" => null,
                "arquivo" => null,
                "source" => null
            ]
        ];

        foreach ($metadados as $i => $dados) {
            if (in_array($dados['key'], ["unique", "extend", "extend_mult", "list", "list_mult", "selecao", "selecao_mult"]))
                $data[$dados['key']][] = $i;

            if (in_array($dados['format'], ["title", "link", "status", "date", "datetime", "valor", "email", "tel", "cpf", "cnpj", "cep", "time", "week", "month", "year"]))
                $data[$dados['format']] = $i;

            if ($dados['key'] === "publisher")
                $data["publisher"] = $i;

            if ($dados['key'] === "source" || $dados['key'] === "sources")
                $data['source'][$this->checkSource($dados['allow']['values'])][] = $i;

            if ($dados['default'] === false)
                $data['required'][] = $i;

            if (!$dados['update'])
                $data["constant"][] = $i;
        }

        return $data;
    }

    private function updateDependenciesEntity()
    {
        Helper::createFolderIfNoExist(PATH_HOME . "entity");
        Helper::createFolderIfNoExist(PATH_HOME . "entity/cache");
        Helper::createFolderIfNoExist(PATH_HOME . "entity/cache/info");

        //importa entidades ausentes para o sistema
        foreach (Helper::listFolder(PATH_HOME . VENDOR) as $lib) {
            if (file_exists(PATH_HOME . VENDOR . "{$lib}/public/entity/cache")) {
                foreach (Helper::listFolder(PATH_HOME . VENDOR . "{$lib}/public/entity/cache") as $file) {
                    if (!file_exists(PATH_HOME . "entity/cache/{$file}") && preg_match('/\w+\.json$/i', $file)) {
                        copy(PATH_HOME . VENDOR . "{$lib}/public/entity/cache/{$file}", PATH_HOME . "entity/cache/{$file}");

                        /* INFO */
                        if (file_exists(PATH_HOME . VENDOR . "{$lib}/public/entity/cache/info/{$file}")) {
                            if (file_exists(PATH_HOME . "entity/cache/info/{$file}"))
                                unlink(PATH_HOME . "entity/cache/info/{$file}");

                            copy(PATH_HOME . VENDOR . "{$lib}/public/entity/cache/info/{$file}", PATH_HOME . "entity/cache/info/{$file}");

                        } elseif (!file_exists(PATH_HOME . "entity/cache/info/{$file}")) {
                            //cria info
                            $data = $this->generateInfo(\Entity\Metadados::getDicionario(PATH_HOME . VENDOR . "{$lib}/public/entity/cache/{$file}"));
                            $fp = fopen(PATH_HOME . "entity/cache/info/" . $file, "w");
                            fwrite($fp, json_encode($data));
                            fclose($fp);
                        }

                        new EntityCreateEntityDatabase(str_replace('.json', '', $file), []);
                    }
                }
            }
        }
    }

    /**
     * @param string $path
     * @param array $listAssets
     * @param array $listData
     * @param string $version
     * @return array
     */
    private function checkCacheContent(string $path, array $listAssets, array $listData, string $version)
    {
        //templates mustache
        if (file_exists(PATH_HOME . "{$path}tpl")) {
            foreach (Helper::listFolder(PATH_HOME . "{$path}tpl") as $tpl) {
                if (preg_match('/\.mst$/i', $tpl))
                    $listAssets[] = HOME . "{$path}tpl/{$tpl}";
            }
        }

        //assets
        if (file_exists(PATH_HOME . "{$path}assets")) {
            foreach (Helper::listFolder(PATH_HOME . "{$path}assets") as $asset) {
                if (!preg_match('/\./i', $asset)) {
                    foreach (Helper::listFolder(PATH_HOME . "{$path}assets/{$asset}") as $a) {
                        if (!preg_match('/\.(js|css)$/i', $a) || preg_match('/\.min\.(js|css)$/i', $a))
                            $listAssets[] = HOME . "{$path}assets/{$asset}/{$a}" . (preg_match('/\.(js|css)$/i', $a) ? "?v=" . $version : "");
                    }
                } elseif (!preg_match('/\.(js|css)$/i', $asset) || preg_match('/\.min\.(js|css)$/i', $asset)) {
                    $listAssets[] = HOME . "{$path}assets/{$asset}" . (preg_match('/\.(js|css)$/i', $asset) ? "?v=" . $version : "");
                }
            }
        }

        //pages
        if (file_exists(PATH_HOME . "{$path}view")) {
            foreach (Helper::listFolder(PATH_HOME . "{$path}view") as $view) {
                if (preg_match('/\.php$/i', $view)) {
                    $listData[] = HOME . str_replace(['.php', 'index'], '', $view);
                    $listData[] = HOME . "get/" . str_replace('.php', '', $view);
                }
            }
        }

        return [$listAssets, $listData];
    }

    /**
     * Create Manifest
     * @param array $dados
     */
    private function createManifest(array $dados)
    {
        //Cria Tamanhos de Ícones
        $this->createFaviconSizes($dados);

        //Create Manifest
        $theme = explode("}", explode(".theme{", file_get_contents(PATH_HOME . "public/assets/theme.min.css"))[1])[0];
        $themeBack = explode("!important", explode("background-color:", $theme)[1])[0];
        $themeColor = explode("!important", explode("color:", $theme)[1])[0];
        $faviconName = pathinfo($dados['favicon'], PATHINFO_FILENAME);
        $faviconExt = pathinfo($dados['favicon'], PATHINFO_EXTENSION);
        $content = str_replace(['{$sitename}', '{$faviconName}', '{$faviconExt}', '{$theme}', '{$themeColor}'], [$dados['sitename'], $faviconName, $faviconExt, $themeBack, $themeColor], file_get_contents(PATH_HOME . VENDOR . "config/public/installTemplates/manifest.txt"));

        $fp = fopen(PATH_HOME . "manifest.json", "w");
        fwrite($fp, $content);
        fclose($fp);
    }

    /**
     * @param array $dados
     */
    private function createFaviconSizes(array $dados)
    {
        $ext = pathinfo($dados['favicon'], PATHINFO_EXTENSION);
        $name = pathinfo($dados['favicon'], PATHINFO_FILENAME);

        Helper::createFolderIfNoExist(PATH_HOME . "uploads");
        Helper::createFolderIfNoExist(PATH_HOME . "uploads/site");

        $fav = \WideImage\WideImage::load(PATH_HOME . $dados['favicon']);
        $fav->resize(256, 256, 'fill')->saveToFile(PATH_HOME . "uploads/site/{$name}-256.{$ext}");
        $fav->resize(192, 192, 'fill')->saveToFile(PATH_HOME . "uploads/site/{$name}-192.{$ext}");
        $fav->resize(152, 152, 'fill')->saveToFile(PATH_HOME . "uploads/site/{$name}-152.{$ext}");
        $fav->resize(144, 144, 'fill')->saveToFile(PATH_HOME . "uploads/site/{$name}-144.{$ext}");
        $fav->resize(128, 128, 'fill')->saveToFile(PATH_HOME . "uploads/site/{$name}-128.{$ext}");
        $fav->resize(90, 90, 'fill')->saveToFile(PATH_HOME . "uploads/site/{$name}-90.{$ext}");
    }

    /**
     * @param array $dados
     */
    private function updateServiceWorker(array $dados)
    {
        //Recria htacces para garantir que links estarão correto
        Config::createHtaccess();

        $listShell = [HOME . "assetsPublic/core.min.js?v=" . $dados['version'], HOME . "assetsPublic/core.min.css?v=" . $dados['version'], HOME . "assetsPublic/fonts.min.css?v=" . $dados['version']];
        $listAssets = [];
        $listData = [];

        if (!empty($dados['logo'])) {
            $listAssets[] = HOME . $dados['logo'];
            $listAssets[] = HOME . 'image/' . $dados['logo'] . "&h=100";
        }

        if (!empty($dados['favicon'])) {
            $listAssets[] = HOME . $dados['favicon'];
            $listAssets[] = HOME . 'image/' . $dados['favicon'] . "&h=100";
        }

        foreach (Helper::listFolder(PATH_HOME . "assetsPublic/fonts") as $font) {
            if (preg_match('/\.(ttf|woff|woff2)$/', $font))
                $listShell[] = HOME . "assetsPublic/fonts/{$font}";
        }

        //Cache Content Link Control
        list($listAssets, $listData) = $this->checkCacheContent("public/", $listAssets, $listData, $dados['version']);

        //Cache Content from libs
        $entidadesNot = Config::getEntityNotAllow();
        foreach (Helper::listFolder(PATH_HOME . VENDOR) as $lib) {
            if(!in_array($lib, $entidadesNot) && !empty(PATH_HOME . VENDOR . "{$lib}/public/view"))
                list($listAssets, $listData) = $this->checkCacheContent(VENDOR . "{$lib}/public/", $listAssets, $listData, $dados['version']);
        }

        $f = fopen(PATH_HOME . "service-worker.js", "w");

        $dadosService = json_decode(str_replace('{$home}', substr(HOME, 0, -1), file_get_contents(PATH_HOME . VENDOR . 'config/public/installTemplates/service-worker.json')), true);
        $dadosService['filesShell'] = array_merge($dadosService['filesShell'], $listShell);
        $dadosService['filesAssets'] = array_merge($dadosService['filesAssets'], $listAssets);
        $dadosService['filesData'] = array_merge($dadosService['filesData'], $listData);

        $content = file_get_contents(PATH_HOME . VENDOR . "config/public/installTemplates/service-worker.txt");
        $content = str_replace("let filesShell = [];", "let filesShell = " . json_encode($dadosService['filesShell'], JSON_UNESCAPED_SLASHES) . ";", $content);
        $content = str_replace("let filesAssets = [];", "let filesAssets = " . json_encode($dadosService['filesAssets'], JSON_UNESCAPED_SLASHES) . ";", $content);
        $content = str_replace("let filesData = [];", "let filesData = " . json_encode($dadosService['filesData'], JSON_UNESCAPED_SLASHES) . ";", $content);
        $content = str_replace("-1.0.0';", "-{$dados['version']}';", $content);

        fwrite($f, $content);
        fclose($f);
    }

    private function checkSource($valores)
    {
        $type = [];
        $data = [
            "image" => ["png", "jpg", "jpeg", "gif", "bmp", "tif", "tiff", "psd", "svg"],
            "video" => ["mp4", "avi", "mkv", "mpeg", "flv", "wmv", "mov", "rmvb", "vob", "3gp", "mpg"],
            "audio" => ["mp3", "aac", "ogg", "wma", "mid", "alac", "flac", "wav", "pcm", "aiff", "ac3"],
            "document" => ["txt", "doc", "docx", "dot", "dotx", "dotm", "ppt", "pptx", "pps", "potm", "potx", "pdf", "xls", "xlsx", "xltx", "rtf"],
            "compact" => ["rar", "zip", "tar", "7z"],
            "denveloper" => ["html", "css", "scss", "js", "tpl", "json", "xml", "md", "sql", "dll"]
        ];

        foreach ($data as $tipo => $dados) {
            if (count(array_intersect($dados, $valores)) > 0)
                $type[] = $tipo;
        }

        if (count($type) > 1) {
            if (count(array_intersect(["document", "compact", "denveloper"], $type)) === 0 && count(array_intersect(["image", "video", "audio"], $type)) > 1)
                return "multimidia";
            else if (count(array_intersect(["document", "compact", "denveloper"], $type)) > 1 && count(array_intersect(["image", "video", "audio"], $type)) === 0)
                return "arquivo";
            else
                return "source";
        } else {
            return $type[0];
        }
    }

    /**
     * @param string $item
     * @param string $tipo
     * @return string
     */
    private function getFontIcon(string $item, string $tipo): string
    {
        $data = "";
        $urlOnline = $tipo === "font" ? "https://fonts.googleapis.com/css?family=" . ucfirst($item) . ":100,300,400,700" : "https://fonts.googleapis.com/icon?family=" . ucfirst($item) . "+Icons";
        if (Helper::isOnline($urlOnline)) {
            $data = file_get_contents($urlOnline);
            foreach (explode('url(', $data) as $i => $u) {
                if ($i > 0) {
                    $url = explode(')', $u)[0];
                    if (!file_exists(PATH_HOME . "assetsPublic/fonts/" . pathinfo($url, PATHINFO_BASENAME))) {
                        if (Helper::isOnline($url)) {
                            Helper::createFolderIfNoExist(PATH_HOME . "assetsPublic/fonts");
                            $f = fopen(PATH_HOME . "assetsPublic/fonts/" . pathinfo($url, PATHINFO_BASENAME), "w+");
                            fwrite($f, file_get_contents($url));
                            fclose($f);
                            $data = str_replace($url, HOME . "assetsPublic/fonts/" . pathinfo($url, PATHINFO_BASENAME), $data);
                        } else {
                            $before = "@font-face" . explode("@font-face", $u[$i - 1])[1] . "url(";
                            $after = explode("}", $u)[0];
                            $data = str_replace($before . $after, "", $data);
                        }
                    } else {
                        $data = str_replace($url, HOME . "assetsPublic/fonts/" . pathinfo($url, PATHINFO_BASENAME), $data);
                    }
                }
            }
        }
        return $data;
    }
}