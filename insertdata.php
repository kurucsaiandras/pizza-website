

<?php
if(isset($_POST['process']) and isset($_POST['orderid'])){
include 'pizza_data.php';
$link = getDb();
$process=mysqli_real_escape_string($link, $_POST['process']);
$orderid=mysqli_real_escape_string($link, $_POST['orderid']);
$query = sprintf("UPDATE ordering set state = %d where id=%d;", $process, $orderid);
mysqli_query($link, $query) or die(mysqli_error($link));

if($process==100){
  $query="UPDATE ordering SET shippingtime = current_timestamp() WHERE id=".$orderid;
  mysqli_query($link, $query);
}

closeDb($link);
}
?>