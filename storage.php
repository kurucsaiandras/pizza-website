<?php
session_start();

if(!isset($_SESSION['usertype']) or $_SESSION['usertype']!='admin')
{
  header("Location: index.php");
}


include 'pizza_data.php';
$link = getDb(); 

if(isset($_POST['add_to_ingredient'])){
    $queryIncreaseIngedient = sprintf("UPDATE ingredient SET amount = amount+%d where id=%d",
        mysqli_real_escape_string($link, $_POST['amount']),
        mysqli_real_escape_string($link, $_POST['ingredient_id'])
    );
    mysqli_query($link, $queryIncreaseIngedient)or die(mysqli_error($link));
}

if(isset($_POST['insert_ingredient']) and $_POST['ingredient_name']){
    $query= sprintf("INSERT INTO ingredient(name, amount) VALUES('%s', 0)",
        mysqli_real_escape_string($link, $_POST['ingredient_name'])
    );
    mysqli_query($link, $query)or die(mysqli_error($link));
}

if(isset($_POST['remove_ingredient'])){
    $query=sprintf("DELETE FROM product_has_ingredient  WHERE ingredient_id=%d",
        mysqli_real_escape_string($link, $_POST['ingredient_id'])
    );
    mysqli_query($link, $query)or die(mysqli_error($link));
    $query=sprintf("DELETE FROM ingredient WHERE id=%d",
        mysqli_real_escape_string($link, $_POST['ingredient_id'])
    );
    mysqli_query($link, $query)or die(mysqli_error($link));
}

?>

<html>
<head>
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
    <title>Pizzafaló - Raktárkészlet</title>
</head>

<body>

<?php include 'menu.php'; ?>
<div class="container main-content">
    <?php
        $querySelect = "SELECT * FROM ingredient";
        $result = mysqli_query($link, $querySelect) or die(mysqli_error($link));
    ?>
            <table class="table table-hover">
                <?php while ($row = mysqli_fetch_array($result)): ?>
                    <tr>
                        <td><?=$row['name']?></td>
                        <td><?=$row['amount']." db"?></td>
                        <td>
                        <form method="post">
                        <td><input type="number" name="amount" style="width: 60px"> <label for="amount">darab</label></td>
                        <td><button type="submit" name="add_to_ingredient" class="btn btn-light">Felvétel a raktárba</button></td>
                        <td><button type="submit" name="remove_ingredient" class="btn btn-danger"><image src="trash.svg"></button></td>
                        <input type="hidden" name="ingredient_id" value="<?=$row['id']?>">
                        </form>
                        </td> 
                    </tr>                
                <?php endwhile; ?> 
            </table>
            <div class="row">
                <div class="col">
                <form method="post"><button type="submit" name="create_ingredient" class="btn btn-outline-warning">Új alapanyag felvétele</button></form>
                </div>
                <div class="col">
                <?php if(isset($_POST['create_ingredient'])): ?>
                    <form method="post">
                    <label for="ingredient_name">Név:</label> <input type="text" name="ingredient_name">
                    <button type="submit" name="insert_ingredient" class="btn btn-outline-warning">Hozzáadás</button>
                    </form>
                <?php endif; ?>
                </div>
            </div>
            <?php
                closeDb($link);
            ?>
</div>
</body>
</html>