<?php

// Açıklayıcı sabit ile API anahtarını tanımla
const OPENAI_API_KEY = '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'; // ← Buraya kendi API anahtarını yaz

/**
 * OpenAI API üzerinden chat completion isteği gönderir.
 *
 * @param string $systemMessage  Sistem mesajı
 * @param string $userMessage    Kullanıcının prompt'u
 * @param int    $maxTokens      Maksimum token sayısı
 * @param float  $temperature    Yanıtın özgünlüğü (0.0-1.0)
 * @return string                OpenAI API yanıtı (JSON)
 */
function callOpenAI($systemMessage, $userMessage, $maxTokens = 2, $temperature = 0.0)
{
    $apiUrl = 'https://api.openai.com/v1/chat/completions';

    $data = [
        'model' => 'gpt-4o',
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
                "Authorization: Bearer " . OPENAI_API_KEY . "\r\n",
            'method'  => 'POST',
            'content' => json_encode($data),
            'ignore_errors' => true
        ]
    ];

    $context  = stream_context_create($options);
    $response = file_get_contents($apiUrl, false, $context);

    return $response;
}



