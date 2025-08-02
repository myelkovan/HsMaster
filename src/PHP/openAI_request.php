<?php

require_once "utils/secrets.php";


/**
 * OpenAI API üzerinden chat completion isteği gönderir.
 *
 * @param string $systemMessage  Sistem mesajı
 * @param string $userMessage    Kullanıcının prompt'u
 * @param int    $maxTokens      Maksimum token sayısı
 * @param float  $temperature    Yanıtın özgünlüğü (0.0-1.0)
 * @return string                OpenAI API yanıtı (JSON)
 */
function callOpenAI($systemMessage, $userMessage, $maxTokens = 2, $temperature = 0.0, $model = "gpt-4o")
{

    global $openAISecretKey;

    $apiUrl = 'https://api.openai.com/v1/chat/completions';

    $data = [
        'model' =>      $model,   //'gpt-4-turbo',  // ürün yasaklı mı kontrollerinde bunu kullanınca chatgpt ile aynı sonuçları alıyoruz. ama iki kat daha pahalı ve biraz daha yavaş.
        'messages' => [
            ['role' => 'system', 'content' => $systemMessage],
            ['role' => 'user', 'content' => $userMessage]
        ],
        'temperature' => $temperature,
        'max_tokens' => $maxTokens,
    ];

    $options = [
        'http' => [
            'header'  => "Content-type: application/json\r\n" .
                "Authorization: Bearer " . $openAISecretKey . "\r\n",
            'method'  => 'POST',
            'content' => json_encode($data),
            'ignore_errors' => true
        ]
    ];

    $context  = stream_context_create($options);
    $response = file_get_contents($apiUrl, false, $context);

    return $response;
}
