<?php

// ==========================
// 404 STATUS
// ==========================
http_response_code(404);

// ==========================
// AUTO REDIRECT
// ==========================
header("refresh:10;url=/pradip/");
?>

<style>

/* =========================
   404 PAGE
========================= */

.error-page{
    min-height:80vh;
    display:flex;
    justify-content:center;
    align-items:center;
    padding:40px 20px;
    background:
        radial-gradient(circle at top left, rgba(37,99,235,0.15), transparent 30%),
        radial-gradient(circle at bottom right, rgba(56,189,248,0.12), transparent 30%);
}

.error-card{
    width:100%;
    max-width:700px;
    text-align:center;
    padding:50px 35px;
    border-radius:24px;
    background:rgba(255,255,255,0.05);
    backdrop-filter:blur(14px);
    border:1px solid rgba(255,255,255,0.08);
    box-shadow:0 20px 50px rgba(0,0,0,0.2);
    position:relative;
    overflow:hidden;
}

/* Glow Effect */

.error-card::before{
    content:"";
    position:absolute;
    width:250px;
    height:250px;
    background:#2563eb;
    filter:blur(120px);
    opacity:0.2;
    top:-100px;
    left:-100px;
}

/* 404 Number */

.error-code{
    font-size:120px;
    font-weight:900;
    line-height:1;
    background:linear-gradient(135deg,#38bdf8,#2563eb);
    -webkit-background-clip:text;
    -webkit-text-fill-color:transparent;
    margin-bottom:15px;
}

/* Title */

.error-title{
    font-size:32px;
    margin-bottom:15px;
    color:var(--text,#fff);
}

/* Text */

.error-text{
    font-size:17px;
    color:var(--muted,#cbd5e1);
    line-height:1.7;
    margin-bottom:30px;
}

/* Redirect */

.redirect-text{
    margin-top:20px;
    color:#94a3b8;
    font-size:14px;
}

/* Button */

.home-btn{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    gap:10px;
    padding:14px 28px;
    border-radius:14px;
    text-decoration:none;
    font-weight:700;
    transition:0.3s;
    background:linear-gradient(135deg,#38bdf8,#2563eb);
    color:white;
    box-shadow:0 10px 25px rgba(37,99,235,0.3);
}

.home-btn:hover{
    transform:translateY(-3px);
    box-shadow:0 15px 35px rgba(37,99,235,0.4);
}

/* Floating Circle */

.circle{
    position:absolute;
    border-radius:50%;
    background:rgba(255,255,255,0.05);
    animation:float 6s infinite ease-in-out;
}

.circle.one{
    width:80px;
    height:80px;
    top:20px;
    right:30px;
}

.circle.two{
    width:50px;
    height:50px;
    bottom:30px;
    left:40px;
    animation-delay:2s;
}

/* Animation */

@keyframes float{
    0%{
        transform:translateY(0px);
    }
    50%{
        transform:translateY(-15px);
    }
    100%{
        transform:translateY(0px);
    }
}

/* Mobile */

@media(max-width:768px){

    .error-code{
        font-size:90px;
    }

    .error-title{
        font-size:24px;
    }

    .error-text{
        font-size:15px;
    }

    .error-card{
        padding:40px 25px;
    }

}

</style>

<div class="error-page">

    <div class="error-card">

        <div class="circle one"></div>
        <div class="circle two"></div>

        <div class="error-code">
            404
        </div>

        <h1 class="error-title">
            Lost in Space
        </h1>

        <p class="error-text">
            The page you are trying to access does not exist,
            may have been moved, or is temporarily unavailable.
        </p>

        <a href="/pradip/" class="home-btn">
            ⬅ Return to Homepage
        </a>

        <div class="redirect-text">
            Redirecting automatically in 5 seconds...
        </div>

    </div>

</div>
