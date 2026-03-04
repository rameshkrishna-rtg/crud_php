<?php
require 'Config/db_Connection.php';

//to verify the user is using the GET method to get the data.
if($_SERVER['REQUEST_METHOD']!=='GET'){
    http_response_code(405);//Method not allowed
    echo json_encode(["Message"=>"Only GET method is allowed"]);
    exit;
}

$conn=DatabaseConnection::getConnection();
$sql="SELECT * FROM test_php";
$stmt=$conn->prepare($sql);
$stmt->execute();
$result=$stmt->fetchAll();
if($result){
    // echo json_encode($result);
    foreach($result as $data){
        echo json_encode($data)."\n";
    }
}
else{
    http_response_code(404);
    echo json_encode(["Message"=>"Data not found"]);
    exit;
}
