<?php
session_start();

include 'pizza_data.php';
$link = getDb();
if(!isset($_SESSION['usertype']))
{
  $_SESSION['usertype'] = 'guest';
}
if(!isset($_SESSION['order_price']))
{
  $_SESSION['order_price'] = 0;
}

//LEKÉRDEZZÜK AZ INGREDIENT TÖMB ID-JAIT A CART SESSION-HÖZ
if(!isset($_SESSION['ingredients_in_cart']))
{
    $querySelectIngredients = "SELECT id FROM ingredient";
    $result_Ingredients = mysqli_query($link, $querySelectIngredients) or die(mysqli_error($link));
    while ($row=mysqli_fetch_array($result_Ingredients)){
        $id = $row['id'];
        $ingr_usedAmount[$id] = 0;
    }
    $_SESSION['ingredients_in_cart']=$ingr_usedAmount;
}

//TERMÉK HOZZÁADÁSA A KOSÁRHOZ
if (isset($_POST['add_to_cart']) and $_POST['order_quantity']>0){
    $product_id = $_POST['product_id'];
    $quantity = $_POST['order_quantity'];

    //CSAK AKKOR ADJUK HOZZÁ HA VAN ELEGENDŐ ALAPANYAG
    $querySelectOrder = "SELECT ingredient_id, product_has_ingredient.amount AS in_prod_amount, ingredient.amount AS available_amount FROM
     product_has_ingredient JOIN ingredient ON ingredient.id=ingredient_id WHERE product_id=".mysqli_real_escape_string($link, $product_id);
    $result_orderedIngredients = mysqli_query($link, $querySelectOrder) or die(mysqli_error($link));
    
    $is_over_max=false;

    while ($row = mysqli_fetch_array($result_orderedIngredients)){
        $id = $row['ingredient_id'];
        if($_SESSION['ingredients_in_cart'][$id] + $row['in_prod_amount']*$quantity > $row['available_amount']){
            $is_over_max = true;
            break;
        }
    }

    if(!$is_over_max){  //HA VAN ELEGENDŐ ALAPANYAG
        mysqli_data_seek($result_orderedIngredients, 0);    //eredmény array elejére állítjuk

        while ($row = mysqli_fetch_array($result_orderedIngredients)){  //hozzáadjuk a session-höz az alapanyagokat
        $id = $row['ingredient_id'];
        $_SESSION['ingredients_in_cart'][$id] += $row['in_prod_amount']*$quantity;
        }

        $querySelectOrder = "SELECT * FROM product WHERE id=".mysqli_real_escape_string($link, $product_id);
        $result_orderedItem = mysqli_query($link, $querySelectOrder) or die(mysqli_error($link));

        $orderedItemData = mysqli_fetch_array($result_orderedItem);

        $name = $orderedItemData['pname'];
        $price = $orderedItemData['price'];

        $cartArray = array(     //új elem a shopping cart-ban
            $product_id => array(
                'id' => $product_id,
                'name' => $name,
                'quantity' => $quantity,
                'price' => $price)
        );

        if(empty($_SESSION['shopping_cart'])){  //ha nincs még shopping cart, létrehozzuk
            $_SESSION['shopping_cart'] = $cartArray;
        }
        else{   //ha van shopping cart
            $is_in_cart=false;
            foreach($_SESSION['shopping_cart'] as $key => $value) {
                if($product_id == $value['id']){    //ha már bennevan a kosárban akkor csak a mennyiséget növeljük
                    $_SESSION["shopping_cart"][$key]['quantity'] = $value['quantity'] + $quantity;
                    $is_in_cart = true;
                    break;
                }
            } 
            if(!$is_in_cart){
                $_SESSION['shopping_cart'] = array_merge($_SESSION['shopping_cart'], $cartArray);
            }
            
        }
        header("Location: etlap.php");  //hogy refresh-nél ne duplikálja a mennyiséget
    }
}

if (isset($_POST['remove_from_cart'])){     //TERMÉK ELTÁVOLÍTÁSA A KOSÁRBÓL

    if(!empty($_SESSION['shopping_cart'])) {
        foreach($_SESSION['shopping_cart'] as $key => $value) {
            if($_POST["item_id"] == $value['id']){
                unset($_SESSION["shopping_cart"][$key]);
                //Kivonjuk a listából a tartalmazott összetevőket
                $querySelectIngredients = "SELECT ingredient_id, amount FROM product_has_ingredient WHERE product_id=".mysqli_real_escape_string($link, $_POST["item_id"]);
                $result_Ingredients = mysqli_query($link, $querySelectIngredients) or die(mysqli_error($link));
                while ($row=mysqli_fetch_array($result_Ingredients)){
                    $id=$row['ingredient_id'];
                    $_SESSION["ingredients_in_cart"][$id] -= $row['amount']*$_POST['item_quantity'];
                }
                break;
            }
        } 
        
        if(empty($_SESSION["shopping_cart"])){
            unset($_SESSION["shopping_cart"]);
            unset($_SESSION["ingredients_in_cart"]);
        }
    }
}

if (isset($_POST['order_send'])){   //RENDELÉS ELKÜLDÉSEKOR
    if($_SESSION['usertype']!='guest'){   
        //RENDELÉS HOZZÁRENDELÉSE A FELHASZNÁLÓHOZ
        $queryInsertOrder = sprintf("INSERT INTO ordering(ordertime, price, user_id) VALUES(current_timestamp(), %d,  %d)",
            mysqli_real_escape_string($link, $_SESSION['order_price']),
            mysqli_real_escape_string($link, $_SESSION['userid'])
        );
        mysqli_query($link, $queryInsertOrder) or die(mysqli_error($link));
        $result_id = mysqli_query($link, "SELECT LAST_INSERT_ID() AS id;");
        $order_id = mysqli_fetch_array($result_id)['id'];

        foreach($_SESSION['shopping_cart'] as $key => $value){
            $product_id = $value['id'];

            //RAKTÁRKÉSZLET CSÖKKENTÉSE
            $querySelectIngredients = sprintf("SELECT i.id AS ingredient_id, pi.amount AS ingredient_amount
                FROM product p
                JOIN product_has_ingredient pi ON p.id=pi.product_id
                JOIN ingredient i ON i.id=pi.ingredient_id
                WHERE p.id=%d",
                $product_id
            );
            $result_ingredient = mysqli_query($link, $querySelectIngredients) or die(mysqli_error($link));
            while ($row = mysqli_fetch_array($result_ingredient)){
                $queryDecreaseIngedient = sprintf("UPDATE ingredient SET amount = amount-%d where id=%d",
                    $value['quantity']*$row['ingredient_amount'],
                    $row['ingredient_id']
                );
                mysqli_query($link, $queryDecreaseIngedient) or die(mysqli_error($link));
            }

            //TERMÉKEK HOZZÁRENDELÉSE A RENDELÉSHEZ

            $queryEditOrder = sprintf("INSERT INTO ordering_has_product(ordering_id, product_id, amount) VALUES(%d, %d, %d)",
                mysqli_real_escape_string($link, $order_id),
                mysqli_real_escape_string($link, $product_id),
                mysqli_real_escape_string($link, $value['quantity'])
            );
            mysqli_query($link, $queryEditOrder) or die(mysqli_error($link));
        }
        unset($_SESSION['shopping_cart']);
        unset($_SESSION["ingredients_in_cart"]);
        unset($_SESSION['order_price']);
        $_SESSION['justordered']=true;
        header("Location: userorders.php");
    }
}

if(isset($_POST['edit_etlap'])){
    $_SESSION['editmode']=true;
}

if(isset($_POST['edit_etlap_off'])){
    unset($_SESSION['editmode']);
}

if(isset($_POST['remove_product'])){    //PIZZA TÖRLÉSE AZ ÉTLAPRÓL
    $queryDeleteOrder = "DELETE FROM ordering_has_product WHERE product_id=".mysqli_real_escape_string($link, $_POST["product_id"]);
    mysqli_query($link, $queryDeleteOrder) or die(mysqli_error($link));
    $queryDeleteProduct = "DELETE FROM product_has_ingredient WHERE product_id=".mysqli_real_escape_string($link, $_POST["product_id"]);
    mysqli_query($link, $queryDeleteProduct) or die(mysqli_error($link));
    $queryDeleteProduct = "DELETE FROM product WHERE id=".mysqli_real_escape_string($link, $_POST["product_id"]);
    mysqli_query($link, $queryDeleteProduct) or die(mysqli_error($link));
}

if(isset($_POST['edit_product']))

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
    <title>Pizzafaló - Étlap</title>
</head>


<body>
    
    <?php 
        include 'menu.php';
        $querySelectPizzas = "SELECT * FROM product";
        $result_menu = mysqli_query($link, $querySelectPizzas) or die(mysqli_error($link));
    ?>
    <div class="container main-content">
        <div class="row">
            <div class="col-md-8">
                <h2>
                    Rendelhető pizzáink
                </h2>

                <?php if(isset($is_over_max) and $is_over_max){ ?>
                        <div class="alert alert-danger" role="alert">
                        Sajnos ekkora mennyiség ebből a pizzából nem rendelhető.
                        </div>
                <?php } ?>

                <table class="table table-hover">
                <colgroup>
                    <col span="1" style="width: 10%;">
                    <col span="1" style="width: 50%;">
                    <col span="1" style="width: 10%;">
                    <col span="1" style="width: 15%;">
                    <col span="1" style="width: 15%;">
                </colgroup>
                    <?php while ($row = mysqli_fetch_array($result_menu)): ?>
                        <tr>
                            <td><?=$row['pname']?></td>
                            <td><?=$row['description']?></td>
                            <td><?=$row['price']." Ft."?></td>
                            
                            <form method="post">
                            <td><input type="number" name="order_quantity" style="width: 60px"> <label for="order_quantity">darab</label></td>
                            <td><button type="submit" name="add_to_cart" class="btn btn-light">Kosárba! <image src="cart2.svg"></button></td>
                            <?php if (isset($_SESSION['editmode'])): ?>
                                <td><button type="submit" name="remove_product" class="btn btn-danger"><image src="trash.svg"></button></td>
                            <?php endif; ?>
                            <input type="hidden" name="product_id" value="<?=$row['id']?>">
                            </form>

                            <?php if (isset($_SESSION['editmode'])): ?>
                            <td><a href="edit_pizza.php?id=<?=$row['id']?>"><button type="submit" name="edit_product" class="btn btn-warning"><image src="pencil.svg"></button><a></td>
                            <?php endif; ?>
                            
                        </tr>                
                    <?php endwhile; ?> 
                </table>
                
                <?php if ($_SESSION['usertype']=='admin' and !isset($_SESSION['editmode'])): ?>
                   <form method="post"><button type="submit" name="edit_etlap" class="btn btn-outline-warning">Étlap szerkesztése</button></form>
                <?php endif;

                if ($_SESSION['usertype']=='admin' and isset($_SESSION['editmode'])): ?>
                <div class="row">
                    <div class="col">
                        <form method="post"><button type="submit" name="edit_etlap_off" class="btn btn-outline-warning">Kilépés a szerkesztésből</button></form>
                    </div>
                    <div class="col">
                        <a href="create_pizza.php"><button type="button" class="btn btn-outline-success">Új termék hozzáadása</button></a>
                    </div>
                </div>
                <?php endif; ?>

            </div>
        
            <?php if(!empty($_SESSION['shopping_cart'])){ ?>
            
            <div class="col-md-4">
                <h2>
                Kosár
                </h2>
                
                <table class="table table-hover">
                    <?php 
                    $total_price = 0;
                    foreach ($_SESSION['shopping_cart'] as $product){ ?>
                    <tr>
                    <td><?php echo $product['name']?></td>
                    <td><?php echo $product['price']." Ft."?></td>
                    <td><?php echo $product['quantity']. " db"?></td>
                    <td><?php echo $product["price"]*$product["quantity"]." Ft."; ?></td>
                    <td>
                    <form method="post">
                    <button type="submit" name="remove_from_cart" class="btn btn-danger"><image src="trash.svg"></button>
                    <input type="hidden" name="item_id" value="<?=$product['id']?>">
                    <input type="hidden" name="item_quantity" value="<?=$product['quantity']?>">
                    </form>
                    </td>
                    </tr>
                    <?php
                    $total_price += ($product["price"]*$product["quantity"]);
                    ?>
                    <?php } ?>
                </table>
                <?php 
                $_SESSION['order_price']=$total_price;
                ?>
                <div class="alert alert-dark" role="alert">
                    <form method='post'>
                    Fizetendő: <b><?php echo  $_SESSION['order_price']?> Ft.</b>
                    <p class="text-end"><button type="submit" name="order_send" class="btn btn-dark">Megrendelés</button></p>
                    </form>
                </div>
                <?php if(isset($_POST['order_send']) and $_SESSION['usertype']=='guest'){ ?>
                    <div class="alert alert-warning" role="alert">
                    Kérjük <a href="login.php">jelentkezzen be</a> a megrendelés elküldéséhez!
                    </div>
                <?php } ?>
            </div>
            <?php } ?>
        </div>
    <?php
        closeDb($link);
    ?>
    </div>
</body>
</html>