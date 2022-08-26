<?php
session_start();

if(!isset($_SESSION['usertype']))
{
  $_SESSION['usertype'] = 'guest';
}

?>

<html>
<head>
  <meta charset="UTF-8" />
    <link rel="icon" href="favicon.ico" type="image/x-icon" />
    <link rel="stylesheet" href="pizzeria.css">
    <link
      rel="stylesheet"
      href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap"
    />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/css/bootstrap.min.css"
    rel="stylesheet" integrity="sha384-eOJMYsd53ii+scO/bJGFsiCZc+5NDVN2yr8+0RDqr0Ql0h+rP48ckxlpbzKgwra6"
    crossorigin="anonymous">
    <title>Pizzafaló</title>
</head>
<?php include 'menu.php'; ?>

<body style="
    background-image: url('https://m.blog.hu/ga/gastrogangster/image/7cfbbb77-3609-4151-8102-4709e07b1ce3.jpeg');
      background-position: center;
      background-size: 100% auto;
    ">
    <div class="container main-content">
      <div class="card text-white text-center" style="width: 50rem; margin:0 auto; background-color:rgba(0, 0, 0, 0.5);">
        <div class="card-body">
          <h2>Üdvözlünk a Pizzafaló Pizzéria oldalán!</h2>
          <p class="pt-5">Széleskörű választékunkból a hét minden napján 11.00 és 22.00 között kedvedre válogathatsz!</p>
          <a href="etlap.php"><button type="button" class="btn btn-success">Tekinsd meg étlapunkat!</button></a>
          <p class="pt-5">Futáraink állnak rendelkezésedre az egész nyitvatartási időben, így otthonra is rendelhetsz!</p>
        </div>
      </div>
    </div>
</body>

</html>