<?php
session_start();

if(!isset($_SESSION['usertype']) or $_SESSION['usertype']!='admin'){
  header("Location: index.php");
}
include 'pizza_data.php';
$link = getDb();
        
$querySelectOrders = "SELECT ordering.id AS order_id, name, adress, phone, email, price, ordertime,
IFNULL(shippingtime, 'FOLYAMATBAN') AS shippingtime, state
FROM user JOIN ordering ON user.id=ordering.user_id 
ORDER BY ordertime DESC";
$result_orders = mysqli_query($link, $querySelectOrders) or die(mysqli_error($link));

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
    <title>Pizzafaló - Megrendelések</title>
</head>

<body>

  <?php include 'menu.php' ?>

  <div class="container main-content">
    <h2>
    Megrendelések
    </h2>
    <?php
      $search = null;
       if (isset($_POST['search'])) {
           $search = $_POST['search'];
      }
    ?>
    <form class="form-inline" method="post">
            <div class="card">
                <div class="card-body">
                <div class="row">
                  <div class="col md-4">
                    Keresés email cím alapján: 
                  </div>
                  <div class="col md-8">
                    <input style="width:500px;margin-left:1em;" class="form-control" type="search" name="search" value="<?=$search?>">
                  </div>
                  <div class="col md-4">
                    <button class="btn btn-success" style="margin-left:1em;" type="submit" >Keresés</button>
                  </div>
                </div>
                </div>
            </div>
        </form>
    <table class="table table-hover">
      <thead>
        <tr>
          <th>Megrendelő neve</th>
          <th>Rendelési cím</th>
          <th>Telefonszám</th>
          <th>E-mail cím</th>
          <th>Fizetendő</th>
          <th>Rendelési idő</th>
          <th>Állapot</th>
          <th></th>
        </tr>
        </thead>
      <?php 
      if ($search){
        $querySelectOrders = sprintf("SELECT ordering.id AS order_id, name, adress, phone, email, price, ordertime,
        IFNULL(shippingtime, 'FOLYAMATBAN') AS shippingtime, state
        FROM user JOIN ordering ON user.id=ordering.user_id
        WHERE LOWER(email) LIKE '%%%s%%'
        ORDER BY ordertime DESC",
        mysqli_real_escape_string($link, strtolower($search)));
      }
      else {
      $querySelectOrders = "SELECT ordering.id AS order_id, name, adress, phone, email, price, ordertime,
      IFNULL(shippingtime, 'FOLYAMATBAN') AS shippingtime, state
      FROM user JOIN ordering ON user.id=ordering.user_id 
      ORDER BY ordertime DESC";
      }
      $result_orders = mysqli_query($link, $querySelectOrders) or die(mysqli_error($link));
      
      
      
      while ($row = mysqli_fetch_array($result_orders)): ?>
        <tbody>
        <tr>
          <td><?=$row['name']?></td>
          <td><?=$row['adress']?></td>
          <td><?=$row['phone']?></td>
          <td><?=$row['email']?></td>
          <td><?=$row['price']?> Ft.</td>
          <td><?=$row['ordertime']?></td>
          <?php if($row['state']==100): ?>
          <td class="bg-success text-white"><?=$row['state']?>%</td>
          <?php elseif($row['state']<=20): ?>
          <td class="bg-danger text-white"><?=$row['state']?>%</td>
          <?php else: ?>
          <td class="bg-warning text-white"><?=$row['state']?>%</td>
          <?php endif; ?>
          <td><a href="edit_order.php?id=<?=$row['order_id']?>">Szerkesztés</a></td>
        </tr> 
        </tbody>               
        <?php endwhile; ?>
    </table>
    <?php
        closeDb($link);
    ?>
  </div>
</body>
</html>