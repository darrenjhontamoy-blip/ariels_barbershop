<?php
session_start();
include 'config.php';

$services = mysqli_query($conn,"SELECT * FROM services WHERE status='active'");
$barbers  = mysqli_query($conn,"SELECT * FROM barbers WHERE status='approved'");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Our Services | Ariel’s Barbershop</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link href="https://fonts.googleapis.com/css2?family=Oswald&family=Playfair+Display:wght@600&display=swap" rel="stylesheet">

<style>
body{margin:0;font-family:Oswald;background:#0e0e0e;color:#fff}

/* VIDEO HERO */
.video-hero{
    position:relative;
    height:80vh;
    overflow:hidden;
}
.video-hero video{
    width:100%;
    height:100%;
    object-fit:cover;
}
.video-overlay{
    position:absolute;
    inset:0;
    background:rgba(0,0,0,.65);
    display:flex;
    align-items:center;
    justify-content:center;
    text-align:center;
}
.video-overlay h1{
    font-family:'Playfair Display',serif;
    font-size:60px;
}
.video-overlay span{color:#c62828}

/* BARBERS */
.barbers{
    background:#111;
    padding:80px 60px;
}
.barbers h2{
    text-align:center;
    font-family:'Playfair Display',serif;
    font-size:40px;
    margin-bottom:40px;
}
.barber-grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
    gap:30px;
}
.barber{
    background:#1a1a1a;
    border-radius:16px;
    padding:25px;
    text-align:center;
}
.barber img{
    width:100%;
    height:240px;
    object-fit:cover;
    border-radius:12px;
}
.barber h3{margin-top:15px}

/* SERVICES */
.services{
    background:#f6f7fb;
    color:#111;
    padding:90px 60px;
}
.services h2{
    text-align:center;
    font-family:'Playfair Display',serif;
    font-size:40px;
    margin-bottom:40px;
}
.services-grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(260px,1fr));
    gap:30px;
}
.service{
    background:#fff;
    border-radius:16px;
    padding:30px;
    text-align:center;
    text-decoration:none;
    color:#111;
    box-shadow:0 10px 30px rgba(0,0,0,.12);
    transition:.3s;
}
.service:hover{
    transform:translateY(-8px);
}
.price{color:#1e88e5;font-weight:600;margin-top:10px}
</style>
</head>

<body>

<!-- VIDEO -->
<section class="video-hero">
    <video autoplay muted loop playsinline>
        <source src="assets/videos/barber42.mp4" type="video/mp4">
    </video>
    <div class="video-overlay">
        <h1>OUR <span>SERVICES</span></h1>
    </div>
</section>

<!-- BARBERS -->
<section class="barbers">
    <h2>Our Barbers</h2>
    <div class="barber-grid">
        <?php while($b = mysqli_fetch_assoc($barbers)): ?>
        <div class="barber">
            <img src="uploads/<?= $b['photo'] ?>">
            <h3><?= htmlspecialchars($b['name']) ?></h3>
        </div>
        <?php endwhile; ?>
    </div>
</section>

<!-- SERVICES -->
<section class="services">
    <h2>Choose a Service</h2>
    <div class="services-grid">
        <?php while($s = mysqli_fetch_assoc($services)): ?>
        <a href="service_view.php?id=<?= $s['id'] ?>" class="service">
            <h3><?= htmlspecialchars($s['service_name']) ?></h3>
            <p><?= htmlspecialchars($s['description']) ?></p>
            <div class="price">₱<?= number_format($s['price'],2) ?> • <?= $s['duration'] ?> min</div>
        </a>
        <?php endwhile; ?>
    </div>
</section>

</body>
</html>
