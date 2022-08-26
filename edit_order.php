<?php
session_start();

if(!isset($_SESSION['usertype']) or ($_SESSION['usertype']!='admin' and $_SESSION['usertype']!='user') or !isset($_GET['id']))
{
  header("Location: index.php");
}
include 'pizza_data.php';

$link = getDb();
$order_id=($_GET['id']);
$querySelectUser=sprintf("SELECT user.id, name, email, adress, phone, ordertime, IFNULL(shippingtime, 'FOLYAMATBAN') AS shippingtime, price, state FROM user
  JOIN ordering ON user.id=ordering.user_id
  WHERE ordering.id=%d;",
  mysqli_real_escape_string($link, $order_id)
  );
$result_user = mysqli_query($link, $querySelectUser) or die(mysqli_error($link));
$userdata = mysqli_fetch_array($result_user);

$querySelectOrder=sprintf("SELECT pname, amount, product.price AS prod_price
  FROM ordering_has_product
  JOIN product ON product_id=product.id
  WHERE ordering_id=%d;",
  mysqli_real_escape_string($link, $order_id)
  );
 $result_order = mysqli_query($link, $querySelectOrder) or die(mysqli_error($link));

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
    <script language="JavaScript" type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js" ></script>
    <script src="https://comet-server.com/CometServerApi.js" type="text/javascript"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyA0KfVHVfkt3W7dY_o7vnUaZLn-ZUlEUXY&callback=initMap&libraries=&v=weekly" async ></script>

    <style type="text/css">
      #map {
        height: 400px;
        width: 100%;
      }
    </style>

    <script type="text/javascript">
    //GOOGLE MAPS SCRIPT:
    function initMap() {
        const map = new google.maps.Map(document.getElementById("map"), {
        zoom: 15,
        });
        const geocoder = new google.maps.Geocoder();
        geocodeAddress(geocoder, map);
    }

    function geocodeAddress(geocoder, resultsMap) {
        const address = "<?php echo $userdata['adress']; ?>";
        geocoder.geocode({ address: address }, (results, status) => {
            if (status === "OK") {
            resultsMap.setCenter(results[0].geometry.location);
            new google.maps.Marker({
                map: resultsMap,
                position: results[0].geometry.location,
            });
            } else {
            alert(
                "Geocode was not successful for the following reason: " + status
            );
            }
        });
    }
    //COMET WEBSOCKET SCRIPT:
    $(document).ready(function()
    { 
        cometApi.start({node:"app.comet-server.ru", dev_id:15 })
        
        cometApi.subscription("simplechat.newMessage", function(event){
            var orderid = "<?php echo $order_id; ?>";
            var usertype = "<?php echo $_SESSION['usertype']; ?>";
            if(event.data.name == orderid || usertype=="admin"){
                $("#realtime_bar").html('<div class="progress"> <div class="progress-bar bg-dark progress-bar-striped progress-bar-animated" role="progressbar" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100" style="width: '+HtmlEncode(event.data.text)+'%"></div> </div>')
            }
        })
    })

    function HtmlEncode(s)
    {
    var el = document.createElement("div");
    el.innerText = el.textContent = s;
    s = el.innerHTML;
    return s;
    }
        
    function send(process)
    {
    var orderid = "<?php echo $order_id; ?>";
    $.ajax({ //küldés a comet szerverre a real time válasz miatt
            url: "https://comet-server.com/doc/CppComet/chat-example/chat.php",
            type: "POST", 
            data:"text="+encodeURIComponent(process)+"&name="+encodeURIComponent(orderid)
    });

    $.ajax({ //küldés az adatbázisba a rögzítés miatt
            url: "/pizzeria/insertdata.php",
            type: "POST", 
            data:"process="+encodeURIComponent(process)+"&orderid="+encodeURIComponent(orderid)
    });
    }

    </script>

</head>

<body>
<?php include 'menu.php' ?>

<div class="container main-content">
    <div>
        <h2>Megrendelés elkészítése</h2>
    </div>  
  
    <div class="container sub-content">
        <div id="realtime_bar">
            <div class="progress"> <!-- ide a databaseből lekérdezzük a bar állását-->
                <div class="progress-bar bg-dark progress-bar-striped progress-bar-animated" role="progressbar" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo $userdata['state'];?>%"></div>
            </div>
        </div>   

        <?php if($_SESSION['usertype']=='admin'): //admin oldalon: ?>

        <div class="row text-center mt-3">
            <div class="col">
            <input type="button" class="btn btn-outline-dark" onclick="send(20);" value="Rendelés elküldve" >
            </div>
            <div class="col">
            <input type="button" class="btn btn-outline-dark" onclick="send(40);" value="Rendelés elfogadva" >
            </div>
            <div class="col">
            <input type="button" class="btn btn-outline-dark" onclick="send(60);" value="Rendelés készül" >
            </div>
            <div class="col">
            <input type="button" class="btn btn-outline-dark" onclick="send(80);" value="Kiszállítás" >
            </div>
            <div class="col">
            <input type="button" class="btn btn-outline-dark" onclick="send(100);" value="Rendelés kiszállítva" >
            </div>
        </div>

        <?php endif;

        if($_SESSION['usertype']=='user'): //user oldalon: ?>

        <div class="row text-center">
            <div class="col">
                Rendelés elküldve
            </div>
            <div class="col">
                Rendelés elfogadva
            </div>
            <div class="col">
                Rendelés készül
            </div>
            <div class="col">
                Kiszállítás
            </div>
            <div class="col">
                Rendelés kiszállítva
            </div>
        </div>

        <?php endif; ?>

    </div>

    <div class="row">
        <div class="col-md-4">
            <h5>Megrendelő adatai</h5>
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
                    <tr>
                        <th scope="row">Rendelés ideje</th>
                        <td><?=$userdata['ordertime']?></td>
                    </tr>
                    <tr>
                        <th scope="row">Kiszállítás ideje</th>
                        <td><?=$userdata['shippingtime']?></td>
                    </tr>
                    <tr>
                        <th scope="row">Fizetendő</th>
                        <td><?=$userdata['price']?> Ft.</td>
                    </tr>
                </tbody>          
            </table>
        </div>
        <div class="col-md-4">
        <?php if ($_SESSION['usertype']=='admin')
            echo   '<h5>Térkép</h5>
                    <div id="map"></div>';
            else echo 
                   '<img src="https://cdn.dribbble.com/users/477729/screenshots/3386182/pizza1.gif" class="img-fluid">';
            ?>
        </div>
        <div class="col-md-4">
            <h5>Rendelt termékek</h5>
            <table class="table">
                <thead>
                    <tr>
                    <th scope="col">Terméknév</th>
                    <th scope="col">Tételszám</th>
                    <th scope="col">Egységár</th>
                    <th scope="col">Ár összesen</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = mysqli_fetch_array($result_order)): ?>
                    <tr>
                    <td><?=$row['pname']?></td>
                    <td><?=$row['amount']?></td>
                    <td><?=$row['prod_price']?></td>
                    <td><?=$row['prod_price']*$row['amount']?></td>
                    <?php endwhile; ?>
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