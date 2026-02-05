<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>Login Page</title>
    <style>
        /* จัดการกลุ่มตัวเลือก */
.options {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    font-size: 0.9rem;
    color: #666;
}

/* ปรับแต่ง Checkbox ให้ดูพรีเมียม */
.options input[type="checkbox"] {
    accent-color: #b3365b; /* เปลี่ยนสี Checkbox เป็นชมพู Mira */
    margin-right: 5px;
    cursor: pointer;
}

/* ปรับแต่งลิงก์ สมัครสมาชิก / ลืมรหัสผ่าน */
.options a {
    color: #b3365b;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
}

.options a:hover {
    color: #ff85a1; /* สีชมพูสว่างขึ้นเวลา Hover */
    text-decoration: underline;
}

/* ปรับแต่งลิงก์สมัครสมาชิกให้เป็นปุ่มแคปซูล */
.register-link {
    color: #b3365b !important; /* สีชมพู Mira */
    background-color: #fff2f6; /* พื้นหลังชมพูอ่อนมาก */
    padding: 6px 20px;
    border-radius: 50px;
    font-weight: 600;
    font-size: 0.9rem;
    text-decoration: none !important; /* เอาเส้นขีดใต้ออกเด็ดขาด */
    transition: all 0.3s ease;
    display: inline-block;
    border: 1px solid rgba(179, 54, 91, 0.1); /* ขอบบางๆ ให้ดูมีมิติ */
}

/* เอฟเฟกต์เวลา Hover (ไม่มีเส้นขีดใต้) */
.register-link:hover {
    background-color: #b3365b; /* เปลี่ยนพื้นหลังเป็นชมพูเข้ม */
    color: #ffffff !important; /* เปลี่ยนตัวหนังสือเป็นขาว */
    text-decoration: none !important; /* ย้ำว่าไม่ต้องมีเส้นขีดใต้ */
    box-shadow: 0 5px 15px rgba(179, 54, 91, 0.2); /* เพิ่มแสงฟุ้งสีชมพู */
    transform: translateY(-2px); /* ลอยขึ้นเล็กน้อย */
}
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, sans-serif;
        }

        body {
            height: 100vh;
            background-image: url("photo/ti.jpg");
            background-size: cover;
            background-position: center;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-container {
            width: 380px;
            padding: 50px;
            border-radius: 50px;
            background: rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(10px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            text-align: center;
            color: #fff;
        }

        .login-container h1 {
            font-size: 22px;
            margin-bottom: 10px;
        }

        .login-container p {
            margin-bottom: 20px;
            font-size: 14px;
        }

        .login-container input {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border-radius: 25px;
            border: none;
            outline: none;
            background: rgba(255,255,255,0.8);
        }

        .login-container button {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 25px;
            background: #f8a5c2;
            color: #333;
            font-weight: bold;
            cursor: pointer;
            margin-bottom: 15px;
        }

        .login-container button:hover {
    transform: translateY(-3px); /* ลอยขึ้นเล็กน้อย */
    background: linear-gradient(45deg, #f78fb3, #f78fb3);
    /* เพิ่มความฟุ้งตอนเอาเมาส์วาง */
    box-shadow: 0 0 25px rgba(255, 51, 153, 0.7), 
                0 0 10px rgba(255, 255, 255, 0.4);
}
login-container button:active {
    transform: translateY(-1px);
}









        .options {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            margin-bottom: 20px;
        }

        .options a {
            color: #fff;
            text-decoration: none;
        }

        .social-login {
            display: flex;
            gap: 10px;
        }

        .social-login button {
            flex: 1;
            background: #ffffff;
            color: #333;
        }




         body {
        background-color: var(--mira-bg);
        font-family: 'Sarabun', sans-serif;
        min-height: 100vh;
        /* ปรับ padding แทนการใช้ flex center เพื่อให้ scroll ได้สวยๆ */
        padding: 50px 0;
    }

    .logo-wrapper {
        text-align: center;
        margin-bottom: -30px; /* ให้โลโก้เกยกับแผ่น glass เล็กน้อยดูมีมิติ */
        position: relative;
        z-index: 10;
    }

   .mira-logo-top {
        width: 140px; /* เพิ่มขนาดขึ้นนิดหน่อยเพราะไม่มีขอบขาวแล้ว */
        height: auto;
        /* ใช้ drop-shadow เพื่อให้ตัวโลโก้ดูมีมิติ ลอยออกมาจากพื้นหลัง */
        filter: drop-shadow(0 8px 12px rgba(179, 54, 91, 0.25));
        
        /* ลบพื้นหลังและขอบออก */
        background: transparent; 
        padding: 0;
        border-radius: 0;
        
        transition: transform 0.4s ease;
    }

    .mira-logo-top:hover {
        /* เพิ่มลูกเล่นเวลาเอาเมาส์มาชี้ ให้โลโก้ขยับเล็กน้อย */
        transform: scale(1.05) rotate(2deg);
    }

    .glass-card {
        /* ... ของเดิม ... */
        padding-top: 60px; /* เว้นพื้นที่ด้านบนเพิ่มเพราะมีโลโก้เกยเข้ามา */
    }
    </style>
</head>
<body>
<div class="container">
    <div class="container">
    <div class="logo-wrapper">
        <img src="../photo/golo.png" alt="Mira Logo" class="mira-logo-top">
    </div>
<div class="login-container">
 
<Form method="post"action="login_db.php">
    <input type="text" placeholder="Username"name="email">
    <input type="password" placeholder="Password"name="password">
    <button>เข้าสู่ระบบ</button>
</Form>
    <div class="options">
    <label style="cursor: pointer;">
        <input type="checkbox"> จดจำฉันไว้
    </label>
    <a href="#">ลืมรหัสผ่าน?</a>
</div>

<div class="text-center mt-3">
    
    <span style="color: #fff; font-size: 0.9rem;">หากคุณยังไม่มีบัญชี?</span>
    <a href="../register/register.php" class="register-link ms-2">สมัครสมาชิก</a>
</div>
</div>
</div>



<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // ตรวจสอบ Parameter จาก URL
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('status') === 'error') {
        Swal.fire({
            icon: 'error',
            title: 'เข้าสู่ระบบไม่สำเร็จ',
            text: 'อีเมลหรือรหัสผ่านไม่ถูกต้อง กรุณาลองใหม่อีกครั้ง',
            confirmButtonColor: '#b3365b', // สีชมพูเข้มตามธีม MIRA
            confirmButtonText: 'ตกลง',
            background: '#fff0f5', // พื้นหลังชมพูอ่อน
            customClass: {
                title: 'mira-header' // ใช้ฟอนต์ Playfair Display ถ้าตั้งค่าไว้
            }
        });
    }
</script>
</body>
</html>
