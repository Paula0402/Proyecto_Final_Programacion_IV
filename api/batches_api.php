<?php

header("Content-Type: application/json");
include("../config/db.php");

$method=$_SERVER['REQUEST_METHOD'];
$data=json_decode(file_get_contents("php://input"),true);

if($method=="GET"){

$sql="SELECT b.*,p.name_product
FROM batches b
JOIN products p ON b.id_product=p.id_product";

$result=$conn->query($sql);

$rows=[];

while($r=$result->fetch_assoc()){
$rows[]=$r;
}

echo json_encode($rows);

}


if($method=="POST"){

$product=$data["id_product"];
$batch=$data["batch_number"];
$exp=$data["expiration_date"];
$qty=$data["initial_quantity"];
$curr_qty=$data["current_quantity"];

$conn->query("INSERT INTO batches
(id_product,batch_number,expiration_date,initial_quantity,current_quantity)
VALUES($product,'$batch','$exp',$qty,$curr_qty)");

echo json_encode(["message"=>"Lote agregado"]);

}