<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>MIRA | Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Sarabun:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <style>
        :root {
            --mira-pink: #f8a5c2;
            --mira-dark-pink: #f8a;
            --mira-soft-pink: #fff2f6;
            --mira-glass: #ffffff;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Sarabun', sans-serif;
        }

       body {
    margin: 0;
    padding: 0;
    width: 100%;
    height: 100vh;
    /* ใส่สีชมพูเป็นพื้นหลังสำรอง ถ้าสีนี้ขึ้นแต่รูปไม่ขึ้น แสดงว่า Path รูปผิดชัวร์ๆ ครับ */
    background-color: #fff2f6; 
    
    /* ลองลบ ../ ออกถ้าไฟล์ login.php อยู่หน้าหลัก */
    background-image: url("photo/op.jpg"); 
    
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    
    display: flex;
    align-items: center;
    justify-content: center;
}

        /* Container หลัก */
        .login-card {
            width: 400px;
            padding: 40px;
            border-radius: 40px;
            background: #ffffff26;
            backdrop-filter: blur(25px);
            -webkit-backdrop-filter: blur(15px);
            border: 1px solid #ffffff4d;
            box-shadow: 0 20px 40px #00000026;
            text-align: center;
        }

        /* จัดการโลโก้ให้เด่น */
        .logo-wrapper {
            margin-bottom: 25px;
        }

        .mira-logo-top {
            width: 120px;
            filter: drop-shadow(0 5px 10px #0000001a);
            transition: 0.4s ease;
        }

        .mira-logo-top:hover {
            transform: scale(1.1);
        }

        /* หัวข้อ */
        h1 {
            color: #ffffff;
            font-family: 'Playfair Display', serif;
            font-size: 24px;
            margin-bottom: 25px;
            letter-spacing: 1px;
        }

        /* ช่องกรอกข้อมูล */
        .input-group {
            margin-bottom: 15px;
            position: relative;
        }

        input {
            width: 100%;
            padding: 14px 20px;
            border-radius: 50px;
            border: 1px solid rgba(255, 255, 255, 0.5);
            background: rgba(255, 255, 255, 0.85);
            outline: none;
            font-size: 0.95rem;
            color: #555;
            transition: 0.3s;
        }

        input:focus {
            background: #fff;
            box-shadow: 0 0 15px #f8a5c2;
            border-color: var(--mira-pink);
        }

        /* ปุ่มเข้าสู่ระบบ */
        .btn-login {
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 50px;
            background: var(--mira-dark-pink);
            color: #fff;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: 0.4s;
            margin-top: 10px;
            box-shadow: 0 5px 15px #F5A5C04D;
        }

        .btn-login:hover {
            background: #f8a5c2;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px #F8a;
        }

        /* ส่วนเลือกเพิ่มเติม (จดจำฉัน/กลับหน้าหลัก) */
        .options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 20px 0;
            font-size: 0.85rem;
            color: #fff;
        }

        .options label {
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .options input[type="checkbox"] {
            width: 16px;
            height: 16px;
            accent-color: var(--mira-dark-pink);
            margin: 0;
        }

        .options a {
            color: #fff;
            text-decoration: none;
            opacity: 0.9;
            transition: 0.3s;
        }

        .options a:hover {
            opacity: 1;
            text-decoration: underline;
        }

        /* ส่วนสมัครสมาชิก */
        .register-section {
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #C7545433;
            color: #fff;
            font-size: 0.9rem;
        }

        .register-link {
            display: inline-block;
            margin-top: 10px;
            background: var(--mira-soft-pink);
            color: var(--mira-dark-pink) !important;
            padding: 8px 25px;
            border-radius: 50px;
            font-weight: 600;
            text-decoration: none;
            transition: 0.3s;
            border: 1px solid transparent;
        }

        .register-link:hover {
            background: var(--mira-dark-pink);
            color: #fff !important;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px #0000001a;
        }

    </style>
</head>
<body>

<div class="login-card">
    <div class="logo-wrapper">
        <img src="../photo/golo.png" alt="Mira Logo" class="mira-logo-top">
    </div>

    <h1>Welcome to MIRA</h1>

    <form method="post" action="login_db.php">
        <div class="input-group">
            <input type="text" placeholder="Username" name="email" required>
        </div>
        <div class="input-group">
            <input type="password" placeholder="Password" name="password" required>
        </div>
        <button type="submit" class="btn-login">เข้าสู่ระบบ</button>
    </form>

    <div class="options">
        <label>
            <input type="checkbox"> จดจำฉันไว้
        </label>
        <a href="../index.php"><i class="bi bi-house-door"></i> กลับหน้าหลัก</a>
    </div>

    <div class="register-section">
        <p>หากคุณยังไม่มีบัญชี?</p>
        <a href="../register/register.php" class="register-link">สมัครสมาชิก</a>
    </div>
</div>

</body>
</html>



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
