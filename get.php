<?php
$host='localhost';
$dbname='user';
$username='root';
$password='';

try{
    $conn=new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql="SELECT * FROM test_php";
    $stmt=$conn->prepare($sql);
    $stmt->execute();
    $result=$stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($result); // json_encode the result data which is in PHP array -> Json format(String format).(Serialization)
}
catch(PDOException $e){
    echo json_encode(["error"=>$e->getMessage()]);
}
?>