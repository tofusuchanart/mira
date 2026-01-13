
</head>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mira</title>
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="icon" href="photo/golo.png">
    <style>
        body { font-family: 'Sarabun', sans-serif; }
        .footer-section {
            background-color: #FFFFFF; /* สีพื้นหลังเทาอ่อนตามภาพ */
            padding: 40px 0;
            color: #000;
        }
        .footer-title {
            font-weight: bold;
            font-size: 1.2rem;
            margin-bottom: 15px;
        }
        .footer-contact-text {
            font-size: 1.1rem;
            text-decoration: none;
            color: #000;
        }
        .social-icons a {
            font-size: 2rem;
            color: #000;
            margin-right: 15px;
            text-decoration: none;
        }
        .text-gray { color: #6c757d; } /* สีเทาสำหรับหัวข้อ */
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-light" style="background-color:#FFFFFF">
  <div class="container-fluid">

    <!-- Logo -->
    <a class="navbar-brand" href="#">
      <img src="photo/golo.png" width="120" height="80" alt="Mira">
    </a>

    <!-- Toggle -->
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
      data-bs-target="#navbarNavDropdown">
      <span class="navbar-toggler-icon"></span>
    </button>

    <!-- Menu -->
    <div class="collapse navbar-collapse" id="navbarNavDropdown">
      <ul class="navbar-nav">

        <li class="nav-item">
          <a class="nav-link active" href="#">Home</a>
        </li>

        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" role="button"
             data-bs-toggle="dropdown">
            Products
          </a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="index.php?link=women">Perfume women</a></li>
            <li><a class="dropdown-item" href="perfume_formen">Perfume for men</a></li>
            <li><a class="dropdown-item" href="#"></a></li>
          </ul>
        </li>

        <li class="nav-item">
          <a class="nav-link" href="#">Promotion</a>
        </li>

        <li class="nav-item">
          <a class="nav-link" href="#">About us</a>
        </li>

        <li class="nav-item">
          <a class="nav-link" href="#">Contact us</a>
        </li>

        <li class="nav-item">
          <a class="nav-link" href="#">Track your order</a>
        </li>

        <li class="nav-item">
          <a class="nav-link" href="login/login.php">Login & Sign up</a>
        </li>
      </ul>
<form class="d-flex ms-auto" role="search">
        <input class="form-control me-2" type="search" placeholder="Search">
        <button class="btn btn-outline-success" type="submit">Search</button>
      </form>

    </div>
  </div>
      </nav>
      <!-- Search -->
      
          <!-- สิ้นสุดแบนเนอร์ -->
          <!-- เริ่มต้นfooter -->
           

<?php include_once "body.php"; ?>





    




<footer class="footer-section">
    <div class="container">
        <div class="row align-items-start">
            
            <div class="col-md-4 mb-4 mb-md-0">
                <h5 class="fw-bold">บริษัท มิรา จำกัด</h5>
                <p class="mb-0">ศาลากลางจังหวัดพะเยา ถนนพหลโยธิน</p>
                <p>ต.บ้านต๋อม อ.เมืองพะเยา จ.พะเยา 56000</p>
            </div>

            <div class="col-md-5 mb-4 mb-md-0">
                <h5 class="text-gray fw-bold mb-3">Feedback & Question</h5>
                <div class="d-flex flex-column gap-2">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-telephone-fill me-2 fs-4"></i>
                        <span class="footer-contact-text fw-bold">098-818-9079</span>
                    </div>
                    <div class="d-flex align-items-center">
                        <i class="bi bi-envelope-fill me-2 fs-4"></i>
                        <a href="mailto:miraperfume@gmail.com" class="footer-contact-text fw-bold">miraperfume@gmail.com</a>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <h5 class="text-gray fw-bold mb-3">Follow Us</h5>
                <div class="social-icons d-flex align-items-center">
                    <a href="#"><i class="bi bi-facebook"></i></a>
                    <a href="#"><i class="bi bi-line"></i></a>
                    <a href="#"><i class="bi bi-instagram"></i></a>
                    <a href="#"><i class="bi bi-youtube"></i></a>
                </div>
            </div>

        </div>
    </div>
</footer>
           <!-- สิ้นสุดfooter -->
        <script src="bootstrap/js/bootstrap.bundle.min.js"></script>
      </body>
      </html>
