<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>Login Page</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, sans-serif;
        }

        body {
            height: 100vh;
            background-image: url("photo/pay.jpg");
            background-size: cover;
            background-position: center;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-container {
            width: 350px;
            padding: 30px;
            border-radius: 16px;
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
            background: #ffb6b6;
            color: #333;
            font-weight: bold;
            cursor: pointer;
            margin-bottom: 15px;
        }

        .login-container button:hover {
            background: #ff9a9a;
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
    </style>
</head>
<body>

<div class="login-container">
    <h1>Mira </h1>
    <p>Have an account?</p>
<Form method="post"action="login_db.php">
    <input type="text" placeholder="Username"name="email">
    <input type="password" placeholder="Password"name="password">
    <button>Login</button>
</Form>
    <div class="options">
        <label>
        <a href="../register/register.php">สมัครสมาชิก</a>
    </div>
    <div class="options">
        <label>
            <input type="checkbox"> Remember Me
        </label>
        <a href="#">Forgot Password</a>
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
