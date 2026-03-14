<?php
session_start();
include 'config.php';

$services = mysqli_query($conn,"SELECT * FROM services WHERE status='active'");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Ariel’s Barbershop</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link href="https://fonts.googleapis.com/css2?family=Oswald:wght@300;400;600&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">

<style>
*{margin:0;padding:0;box-sizing:border-box}
html{scroll-behavior:smooth}
body{font-family:'Oswald',sans-serif;background:#0e0e0e;color:#fff}

/* NAVBAR */
.navbar{
    position:fixed;top:0;width:100%;z-index:1000;
    background:rgba(0,0,0,.95);
    display:flex;justify-content:space-between;align-items:center;
    padding:18px 40px;border-bottom:1px solid rgba(255,255,255,.08)
}
.logo{font-family:'Playfair Display',serif;font-size:26px}
.logo span{color:#c62828}

.nav-links a{
    margin-left:28px;text-decoration:none;color:#eee;
    font-size:15px;letter-spacing:1px;padding-bottom:4px;
}
.nav-links a:hover{color:#c62828}
.nav-links a.active{color:#c62828;border-bottom:2px solid #c62828}
.login-btn{padding:6px 14px;border:1px solid #c62828}
.login-btn:hover{background:#c62828;color:#fff}

/* HERO */
.hero{
    height:100vh;display:flex;align-items:center;
    padding:0 80px;
    background:
        linear-gradient(90deg,rgba(0,0,0,.9),rgba(0,0,0,.4)),
        url('ariels..jpg') center/cover no-repeat;
}
.hero-content{max-width:620px}
.hero-title{
    font-family:'Playfair Display',serif;
    font-size:64px;margin-bottom:20px
}
.hero-title span{color:#c62828}
.hero-desc{color:#ccc;margin-bottom:35px}
.hero-buttons a{
    display:inline-block;
    padding:14px 36px;
    text-decoration:none;
    margin-right:15px;
    letter-spacing:1px;
}
.btn-primary{background:#c62828;color:#fff}
.btn-secondary{
    border:2px solid #1e88e5;
    background:#1e88e5;
    color:#fff;
    transition:.3s;
}
.btn-secondary:hover{
    background:#1565c0;
}

/* SECTIONS */
section{scroll-margin-top:100px;padding:120px 60px}

/* WHITE SECTIONS */
#services,#barbers,#about,#contact{background:#fff;color:#111}

/* TITLES */
.services-title{
    text-align:center;
    font-family:'Playfair Display',serif;
    font-size:50px;
    margin-bottom:10px;
    position:relative;
    top:-50px;
}
.services-sub{
    text-align:center;
    color:#555;
    margin-bottom:10px;
    position:relative;
    top:-30px;
}

/* VIDEO */
.services-video{
    max-width:1100px;
    margin:0 auto 50px;
    position:relative;
}
.services-video video{
    width:100%;
    border-radius:20px;
    box-shadow:0 20px 50px rgba(0,0,0,.25);
}
#unmuteBtn{
    position:absolute;
    top:15px;
    right:15px;
    padding:8px 14px;
    background:#c62828;
    color:#fff;
    border:none;
    border-radius:6px;
    cursor:pointer;
}

/* SERVICES GRID */
.services-grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(260px,1fr));
    gap:30px;
}
.service-card{
    background:#fff;
    border-radius:16px;
    padding:35px 25px;
    text-align:center;
    text-decoration:none;
    color:#111;
    transition:.3s;
    box-shadow:0 10px 30px rgba(0,0,0,.1)
}
.service-card:hover{
    transform:translateY(-10px);
    box-shadow:0 20px 40px rgba(0,0,0,.15)
}
.service-price{color:#1e88e5;font-weight:600}

/* CATALOG */
.catalog-grid{
    display:flex;
    gap:30px;
    flex-wrap:wrap;
    justify-content:center;
    margin-top:100px;
    position:relative;
    top:-10px;
}
.catalog-card{
    background:#f5f5f5;
    border-radius:16px;
    padding:20px;
    max-width:320px;
    text-align:center;
    box-shadow:0 10px 25px rgba(0,0,0,.1);
    transition:.3s;
    position:relative;
    top:-30px;
}
.catalog-card:hover{
    transform:translateY(-10px);
    box-shadow:0 20px 40px rgba(0,0,0,.15)
}
.carousel{
    display:flex;
    gap:10px;
    overflow-x:auto;
}
.carousel img{
    width:200px;height:200px;
    object-fit:cover;
    border-radius:12px;
    cursor:pointer;
    flex-shrink:0;
}

/* BARBERS */
.barbers-grid{
    display:flex;
    justify-content:center;
    gap:60px;
    flex-wrap:wrap;
    margin-top:100px;
    position:relative;
    top:-80px;
}
.barber-card{text-align:center}
.barber-card img{
    width:200px;height:200px;
    object-fit:cover;border-radius:50%;
    margin-bottom:15px;
    cursor:pointer;
}
.barber-card p{color:#555}

/* ABOUT / CONTACT */
#about h2,#contact h2{
    font-size:50px;text-align:center;margin-bottom:20px;position:relative;top:-150px;
    font-family:'Playfair Display',serif;
    
}
#about p,#contact p{
    text-align:center;
    max-width:800px;
    margin:20px auto;
    font-size:25px;
    line-height:2;
    position: relative;
    top: -150px;
}

/* ===============================
   VERTICAL BARBER POLES + DIVIDER FIX
=================================*/

/* Sections with poles */
.section-with-poles {
    position: relative;             
    padding-left: 10px;             
    padding-right: 10px;            
    margin-bottom: 0px;            
}

/* Left and Right Poles */
.section-with-poles::before,
.section-with-poles::after {
    content: "";
    position: absolute;
    top: 0;
    bottom: 0;                      
    width: 10px;
    background: repeating-linear-gradient(
        to bottom,                  
        #c62828, #c62828 15px,
        #fff 15px, #fff 30px,
        #1e88e5 30px, #1e88e5 45px
    );
    border-radius: 10px;  
}

/* Left pole */
.section-with-poles::before { left: 0; }

/* Right pole */
.section-with-poles::after { right: 0; }

/* Section Divider Line */
.section-divider{
    width: calc(100% - 0px);
    margin: 80px auto;
    background:#c62828;
    height:10px;
    border-radius:5px;
}
/* Ensure hero also displays full poles */
.hero.section-with-poles {
    position: relative;
}

</style>
</head>
<body>

<header class="navbar">
    <div class="logo">Ariel’s <span>Barbershop</span></div>
    <nav class="nav-links">
        <a href="#home" class="nav-link active">Home</a>
        <a href="#services" class="nav-link">Services</a>
        <a href="#about" class="nav-link">About</a>
        <a href="#contact" class="nav-link">Contact</a>
        <a href="login.php" class="login-btn">Login</a>
    </nav>
</header>

<!-- HOME -->
<section class="hero" id="home">
    <div class="hero-content">
        <h1 class="hero-title">ARIEL’S <span>BARBERSHOP</span></h1>
        <p class="hero-desc">Precision cuts, clean fades, and professional grooming.</p>
        <div class="hero-buttons">
            <a href="#services" class="btn-primary">VIEW SERVICES</a>
            <a href="register.php" class="btn-secondary">BOOK NOW</a>
        </div>
    </div>
</section>

<!-- SERVICES -->
<section class="section-with-poles" id="services">
    <h2 class="services-title">Our Services</h2>
    <p class="services-sub">Professional grooming at its finest</p>

    <div class="services-video">
        <video id="servicesVideo" muted loop playsinline preload="auto">
            <source src="ars.mp4.mp4" type="video/mp4">
        </video>
        <button id="unmuteBtn">🔊 Unmute</button>
    </div>

    <div class="services-grid">
        <?php while($s=mysqli_fetch_assoc($services)): ?>
        <a href="service_view.php?service=<?= urlencode($s['service_name']) ?>" class="service-card">
            <h3><?= htmlspecialchars($s['service_name']) ?></h3>
            <p><?= $s['duration'] ?> minutes</p>
            <div class="service-price">₱<?= number_format($s['price'],2) ?></div>
        </a>
        <?php endwhile; ?>
    </div>

    <hr class="section-divider">

   <!-- CATALOG -->
<h2 class="services-title">Our Haircut</h2>
<p class="services-sub">Click the images to zoom</p>

<div class="catalog-grid">
    <div class="catalog-card">
        <h3>Classic Haircut - 30 mins - ₱90</h3>
        <div class="carousel">
            <img src="barber.jpg" alt="Classic Haircut">
            <img src="semi_barber.jpg" alt="Classic Haircut">
            <img src="semi_kalbo.jpg" alt="Classic Haircut">
            <img src="semi_flat_top.jpg" alt="Classic Haircut">
            <img src="clean_cut.jpg" alt="Classic Haircut">
            <img src="army_cut.jpg" alt="Classic Haircut">
        </div>
    </div>

    <div class="catalog-card">
        <h3>Modern Haircut - 30 mins - ₱90</h3>
        <div class="carousel">
            <img src="burst_fade.jpg" alt="Modern Haircut">
            <img src="buzz_cut.jpg" alt="Modern Haircut">
            <img src="low_fade_v.jpg" alt="Modern Haircut">
            <img src="mid_fade.jpg" alt="Modern Haircut">
            <img src="mide_mullet.jpg" alt="Modern Haircut">
            <img src="taper_fade.jpg" alt="Modern Haircut">
        </div>
    </div>

    <div class="catalog-card">
        <h3>Haircut + Beard - 35 mins - ₱100</h3>
        <div class="carousel">
            <img src="aws.jpg" alt="Haircut + Beard">
            <img src="ews.jpg" alt="Haircut + Beard">
            <img src="iws.jpg" alt="Haircut + Beard">
            <img src="uws.jpg" alt="Haircut + Beard">
            <img src="ows.jpg" alt="Haircut + Beard">
            <img src="ops.jpg" alt="Haircut + Beard">
        </div>
    </div>
</div>

<hr class="section-divider">

<!-- IMAGE LIGHTBOX MODAL -->
<div id="imgModal" style="display:none; position:fixed; z-index:10000; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.9); justify-content:center; align-items:center;">
    <span id="closeModal" style="position:absolute; top:20px; right:35px; font-size:40px; color:white; cursor:pointer;">&times;</span>
    <img id="modalImg" style="max-width:90%; max-height:90%; border-radius:10px;">
</div>

<!-- BARBERS -->
<section class="section-with-poles" id="barbers">
    <h2 class="services-title">Our Barbers</h2>
    <p class="services-sub">Meet the skilled team</p>

    <div class="barbers-grid">
        <div class="barber-card">
            <img src="ars..jpg" alt="Ariel Gonzales Trijo">
            <h3>Ariel Gonzales Trijo</h3>
            <p>Senior Barber</p>
        </div>
        <div class="barber-card">
            <img src="KIM.jpg" alt="Kim Zaragoza">
            <h3>Kim Zaragoza</h3>
            <p>Stylist</p>
        </div>
    </div>

    <!-- Divider under barbers -->
    <hr class="section-divider">
</section>

<!-- ABOUT --> 
<section class="section-with-poles" id="about">
    <h2 style="text-align: center; margin-bottom: 20px;">About Us</h2> 
    <p> Ariel’s Barbershop in Brgy. Canlalay, Biñan City, Laguna is dedicated to providing precise haircuts, professional grooming, and an exceptional customer experience. </p> 
    <p> Our goal is to make every visit seamless and enjoyable. By integrating online booking, digital queueing, style history management, and operational dashboards, we ensure that both customers and barbers enjoy an organized, efficient, and stress-free environment. </p>

    <hr class="section-divider">
</section>

<section id="contact"> 
    <h2>Contact</h2> 
    <p>Visit us or reach out to book your next appointment at Ariel’s Barbershop.</p> 
    <p>SBR Center Builders Inc, Unit 5 Lot 2115-5-3-G, Maribel Subd St, Maribel Sub, Canlalay, Biñan, Laguna</p> 
    <p>TM: 0965-893-2994 | SMART: 0949-415-9956</p> <p>Email: <a href="mailto:Trijoariel@yahoo.com">Trijoariel@yahoo.com</p> 
</section>

<!-- LIGHTBOX SCRIPT -->
<script>
  const modal = document.getElementById("imgModal");
  const modalImg = document.getElementById("modalImg");
  const closeModal = document.getElementById("closeModal");

  document.querySelectorAll(".catalog-card .carousel img, .barber-card img").forEach(img => {
      img.addEventListener("click", () => {
          modal.style.display = "flex";
          modalImg.src = img.src;
      });
  });

  closeModal.onclick = () => {
      modal.style.display = "none";
  };

  modal.onclick = (e) => {
      if(e.target === modal) modal.style.display = "none";
  };
</script>

<script>
  // ACTIVE NAV
  const sections=document.querySelectorAll("section");
  const links=document.querySelectorAll(".nav-link");

  window.addEventListener("scroll",()=>{
      let current="";
      sections.forEach(sec=>{
          if(pageYOffset>=sec.offsetTop-150){current=sec.id}
      });
      links.forEach(a=>{
          a.classList.remove("active");
          if(a.getAttribute("href")==="#"+current){
              a.classList.add("active");
          }
      });
  });

  // VIDEO AUTOPLAY + UNMUTE
  const video=document.getElementById('servicesVideo');
  const unmuteBtn=document.getElementById('unmuteBtn');

  function checkVideo(){
      const r=video.getBoundingClientRect();
      const h=window.innerHeight;
      if(r.top<h*0.6 && r.bottom>h*0.4){
          if(video.paused) video.play();
      }else{
          if(!video.paused) video.pause();
      }
  }
  window.addEventListener('scroll',checkVideo);
  window.addEventListener('load',checkVideo);

  unmuteBtn.onclick=()=>{
      video.muted=!video.muted;
      unmuteBtn.textContent=video.muted?'🔇 Muted':'🔊 Unmuted';
  };
</script>

</body>
</html>