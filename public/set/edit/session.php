<?php
$dados = filter_input(INPUT_POST, 'dados', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);

foreach ($dados as $dado => $value) {
    $dado = str_replace('dados.', '', $dado);
    if ($dado === "imagem")
        $_SESSION['userlogin']['imagem'] = json_decode($value, true)[0]['url'];
    else
        $_SESSION['userlogin'][$dado] = $value;
}