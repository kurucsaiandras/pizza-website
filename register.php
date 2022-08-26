<?php
session_start();

if(!isset($_SESSION['usertype']))
{
  $_SESSION['usertype'] = 'guest';
}
    
include 'pizza_data.php';
$link = getDb();
$empty=false;
$registered=false;

if (isset($_POST['pressed'])) {
    $name = mysqli_real_escape_string($link, $_POST['name']);
    $email = mysqli_real_escape_string($link, $_POST['email']);
    $adress = mysqli_real_escape_string($link, $_POST['adress']);
    $phone = mysqli_real_escape_string($link, $_POST['phone']);
    $password = mysqli_real_escape_string($link, $_POST['password']);

    if (!$name or !$email or !$adress or !$phone or !$password) {
        $empty=true;
    }
    else{
      $empty=false;
      //lekérdezzük, hogy regisztrálva van e már:
      $query = mysqli_query($link, "SELECT * FROM user WHERE email='$email' or phone='$phone'");
      if (mysqli_num_rows($query)==0){
          $createQuery = sprintf("INSERT INTO user(name, email, adress, phone, password) VALUES('%s', '%s', '%s', '%s', '%s')",
              $name,
              $email,
              $adress,
              $phone,
              $password
          );
          mysqli_query($link, $createQuery) or die(mysqli_error($link));
          header("Location: login.php");
      }
      else $registered=true;
    }
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
    <title>Pizzafaló - Regisztrálás</title>
</head>

<body>

  <?php include 'menu.php'; ?>

  <div class="container main-content" style="width: 400px">

    <?php if($empty==true): ?>
      <div class="alert alert-danger" role="alert">
        Kérem töltsön ki minden mezőt!
      </div>
    <?php endif; ?>

    <?php if($registered==true): ?>
      <div class="alert alert-danger" role="alert">
        A megadott E-mail címmel vagy telefonszámmal már van regisztrált felhasználó!
      </div>
    <?php endif; ?>

    <form method="post">

      <!-- Name input -->
      <div class="form-outline mb-4">
        <label class="form-label" for="form2Example1">Név</label>
        <input type="text" name="name" id="form2Example1" class="form-control" />
      </div>

      <!-- Email input -->
      <div class="form-outline mb-4">
        <label class="form-label" for="form2Example1">Email cím</label>
        <input type="email" name="email" id="form2Example1" class="form-control" />
      </div>

      <!-- Adress input -->
      <div class="form-outline mb-4">
        <label class="form-label" for="form2Example2">Cím</label>
        <input type="text" name="adress"id="form2Example2" class="form-control" />
      </div>

      <!-- Phone input -->
      <div class="form-outline mb-4">
        <label class="form-label" for="form2Example2">Telefonszám</label>
        <input type="text" name="phone"id="form2Example2" class="form-control" />
      </div>

      <!-- Password input -->
      <div class="form-outline mb-4">
        <label class="form-label" for="form2Example2">Jelszó</label>
        <input type="password" name="password"id="form2Example2" class="form-control" />
      </div>


      <!-- Submit button -->
      <div class="d-grid gap-2">
      <button type="submit" name="pressed" class="btn btn-primary btn-block mb-4">Regisztráció</button>
      </div>
      
    </form>
    <?php
        closeDb($link);
    ?>
  </div>
</body>
</html>