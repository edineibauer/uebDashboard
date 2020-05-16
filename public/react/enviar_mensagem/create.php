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
                        foreach ($dicionario as $dic) {
                            if ($dic['format'] === "email") {
                                $email = $dic['column'];
                                break;
                            }
                        }
                        if($email === "") {
                            foreach (\Entity\Metadados::getDicionario($mensagem['enviar_para']) as $dic) {
                                if ($dic['format'] === "email") {
                                    $email = $dic['column'];
                                    break;
                                }
                            }
                        }
                    }

                    if (!empty($column)) {
                        $sql = new \Conn\SqlCommand();
                        if ($report->getResult()) {
                            if(!$isReportCliente) {

                                /**
                                 * Obtém lista de ids
                                 * se tiver email, obtém lista de email
                                 */
                                $ids = [];
                                foreach ($report->getResult() as $r) {
                                    $ids[] = (int) $r[$column];

                                    //email
                                    if(!empty($email) && !empty($r[$email]) && !in_array($r[$email], $emails))
                                        $emails[] = $r[$email];
                                }

                                /**
                                 * Busca ids de usuário com a lista de ids de setor
                                 */
                                $sql->exeCommand("SELECT usuarios_id FROM ". PRE . $mensagem['enviar_para'] . " WHERE id IN (" . implode(',', $ids) . ")");
                                if($sql->getResult()) {
                                    foreach ($sql->getResult() as $item) {
                                        if(!in_array($item['usuarios_id'], $usuarios))
                                            $usuarios[] = (int) $item['usuarios_id'];

                                        //email
                                        if(!empty($email) && !empty($item[$email]) && !in_array($item[$email], $emails))
                                            $emails[] = $item[$email];
                                    }
                                }

                            } else {

                                foreach ($report->getResult() as $result) {
                                    if (!in_array($result[$column], $usuarios)) {
                                        $usuarios[] = $result[$column];

                                        //email
                                        if (!empty($email) && !empty($result[$email]))
                                            $emails[$result[$column]] = $result[$email];
                                    }
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