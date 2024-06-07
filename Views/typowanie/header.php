<!doctype html>
<html>

<head>
    <base href="<?= base_url(); ?>">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Typer Mistrzostw Świata w Katarze</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-Zenh87qX5JnK2Jl0vWa8Ck2rdkQ2Bzep5IDxbcnCeuOxjzrPF/et3URy9Bv1WTRi" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.3.0/font/bootstrap-icons.css">
    <link rel="stylesheet" type="text/css" href="/public/nowystyl_alpha_014.css">
    <!-- <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>-->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bangers&family=Black+Ops+One&display=swap" rel="stylesheet">

<!--<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">-->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>
<meta name="viewport" content="width=device-width, initial-scale=1">
<script src="https://code.jquery.com/jquery-3.6.0.js" integrity="sha256-H+K7U5CnXl1h5ywQfKtSj8PCmoN9aaq30gDh27Xc0jk=" crossorigin="anonymous"></script>
<!-- Load an icon library to show a hamburger menu (bars) on small screens -->
<!--<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">-->

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

/* Style the hamburger menu */
.topnav a.icon {
  background: black;
  display: block;
  position: absolute;
  right: 0;
  top: 0;
}

/* Add a grey background color on mouse-over */
.topnav a:hover {
  background-color: ##AAAE8E;
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
    <a class="nav-link" href="/typowanie" id="Mecze live">Pierwszy sposób typowania</a>
    <a class="nav-link" href="/wszystkieMecze" id="Ekstraklasa">Drugi sposob typowania (PL)</a>
    <a class="nav-link" href="/tabelaMecze" id="Ekstraklasa">Ranking (same mecze)</a>
    <a class="nav-link" href="/ileDokladnychTypow">Tabela tylko dokładnych typów</a>
    <a class="nav-link" href="/tabelaPytania" id="Ekstraklasa">Ranking (same pytania)</a>
    <a class="nav-link" href="/strzelcy" id="Strzelcy" >Najlepsi strzelcy turnieju</a>
    <a class="nav-link" href="/fazaGrupowa" id="Faza Grupowa" >Mecze fazy grupowej</a>
    <a class="nav-link" href="/zasady" id="1st_Liga" >Zasady typera</a>
    <a class="nav-link" href="/komentarzDoTypera" id="1st_Liga" >Komentarz odautorski</a>
    <a class="nav-link" href="/auth/logout" id="1_Bundesliga">Wyloguj Się</a>
  <!--  <a class="nav-link" href="/rozgrywki/93/27" id="2_Bundesliga">2. Bundesliga</a>
    <a class="nav-link" href="/rozgrywki/2/20" id="Bundesliga">Premiership</a>
    <a class="nav-link" href="/rozgrywki/3/29" id="Bundesliga">La Liga</a> -->
  </div>
  <!-- "Hamburger menu" / "Bar icon" to toggle the navigation links -->
  <a href="javascript:void(0);" class="icon" onclick="myFunction()">
  <h3><i class="bi bi-list"></i></h3>
  </a>
</div>

<script>

var element = document.getElementById("<?=$title?>");
element.classList.add("active");

/* Toggle between showing and hiding the navigation menu links when the user clicks on the hamburger menu / bar icon */
function myFunction() {
  var x = document.getElementById("myLinks");
  if (x.style.display === "block") {
    x.style.display = "none";
  } else {
    x.style.display = "block";
  }
}


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