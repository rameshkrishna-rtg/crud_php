<?php
$host='localhost';
$dbname='user';
$userName='root';
$password='';

try{
    $conn=new PDO("mysql:host=$host;dbname=$dbname;charset=utf8",$userName,$password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connected Successfully\n";
    $sql="INSERT INTO test_php(emp_name,emp_phno) VALUES(:name, :phno)";
    $stmt= $conn->prepare($sql);
    $stmt->execute([
        ':name'=>'Krishna',
        ':phno'=>'9976712151'
    ]);
    echo "Data inserted successfully";
}
catch(PDOException $e){
        echo "Connection Failed: ".$e->getMessage();
}
?>
