<?php
session_start();
include '../config/db.php';

$user = null;
$uid = 0;

if(isset($_SESSION['user_id'])){
    $uid = $_SESSION['user_id'];
    $user = mysqli_fetch_assoc(
        mysqli_query($conn,"SELECT username,balance FROM users WHERE id=$uid")
    );
}

/* 🔢 นับจำนวนสินค้าในตะกร้า */
$cartCount = 0;
if($uid){
    $c = mysqli_fetch_assoc(
        mysqli_query($conn,"
            SELECT SUM(qty) AS total
            FROM cart
            WHERE user_id = $uid
        ")
    );
    $cartCount = $c['total'] ?? 0;
}

/* สินค้า */
$result = mysqli_query($conn, "SELECT * FROM products");
?>

<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>BEZSHOP</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/style.css">

<style>
.product-card{ transition:.2s; }
.product-card:hover{ transform:translateY(-4px); }

.fav-btn{
    position:absolute;
    top:10px;
    right:10px;
    background:#fff;
    border-radius:50%;
    width:38px;
    height:38px;
    display:flex;
    align-items:center;
    justify-content:center;
    box-shadow:0 2px 6px rgba(0,0,0,.2);
    text-decoration:none;
    font-size:18px;
}
.fav-btn.red{ color:#e74c3c; }
.fav-btn.white{ color:#999; }
</style>
</head>

<body>

<nav class="navbar navbar-light bg-white shadow-sm px-4 d-flex justify-content-between">
    <span class="navbar-brand fw-bold fs-4">BEZSHOP</span>

    <div class="d-flex align-items-center">
        <?php if(isset($_SESSION['role']) && $_SESSION['role']=='admin'){ ?>
            <a href="../admin/index.php" class="btn btn-dark me-3">🛠 ADMIN</a>
        <?php } ?>

        <?php if($user){ ?>
            <span class="me-3">
                👤 <b><?= htmlspecialchars($user['username']) ?></b>
                | 💰 <?= number_format($user['balance']) ?> บาท
            </span>

            <a href="favorites.php" class="btn btn-outline-danger me-2">❤️</a>

            <a href="cart.php" class="btn btn-outline-dark me-2">
                🛒
                <?php if($cartCount>0){ ?>
                    <span class="badge bg-dark"><?= $cartCount ?></span>
                <?php } ?>
            </a>

            <a href="wallet.php" class="btn btn-outline-dark me-2">Wallet</a>
            <a href="../backend/logout.php" class="btn btn-dark">Logout</a>
        <?php } else { ?>
            <a href="login.php" class="btn btn-outline-dark">Login</a>
        <?php } ?>
    </div>
</nav>

<div class="container mt-5">
<h3 class="mb-4">สินค้าใหม่</h3>

<div class="row">
<?php while($row=mysqli_fetch_assoc($result)){ ?>

<?php
/* ❤️ เช็ค favorite */
$isFav = false;
if($uid){
    $fav = mysqli_query($conn,"
        SELECT id FROM favorites
        WHERE user_id=$uid AND product_id={$row['id']}
    ");
    $isFav = mysqli_num_rows($fav)>0;
}
?>

<div class="col-md-4 mb-4">
<div class="card product-card h-100 position-relative">

<?php if($user){ ?>
<a href="../backend/toggle_favorite.php?id=<?= $row['id'] ?>"
   class="fav-btn <?= $isFav ? 'red' : 'white' ?>">
   ❤️
</a>
<?php } ?>

<a href="product.php?id=<?= $row['id'] ?>">
    <img src="../assets/img/<?= $row['image'] ?>" class="card-img-top">
</a>

<div class="card-body d-flex flex-column">
    <h5><?= htmlspecialchars($row['name']) ?></h5>
    <p><?= number_format($row['price']) ?> บาท</p>
    <small class="text-muted mb-2">คงเหลือ <?= $row['stock'] ?> ชิ้น</small>

    <?php if($row['stock']<=0){ ?>
        <span class="badge bg-danger">สินค้าหมด</span>
    <?php } elseif($user){ ?>
        <a href="../backend/add_cart.php?id=<?= $row['id'] ?>"
           class="btn btn-dark mt-auto">เพิ่มลงตะกร้า</a>
    <?php } else { ?>
        <a href="login.php" class="btn btn-dark mt-auto">Login เพื่อซื้อ</a>
    <?php } ?>
</div>

</div>
</div>

<?php } ?>
</div>
</div>

</body>
</html>
