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
            background: url('../register/photo/bv.jpg'); /* รูปพื้นหลังน้ำหอมโทนชมพู */
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
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            padding: 40px;
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
    </style>
</head>
<body>

<div class="glass-card">
    <h2 class="title">Mira - Register</h2>
    <form action="register.php" method="POST" enctype="multipart/form-data">
        <input type="text" name="fullname" class="form-control" placeholder="Full Name" required>
        <input type="email" name="email" class="form-control" placeholder="Email Address" required>
        <input type="password" name="password" class="form-control" placeholder="Password" required>
        <input type="text" name="phone" class="form-control" placeholder="Phone Number">
        <textarea name="address" class="form-control" placeholder="Address" rows="2"></textarea>
        
        <div class="mb-3">
            <label class="text-white small mb-1">Upload Profile Picture</label>
            <input type="file" name="profile_img" class="form-control" accept="image/*">
        </div>

        <button type="submit" class="btn-register">Sign Up</button>
        <div class="text-center mt-3">
            <a href="../login/login.php" class="text-white small" style="text-decoration: none;">Already have an account? Login</a>
        </div>
    </form>
</div>

</body>
</html>