<?php
require_once "../config.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $password = $_POST['password']; // ควรใช้ password_hash ในการใช้งานจริง
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    
    // จัดการอัปโหลดรูปโปรไฟล์
    $profile_img = "";
    if (isset($_FILES['profile_img']) && $_FILES['profile_img']['error'] == 0) {
        $ext = pathinfo($_FILES['profile_img']['name'], PATHINFO_EXTENSION);
        $profile_img = "user_" . uniqid() . "." . $ext;
        move_uploaded_file($_FILES['profile_img']['tmp_name'], "photo/" . $profile_img);
    }

    try {
        $sql = "INSERT INTO users (fullname, email, password, role, phone, address, profile_img) 
                VALUES (?, ?, ?, 'customer', ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if ($stmt->execute([$fullname, $email, $password, $phone, $address, $profile_img])) {
            echo "<script>alert('สมัครสมาชิกสำเร็จ!'); window.location='../login/login.php';</script>";
        }
    } catch(PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>Register - Mira</title>
    <link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: url('../register/photo/ki.jpg'); /* รูปพื้นหลังน้ำหอมโทนชมพู */
            background-size: cover;
            background-position: center;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border-radius: 50px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            padding: 50px;
            width: 100%;
            max-width: 500px;
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
        }
        .form-control {
            background: rgba(255, 255, 255, 0.8);
            border: none;
            border-radius: 10px;
            padding: 12px;
            margin-bottom: 15px;
        }
        .btn-register {
            background-color: #f8a5c2; /* สีชมพูพาสเทลตามรูป */
            border: none;
            border-radius: 10px;
            padding: 12px;
            width: 100%;
            font-weight: bold;
            color: white;
            transition: 0.3s;
        }
        .btn-register:hover { background-color: #f78fb3; }
        .title { color: white; text-align: center; font-weight: bold; margin-bottom: 25px; }

        .btn-register:hover {
    transform: translateY(-3px); /* ลอยขึ้นเล็กน้อย */
    background: linear-gradient(45deg, #f78fb3, #f78fb3);
    /* เพิ่มความฟุ้งตอนเอาเมาส์วาง */
    box-shadow: 0 0 25px rgba(255, 51, 153, 0.7), 
                0 0 10px rgba(255, 255, 255, 0.4);
}
.btn-register:active {
    transform: translateY(-1px);
}


/* ลิงก์เข้าสู่ระบบด้านล่าง */
.login-link {
    color: rgba(255, 255, 255, 0.7) !important;
    transition: all 0.3s ease;
    position: relative;
    padding: 5px 10px;
}

.login-link:hover {
    color: #fff !important;
    text-shadow: 0 0 8px rgba(255, 255, 255, 0.8); /* อักษรเรืองแสง */
    letter-spacing: 0.5px; /* ยืดตัวอักษรเล็กน้อยให้ดูหรู */
}

/* เพิ่มเส้นใต้แบบอนิเมชั่น (Option เสริม) */
.login-link::after {
    content: '';
    display: block;
    width: 0;
    height: 1px;
    background: #fff;
    transition: width .3s;
    margin: 0 auto;
    box-shadow: 0 0 5px #fff;
}

.login-link:hover::after {
    width: 100%;
}

.profile-upload-container {
    text-align: center;
    margin-bottom: 20px;
}

.profile-preview {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    margin: 0 auto;
    border: 3px solid #f78fb3;
    overflow: hidden;
    cursor: pointer;
    position: relative;
    background: #fff0f5;
    transition: 0.3s;
}

.profile-preview:hover {
    opacity: 0.8;
}

.profile-preview img {
    width: 100%;
    height: 115%;
    object-fit: cover;
}

.upload-icon {
    position: absolute;
    bottom: 0;
    background: #fff0f5;
    width: 100%;
    color: white;
    font-size: 12px;
    padding: 2px 0;
}

    </style>
</head>
<body>
   

<div class="glass-card">
    <div class="profile-upload-container">
            <div class="profile-preview" onclick="document.getElementById('profile_img').click();">
                <img id="image-preview" src="photo/pf.jpg" alt="Profile Preview">
                <div class="upload-icon">
                    <i class="fas fa-camera"></i> </div>
            </div>
            <input type="file" name="profile_img" id="profile_img" accept="image/*" style="display: none;" onchange="previewImage(event)">
            <label class="text-white small mt-1 d-block text-center">คลิกที่รูปเพื่ออัปโหลด</label>
        </div>
    <h2 class="title">ลงทะเบียน</h2>
    <form action="register.php" method="POST" enctype="multipart/form-data">
        <input type="text" name="fullname" class="form-control" placeholder="ชื่อ" required>
        <input type="email" name="email" class="form-control" placeholder="อีเมล" required>
        <input type="password" name="password" class="form-control" placeholder="รหัส" required>
        <input type="text" name="phone" class="form-control" placeholder="หมายเลขโทรศัพท์" required>
        <textarea name="address" class="form-control" placeholder="ที่อยู๋" rows="2"></textarea>
        
        

        <button type="submit" class="btn-register">ลงชื่อเข้าใช้</button>
        <div class="text-center mt-4">
    <a href="../login/login.php" class="login-link small" style="text-decoration: none;">
        มีบัญชีอยู่แล้วใช่ไหม? เข้าสู่ระบบ
    </a>
</div>
    </form>
</div>

<script>
    function previewImage(event) {
    const reader = new FileReader();
    reader.onload = function(){
        const output = document.getElementById('image-preview');
        output.src = reader.result;
    }
    reader.readAsDataURL(event.target.files[0]);
}
</script>



</body>
</html>