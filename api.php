<?php

include_once 'config.php';

function getBotReply($message, $session_id, $conn) {

    $apiKey = "YOUR_API_KEY";

    // LOAD PREVIOUS CHATS

    $chatHistory = [];

   $result = $conn->query("
    SELECT user_message, bot_reply
    FROM chats
    WHERE session_id=$session_id
    ORDER BY id DESC
    LIMIT 6
    ");

    $rows = [];

    while($row = $result->fetch_assoc()){

        $rows[] = $row;
    }

$rows = array_reverse($rows);

    // SYSTEM PROMPT

    $chatHistory[] = [
        "role" => "system",
        "content" => "You are a professional AI assistant like ChatGPT. Give clean, elegant, and interactive responses. Continue conversations naturally using previous chat context. Give simple explanations, little paragraphs, and bullet points where suitable."
    ];

    // PREVIOUS MESSAGES

    foreach($rows as $row) {

        $chatHistory[] = [
            "role" => "user",
            "content" => $row['user_message']
        ];

        $chatHistory[] = [
            "role" => "assistant",
            "content" => $row['bot_reply']
        ];
    }

    // CURRENT MESSAGE

    $chatHistory[] = [
        "role" => "user",
        "content" => $message
    ];

    // API DATA

    $data = [
        "model" => "llama-3.3-70b-versatile",
        "messages" => $chatHistory
    ];

    // CURL

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, "https://api.groq.com/openai/v1/chat/completions");

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    curl_setopt($ch, CURLOPT_POST, true);

    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer " . $apiKey,
        "Content-Type: application/json"
    ]);

    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    $response = curl_exec($ch);

    curl_close($ch);

    $result = json_decode($response, true);

    return trim($result['choices'][0]['message']['content'] ?? "No response");
}

?>