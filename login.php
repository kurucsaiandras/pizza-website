<?php
session_start();

if(!isset($_SESSION['usertype']))
{
  $_SESSION['usertype'] = 'guest';
}

include 'pizza_data.php';
$link = getDb();
$empty=false;
$nomail=false;
$badpass=false;

if (isset($_POST['pressed'])) {
    $email = mysqli_real_escape_string($link, $_POST['email']);
    $password = mysqli_real_escape_string($link, $_POST['password']);
    
    if (!$email or !$password) {
      $empty=true;
    }

    else{
      $empty=false;
      $query= sprintf("SELECT * FROM user WHERE email='%s'", mysqli_real_escape_string($link, $email));
      $result = mysqli_query($link, $query);
      if (mysqli_num_rows($result)==0){
          $nomail=true;
      }
      //az email címhez tartozó jelszó kiolvasása az adatbázisból és ellenőrzés:
      else {
          $nomail=false;
          $userdata=mysqli_fetch_array($result);
          if($userdata['password']==$password){  
              //felhasználói jogok és adatok hozzárendelése
              $_SESSION['usertype']=$userdata['usertype'];
              $_SESSION['username']=$userdata['name'];
              $_SESSION['userid']=$userdata['id'];
              if($_SESSION['usertype']=='admin'){
                  header("Location: adminorders.php");
              }
              else header("Location: etlap.php");
          }
          else $badpass=true;
      }
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
      <title>Pizzafaló - Belépés</title>
  </head>
  <body>

    <?php include 'menu.php'; ?>

    <div class="container main-content" style="width: 400px">

      <?php if($empty==true): ?>
        <div class="alert alert-danger" role="alert">
          Kérem töltsön ki minden mezőt!
        </div>
      <?php endif; ?>

      <?php if($nomail==true): ?>
        <div class="alert alert-danger" role="alert">
          A megadott E-mail címmel nincs regisztrált felhasználó!
        </div>
      <?php endif; ?>

      <?php if($badpass==true): ?>
        <div class="alert alert-danger" role="alert">
          Helytelen jelszó!
        </div>
      <?php endif; ?>

      <form method="post">
        <!-- Email input -->
        <div class="form-outline mb-4">
          <label class="form-label" for="form2Example1">Email cím</label>
          <input type="email" name="email" id="form2Example1" class="form-control" />
        </div>

        <!-- Password input -->
        <div class="form-outline mb-4">
          <label class="form-label" for="form2Example2">Jelszó</label>
          <input type="password" name="password"id="form2Example2" class="form-control" />
        </div>

        <!-- Submit button -->
        <div class="d-grid gap-2">
        <button type="submit" name="pressed" class="btn btn-primary btn-block mb-4">Belépés</button>
        </div>

        <!-- Register buttons -->
        <div class="text-center">
          <p>Még nincs fiókja? <a href="register.php">Regisztráljon itt!</a></p>
        </div>
      </form>

      <?php
        closeDb($link);
      ?>

    </div>
  </body>
</html>