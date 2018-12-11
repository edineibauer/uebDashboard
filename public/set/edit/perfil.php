<?php
$d = new \Entity\Dicionario("usuarios");
$form = new \FormCrud\Form("usuarios");
$data['data'] = $form->getForm($_SESSION['userlogin']['id'], [$d->getRelevant()->getColumn(), "email", "imagem", "password", "telefone", "nova_senha"]);