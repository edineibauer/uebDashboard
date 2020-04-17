<?php

use Conn\Read;
use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

if (defined("PUSH_PUBLIC_KEY") && !empty(PUSH_PUBLIC_KEY) && defined("PUSH_PRIVATE_KEY") && !empty(PUSH_PRIVATE_KEY)) {

    /**
     * Lê as notificações pendêntes
     */
    $up = new \Conn\Update();
    $read = new Read();

    $read->exeRead("notifications_report", "WHERE data_de_envio < NOW() AND (enviou = 0 || enviou IS NULL)");
    if ($read->getResult()) {
        $pushs = [];
        $inscricao = [];
        $notifications = [];
        $totalEnvios = [];

        foreach ($read->getResult() as $item) {
            /**
             * Dispara os pushs para os usuários
             */
            if (!isset($pushs[$item['notificacao']])) {
                $read->exeRead("notifications", "WHERE id = :id", "id={$item['notificacao']}");
                $pushs[$item['notificacao']] = $read->getResult()[0] ?? [];
            }

            /**
             * Lê inscrições
             */
            if (!isset($inscricao[$item['usuario']])) {
                $read->exeRead("push_notifications", "WHERE usuario = :au", "au={$item['usuario']}");
                $inscricao[$item['usuario']] = $read->getResult() ?? [];
            }

            /**
             * Monta o array com as informações para o push
             */
            if(!empty($inscricao[$item['usuario']]) && !empty($pushs[$item['notificacao']])) {
                foreach ($inscricao[$item['usuario']] as $insc) {
                    $notifications[] = [
                        'subscription' => Subscription::create(json_decode($insc['subscription'], !0)),
                        'payload' => json_encode(
                            [
                                "id" => $item['id'],
                                "title" => $pushs[$item['notificacao']]['titulo'],
                                "body" => $pushs[$item['notificacao']]['descricao'] ?? "",
                                "badge" => HOME . "assetsPublic/img/favicon.png?v=" . VERSION,
                                "data" => $pushs[$item['notificacao']]['url'] ?? "",
                                "icon" => $pushs[$item['notificacao']]['imagem'] ?? HOME . "assetsPublic/img/favicon.png?v=" . VERSION,
                                "imagem" => $pushs[$item['notificacao']]['background'] ?? ""
                            ]
                        )
                    ];
                }

                /**
                 * Atualia status informando que o push foi enviado
                 */
                $up->exeUpdate("notifications_report", ["enviou" => 1], "WHERE id = :ud", "ud={$item['id']}");
                $totalEnvios[$item['enviar_mensagem_id']] = (!isset($totalEnvios[$item['enviar_mensagem_id']]) ? 1 : $totalEnvios[$item['enviar_mensagem_id']] + 1);
            }
        }

        /**
         * Atualiza total de envios para as mensagem caso tenha
         */
        if(!empty($totalEnvios)) {
            foreach ($totalEnvios as $idMensagem => $total)
                $up->exeUpdate("enviar_mensagem", ["total_de_envios" => $total], "WHERE id = :ud", "ud={$idMensagem}");
        }

        /**
         * Faz o envio dos pushs
         */
        $auth = array(
            'VAPID' => array(
                'subject' => HOME,
                'publicKey' => PUSH_PUBLIC_KEY, // don't forget that your public key also lives in app.js
                'privateKey' => PUSH_PRIVATE_KEY, // in the real world, this would be in a secret file
            ),
        );
        $webPush = new WebPush($auth);
        foreach ($notifications as $notification) {
            $webPush->sendNotification(
                $notification['subscription'],
                $notification['payload']
            );
        }

        /**
         * Check sent results
         * @var MessageSentReport $report
         */
        foreach ($webPush->flush() as $report) {
            $endpoint = $report->getRequest()->getUri()->__toString();

            /*if ($report->isSuccess()) {
                echo "[v] Message sent successfully for subscription {$endpoint}.";
            } else {
                echo "[x] Message failed to sent for subscription {$endpoint}: {$report->getReason()}";
            }*/
        }
    }
}