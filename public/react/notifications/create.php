<?php

use Conn\Read;
use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

if (defined("PUSH_PUBLIC_KEY") && !empty(PUSH_PUBLIC_KEY) && defined("PUSH_PRIVATE_KEY") && !empty(PUSH_PRIVATE_KEY)) {
    $notifications = [];
    $read = new Read();
    $read->exeRead("push_notifications", "WHERE usuario = :au", "au={$dados['usuario']}");
    if ($read->getResult()) {
        foreach ($read->getResult() as $item) {
            $notifications[] = [
                'subscription' => Subscription::create(json_decode($item['subscription'], !0)),
                'payload' => json_encode(
                    [
                        "title" => $dados['titulo'],
                        "body" => $dados['descricao'] ?? "",
                        "badge" => HOME . "assetsPublic/img/favicon.png?v=" . VERSION,
                        "data" => $dados['url'] ?? "",
                        "icon" => $dados['imagem'] ?? HOME.FAVICON,
                        "imagem" => $dados['background'] ?? ""
                    ]
                )
            ];
        }
    }

    $auth = array(
        'VAPID' => array(
            'subject' => HOME,
            'publicKey' => PUSH_PUBLIC_KEY, // don't forget that your public key also lives in app.js
            'privateKey' => PUSH_PRIVATE_KEY, // in the real world, this would be in a secret file
        ),
    );

    $webPush = new WebPush($auth);

// send multiple notifications with payload
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