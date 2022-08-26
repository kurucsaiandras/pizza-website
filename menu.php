

<div
  class="img-fluid" style="
  background-image: url('https://upload.wikimedia.org/wikipedia/commons/c/c8/Pizza_Margherita_stu_spivack.jpg');
    height: 200px;
    background-position: center;
    background-size: 100% auto;
  "
>
  <h1 class="text-white px-5 pt-5">Pizzafaló Pizzéria</h1>
  <h5 class="text-white px-5">A legfinomabb pizzák a városban!</h5>
</div>


<nav class="navbar navbar-expand-lg navbar-light bg-light">
  <div class="container">
    <nav class="collapse navbar-collapse">
      <a class="nav-link" href="index.php">Főoldal</a>
      <a class="nav-link" href="etlap.php">Étlap</a>
      <?php
          if($_SESSION['usertype'] == 'user')
          {
            echo '<a class="nav-link" href="userorders.php">Rendeléseim</a>';
          }
          else if($_SESSION['usertype']=='admin')
          {
            echo '<a class="nav-link" href="adminorders.php">Rendelések</a>';
            echo '<a class="nav-link" href="storage.php">Raktárkészlet</a>';
            echo '<a class="nav-link" href="userorders.php">Rendeléseim</a>';
          }
          else{
             echo '<a class="nav-link" href="login.php">Belépés</a>';
             echo '<a class="nav-link" href="register.php">Regisztráció</a>';
          }
      ?>
    </nav>
    <nav class="collapse navbar-collapse">
      <?php
            if($_SESSION['usertype'] == 'user' or $_SESSION['usertype']=='admin')
            {
              echo 'Belépve mint ';
              echo $_SESSION['username'];
              echo '<a class="nav-link" href="logout.php">Kijelentkezés</a>';
            }
        ?>
    </nav>
  </div>
</nav>

