<?php
class DatabaseConnection{
    private static $host='localhost';
    private static $dbname='user';
    private static $username='root';
    private static $password='';

    public static function getConnection(){
        try{
            $pdo=new PDO("mysql:host=".self::$host.";dbname=".self::$dbname.";charset=utf8mb4",self::$username,self::$password,[
                PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES=>false
            ]);
            return $pdo;
        }
        catch(PDOException $e){
            echo json_encode(["Message"=>"DB Connection Failed"]);
        }
    }
}
?>

