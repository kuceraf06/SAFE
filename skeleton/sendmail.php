<?php

function sendEmail($toEmail, $subject, $content ) {
    $url = "https://api.sendgrid.com/v3/mail/send";
    $headers = [
        "Content-Type: application/json",
        "Authorization: Bearer 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" // Use your actual SendGrid API key here
    ];

    $data = [
        "personalizations" => [
            [
                "to" => [
                    [
                        "email" => $toEmail
                    ]
                ],
                "subject" => $subject
            ]
        ],
        "content" => [
            [
                "type" => "text/html",
                "value" => $content
            ]
        ],
        "from" => [
            "email" => "safe@minerskladno.cz",
            "name" => "SAFE Miners Kladno"
        ]
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

    // Check if the response status code is 202 (SendGrid returns 202 for successful request)
    return $httpCode === 202;
}

?>