<?php

include_once 'config.php';

session_start();

// CREATE SESSION

if (!isset($_SESSION['session_id'])) {

    $conn->query("INSERT INTO chat_sessions (session_name) VALUES ('New Chat')");

    $_SESSION['session_id'] = $conn->insert_id;
}

$session_id = $_SESSION['session_id'];

// LOAD CHATS

$messages = $conn->query("
SELECT * FROM chats
WHERE session_id=$session_id
ORDER BY id ASC
");

// LOAD SESSIONS

$sessions = $conn->query("
SELECT * FROM chat_sessions
ORDER BY id DESC
");

?>

<!DOCTYPE html>
<html>

<head>

    <title>AI Chatbot</title>

<style>

body{
    margin:0;
    font-family:Arial, sans-serif;
    background:#0f172a;
}

/* MAIN LAYOUT */

.container{
    display:flex;
    height:100vh;
}

/* SIDEBAR */

.sidebar{
    width:260px;
    background:#111827;
    color:white;
    padding:20px;
    overflow-y:auto;
}

.sidebar h2{
    margin-top:0;
}

.chat-history{
    background:#1f2937;
    padding:12px;
    border-radius:12px;
    margin-bottom:10px;
    cursor:pointer;
    transition:0.3s;
}

.chat-history:hover{
    background:#374151;
}

/* MAIN CHAT AREA */

.main{
    flex:1;
    display:flex;
    flex-direction:column;
}

/* CHAT AREA */

.chat-box{
    flex:1;
    overflow-y:auto;
    padding:10px;
    display:flex;
    flex-direction:column;
}

/* MESSAGE */

.msg{
    max-width:70%;
    padding:16px 20px;
    margin:4px 0;
    border-radius:22px;
    line-height:1.7;
    font-size:15px;
    word-wrap:break-word;
}

/* USER */

.user{
    background:#2563eb;
    color:white;
    align-self:flex-end;
    border-bottom-right-radius:8px;
}

/* BOT */

.bot{
    background:#1f2937;
    color:white;
    align-self:flex-start;
    border-bottom-left-radius:8px;
}

/* INPUT AREA */

.input-area{
    display:flex;
    padding:18px;
    background:#111827;
}

/* INPUT */

input{
    flex:1;
    padding:14px;
    border:none;
    border-radius:14px;
    font-size:16px;
    outline:none;
}

/* BUTTON */

button{
    margin-left:10px;
    padding:14px 22px;
    background:#22c55e;
    border:none;
    color:white;
    border-radius:14px;
    cursor:pointer;
    font-size:16px;
}

/* SCROLLBAR */

::-webkit-scrollbar{
    width:6px;
}

::-webkit-scrollbar-thumb{
    background:#475569;
    border-radius:10px;
}

</style>

</head>

<body>

<div class="container">

    <!-- SIDEBAR -->

    <div class="sidebar">

        <h2>🤖 AI Chatbot</h2>

        <?php while($s = $sessions->fetch_assoc()) { ?>

            <div class="chat-history">
                💬 Chat <?php echo $s['id']; ?>
            </div>

        <?php } ?>

    </div>

    <!-- MAIN CHAT -->

    <div class="main">

        <div class="chat-box" id="chat-box">

            <?php while($row = $messages->fetch_assoc()) { ?>

                <div class="msg user">
                    <?php echo htmlspecialchars($row['user_message']); ?>
                </div>

                <div class="msg bot">
                    <?php echo nl2br(htmlspecialchars($row['bot_reply'])); ?>
                </div>

            <?php } ?>

        </div>

        <!-- INPUT -->

        <div class="input-area">

            <input 
                type="text"
                id="message"
                placeholder="Type your message..."
                onkeypress="handleEnter(event)"
            >

            <button onclick="sendMessage()">
                Send
            </button>

        </div>

    </div>

</div>

<script>

// FORMAT BOT RESPONSE

function formatReply(text)
{
    return text

        // remove ** bold markdown
        .replace(/\*\*/g,"")

        // remove single *
        .replace(/\*/g,"")

        // line breaks
        .replace(/\n/g,"<br><br>");
}

// ENTER KEY

function handleEnter(event){

    if(event.key === "Enter"){

        sendMessage();
    }
}

// SEND MESSAGE

function sendMessage(){

    let msgInput = document.getElementById("message");

    let msg = msgInput.value.trim();

    if(msg === "") return;

    let chatBox = document.getElementById("chat-box");

    // USER MESSAGE

    chatBox.innerHTML += `
        <div class="msg user">
            ${msg}
        </div>
    `;

    // CLEAR INPUT

    msgInput.value = "";

    // TYPING EFFECT

    let typingDiv = document.createElement("div");

    typingDiv.className = "msg bot";

    typingDiv.innerHTML = "Typing...";

    chatBox.appendChild(typingDiv);

    chatBox.scrollTop = chatBox.scrollHeight;

    // SEND TO PHP

    fetch("/php/send.php", {

        method:"POST",

        headers:{
            "Content-Type":"application/x-www-form-urlencoded"
        },

        body:"message=" + encodeURIComponent(msg)
    })

    .then(response => response.text())

    .then(data => {

        typingDiv.remove();

        chatBox.innerHTML += `
            <div class="msg bot">
                ${formatReply(data)}
            </div>
        `;

        chatBox.scrollTop = chatBox.scrollHeight;
    })

    .catch(error => {

        typingDiv.remove();

        chatBox.innerHTML += `
            <div class="msg bot">
                Error loading response
            </div>
        `;

        console.log(error);
    });
}

</script>

</body>
</html>