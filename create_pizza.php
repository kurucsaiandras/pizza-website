<?php
session_start();

if(!isset($_SESSION['usertype']) or $_SESSION['usertype']!='admin')
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
    <title>Pizzafaló - Étlap</title>
</head>


<body>
    <?php 
        include 'menu.php';
    ?>
    <div class="container main-content">
        <div class="row">
        <?php if(!isset($_POST['add_product']) and !isset($_POST['add_ingredient'])) {//HA MÉG NEM ADTUK HOZZÁ A PIZZÁT ?>
            <div class="col-md-4">
                <h5>Új termék hozzáadása</h5>
                    <form method="post">

                        <!-- Name input -->
                        <div class="form-outline mb-4">
                            <label class="form-label" for="form2Example1">Pizza neve</label>
                            <input type="text" name="name" id="form2Example1" class="form-control" />
                        </div>

                        <!-- description input -->
                        <div class="form-outline mb-4">
                            <label class="form-label" for="form2Example1">Leírás</label>
                            <input type="text" name="description" id="form2Example1" class="form-control" />
                        </div>

                        <!-- Price input -->
                        <div class="form-outline mb-4">
                            <label class="form-label" for="form2Example2">Ár (Forint)</label>
                            <input type="number" name="price"id="form2Example2" class="form-control" />
                        </div>

                        <!-- Submit button -->
                        <div class="d-grid gap-2">
                        <button type="submit" name="add_product" class="btn btn-outline-success">Hozzáadás</button>
                        </div>
                
                    </form>
            </div>
        <?php } 
                if (isset($_POST['add_product']) or isset($_POST['add_ingredient'])) {//HA MÁR HOZZÁADTUK A PIZZÁT
                    if(isset($_POST['add_product'])){
                        $name = mysqli_real_escape_string($link, $_POST['name']);
                        $description = mysqli_real_escape_string($link, $_POST['description']);
                        $price = mysqli_real_escape_string($link, $_POST['price']);
                        
                    
                        if (!$name or !$description or !$price) {
                            die("<div class='text-center'><div class='text-danger'>Kérem töltsön ki minden mezőt!</div></div>");
                        }
                        $query = mysqli_query($link, "SELECT * FROM product WHERE pname='$name'");
                        if (mysqli_num_rows($query)==0){
                            $createQuery = sprintf("INSERT INTO product(pname, description, price) VALUES('%s', '%s', %d)",
                                $name,
                                $description,
                                $price
                            );
                            mysqli_query($link, $createQuery) or die(mysqli_error($link));
                            $result_id = mysqli_query($link, "SELECT LAST_INSERT_ID() AS id");
                            $product_id = mysqli_fetch_array($result_id)['id'];
                        }
                        else die("<div class='text-center'><div class='text-danger'>Már van ilyen nevű pizza!</div></div>");
                    }
                    if(isset($_POST['add_ingredient'])){
                        $product_id=mysqli_real_escape_string($_POST['product_id']);
                        $query = mysqli_query($link, "SELECT * FROM product WHERE id=".$product_id);
                        $product=mysqli_fetch_array($query);
                        $name = $product['pname'];
                        $description = $product['description'];
                        $price = $product['price'];

                        
                        if($_POST['amount']>0){
                            $ingredient_id=mysqli_real_escape_string($link, $_POST['ingredient_id']);
                            $amount=mysqli_real_escape_string($link, $_POST['amount']);
                            $queryInsert=sprintf("INSERT INTO product_has_ingredient(product_id, ingredient_id, amount) VALUES(%d, %d, %d)",
                                $product_id,
                                $ingredient_id,
                                $amount
                            );
                            mysqli_query($link, $queryInsert);
                        }
                    }
                    ?>

                    <div class="col-md-4">
                        <div class="alert alert-success" role="alert">
                        Termék sikeresen hozzáadva!
                        </div>
                        <table class="table">
                            <tbody>
                                <tr>
                                    <th scope="row">Név</th>
                                    <td><?=$name?></td>
                                </tr>
                                <tr>
                                    <th scope="row">Leírás</th>
                                    <td><?=$description?></td>
                                </tr>
                                <tr>
                                    <th scope="row">Ár</th>
                                    <td><?=$price?> Ft.</td>
                                </tr>
                            </tbody>
                        </table>

                        <b>Összetevők:</b>
                        <?php
                            
                            $result_ingredients = mysqli_query($link, "SELECT product_has_ingredient.amount as amount, name FROM product_has_ingredient
                            JOIN ingredient ON ingredient_id=ingredient.id WHERE product_id=".mysqli_real_escape_string($link, $product_id));
                        ?>

                        <table class="table">
                            <tbody>
                            <?php while($row=mysqli_fetch_array($result_ingredients)): ?>
                                <tr>
                                    <th scope="row"><?=$row['name']?></th>
                                    <td><?=$row['amount']?></td>
                                </tr>
                            <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="col-md-4">
                        <h5>Összetevők hozzáadása:</h5>
                        <?php $result_allIngredients = mysqli_query($link, "SELECT name, id FROM ingredient"); ?>
                        <table class="table table-hover">
                            <?php while ($row = mysqli_fetch_array($result_allIngredients)): ?>
                                <tr>
                                    <td><?=$row['name']?></td>
                                    <form method="post">
                                    <td><input type="number" name="amount" style="width: 60px"> <label for="amount">darab</label></td>
                                    <td><button type="submit" name="add_ingredient" class="btn btn-light">Hozzáadás</button></td>
                                    <input type="hidden" name="ingredient_id" value="<?=$row['id']?>">
                                    <input type="hidden" name="product_id" value="<?=$product_id?>">
                                    </form>
                                </tr>                
                            <?php endwhile; ?> 
                        </table>
                    </div>
                    <div class="col-md-4">
                    <a href="etlap.php"><button type="submit" name="edit_etlap_off" class="btn btn-outline-success">Kész</button></a>
                    </div>
                <?php } ?>
        </div>
        <?php
        closeDb($link);
        ?>
    </div>
</body>
</html>