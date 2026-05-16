<!doctype html>
<html data-bs-theme="light">

<head>
    <base href="<?= base_url(); ?>">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Piłkarski typer na <?=esc($title); ?></title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" type="text/css" href="/public/nowystyl_alpha_017.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bangers&family=Black+Ops+One&display=swap" rel="stylesheet">



<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.js" integrity="sha256-H+K7U5CnXl1h5ywQfKtSj8PCmoN9aaq30gDh27Xc0jk=" crossorigin="anonymous"></script>


<style>
  /* Style the navigation menu */
.topnav {
  overflow: hidden;
  background-color: #3a606e;
  position: relative;
  margin-bottom: 10px;
}

/* Hide the links inside the navigation menu (except for logo/home) */
.topnav #myLinks {
  display: none;
}

/* Style navigation menu links */
.topnav a {
  color: white;
  padding: 14px 16px;
  text-decoration: none;
  font-size: 17px;
  display: block;
}

/* Stylowanie ikonek */
.topnav .icon-group {
  position: absolute;
  right: 0;
  top: 0;
  display: flex;
  background: black;
}
.topnav .icon-group a {
  color: white;
  padding: 14px 16px;
  text-decoration: none;
  display: block;
}




/* Add a grey background color on mouse-over */
.topnav a:hover {
  background-color: #AAAE8E;
  color: black;
}

/* Style the active link (or home/logo) */
.active {
  background-color: #04AA6D;
  color: white;
}

/* Set height of body and the document to 100% to enable "full page tabs" */
body, html {
  height: 100%;
  margin: 0;
  font-family: Arial;
}

/* Style tab links */
.tablink {
  background-color: #607b7d;
  color: white;
  float: left;
  border: none;
  outline: none;
  cursor: pointer;
  padding: 14px 16px;
  font-size: 17px;
  width: 33%;
}

.tablink:hover {
  background-color: #777;
}

/* Style the tab content (and add height:100% for full page content) */
.tabcontent {
  color: white;
  display: none;
  padding: 100px 20px;
  /*height: 100%;*/
}

.accordion-body{
  color: black;
}

#Home {background-color: #828e82;}
#News {background-color: #828e82;}
#Contact {background-color: #828e82;}
#About {background-color: #828e82;}

.collapsible {
  background-color: #777;
  color: white;
  cursor: pointer;
  padding: 18px;
  width: 100%;
  border: none;
  text-align: left;
  outline: none;
  font-size: 15px;
}

  .notyetcollapsible {
  background-color: #a0a0a0;
  color: white;
  cursor: pointer;
  padding: 18px;
  width: 100%;
  border: none;
  text-align: left;
  outline: none;
  font-size: 15px;
}

.active, .collapsible:hover {
  background-color: #555;
}

.collapsible:after {
  content: "\2139";
  color: white;
  font-weight: bold;
  float: right;
  margin-left: 5px;
}

.active:after {
  content: "\2716";
}

.rozgrywki_370:before, .rozgrywki_371:before {
content:"🤝 | ";
}

.rozgrywki_362:before{
	content:"🏆🌏 | ";
}

.rozgrywki_244:before{
	content:"🏆🇪🇺 LM | ";
	}

.rozgrywki_1:before{
	content:"🇩🇪 | ";
}

.rozgrywki_167:before{
	content:"🏆🇩🇪 | ";
}

.rozgrywki_2:before{
	content:"🏴󠁧󠁢󠁥󠁮󠁧󠁿 | ";
}

.rozgrywki_3:before{
	content:"🇪🇸 | ";
}

.rozgrywki_334:before{
	content:"🏆🇪🇸 | ";
}


.rozgrywki_4:before{
	content:"🇮🇹 | ";
}

.rozgrywki_60:before{
	content:"🇵🇱ᴱ | ";
}
.rozgrywki_209:before{
	content:"🇵🇱\00b9ᴸ | ";
}
.rozgrywki_245:before{
	content:"🇪🇺 LE | ";
}
.rozgrywki_446:before{
	content:"🇪🇺 LK | ";
}

.content {
  padding: 0 18px;
  max-height: 0;
  overflow: hidden;
  transition: max-height 0.2s ease-out;
  background-color: #f1f1f1;
}

p.h {
text-align: left;
}

p.a {
text-align: right;
}

footer{
background:#111;
padding-top:25px;
color:#fff;

	}
	
.footer-content{
display: flex;
align-items: center;
justify-content: center;
flex-direction:column;
text-align:center;

}

.footer-content h4{
font-size:1.5rem;
font-weight: 500;
line-height:2rem;
}

.footer-content h4 sup{
font-weight:300;
font-size:11px;
}

.footer-content p{
font-size:14px;
margin: 10px auto;
line-height:20px;
color: #cacdd2;
}

.footer-content p a{

color:#44bea8;
font-size:14px;

}

.footer-bottom{
background:#000;
padding:15px;
padding-bottom: 20px;
text-align:center;

}

.footer-bottom p{
float:left;
word-spacing:2px;
font-size:13px;
}

.footer-bottom p a{

color:#44bea8;
font-size:14px;

}

.gracz{
text-transform:capitalize;
}

.gracz goal{
font-weight:600;
}

.gracz subin{
color:#116b22;
font-weight: 500;
}

.gracz subout{
color:#919191;
font-weight:300;
}
.panel {
  display: none; 
}

.show { 
  display: block; 
}

.match-container {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  margin-bottom: 2rem;
}

.match-entry {
  border: 1px solid #d9d9d9;
  border-radius: 8px;
  padding: 1rem;
  background-color: white;
  box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.match-header {
  font-weight: bold;
  color: #333;
  margin-bottom: 0.5rem;
}

.match-details,
.match-form {
  padding: 0.5rem 0;
  border-top: 1px solid #ececec;
}

.match-details {
  display: none;
}

.match-form {
  display: none;
}

.btn-toggle {
  background-color: #007bff;
  color: white;
  border: none;
  padding: 0.375rem 0.75rem;
  border-radius: 4px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
}

.btn-toggle .icon {
  transform: rotate(0deg);
  transition: transform 0.3s ease-in-out;
}

.btn-toggle.expanded .icon {
  transform: rotate(180deg);
}

@media (max-width: 768px) {
  .match-container {
    padding: 0.5rem;
  }

  .match-entry {
    padding: 0.5rem;
  }

  .btn-toggle {
    padding: 0.25rem 0.5rem;
  }
}

[data-bs-theme="dark"] .topnav {
    background-color: #1a2e35;
}
[data-bs-theme="dark"] .topnav a {
    color: #e0e0e0;
}
[data-bs-theme="dark"] .topnav a.icon {
    background: #111;
}
[data-bs-theme="dark"] .accordion-button {
    background-color: #2b2b2b;
    color: #e0e0e0;
}
[data-bs-theme="dark"] .accordion-button:not(.collapsed) {
    background-color: #1e3a40;
    color: #fff;
}
[data-bs-theme="dark"] .accordion-item {
    background-color: #1e1e1e;
    border-color: #444;
}
[data-bs-theme="dark"] .score-display {
    color: #fff;
}
[data-bs-theme="dark"] .betting-hints {
    color: #aaa;
}


</style>





<title><?=$title?> | JakiWynik.com</title>
<link rel="canonical" href="<?=site_url()?>">
</head>

<body>

<!-- Top Navigation Menu -->
<div class="topnav">
  <a href="/typowanie"><?=$title?> </a>
  <!-- Navigation links (hidden by default) -->
   <div id="myLinks">
    <a class="nav-link" href="/zasady" id="1st_Liga" >Zasady typera</a> 
    <a class="nav-link" href="/profil" id="profil">edycja preferencji</a>
    <a class="nav-link" href="/auth/logout" id="1_Bundesliga">Wyloguj Się</a>
  </div> 
<!-- MA BYĆ: -->
  <div class="icon-group">
    <a href="javascript:void(0);" id="themeToggle" onclick="toggleTheme()" title="Zmień motyw">
      <i class="bi bi-moon-fill" id="themeIcon"></i>
    </a>
    <a href="javascript:void(0);" onclick="myFunction()">
      <i class="bi bi-list"></i>
    </a>
  </div>
</div>



</div>

<script>

var element = document.getElementById("<?=$title?>");
if (element) { element.classList.add("active"); }

/* Toggle between showing and hiding the navigation menu links when the user clicks on the hamburger menu / bar icon */
function myFunction() {
  var x = document.getElementById("myLinks");
  if (x.style.display === "block") {
    x.style.display = "none";
  } else {
    x.style.display = "block";
  }
}


function initTheme() {
    var saved = localStorage.getItem('theme') || 'light';
    document.documentElement.setAttribute('data-bs-theme', saved);
    document.getElementById('themeIcon').className =
        saved === 'dark' ? 'bi bi-sun-fill' : 'bi bi-moon-fill';
}

function toggleTheme() {
    var current = document.documentElement.getAttribute('data-bs-theme');
    var next = current === 'dark' ? 'light' : 'dark';
    document.documentElement.setAttribute('data-bs-theme', next);
    localStorage.setItem('theme', next);
    document.getElementById('themeIcon').className =
        next === 'dark' ? 'bi bi-sun-fill' : 'bi bi-moon-fill';
}

initTheme();


</script>

<?php
    $session = \Config\Services::session();
    $sukces = $session->getFlashData("success");
    $fail = $session->getFlashData("fail");
 

    if ($sukces){

      ?>
      <div class="alert alert-success">
        
        <?
        echo $sukces;
        ?>

      </div>

      <?
    } else if ($fail){

      ?>
      <div class="alert alert-danger">
        <?
        echo $fail;
        ?>
      </div>

      <?
    }



    ?>
    <div class="container">