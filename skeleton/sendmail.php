<?php

function sendEmail($toEmail, $subject, $content)
{
    $secretsFile = __DIR__ . '/secrets.php';
    if (!is_readable($secretsFile)) {
        return false;
    }

    $secrets = require $secretsFile;
    $apiKey = $secrets['sendgrid_api_key'] ?? '';
    if ($apiKey === '' || $apiKey === 'YOUR_SENDGRID_API_KEY_HERE') {
        return false;
    }

    $url = 'https://api.sendgrid.com/v3/mail/send';
    $headers = [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey,
    ];

    $data = [
        'personalizations' => [
            [
                'to' => [
                    [
                        'email' => $toEmail,
                    ],
                ],
                'subject' => $subject,
            ],
        ],
        'content' => [
            [
                'type' => 'text/html',
                'value' => $content,
            ],
        ],
        'from' => [
            'email' => 'safe@minerskladno.cz',
            'name' => 'SAFE Miners Kladno',
        ],
    ];

    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if (curl_errno($ch)) {
        curl_close($ch);
        return false;
    }

    curl_close($ch);

    return $httpCode === 202;
}
