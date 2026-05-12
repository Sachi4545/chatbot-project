<?php

include_once 'config.php';
include_once 'api.php';

session_start();

$session_id = $_SESSION['session_id'];

// GET MESSAGE

$message = trim($_POST['message'] ?? '');

if($message == ''){
    exit("Empty message");
}

// GET BOT REPLY

$reply = getBotReply($message, $session_id, $conn);

// SAVE TO DATABASE

$stmt = $conn->prepare("
INSERT INTO chats(session_id, user_message, bot_reply)
VALUES (?, ?, ?)
");

$stmt->bind_param("iss", $session_id, $message, $reply);

$stmt->execute();

// RETURN RESPONSE

echo nl2br(htmlspecialchars($reply));

?>