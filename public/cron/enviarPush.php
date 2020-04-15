<?php

use Conn\Read;
use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

if (defined("PUSH_PUBLIC_KEY") && !empty(PUSH_PUBLIC_KEY) && defined("PUSH_PRIVATE_KEY") && !empty(PUSH_PRIVATE_KEY)) {

    /**
     * Lê as notificações pendêntes
     */
    $read = new Read();
    $read->exeRead("notifications_report", "WHERE data_de_envio > NOW() AND (enviou = 0 || enviou IS NULL)");
    if ($read->getResult()) {
        $pushs = [];
        $inscricao = [];
        $notifications = [];

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
                $read->exeRead("push_notifications", "WHERE usuario = :au ORDER BY id DESC LIMIT 1", "au={$item['usuario']}");
                $inscricao[$item['usuario']] = $read->getResult()[0] ?? [];
            }

            /**
             * Monta o array com as informações para o push
             */
            if(!empty($inscricao[$item['usuario']]) && !empty($pushs[$item['notificacao']])) {
                $notifications[] = [
                    'subscription' => Subscription::create(json_decode($inscricao[$item['usuario']]['subscription'], !0)),
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

                /**
                 * Atualia status informando que o push foi enviado
                 */
                $up = new \Conn\Update();
                $up->exeUpdate("notifications_report", ["enviou" => 1], "WHERE id = :ud", "ud={$item['id']}");
            }
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
        /*foreach ($webPush->flush() as $report) {
            $endpoint = $report->getRequest()->getUri()->__toString();

            if ($report->isSuccess()) {
                $up = new \Conn\Update();
                $sql = new \Conn\SqlCommand("UPDATE " . PRE . "notifications SET enviou = 1 WHERE id = {}");
                $sql->exeCommand("");
            }

            if ($report->isSuccess()) {
                echo "[v] Message sent successfully for subscription {$endpoint}.";
            } else {
                echo "[x] Message failed to sent for subscription {$endpoint}: {$report->getReason()}";
            }
        }*/
    }
}