<?php
require 'Config/db_Connection.php';

    //to verify user using post method to insert the data in the postman.
    if($_SERVER['REQUEST_METHOD']!=='POST'){
        http_response_code(405);//405->method not Allowed(client error).
        echo json_encode(['Message'=>'Only POST method allowed']);
        exit;
    }
    
    $input=json_decode(file_get_contents("php://input"),true); //file_get_contents("php://input")->rawdata which is plain text.It converts the json 
    // if(!$input){                                 // data to PHP array format data because of using the true if not it will decoded as object format
    //     http_response_code(400);//404->bad request, like invalid json....kindoff
    //     echo json_encode(["Error"=>"Invalid Json"]);
    //     exit;
    // }
    
    if(!isset($input[0])){
        http_response_code(400);
        echo json_encode(["Error"=>"Send data in array of json"]);
        exit;
    }
    
    $conn=DatabaseConnection::getConnection();
    $sql="INSERT INTO test_php(emp_name,emp_phno) VALUES(?,?)";
    $stmt=$conn->prepare($sql);
    foreach($input as $user){

        if(!isset($user['name']) || !isset($user['phno'])){
        http_response_code(400);
        echo json_encode(["Error"=>"Missing required fields"]);
        exit;
    }
    if(strlen($user['phno'])!=10){
        $stmt->execute([
        $user['name'],$user['phno']
                ]);

    }
    else
    {
        http_response_code(401);
        echo json_encode(["Error"=>"Phnum must be 10 digits"]);
        exit;
    }
           
    }

    echo json_encode([
        "Status"=>"Success",
        "Message"=>"Data stored successfully"
    ]);


?>