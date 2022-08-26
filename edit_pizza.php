<?php
session_start();
$empty=false;
if(!isset($_GET['id']) or !isset($_SESSION['usertype']) or $_SESSION['usertype']!='admin'){
    header("Location: index.php");
}

include 'pizza_data.php';
$link = getDb();
$id=mysqli_real_escape_string($link, $_GET['id']);

if(isset($_POST['edit_product'])){
    $name=mysqli_real_escape_string($link, $_POST['name']);
    $description=mysqli_real_escape_string($link, $_POST['description']);
    $price=mysqli_real_escape_string($link, $_POST['price']);
    if (!$name or !$description or !$price){
        $empty=true;
    }
    else {
        $empty=false;
        $query=sprintf("UPDATE product SET pname='%s', description='%s', price=%d WHERE id=%d",
            $name, $description, $price, $id
        );
        mysqli_query($link, $query) or die(mysqli_error($link));
    }
}

if(isset($_POST['add_ingredient'])){
    $ingredient_id=mysqli_real_escape_string($link, $_POST['ingredient_id']);
    $amount=mysqli_real_escape_string($link, $_POST['amount']);
    $query=sprintf("SELECT * FROM product_has_ingredient WHERE product_id=%d AND ingredient_id=%d",
        $id, $ingredient_id
    );
    $result=mysqli_query($link, $query) or die(mysqli_error($link));
    if(mysqli_num_rows($result)==0 and $amount>0){
        $query=sprintf("INSERT INTO product_has_ingredient(product_id, ingredient_id, amount) VALUES(%d, %d, %d)",
            $id, $ingredient_id, $amount
        );
        mysqli_query($link, $query) or die(mysqli_error($link));
    }
    elseif(mysqli_num_rows($result)>0){
        $row=mysqli_fetch_array($result);
        if($row['amount']+$amount<=0){
            $query=sprintf("DELETE FROM product_has_ingredient WHERE product_id=%d AND ingredient_id=%d",
                $id, $ingredient_id
            );
            mysqli_query($link, $query) or die(mysqli_error($link));
        }
        else{
            $query=sprintf("UPDATE product_has_ingredient SET amount=%d WHERE product_id=%d AND ingredient_id=%d",
                $row['amount']+$amount, $id, $ingredient_id
            );
            mysqli_query($link, $query) or die(mysqli_error($link));
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
    <title>Pizzafaló</title>
</head>


<body>
    <?php 
        include 'menu.php';
    ?>
    <div class="container main-content">
        <div class="row">
            <div class="col md-4">
                <h3>Jelenlegi adatok</h3>
                <?php
                    $query="SELECT * FROM product WHERE id=".$id;
                    $result_pizza=mysqli_query($link, $query) or die(mysqli_error($link));
                    $row=mysqli_fetch_array($result_pizza); ?>
                <table class="table">
                    <tbody>
                        <tr>
                            <th scope="row">Név</th>
                            <td><?=$row['pname']?></td>
                        </tr>
                        <tr>
                            <th scope="row">Leírás</th>
                            <td><?=$row['description']?></td>
                        </tr>
                        <tr>
                            <th scope="row">Ár</th>
                            <td><?=$row['price']?> Ft.</td>
                        </tr>
                    </tbody>          
                </table>
                <b>Összetevők:</b>
                <?php 
                $query="SELECT name, product_has_ingredient.amount AS amount FROM product_has_ingredient JOIN ingredient ON ingredient_id=ingredient.id WHERE product_id=".$id;
                $result_ingredient=mysqli_query($link, $query) or die(mysqli_error($link));
                ?>
                <table class="table">
                    <tbody>
                        <?php while ($row=mysqli_fetch_array($result_ingredient)): ?>
                        <tr>
                            <th scope="row"><?=$row['name']?></th>
                            <td><?=$row['amount']?> db</td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>          
                </table>
            </div>
            <div class="col md-4">
                <h3>Új adatok</h3>
                <?php if($empty==true): ?>
                    <div class="alert alert-danger" role="alert">
                        Kérem töltsön ki minden mezőt!
                    </div>
                <?php endif; ?>
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
                        <button type="submit" name="edit_product" class="btn btn-outline-success">Módosítás</button>
                        </div>
                
                </form>
                
            </div>
            <div class="col md-4">
                <h3>Összetevők felülírása</h3>
                    <?php $result_allIngredients = mysqli_query($link, "SELECT name, id FROM ingredient"); ?>
                    <table class="table table-hover">
                        <?php while ($row = mysqli_fetch_array($result_allIngredients)): ?>
                            <tr>
                                <td><?=$row['name']?></td>
                                <form method="post">
                                <td><input type="number" name="amount" style="width: 60px"> <label for="amount">darab</label></td>
                                <td><button type="submit" name="add_ingredient" class="btn btn-light">Hozzáadás</button></td>
                                <input type="hidden" name="ingredient_id" value="<?=$row['id']?>">
                                </form>
                            </tr>                
                        <?php endwhile; ?> 
                    </table>
            </div>
        </div>
    </div>

</body>