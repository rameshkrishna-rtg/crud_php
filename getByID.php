<?php
$host='localhost';
$dbname='user';
$username='root';
$password='';

    header("content-Type: application/json");
try{
    $conn=new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4",$username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
     $id=$_GET['id']??null;
     if($id===null){
        echo json_encode(["error"=>"ID is required"]);
        exit;
     }
    $sql="SELECT * FROM test_php WHERE emp_id=:id";
    $stmt=$conn->prepare($sql);
    $stmt->execute([
        ':id'=>$id
    ]);
    $result=$stmt->fetch(PDO::FETCH_ASSOC);
    if($result){
        echo json_encode($result);
    }
    else 
    {
        echo json_encode(["message"=>"NO record found for this id"]);
    }
}


