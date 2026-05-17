<?php include 'header.php'; ?>
<?php

http_response_code(404);

// Auto Redirect After 8 Seconds
header("refresh:8;url=/pradip/");


?>

<style>

/* =========================
   FULLSCREEN 404
========================= */

.error-wrapper{
    min-height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
    background:
        linear-gradient(135deg,#020617,#0f172a);
    overflow:hidden;
    position:relative;
    padding:20px;
}

/* Animated Background */

.error-wrapper::before,
.error-wrapper::after{
    content:"";
    position:absolute;
    width:500px;
    height:500px;
    border-radius:50%;
    filter:blur(120px);
    opacity:0.15;
}

.error-wrapper::before{
    background:#2563eb;
    top:-150px;
    left:-150px;
}

.error-wrapper::after{
    background:#38bdf8;
    bottom:-150px;
    right:-150px;
}

/* Card */

.error-box{
    position:relative;
    z-index:2;
    width:100%;
    max-width:750px;
    background:rgba(255,255,255,0.06);
    border:1px solid rgba(255,255,255,0.08);
    backdrop-filter:blur(18px);
    border-radius:28px;
    padding:60px 40px;
    text-align:center;
    box-shadow:0 25px 60px rgba(0,0,0,0.45);
}

/* Big Text */

.error-box h1{
    font-size:160px;
    line-height:1;
    margin-bottom:10px;
    font-weight:900;
    color:transparent;
    background:linear-gradient(135deg,#38bdf8,#2563eb);
    -webkit-background-clip:text;
}

/* Title */

.error-box h2{
    font-size:36px;
    margin-bottom:20px;
    color:white;
}

/* Description */

.error-box p{
    font-size:18px;
    line-height:1.8;
    color:#cbd5e1;
    margin-bottom:35px;
}

/* Button Group */

.btn-group{
    display:flex;
    justify-content:center;
    gap:15px;
    flex-wrap:wrap;
}

/* Buttons */

.btn{
    padding:14px 28px;
    border-radius:14px;
    text-decoration:none;
    font-weight:700;
    transition:0.3s;
    display:inline-flex;
    align-items:center;
    justify-content:center;
}

/* Primary */

.btn-home{
    background:linear-gradient(135deg,#38bdf8,#2563eb);
    color:white;
    box-shadow:0 10px 25px rgba(37,99,235,0.35);
}

.btn-home:hover{
    transform:translateY(-4px);
}

/* Secondary */

.btn-back{
    background:rgba(255,255,255,0.08);
    color:white;
    border:1px solid rgba(255,255,255,0.08);
}

.btn-back:hover{
    background:rgba(255,255,255,0.15);
}

/* Countdown */

.redirect-info{
    margin-top:30px;
    font-size:14px;
    color:#94a3b8;
}

/* Floating Stars */

.star{
    position:absolute;
    width:4px;
    height:4px;
    background:white;
    border-radius:50%;
    opacity:0.5;
    animation:blink 3s infinite;
}

.star:nth-child(1){
    top:10%;
    left:20%;
}

.star:nth-child(2){
    top:30%;
    left:80%;
}

.star:nth-child(3){
    top:70%;
    left:15%;
}

.star:nth-child(4){
    top:85%;
    left:75%;
}

.star:nth-child(5){
    top:50%;
    left:50%;
}

@keyframes blink{
    0%,100%{
        opacity:0.2;
        transform:scale(1);
    }
    50%{
        opacity:1;
        transform:scale(1.8);
    }
}

/* Mobile */

@media(max-width:768px){

    .error-box{
        padding:45px 25px;
    }

    .error-box h1{
        font-size:100px;
    }

    .error-box h2{
        font-size:26px;
    }

    .error-box p{
        font-size:15px;
    }

    .btn{
        width:100%;
    }

}

</style>

<div class="error-wrapper">

    <!-- Stars -->
    <div class="star"></div>
    <div class="star"></div>
    <div class="star"></div>
    <div class="star"></div>
    <div class="star"></div>

    <div class="error-box">

        <h1>404</h1>

        <h2>Oops! Page Drifted Away</h2>

        <p>
            The page you requested could not be found.
            It may have been deleted, renamed,
            or never existed in the first place.
        </p>

        <div class="btn-group">

            <a href="/pradip/" class="btn btn-home">
                🏠 Homepage
            </a>

            <a href="javascript:history.back()" class="btn btn-back">
                ⬅ Go Back
            </a>

        </div>

        <div class="redirect-info">
            Automatic redirect to homepage in 8 seconds...
        </div>

    </div>

</div>

<?php include 'footer.php'; ?>