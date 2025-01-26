<!doctype html>
<html>

<head>
<!--<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">-->
<!--<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>-->
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

.panel { display: none; }
.show { display: block; }

</style>





<title><?= esc($title) ?></title>

</head>

<body>

<!-- Top Navigation Menu -->
<div class="topnav">
  <a href="/">Jakiwynik.com  | <?=$title?> </a>
  <!-- Navigation links (hidden by default) -->
  <!--<div id="myLinks">
    <a class="nav-link" href="/live" id="Mecze live">Obecnie grają</a>
    <a class="nav-link" href="/rozgrywki/60/26" id="Ekstraklasa">Ekstraklasa (PL)</a>
    <a class="nav-link" href="/rozgrywki/209/24" id="1st_Liga" >1 liga (PL)</a>
    <a class="nav-link" href="/rozgrywki/1/27" id="1_Bundesliga">1. Bundesliga</a>
    <a class="nav-link" href="/rozgrywki/93/27" id="2_Bundesliga">2. Bundesliga</a>
    <a class="nav-link" href="/rozgrywki/2/20" id="Bundesliga">Premiership</a>
    <a class="nav-link" href="/rozgrywki/3/29" id="Bundesliga">La Liga</a>
  </div> -->
  <!-- "Hamburger menu" / "Bar icon" to toggle the navigation links -->
  <!-- <a href="javascript:void(0);" class="icon" onclick="myFunction()">
    <i class="fa fa-bars"></i>
  </a>-->
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