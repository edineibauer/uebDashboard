<?php

use Conn\Read;

if (defined("FB_SERVER_KEY") && !empty(FB_SERVER_KEY)) {



        /**
     * Lê as notificações pendêntes
     */
    $up = new \Conn\Update();
    $read = new Read();

    $read->exeRead("notifications_report", "WHERE data_de_envio < NOW() AND (enviou = 0 || enviou IS NULL)", !0);
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
                $read->exeRead("notifications", "WHERE id = :id", "id={$item['notificacao']}", !0);
                $pushs[$item['notificacao']] = $read->getResult()[0] ?? [];
            }

            /**
             * Monta o array com as informações para o push
             */
            if (!empty($pushs[$item['notificacao']])) {
                $notifications[] = [
                    "id" => $item['id'],
                    "title" => $pushs[$item['notificacao']]['titulo'],
                    "body" => $pushs[$item['notificacao']]['descricao'] ?? "",
                    "badge" => HOME . "assetsPublic/img/favicon.png?v=" . VERSION,
                    "data" => $pushs[$item['notificacao']]['url'] ?? "",
                    "icon" => $pushs[$item['notificacao']]['imagem'] ?? HOME . "assetsPublic/img/favicon.png?v=" . VERSION,
                    "imagem" => $pushs[$item['notificacao']]['background'] ?? ""
                ];

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
        if (!empty($totalEnvios)) {
            foreach ($totalEnvios as $idMensagem => $total)
                $up->exeUpdate("enviar_mensagem", ["total_de_envios" => $total], "WHERE id = :ud", "ud={$idMensagem}");
        }

        if (file_exists(PATH_HOME . "_config/firebase.json")) {

            $client = new Google_Client();
            $client->setAuthConfig(PATH_HOME . '_config/firebase.json');
            $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
            $httpClient = $client->authorize();
            $project = json_decode(file_get_contents(PATH_HOME . "_config/firebase.json"), !0)['project_id'];

            // Creates a notification for subscribers to the debug topic
            $message = [
                "message" => [
                    "topic" => "debug",
                    "notification" => [
                        "id" => $item['id'],
                        "body" => $pushs[$item['notificacao']]['descricao'] ?? "",
                        "title" => $pushs[$item['notificacao']]['titulo'],
                        "badge" => HOME . "assetsPublic/img/favicon.png?v=" . VERSION,
                        "data" => $pushs[$item['notificacao']]['url'] ?? "",
                        "icon" => $pushs[$item['notificacao']]['imagem'] ?? HOME . "assetsPublic/img/favicon.png?v=" . VERSION,
                        "imagem" => $pushs[$item['notificacao']]['background'] ?? ""
                    ]
                ]
            ];

            $response = $httpClient->post("https://fcm.googleapis.com/v1/projects/{$project}/messages:send", ['json' => $message]);

            if($response->getStatusCode() !== 200) {
                //error
            }
        }
    }
}