<?php
include 'db_connect.php';

// ดึงข้อมูลสรุป
$summary = $conn->query("SELECT 
    COUNT(*) as total_items, 
    SUM(price * stock) as total_value,
    SUM(CASE WHEN stock < 10 AND stock > 0 THEN 1 ELSE 0 END) as low_stock,
    SUM(CASE WHEN stock <= 0 THEN 1 ELSE 0 END) as out_of_stock
    FROM products")->fetch_assoc();

// ตัวกรอง
$search = $_GET['search'] ?? '';
$sex = $_GET['sex'] ?? '';
$sort = $_GET['sort'] ?? 'stock';

$sql = "SELECT * FROM products WHERE product_name LIKE '%$search%'";
if ($sex) $sql .= " AND sex = '$sex'";
$sql .= ($sort == 'price') ? " ORDER BY price DESC" : " ORDER BY stock ASC";
$result = $conn->query($sql);

// ดึงประวัติ Stock Log
$logs = $conn->query("SELECT l.*, p.product_name FROM stock_log l 
                     JOIN products p ON l.product_id = p.product_id 
                     ORDER BY l.created_at DESC LIMIT 5");
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>Stock Management | MIRA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-11">
            
            <a href="../index_ad.php" class="back-link mb-3 d-inline-block">
                <i class="bi bi-arrow-left me-1"></i> กลับสู่หน้า Dashboard
            </a>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="fw-bold m-0" style="color: var(--mira-pink);">จัดการคลังสินค้า</h2>
                
            </div>

            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="glass-card text-center py-4">
                        <div class="small text-muted mb-1">มูลค่าสต็อกรวม</div>
                        <h4 class="fw-bold text-pink">฿<?= number_format($summary['total_value'], 2) ?></h4>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="glass-card text-center py-4">
                        <div class="small text-muted mb-1">สินค้าทั้งหมด</div>
                        <h4 class="fw-bold"><?= $summary['total_items'] ?></h4>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="glass-card text-center py-4">
                        <div class="small text-muted mb-1 text-warning">ใกล้หมด</div>
                        <h4 class="fw-bold text-warning"><?= $summary['low_stock'] ?></h4>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="glass-card text-center py-4 border-danger">
                        <div class="small text-muted mb-1 text-danger">หมดสต็อก</div>
                        <h4 class="fw-bold text-danger"><?= $summary['out_of_stock'] ?></h4>
                    </div>
                </div>
            </div>

            <div class="glass-card mb-4 p-3">
    <form method="GET" class="row g-3 align-items-center">
        <div class="col-md-4">
            <input type="text" name="search" class="form-control border-0 bg-light" style="border-radius: 12px;" placeholder="ค้นหาชื่อสินค้า..." value="<?= $search ?>">
        </div>

        <div class="col-md-3">
            <select name="sex" class="form-select border-0 bg-light" style="border-radius: 12px;">
                <option value="">ทุกหมวดหมู่</option>
                <option value="male" <?= $sex=='male'?'selected':'' ?>>Men</option>
                <option value="female" <?= $sex=='female'?'selected':'' ?>>Women</option>
                <option value="unisex" <?= $sex=='unisex'?'selected':'' ?>>Unisex</option>
            </select>
        </div>

        <div class="col-md-2">
            <button type="submit" class="btn btn-mira-filter w-100" title="กรองข้อมูล">
                <i class="bi bi-search-heart me-1"></i> 
            </button>
        </div>

        <div class="col-md-3">
            <div class="d-flex gap-2">
                <button type="button" onclick="window.print()" class="btn btn-mira-export pdf flex-fill" title="ส่งออกเป็น PDF">
                    <i class="bi bi-file-earmark-pdf-fill"></i> PDF
                </button>
                <button type="button" onclick="exportExcel()" class="btn btn-mira-export excel flex-fill" title="ส่งออกเป็น Excel">
                    <i class="bi bi-file-earmark-spreadsheet-fill"></i> Excel
                </button>
            </div>
        </div>
    </form>
</div>

            <div class="table-responsive mb-5">
                <table class="table align-middle" id="stockTable">
                    <thead>
                        <tr>
                            <th>รูป</th>
                            <th>ข้อมูลสินค้า</th>
                            <th>เพศ</th>
                            <th>ราคา</th>
                            <th class="text-center">คงเหลือ</th>
                            <th>สถานะ</th>
                            <th class="text-end">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $result->fetch_assoc()): 
                            $status = ($row['stock'] <= 0) ? ['หมด', 'badge-out'] : (($row['stock'] < 10) ? ['ใกล้หมด', 'badge-low'] : ['ปกติ', 'badge-ok']);
                        ?>
                        <tr>
                            <td><img src="../../photo/<?= $row['image'] ?>" class="rounded-3" style="width:50px; height:50px; object-fit:cover;"></td>
                            <td>
                                <div class="fw-bold"><?= $row['product_name'] ?></div>
                                <div class="small text-muted">รหัสสินค้า: MIRA-<?= $row['product_id'] ?></div>
                            </td>
                            <td><span class="badge rounded-pill bg-light text-dark border"><?= ucfirst($row['sex']) ?></span></td>
                            <td class="fw-bold text-pink">฿<?= number_format($row['price'], 2) ?></td>
                            <td class="text-center fw-bold"><?= $row['stock'] ?></td>
                            <td><span class="badge <?= $status[1] ?>"><?= $status[0] ?></span></td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-light border" data-bs-toggle="modal" data-bs-target="#addStock<?= $row['product_id'] ?>">
                                    <i class="bi bi-plus-lg"></i>
                                </button>
                                <button class="btn btn-sm btn-light border" title="ตัดสต็อก / สินค้าชำรุด" data-bs-toggle="modal" data-bs-target="#outStock<?= $row['product_id'] ?>">
            <i class="bi bi-dash-lg text-danger"></i>
        </button>
                            </td>
                        </tr>
                        <div class="modal fade" id="addStock<?= $row['product_id'] ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 25px;">
            <form action="update_stock.php" method="POST">
                <div class="modal-header border-0 pb-0">
                    <h5 class="fw-bold text-pink mx-auto">เพิ่มสินค้าเข้าคลัง</h5>
                    <button type="button" class="btn-close ms-0" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="text-center mb-4">
                        <img src="../../photo/<?= $row['image'] ?>" class="rounded-3 mb-2" style="width:80px; height:80px; object-fit:cover;">
                        <h6 class="fw-bold mb-0"><?= $row['product_name'] ?></h6>
                        <small class="text-muted">คงเหลือปัจจุบัน: <?= $row['stock'] ?> ชิ้น</small>
                    </div>

                    <input type="hidden" name="product_id" value="<?= $row['product_id'] ?>">
                    
                    <div class="mb-3">
                        <label class="form-label small fw-bold">จำนวนที่รับเข้า (ชิ้น)</label>
                        <input type="number" name="quantity" class="form-control form-control-lg bg-light border-0" 
                               placeholder="0" required min="1" style="border-radius: 12px;">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Supplier</label>
                        <input type="text" name="supplier" class="form-control bg-light border-0" placeholder="ชื่อผู้จัดส่ง..." style="border-radius: 12px;">
                    </div>

                    <div class="mb-0">
                        <label class="form-label small fw-bold">หมายเหตุ</label>
                        <textarea name="remark" class="form-control bg-light border-0" rows="2" 
                                  placeholder="ระบุรายละเอียดเพิ่มเติม..." style="border-radius: 12px;"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="submit" class="btn btn-mira w-100 py-3 fw-bold shadow-sm">
                        <i class="bi bi-check-circle me-2"></i>ยืนยันการเพิ่มสต็อก
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>





<div class="modal fade" id="outStock<?= $row['product_id'] ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 25px;">
            <form action="process_stock_out.php" method="POST">
                <div class="modal-header border-0 pb-0">
                    <h5 class="fw-bold text-danger mx-auto">ตัดสินค้าออกจากคลัง</h5>
                    <button type="button" class="btn-close ms-0" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="text-center mb-4">
                        <img src="../../photo/<?= $row['image'] ?>" class="rounded-3 mb-2" style="width:80px; height:80px; object-fit:cover;">
                        <h6 class="fw-bold mb-0"><?= $row['product_name'] ?></h6>
                        <small class="text-danger">จำนวนที่ตัดออกได้ไม่เกิน: <?= $row['stock'] ?> ชิ้น</small>
                    </div>

                    <input type="hidden" name="product_id" value="<?= $row['product_id'] ?>">
                    
                    <div class="mb-3">
                        <label class="form-label small fw-bold">จำนวนที่ตัดออก</label>
                        <input type="number" name="quantity" class="form-control form-control-lg bg-light border-0" 
                               placeholder="0" required min="1" max="<?= $row['stock'] ?>" style="border-radius: 12px;">
                    </div>

                    <div class="mb-0">
                        <label class="form-label small fw-bold">เหตุผลการตัดสต็อก</label>
                        <select name="remark" class="form-select bg-light border-0" style="border-radius: 12px;" required>
                            <option value="สินค้าชำรุด">สินค้าชำรุด</option>
                            <option value="สินค้าสูญหาย">สินค้าสูญหาย / นับสต็อกพลาด</option>
                            <option value="เบิกตัวอย่าง">เบิกเป็นสินค้าตัวอย่าง</option>
                            <option value="หมดอายุ">สินค้าหมดอายุ</option>
                            <option value="อื่นๆ">อื่นๆ (ระบุในหมายเหตุ)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                   <button type="button" id="btnConfirmOut<?= $row['product_id'] ?>" class="btn btn-dark w-100 py-3 fw-bold shadow-sm" style="border-radius: 15px;" onclick="confirmStockOut(<?= $row['product_id'] ?>)">
    <i class="bi bi-box-arrow-right me-2"></i>ยืนยันการตัดสต็อก
</button>
                </div>
            </form>
        </div>
    </div>
</div>

                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <h5 class="fw-bold mb-3"><i class="bi bi-clock-history me-2"></i>ประวัติล่าสุด</h5>
            <div class="glass-card p-0 overflow-hidden">
                <table class="table table-sm mb-0" style="font-size: 0.85rem;">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">วัน/เวลา</th>
                            <th>สินค้า</th>
                            <th>ประเภท</th>
                            <th>จำนวน</th>
                            <th>หมายเหตุ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($l = $logs->fetch_assoc()): ?>
                        <tr>
                            <td class="ps-4 text-muted"><?= date('d/m/Y H:i', strtotime($l['created_at'])) ?></td>
                            <td class="fw-bold"><?= $l['product_name'] ?></td>
                            <td><?= $l['type'] == 'in' ? '<span class="text-success">เข้า</span>' : '<span class="text-danger">ออก</span>' ?></td>
                            <td class="fw-bold"><?= $l['quantity'] ?></td>
                            <td class="text-muted"><?= $l['remark'] ?></td>
                        </tr>


                        
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>



<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('status')) {
        const status = urlParams.get('status');
        if (status === 'success') {
            Swal.fire({
                icon: 'success',
                title: 'อัปเดตสต็อกเรียบร้อย!',
                showConfirmButton: false,
                timer: 1500,
                customClass: { popup: 'my-rounded-swal' }
            });
        }
    }
</script>
<script>
    const urlParams = new URLSearchParams(window.location.search);
    const status = urlParams.get('status');
    
    if (status === 'success_out') {
        Swal.fire({
            icon: 'warning', // ใช้สีส้มเพื่อให้รู้ว่าเป็นรายการนำออก
            title: 'ตัดสต็อกสำเร็จ',
            text: 'ระบบอัปเดตจำนวนสินค้าที่ถูกนำออกแล้ว',
            showConfirmButton: false,
            timer: 2000,
            customClass: { popup: 'my-rounded-swal' }
        });
    } else if (status === 'insufficient') {
        Swal.fire({
            icon: 'error',
            title: 'ผิดพลาด!',
            text: 'จำนวนที่ตัดออก มากกว่าจำนวนที่มีอยู่ในคลัง',
            confirmButtonColor: '#b3365b'
        });
    }
</script>

<style>
    .my-rounded-swal { border-radius: 20px !important; font-family: 'Sarabun', sans-serif; }
</style>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function exportExcel() {
    let table = document.getElementById("stockTable");
    let html = table.outerHTML;
    window.open('data:application/vnd.ms-excel,' + encodeURIComponent(html));
}
</script>





<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
function confirmStockOut(productId) {
    // แสดง Popup ยืนยันก่อนตัดจริง
    Swal.fire({
        title: 'ยืนยันการตัดสต็อก?',
        text: "คุณแน่ใจนะว่าจะนำสินค้าชิ้นนี้ออก 🥺",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#212529', // สีปุ่มยืนยัน (Dark)
        cancelButtonColor: '#d33',
        confirmButtonText: 'ใช่, ตัดสต็อกเลย',
        cancelButtonText: 'ยกเลิก',
        border: 'none',
        customClass: {
            popup: 'my-rounded-swal'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // เมื่อกดตกลง ให้แสดงหน้าจอ "ลบออกแล้ว"
            Swal.fire({
                title: 'เรียบร้อย! 🥺',
                text: 'สินค้าถูกตัดออกจากคลังแล้วน้า...',
                icon: 'success',
                showConfirmButton: false,
                timer: 1500,
                customClass: {
                    popup: 'my-rounded-swal'
                }
            }).then(() => {
                // ส่งฟอร์มไปยัง process_stock_out.php
                document.querySelector(`#outStock${productId} form`).submit();
            });
        }
    });
}
</script>

<style>
    /* ปรับแต่งความโค้งมนให้เข้ากับ MIRA Style */
    .my-rounded-swal {
        border-radius: 25px !important;
        font-family: 'Sarabun', sans-serif !important;
    }
    .swal2-title {
        font-weight: bold !important;
        color: #333 !important;
    }
</style>




</body>
</html>