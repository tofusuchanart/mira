<?php

/**
 * MIRA Live Support System - Stable Version
 * ปรับปรุงระบบ Emoji ใหม่เพื่อแก้ปัญหา ReferenceError
 */
session_start();
require_once "../../config.php";

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    header("Location: ../login.php");
    exit;
}
$fullname = $_SESSION['fullname'] ?? '';
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MIRA | Live Support</title>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600&display=swap" rel="stylesheet">
    <link href="../../bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <style>
        :root {
            --mira-pink: #f8a5c2;
            --mira-dark-pink: #b3365b;
            --mira-bg: #fff0f5;
        }

        body {
            background-color: var(--mira-bg);
            font-family: 'Sarabun', sans-serif;
            height: 100vh;
            display: flex;
            align-items: center;
            overflow: hidden;
            margin: 0;
        }

        .chat-container {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(20px);
            border-radius: 30px;
            height: 90vh;
            width: 95%;
            max-width: 1200px;
            margin: auto;
            display: flex;
            box-shadow: 0 20px 50px rgba(179, 54, 91, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.5);
        }

        .chat-sidebar {
            width: 300px;
            border-right: 1px solid rgba(179, 54, 91, 0.1);
            padding: 25px;
            display: flex;
            flex-direction: column;
        }

        .topic-card {
            background: white;
            padding: 12px 18px;
            border-radius: 15px;
            margin-bottom: 12px;
            cursor: pointer;
            transition: 0.3s;
            border: 1px solid transparent;
            font-size: 0.9rem;
        }

        .topic-card.active {
            background: var(--mira-bg);
            border-color: var(--mira-pink);
            color: var(--mira-dark-pink);
            font-weight: 600;
        }

        .chat-main {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        #chat-messages {
            flex-grow: 1;
            padding: 25px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .bubble {
            max-width: 70%;
            padding: 12px 16px;
            border-radius: 18px;
            font-size: 0.95rem;
            word-wrap: break-word;
        }

        .bubble.me {
            align-self: flex-end;
            background: linear-gradient(135deg, #f8a5c2, #b3365b);
            color: white;
            border-bottom-right-radius: 4px;
        }

        .bubble.admin {
            align-self: flex-start;
            background: white;
            border: 1px solid #eee;
            border-bottom-left-radius: 4px;
        }

        .chat-input-area {
            padding: 20px;
            background: white;
            border-bottom-right-radius: 30px;
        }

        .input-wrapper {
            background: var(--mira-bg);
            border-radius: 30px;
            padding: 5px 15px;
            display: flex;
            align-items: center;
        }

        .chat-input {
            border: none;
            background: transparent;
            flex-grow: 1;
            padding: 10px;
            outline: none;
        }

        .btn-icon {
            font-size: 1.3rem;
            color: var(--mira-dark-pink);
            cursor: pointer;
            margin: 0 8px;
        }

        .btn-send {
            background: var(--mira-dark-pink);
            color: white;
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-left: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        #file-preview-container {
            display: none;
            padding: 10px 25px;
            background: #fff;
            border-top: 1px solid #eee;
        }

        .preview-box img {
            max-height: 80px;
            border-radius: 10px;
        }

        /* ปรับแต่งหน้าตา Emoji Picker ใหม่ */
        .fg-emoji-container {
            z-index: 9999 !important;
            border-radius: 15px !important;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1) !important;
        }
    </style>
</head>

<body>
    <div class="chat-container">
        <div class="chat-sidebar">
            <h5 class="fw-bold mb-4" style="color: var(--mira-dark-pink);">MIRA Support</h5>
            <div class="topic-card active" onclick="selectTopic('สอบถามเกี่ยวกับสินค้า', this)">สินค้าและสต็อก</div>
            <div class="topic-card" onclick="selectTopic('ปัญหาการชำระเงิน', this)">การชำระเงิน</div>
            <div class="topic-card" onclick="selectTopic('ติดตามสถานะพัสดุ', this)">ติดตามพัสดุ</div>
            <div class="topic-card" onclick="selectTopic('อื่นๆ', this)">เรื่องอื่นๆ</div>
            <div class="mt-auto"><a href="../../users/index_users.php" class="text-decoration-none text-muted small"><i class="bi bi-arrow-left"></i> กลับหน้าหลัก</a></div>
        </div>

        <div class="chat-main">
            <div class="p-3 bg-white border-bottom fw-bold" id="topic-title">สอบถามเกี่ยวกับสินค้า</div>
            <div id="chat-messages"></div>
            <div id="file-preview-container">
                <div class="preview-box position-relative d-inline-block">
                    <div id="preview-content"></div>
                    <button class="btn btn-danger btn-sm position-absolute top-0 end-0 rounded-circle" onclick="clearFile()" style="padding: 0 5px; transform: translate(50%, -50%);">&times;</button>
                </div>
            </div>

            <div class="chat-input-area">
                <div class="input-wrapper">
                    <i class="bi bi-emoji-smile btn-icon" id="emoji-btn"></i>
                    <label for="file-upload" class="mb-0"><i class="bi bi-image btn-icon"></i></label>
                    <input type="file" id="file-upload" hidden accept="image/*">
                    <input type="text" id="messageInput" class="chat-input" placeholder="พิมพ์ข้อความ...">
                    <button class="btn-send" onclick="sendMessage()"><i class="bi bi-send-fill"></i></button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/fg-emoji-picker@1.0.1/fgEmojiPicker.min.js"></script>

    <script>
        var currentTopic = 'สอบถามเกี่ยวกับสินค้า';

        $(document).ready(function() {
            // --- ตั้งค่า Emoji Picker ตัวใหม่ ---
            try {
                new FGEmojiPicker({
                    trigger: ['#emoji-btn'],
                    removeOnSelection: false,
                    closeByClickingOutside: true,
                    insertInto: document.querySelector('#messageInput'),
                    directory: 'https://cdn.jsdelivr.net/npm/fg-emoji-picker@1.0.1/full-emoji-list.json'
                });
                console.log("Emoji System Ready (Alternative)");
            } catch (e) {
                console.error("Emoji Picker Error:", e);
            }

            // ระบบแชทปกติ
            loadMessages();
            setInterval(loadMessages, 5000);

            $('#messageInput').on('keypress', function(e) {
                if (e.which == 13) sendMessage();
            });

            $('#file-upload').on('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(event) {
                        $('#file-preview-container').show();
                        $('#preview-content').html(`<img src="${event.target.result}" class="img-fluid">`);
                    };
                    reader.readAsDataURL(file);
                }
            });
        });

        function selectTopic(topic, el) {
            currentTopic = topic;
            $('#topic-title').text(topic);
            $('.topic-card').removeClass('active');
            $(el).addClass('active');
            loadMessages();
        }

        function loadMessages() {
            $.get('fetch_messages.php', {
                topic: currentTopic
            }, function(response) {
                try {
                    const res = JSON.parse(response);
                    $('#chat-messages').html(res.html);
                    var chat = document.getElementById('chat-messages');
                    chat.scrollTop = chat.scrollHeight;
                } catch (e) {}
            });
        }

        function sendMessage() {
            const msg = $('#messageInput').val().trim();
            const file = document.getElementById('file-upload').files[0];
            if (!msg && !file) return;

            const formData = new FormData();
            formData.append('subject', currentTopic);
            formData.append('message', msg);
            if (file) formData.append('chat_file', file);

            $.ajax({
                url: 'send_message_ajax.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function() {
                    $('#messageInput').val('');
                    clearFile();
                    loadMessages();
                }
            });
        }

        function clearFile() {
            $('#file-upload').val('');
            $('#file-preview-container').hide();
        }
    </script>
</body>

</html>