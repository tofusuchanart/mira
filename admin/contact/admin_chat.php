<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MIRA Admin - Help Center</title>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        :root {
            --primary-pink: #f8e1e7;
            --dark-pink: #d17a8e;
            --accent-pink: #9c3353;
            --bg-light: #fdf8f9;
            --text-main: #5a5a5a;
        }

        body {
            font-family: 'Kanit', sans-serif;
            background-color: var(--primary-pink);
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .main-container {
            width: 90%;
            max-width: 1100px;
            height: 85vh;
            background: white;
            border-radius: 25px;
            display: flex;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }

        /* --- Sidebar --- */
        .sidebar {
            width: 300px;
            background-color: var(--bg-light);
            border-right: 1px solid #eee;
            display: flex;
            flex-direction: column;
        }

        .back-header {
            padding: 15px 20px;
        }

        .back-dashboard {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            color: #8e8e8e;
            font-size: 14px;
            transition: 0.2s;
        }

        .back-dashboard:hover {
            color: var(--accent-pink);
            transform: translateX(-3px);
        }

        .sidebar-header {
            padding: 15px 20px 25px;
            color: var(--accent-pink);
        }

        .sidebar-header h2 {
            margin: 0;
            font-size: 24px;
        }

        .sidebar-header p {
            font-size: 13px;
            color: #aaa;
            margin: 5px 0 0;
        }

        .chat-list {
            flex-grow: 1;
            overflow-y: auto;
        }

        .chat-item {
            padding: 15px 20px;
            display: flex;
            align-items: center;
            cursor: pointer;
            transition: 0.3s;
            border-left: 4px solid transparent;
            position: relative;
        }

        .chat-item:hover {
            background-color: #fff;
        }

        .chat-item.active {
            background-color: #fff;
            border-left-color: var(--accent-pink);
        }

        .user-img {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            margin-right: 12px;
            object-fit: cover;
            border: 2px solid white;
        }

        .user-info h4 {
            margin: 0;
            font-size: 15px;
            color: var(--text-main);
        }

        .user-info p {
            margin: 2px 0 0;
            font-size: 12px;
            color: #999;
        }

        /* --- Chat Window --- */
        .chat-window {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            background: white;
        }

        .chat-header {
            padding: 20px;
            border-bottom: 1px solid #f9f9f9;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .status-dot {
            color: #2ecc71;
            font-size: 10px;
            margin-right: 5px;
        }

        .chat-messages {
            flex-grow: 1;
            padding: 25px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            background-image: radial-gradient(#f8e1e7 0.5px, transparent 0.5px);
            background-size: 15px 15px;
        }

        .msg {
            margin-bottom: 15px;
            max-width: 70%;
            padding: 12px 18px;
            border-radius: 18px;
            font-size: 14px;
            position: relative;
        }

        .msg-user {
            background: #f1f1f1;
            align-self: flex-start;
            border-bottom-left-radius: 4px;
            color: #444;
        }

        .msg-admin {
            background: var(--accent-pink);
            color: white;
            align-self: flex-end;
            border-bottom-right-radius: 4px;
        }

        .badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background-color: #ff4d4d;
            color: white;
            font-size: 10px;
            padding: 2px 6px;
            border-radius: 10px;
            border: 2px solid white;
        }

        /* --- Input Area --- */
        .input-area {
            padding: 20px;
            border-top: 1px solid #f9f9f9;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .input-wrapper {
            flex-grow: 1;
            background: #f7f7f7;
            border-radius: 30px;
            padding: 5px 15px;
            display: flex;
            align-items: center;
        }

        .input-wrapper input {
            border: none;
            background: transparent;
            padding: 10px;
            width: 100%;
            outline: none;
            font-family: 'Kanit', sans-serif;
        }

        .btn-icon {
            color: #d17a8e;
            cursor: pointer;
            font-size: 20px;
            transition: 0.2s;
        }

        .btn-icon:hover {
            color: var(--accent-pink);
        }

        .btn-send {
            background: var(--accent-pink);
            color: white;
            width: 45px;
            height: 45px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 10px rgba(156, 51, 83, 0.3);
        }
    </style>
</head>

<body>

    <div class="main-container">
        <div class="sidebar">
            <div class="back-header">
                <a href="../index_ad.php" class="back-dashboard">
                    <i class="fas fa-arrow-left"></i>
                    <span>กลับสู่หน้า Dashboard</span>
                </a>
            </div>
            <div class="sidebar-header">
                <h2>Help Center</h2>
                <p>เลือกแชทลูกค้าเพื่อตอบกลับ</p>
            </div>
            <div class="chat-list" id="chatList">
            </div>
        </div>

        <div class="chat-window">
            <div class="chat-header">
                <div>
                    <span id="currentUserName" style="font-weight: 500;">เลือกการสนทนา</span><br>
                    <small><i class="fas fa-circle status-dot"></i> Admin Online</small>
                </div>
            </div>

            <div class="chat-messages" id="messageContainer">
                <div style="text-align: center; color: #ccc; margin-top: 50px;">ยังไม่มีการเลือกแชท</div>
            </div>

            <div id="preview-container" style="display: none; padding: 10px 20px; background: #fff; border-top: 1px solid #f9f9f9;">
                <div style="position: relative; display: inline-block;">
                    <img id="image-preview" src="" style="max-height: 100px; border-radius: 10px; border: 2px solid var(--primary-pink);">
                    <button onclick="clearPreview()" style="position: absolute; top: -10px; right: -10px; background: var(--accent-pink); color: white; border: none; border-radius: 50%; width: 25px; height: 25px; cursor: pointer;">
                        <i class="fas fa-times" style="font-size: 12px;"></i>
                    </button>
                </div>
            </div>

            <div class="input-area">
                <i class="far fa-smile btn-icon" id="emoji-trigger" title="Emoji"></i>
                <label for="file-upload">
                    <i class="fas fa-paperclip btn-icon" title="แนบไฟล์รูปภาพ"></i>
                </label>
                <input type="file" id="file-upload" hidden accept="image/*">

                <div class="input-wrapper">
                    <input type="text" id="adminReply" placeholder="พิมพ์ข้อความของคุณที่นี่..." onkeypress="if(event.key === 'Enter') sendMessage()">
                </div>

                <button class="btn-send" onclick="sendMessage()">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
        </div>
    </div>

    <audio id="notif-sound" src="notify.mp3" preload="auto"></audio>

    <script src="https://cdn.jsdelivr.net/npm/@joeattardi/emoji-button@4.6.4/dist/index.min.js"></script>
    <script>
        let currentUserId = null;
        let lastTotalUnread = 0;

        // --- 1. Emoji Setup ---
        const picker = new EmojiButton({
            position: 'top-start'
        });
        const emojiBtn = document.getElementById('emoji-trigger');
        const inputField = document.getElementById('adminReply');

        emojiBtn.addEventListener('click', () => picker.togglePicker(emojiBtn));
        picker.on('emoji', selection => {
            inputField.value += selection.emoji;
            inputField.focus();
        });

        // --- 2. Load Chat List ---
        function loadChatList() {
            fetch('get_chat_list.php')
                .then(res => res.text())
                .then(data => {
                    document.getElementById('chatList').innerHTML = data;

                    // แจ้งเตือนเสียงเมื่อมีข้อความใหม่
                    let currentUnread = 0;
                    document.querySelectorAll('.badge').forEach(b => currentUnread += parseInt(b.innerText) || 0);
                    if (currentUnread > lastTotalUnread) {
                        document.getElementById('notif-sound').play().catch(() => {});
                    }
                    lastTotalUnread = currentUnread;
                });
        }

        // --- 3. Load Messages ---
        function loadMessages(userId, userName) {
            currentUserId = userId;
            document.getElementById('currentUserName').innerText = userName;

            // Highlight active chat
            document.querySelectorAll('.chat-item').forEach(item => item.classList.remove('active'));
            const activeItem = event?.currentTarget;
            if (activeItem) activeItem.classList.add('active');

            fetch('get_messages.php?user_id=' + userId)
                .then(res => res.text())
                .then(data => {
                    const container = document.getElementById('messageContainer');
                    container.innerHTML = data;
                    container.scrollTop = container.scrollHeight;
                });
        }

        // --- 4. Send Message ---
        function sendMessage() {
            const input = document.getElementById('adminReply');
            const fileInput = document.getElementById('file-upload');
            const msg = input.value.trim();

            if (!currentUserId) {
                alert("กรุณาเลือกแชทก่อนส่งข้อความค่ะ");
                return;
            }

            if (msg !== "" || fileInput.files.length > 0) {
                const formData = new FormData();
                formData.append('user_id', currentUserId);
                formData.append('reply', msg);
                if (fileInput.files.length > 0) formData.append('chat_file', fileInput.files[0]);

                fetch('send_reply.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(() => {
                        input.value = "";
                        clearPreview();
                        loadMessages(currentUserId, document.getElementById('currentUserName').innerText);
                    });
            }
        }

        // --- 5. Image Preview ---
        document.getElementById('file-upload').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file && file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('image-preview').src = e.target.result;
                    document.getElementById('preview-container').style.display = 'block';
                }
                reader.readAsDataURL(file);
            }
        });

        function clearPreview() {
            document.getElementById('file-upload').value = '';
            document.getElementById('preview-container').style.display = 'none';
        }

        // --- 6. Intervals ---
        setInterval(loadChatList, 3000);
        setInterval(() => {
            if (currentUserId) {
                loadMessages(currentUserId, document.getElementById('currentUserName').innerText);
            }
        }, 3000);

        window.onload = loadChatList;
    </script>
</body>

</html>