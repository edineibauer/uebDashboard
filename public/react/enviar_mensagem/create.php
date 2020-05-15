<?php

$usuarios = [];
$emails = [];

$mensagem = $dados;
$mensagem['imagem'] = (!empty($mensagem['imagem']) ? json_decode($mensagem['imagem'], !0)[0]['url'] : "");
$mensagem['canais'] = json_decode($mensagem['canais'], !0);

if (!empty($mensagem['enviar_para_relatorios'])) {
    $relatorios = json_decode($mensagem['enviar_para_relatorios'], !0);

    if (!empty($relatorios)) {
        foreach ($relatorios as $relatorio) {
            $report = new \Report\Report((int)$relatorio);
            $reportData = $report->getReport();

            if (!empty($report->getResult())) {
                foreach ($report->getResult() as $result) {
                    $dicionario = \Entity\Metadados::getDicionario($reportData['entidade']);
                    $isReportCliente = $reportData['entidade'] === $mensagem['enviar_para'];

                    /**
                     * Encontra a coluna
                     */
                    $column = "";
                    if ($isReportCliente) {
                        $column = "usuarios_id";
                    } else {
                        foreach ($dicionario as $item) {
                            if ($item['relation'] === $mensagem['enviar_para']) {
                                $column = $item['column'];
                                break;
                            }
                        }
                    }

                    /**
                     * Encontra email
                     */
                    $email = "";
                    if (in_array("2", $mensagem['canais'])) {
                        if ($isReportCliente) {
                            $dicionarioCliente = $dicionario;
                        } else {
                            $dicionarioCliente = \Entity\Metadados::getDicionario($mensagem['enviar_para']);
                        }

                        foreach ($dicionarioCliente as $dic) {
                            if ($dic['format'] === "email") {
                                $email = $dic['column'];
                                break;
                            }
                        }
                    }

                    if (!empty($column)) {

                        if ($report->getResult()) {
                            foreach ($report->getResult() as $result) {
                                if (!in_array($result['usuarios_id'], $usuarios)) {
                                    $usuarios[] = $result['usuarios_id'];

                                    if (!empty($email) && !empty($result[$email]))
                                        $emails[$result['usuarios_id']] = $result[$email];
                                }
                            }
                        }

                        /**
                         * Atualiza total de mensagens enviada em Enviar Mensagem
                         */
                        $up = new \Conn\Update();
                        $up->exeUpdate("enviar_mensagem", ["total_de_envios" => count($usuarios)], "WHERE id = :mid", "mid={$mensagem['id']}");

                        if (!empty($usuarios)) {

                            /**
                             * push notification
                             */
                            if (in_array("1", $mensagem['canais'])) {

                                /**
                                 * Para cada usuário, envia a mensagem
                                 */
                                $note = new \Dashboard\Notification();
                                $note->setTitulo($mensagem['assunto']);
                                $note->setDescricao($mensagem['descricao']);
                                $note->setImagem($mensagem['imagem']);
                                $note->setEnviarMensagemAssociation($mensagem['id']);

                                if (!empty($mensagem['url']))
                                    $note->setUrl($mensagem['url']);

                                $note->setUsuarios($usuarios);
                                $note->enviar();
                            }

                            /**
                             * Email notification
                             */
                            if (in_array("2", $mensagem['canais'])) {

                                /**
                                 * Encontra email
                                 */
                                $email = "";
                                if (in_array("2", $mensagem['canais'])) {
                                    if ($isReportCliente) {
                                        $dicionarioCliente = $dicionario;
                                    } else {
                                        $dicionarioCliente = \Entity\Metadados::getDicionario($mensagem['enviar_para']);
                                    }

                                    foreach ($dicionarioCliente as $dic) {
                                        if ($dic['format'] === "email") {
                                            $email = $dic['column'];
                                            break;
                                        }
                                    }
                                }


                                foreach ($usuarios as $usuario) {
                                    if (!empty($emails[$usuario])) {
                                        $emailSend = new \Email\Email();
                                        $emailSend->setAssunto($mensagem['assunto']);
                                        $emailSend->setMensagem($mensagem['descricao']);
                                        $emailSend->setDestinatarioEmail($emails[$usuario]);
                                        $emailSend->enviar();
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}