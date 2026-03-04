<?php
require 'Config/db_Connection.php';

if($_SERVER['REQUEST_METHOD']!=='DELETE'){
    http_response_code(405);
    echo json_encode(["Message"=>"Only DELETE method allowed"]);
    exit;
}

$conn=DatabaseConnection::getConnection();
$sql="DELETE FROM test_php WHERE LENGTH(emp_phno)!=10";
$stmt=$conn->prepare($sql);
$stmt->execute();
$rows=$stmt->rowcount();
if($rows){
    http_response_code(200);
    echo json_encode(["Message"=>"Record deleted Successfully"]);
    exit;
}
else{
    echo json_encode(["Message"=>"Phone number Records are proper"]);
}
// $sql="SELECT * FROM test_php WHERE LENGTH(emp_phno)!=10";
// $stmt=$conn->prepare($sql);
// $result=$stmt->execute();
// if($result){
//     $sqlD="DELETE FROM test_php WHERE LENGTH(emp_phno)!=10";
//     $stmt=$conn->prepare($sqlD);
//     $stmt->execute();

//     http_response_code(200);
//     echo json_encode(["Message"=>"Record deleted Successfully"]);
//     exit;
// }
// else{
//     echo json_encode(["Message"=>"Phone number Records are proper"]);
// }
