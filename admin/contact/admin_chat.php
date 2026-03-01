<?php

/**
 * MIRA - Admin Chat / Help Center (Final Version)
 * ไฟล์นี้ออกแบบมาให้ทำงานร่วมกับตาราง contact_messages และ users ใน mira_db
 * รองรับการตอบกลับแบบ Ticket-based Chat
 */
session_start();
require '../../config.php'; // ตรวจสอบ Path การเชื่อมต่อฐานข้อมูลของคุณ

// ตรวจสอบสิทธิ์ (ถ้ามี)
// if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
//     header("Location: ../login.php");
//     exit;
// }
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MIRA Admin - Help Center (System Ready)</title>

    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@200;300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        /* บังคับหน้าต่าง Emoji ให้อยู่บนสุด และมีขนาดที่มองเห็นชัด */
        .fg-emoji-picker {
            z-index: 99999 !important;
            /* บังคับให้อยู่บนสุดเหนือทุกอย่าง */
            position: absolute !important;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2) !important;
        }

        /* ปรับให้ช่องพิมพ์ขยายตามข้อความ และรองรับ Emoji */
        #adminReply {
            font-size: 16px !important;
            /* ขนาดที่เหมาะสมสำหรับ Emoji */
        }

        :root {
            --primary-pink: #f8e1e7;
            --dark-pink: #d17a8e;
            --accent-pink: #9c3353;
            --bg-light: #fdf8f9;
            --text-main: #5a5a5a;
            --shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            --transition: all 0.3s ease;
        }

        /* --- Scrollbar Customization --- */
        ::-webkit-scrollbar {
            width: 6px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: #ecd0d8;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--dark-pink);
        }

        body {
            font-family: 'Kanit', sans-serif;
            background-color: var(--primary-pink);
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            color: var(--text-main);
        }

        .main-container {
            width: 95%;
            max-width: 1200px;
            height: 90vh;
            background: white;
            border-radius: 30px;
            display: flex;
            box-shadow: var(--shadow);
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        /* --- Sidebar Style --- */
        .sidebar {
            width: 350px;
            background-color: var(--bg-light);
            border-right: 1px solid #f0f0f0;
            display: flex;
            flex-direction: column;
        }

        .back-header {
            padding: 20px;
        }

        .back-dashboard {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            color: #8e8e8e;
            font-size: 14px;
            font-weight: 400;
            transition: var(--transition);
        }

        .back-dashboard:hover {
            color: var(--accent-pink);
            transform: translateX(-5px);
        }

        .sidebar-header {
            padding: 10px 25px 25px;
            border-bottom: 1px solid #f0f0f0;
        }

        .sidebar-header h2 {
            margin: 0;
            font-size: 26px;
            font-weight: 600;
            color: var(--accent-pink);
            letter-spacing: -0.5px;
        }

        .sidebar-header p {
            font-size: 13px;
            color: #999;
            margin: 5px 0 0;
        }

        .chat-list {
            flex-grow: 1;
            overflow-y: auto;
            background: #fff;
        }

        .chat-item {
            padding: 18px 20px;
            display: flex;
            align-items: center;
            cursor: pointer;
            transition: var(--transition);
            border-left: 5px solid transparent;
            border-bottom: 1px solid #fafafa;
            position: relative;
        }

        .chat-item:hover {
            background-color: var(--bg-light);
        }

        .chat-item.active {
            background-color: #fff6f8;
            border-left-color: var(--accent-pink);
        }

        .user-img-wrapper {
            position: relative;
        }

        .user-img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            margin-right: 15px;
            object-fit: cover;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
        }

        .user-info {
            flex-grow: 1;
            overflow: hidden;
        }

        .user-info h4 {
            margin: 0;
            font-size: 15px;
            color: #333;
            display: flex;
            justify-content: space-between;
        }

        .user-info p {
            margin: 4px 0 0;
            font-size: 12px;
            color: #888;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .badge {
            background-color: #ff4d4d;
            color: white;
            font-size: 11px;
            padding: 3px 7px;
            border-radius: 20px;
            font-weight: 500;
            min-width: 18px;
            text-align: center;
        }

        /* --- Chat Window Style --- */
        .chat-window {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            background: #fff;
        }

        .chat-header {
            padding: 20px 30px;
            border-bottom: 1px solid #f5f5f5;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #fff;
        }

        .header-user-info h3 {
            margin: 0;
            font-size: 18px;
            font-weight: 500;
        }

        .status-online {
            color: #2ecc71;
            font-size: 12px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .chat-messages {
            flex-grow: 1;
            padding: 30px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            background-color: #fcfcfc;
            background-image: radial-gradient(var(--primary-pink) 0.5px, transparent 0.5px);
            background-size: 20px 20px;
        }

        /* --- Bubble Style --- */
        .msg-group {
            display: flex;
            flex-direction: column;
            margin-bottom: 20px;
        }

        .msg {
            max-width: 65%;
            padding: 14px 20px;
            border-radius: 20px;
            font-size: 14px;
            line-height: 1.5;
            position: relative;
            margin-bottom: 5px;
            word-wrap: break-word;
        }

        .msg-user {
            background: #ffffff;
            align-self: flex-start;
            border-bottom-left-radius: 5px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
            color: #444;
            border: 1px solid #f0f0f0;
        }

        .msg-admin {
            background: var(--accent-pink);
            color: white;
            align-self: flex-end;
            border-bottom-right-radius: 5px;
            box-shadow: 0 4px 15px rgba(156, 51, 83, 0.2);
        }

        .msg-time {
            font-size: 10px;
            margin-top: 4px;
            opacity: 0.7;
        }

        .msg-user .msg-time {
            color: #999;
        }

        .msg-admin .msg-time {
            color: #eee;
            text-align: right;
        }

        .chat-img {
            max-width: 100%;
            border-radius: 12px;
            margin-top: 8px;
            cursor: pointer;
            transition: 0.2s;
        }

        .chat-img:hover {
            opacity: 0.9;
        }

        /* --- Input Area --- */
        .input-area {
            padding: 25px 30px;
            border-top: 1px solid #f5f5f5;
            display: flex;
            align-items: center;
            gap: 15px;
            background: #fff;
        }

        .input-wrapper {
            flex-grow: 1;
            background: #f8f9fb;
            border-radius: 30px;
            padding: 5px 20px;
            display: flex;
            align-items: center;
            border: 1px solid #eee;
        }

        .input-wrapper input {
            border: none;
            background: transparent;
            padding: 12px;
            width: 100%;
            outline: none;
            font-family: 'Kanit', sans-serif;
            font-size: 14px;
        }

        .action-btn {
            color: var(--dark-pink);
            cursor: pointer;
            font-size: 20px;
            transition: var(--transition);
            background: none;
            border: none;
        }

        .action-btn:hover {
            color: var(--accent-pink);
            transform: scale(1.1);
        }

        .btn-send {
            background: var(--accent-pink);
            color: white;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(156, 51, 83, 0.3);
            transition: var(--transition);
        }

        .btn-send:hover {
            transform: translateY(-2px) rotate(-10deg);
            background: #862a46;
        }

        #preview-container {
            display: none;
            padding: 15px 30px;
            background: #fff;
            border-top: 1px solid #f5f5f5;
        }

        .preview-box {
            position: relative;
            display: inline-block;
        }

        .preview-box img {
            height: 80px;
            border-radius: 10px;
            border: 2px solid var(--primary-pink);
        }

        .close-preview {
            position: absolute;
            top: -10px;
            right: -10px;
            background: var(--accent-pink);
            color: white;
            border: none;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            cursor: pointer;
            font-size: 12px;
        }

        .empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: #ccc;
        }

        .empty-state i {
            font-size: 80px;
            margin-bottom: 20px;
            color: var(--primary-pink);
        }
    </style>
</head>

<body>

    <div class="main-container">
        <div class="sidebar">
            <div class="back-header">
                <a href="../index_ad.php" class="back-dashboard">
                    <i class="fas fa-chevron-left"></i>
                    <span>Dashboard</span>
                </a>
            </div>
            <div class="sidebar-header">
                <h2>Message</h2>
                <p>Support Ticket Center</p>
            </div>

            <div class="chat-list" id="chatList">
                <div style="text-align:center; padding:50px; color:#ddd;">
                    <i class="fas fa-spinner fa-spin"></i>
                </div>
            </div>
        </div>

        <div class="chat-window">
            <div id="chatDefaultView" class="empty-state">
                <i class="fas fa-comments"></i>
                <p>เลือกการสนทนาเพื่อเริ่มต้นตอบกลับลูกค้า</p>
            </div>

            <div id="chatActiveView" style="display: none; flex-direction: column; height: 100%;">
                <div class="chat-header">
                    <div class="header-user-info">
                        <h3 id="currentUserName">ชื่อลูกค้า</h3>
                        <span class="status-online"><i class="fas fa-circle" style="font-size: 8px;"></i> กำลังเชื่อมต่อ</span>
                    </div>
                    <div class="header-actions">
                        <button class="action-btn" title="รีเฟรช" onclick="refreshCurrentChat()"><i class="fas fa-sync-alt"></i></button>
                    </div>
                </div>

                <div class="chat-messages" id="messageContainer">
                </div>

                <div id="preview-container">
                    <div class="preview-box">
                        <img id="image-preview" src="">
                        <button onclick="clearPreview()" class="close-preview"><i class="fas fa-times"></i></button>
                    </div>
                </div>

                <div class="input-area">
                    <button class="action-btn" id="emoji-trigger"><i class="far fa-smile"></i></button>

                    <label for="file-upload" class="action-btn">
                        <i class="fas fa-image"></i>
                    </label>
                    <input type="file" id="file-upload" hidden accept="image/*">

                    <div class="input-wrapper">
                        <input type="text" id="adminReply" placeholder="พิมพ์ข้อความตอบกลับ..." autocomplete="off">
                    </div>

                    <button class="btn-send" onclick="sendMessage()">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <button class="action-btn" id="emoji-trigger" onclick="toggleEmojiPicker(event)">
       
    </button>

    <div id="simple-emoji-picker" style="
    display: none; 
    position: absolute; 
    bottom: 150px;           /* อยู่เหนือ input-area */
    left: 50%;              /* ย้ายมาที่กึ่งกลางจอ */
    transform: translateX(-50%); /* ดันกลับมาครึ่งหนึ่งเพื่อให้กลางเป๊ะ */
    background: white; 
    border: 1px solid #eee; 
    border-radius: 20px; 
    padding: 15px; 
    z-index: 10000; 
    box-shadow: 0 10px 30px rgba(156, 51, 83, 0.15); /* เงาสีชมพูอ่อนๆ ให้เข้ากับธีม */
    width: 250px; 
    display: none;          /* เริ่มต้นซ่อนไว้ */
    grid-template-columns: repeat(5, 1fr); 
    gap: 10px; 
    user-select: none;
    border: 2px solid var(--primary-pink);
">
        <span onclick="addEmoji('😊')" style="cursor:pointer; font-size:24px; text-align:center; transition:0.2s;" onmouseover="this.style.transform='scale(1.2)'" onmouseout="this.style.transform='scale(1)'">😊</span>
        <span onclick="addEmoji('😂')" style="cursor:pointer; font-size:24px; text-align:center; transition:0.2s;" onmouseover="this.style.transform='scale(1.2)'" onmouseout="this.style.transform='scale(1)'">😂</span>
        <span onclick="addEmoji('😍')" style="cursor:pointer; font-size:24px; text-align:center; transition:0.2s;" onmouseover="this.style.transform='scale(1.2)'" onmouseout="this.style.transform='scale(1)'">😍</span>
        <span onclick="addEmoji('👍')" style="cursor:pointer; font-size:24px; text-align:center; transition:0.2s;" onmouseover="this.style.transform='scale(1.2)'" onmouseout="this.style.transform='scale(1)'">👍</span>
        <span onclick="addEmoji('❤️')" style="cursor:pointer; font-size:24px; text-align:center; transition:0.2s;" onmouseover="this.style.transform='scale(1.2)'" onmouseout="this.style.transform='scale(1)'">❤️</span>
        <span onclick="addEmoji('🙏')" style="cursor:pointer; font-size:24px; text-align:center; transition:0.2s;" onmouseover="this.style.transform='scale(1.2)'" onmouseout="this.style.transform='scale(1)'">🙏</span>
        <span onclick="addEmoji('🤣')" style="cursor:pointer; font-size:24px; text-align:center; transition:0.2s;" onmouseover="this.style.transform='scale(1.2)'" onmouseout="this.style.transform='scale(1)'">🤣</span>
        <span onclick="this.style.transform='scale(1.2)'" onclick="addEmoji('😭')" style="cursor:pointer; font-size:24px; text-align:center; transition:0.2s;">😭</span>
        <span onclick="addEmoji('🔥')" style="cursor:pointer; font-size:24px; text-align:center; transition:0.2s;">🔥</span>
        <span onclick="addEmoji('✨')" style="cursor:pointer; font-size:24px; text-align:center; transition:0.2s;">✨</span>
    </div>
    <audio id="notif-sound" src="notify.mp3.mp3" preload="auto"></audio>
    <script src="https://cdn.jsdelivr.net/npm/fg-emoji-picker@1.1.1/fgEmojiPicker.min.js"></script>

    <script>
        let currentUserId = null;
        let lastUnreadCount = 0;

        window.addEventListener('DOMContentLoaded', () => {
            // 1. Logic สำหรับเปิด/ปิดหน้าต่าง Emoji (ทำเองแบบ Native)
            const emojiBtn = document.getElementById('emoji-trigger');
            const picker = document.getElementById('simple-emoji-picker');

            if (emojiBtn && picker) {
                emojiBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    // สลับการแสดงผล (Toggle)
                    picker.style.display = (picker.style.display === 'none' || picker.style.display === '') ? 'grid' : 'none';
                });

                // คลิกที่อื่นให้ปิดหน้าต่าง Emoji
                document.addEventListener('click', (e) => {
                    if (!emojiBtn.contains(e.target) && !picker.contains(e.target)) {
                        picker.style.display = 'none';
                    }
                });
            }

            // 2. Logic การกด Enter เพื่อส่งข้อความ (ของเดิมที่คุณต้องการรักษาไว้)
            const inputField = document.getElementById('adminReply');
            if (inputField) {
                inputField.addEventListener('keypress', (e) => {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        sendMessage();
                    }
                });
            }
        });

        // ฟังก์ชันสำหรับจิ้ม Emoji แล้วให้ไปโผล่ในช่องพิมพ์
        function addEmoji(emoji) {
            const input = document.getElementById('adminReply');
            if (input) {
                input.value += emoji;
                input.focus();
            }
        }

        function loadChatList() {
            fetch('get_chat_list.php')
                .then(res => res.text())
                .then(data => {
                    document.getElementById('chatList').innerHTML = data;

                    // ระบบแจ้งเตือนเสียง
                    let currentTotalUnread = 0;
                    document.querySelectorAll('.badge').forEach(b => {
                        currentTotalUnread += parseInt(b.innerText) || 0;
                    });

                    if (currentTotalUnread > lastUnreadCount) {
                        document.getElementById('notif-sound').play().catch(() => {});
                    }
                    lastUnreadCount = currentTotalUnread;
                });
        }

        // 3. Load Messages
      function loadMessages(userId, userName) {
    currentUserId = userId;
    document.getElementById('chatDefaultView').style.display = 'none';
    document.getElementById('chatActiveView').style.display = 'flex';
    document.getElementById('currentUserName').innerText = userName;

    // ไฮไลท์คนที่เลือก
    document.querySelectorAll('.chat-item').forEach(item => {
        item.classList.remove('active');
        // เพิ่ม: ถ้าเป็น item ที่กดอยู่ ให้ลบ Badge ทิ้งทันทีแบบ Real-time (UI Trick)
        if(item.getAttribute('onclick').includes(userId)) {
            const badge = item.querySelector('.badge');
            if(badge) badge.remove(); 
        }
    });

    fetch(`get_messages.php?user_id=${userId}`)
        .then(res => res.text())
        .then(data => {
            const container = document.getElementById('messageContainer');
            container.innerHTML = data;
            container.scrollTop = container.scrollHeight;
            
            // --- เพิ่มส่วนนี้ ---
            // หลังจากอัปเดตสถานะใน DB ผ่าน get_messages.php แล้ว
            // ให้รีเฟรชลิสต์รายชื่อฝั่งซ้ายเพื่อให้เลข Badge หายไปจริงๆ
            loadChatList(); 
        });
}

        function refreshCurrentChat() {
            if (currentUserId) loadMessages(currentUserId, document.getElementById('currentUserName').innerText);
        }

        // 4. Send Message
        function sendMessage() {
            const input = document.getElementById('adminReply');
            const fileInput = document.getElementById('file-upload');
            const msg = input.value.trim();

            if (!currentUserId) return;
            if (msg === "" && fileInput.files.length === 0) return;

            const formData = new FormData();
            formData.append('user_id', currentUserId);
            formData.append('reply', msg);
            if (fileInput.files.length > 0) {
                formData.append('chat_file', fileInput.files[0]);
            }

            // แสดงสถานะการส่ง (UI Feedback)
            const btn = document.querySelector('.btn-send');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i>';

            fetch('send_reply.php', {
                    method: 'POST',
                    body: formData
                })
                .then(() => {
                    input.value = "";
                    clearPreview();
                    refreshCurrentChat();
                    loadChatList();
                })
                .finally(() => {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-paper-plane"></i>';
                });
        }

        // 5. Image Preview Handling
        document.getElementById('file-upload').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file && file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    document.getElementById('image-preview').src = event.target.result;
                    document.getElementById('preview-container').style.display = 'block';
                }
                reader.readAsDataURL(file);
            }
        });

        function clearPreview() {
            document.getElementById('file-upload').value = '';
            document.getElementById('preview-container').style.display = 'none';
        }

        // 6. Real-time Polling
        setInterval(loadChatList, 4000); // อัปเดตรายชื่อทุก 4 วิ
        setInterval(refreshCurrentChat, 3000); // อัปเดตข้อความในแชทที่เปิดอยู่ทุก 3 วิ

        // Initial Load
        window.onload = loadChatList;
    </script>
</body>

</html>