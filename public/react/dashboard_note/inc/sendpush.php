<?php

use Conn\Read;
use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

$notifications = [];
$read = new Read();
$read->exeRead("dashboard_push", "WHERE autor = :au", "au={$_SESSION['userlogin']['id']}");
if ($read->getResult()) {
    foreach ($read->getResult() as $item) {
        $notifications[] = [
            'subscription' => Subscription::create(json_decode($item['subscription'], !0)),
            'payload' => '{msg:"' . $dados['titulo'] .'"}',
        ];
    }
}

$webPush = new WebPush();

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