
<?php

$data['data'] = [];
foreach (\Helpers\Helper::listFolder(PATH_HOME . "entity/cache") as $entity) {
    if(is_dir($entity) || pathinfo($entity, PATHINFO_EXTENSION) !== "json")
        continue;

    $entity = str_replace(".json", "", $entity);

    if(\Config\Config::haveEntityPermission($entity, ["menu"]) && file_exists(PATH_HOME . "entity/cache/info/{$entity}.json")) {
        $info = json_decode(file_get_contents(PATH_HOME . "entity/cache/info/{$entity}.json"), !0);
        if($info['user'] !== 3)
            $data['data'][] = ["entidade" => $entity, "info" => $info];
    }
}