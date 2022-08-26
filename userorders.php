<?php
session_start();

if(!isset($_SESSION['usertype']))
{
  header("Location: index.php");
}

include 'pizza_data.php';
$link = getDb();

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
    <title>Pizzafaló - Megrendeléseim</title>
</head>

<body>
<?php include 'menu.php';
$querySelectOrders = sprintf("SELECT id, price, ordertime,
        IFNULL(shippingtime, 'FOLYAMATBAN') AS shippingtime, state FROM ordering
        WHERE user_id=%d ORDER BY ordertime DESC;", 
        mysqli_real_escape_string($link, $_SESSION['userid'])
        );
        
        $result_orders = mysqli_query($link, $querySelectOrders) or die(mysqli_error($link));
?>
  <div class="container main-content">
  <?php if(isset($_SESSION['justordered']) and $_SESSION['justordered']==true){
      echo "<div class='alert alert-success' role='alert'> A rendelése sikeresen elküldve! </div>";
      unset($_SESSION['justordered']);
  } ?>
    <div class="row">
      <div class="col-md-8">
        <h2>
        Megrendeléseim
        </h2>
        <table class="table table-hover">
          <thead>
          <tr>
            <th>Rendelési idő</th>
            <th>Rendelt termékek</th>
            <th>Kiszállítás ideje</th>
            <th></th>
          </tr>
          </thead>
          <tbody>
          <?php
          
          while ($row_order = mysqli_fetch_array($result_orders)):
          ?>
            <tr>
              <td><?=$row_order['ordertime']?></td>

              <td>
              <?php 
              $querySelectProducts = sprintf("SELECT amount, pname
              FROM ordering_has_product JOIN product ON product_id=product.id WHERE ordering_id=%d;", 
              mysqli_real_escape_string($link, $row_order['id'])
              );
              $result_products = mysqli_query($link, $querySelectProducts) or die(mysqli_error($link));
              while($row_products = mysqli_fetch_array($result_products)){
                echo $row_products['amount']." db ".$row_products['pname']."</br>";
              }
              ?>
              </td>

              <td><?=$row_order['shippingtime']?></td>
              <td><a href="edit_order.php?id=<?=$row_order['id']?>">Részletek</a></td>
            </tr>                
            <?php endwhile; ?>
            </tbody>
        </table>
      </div>

      <div class="col-md-4">
        <h2>
        Felhasználói adatok
        </h2>
        <?php 
          $querySelectUser=sprintf("SELECT name, email, adress, phone FROM user WHERE id=%d", mysqli_real_escape_string($link, $_SESSION['userid']));
          $result_user=mysqli_query($link, $querySelectUser);
          $userdata=mysqli_fetch_array($result_user);
        ?>
          <table class="table">
            <tbody>
                 <tr>
                     <th scope="row">Név</th>
                    <td><?=$userdata['name']?></td>
                </tr>
                <tr>
                    <th scope="row">Email cím</th>
                    <td><?=$userdata['email']?></td>
                </tr>
                 <tr>
                    <th scope="row">Szállítási cím</th>
                    <td><?=$userdata['adress']?></td>
                </tr>
                <tr>
                    <th scope="row">Telefonszám</th>
                    <td><?=$userdata['phone']?></td>
                </tr>
            </tbody>          
          </table>
        
      </div>
    </div>
    <?php
        closeDb($link);
    ?>
  </div>
</body>
</html>