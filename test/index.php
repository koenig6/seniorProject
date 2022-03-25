<?php
session_start();
/* 
require $_SERVER['DOCUMENT_ROOT'] .'/physicalInventory/model.php';

 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 

require $_SERVER['DOCUMENT_ROOT'] . '/library/library.php';
require $_SERVER['DOCUMENT_ROOT'] . '/library/model.php';



$x = getPastDue();
echo "worked " . $x;


function getPastDue(){
    $connection = connWhitneyAdmin();
    try {
     $sql = 'SET @testing := 101';
     
        $stmt = $connection->prepare($sql);
        $stmt->execute();
        //$stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return True;
}


$fileatt_type = "text/csv";
$myfile = "C:\GWPG_BOM.txt";

$from_name = "Nathan";
$from_mail = "nate.wittmann@goldenwestpackaging.com";
$replyto = "nate.wittmann@goldenwestpackaging.com";

$to = "nathan@wittmann.tech";
$subject = "TEST";
$txt = "Hello world!";
$headers = "From: nate.wittmann@goldenwestpackaging.com" . "\r\n" .
"CC: nathan@wittmannfamily.net";

$headers = 'From: nate.wittmann@goldenwestpackaging.com'. "\r\n" .
'MIME-Version: 1.0'. "\r\n" .
 * 'Content-Type: text/html; charset=utf-8';

echo "sent";


$headers = 'From: wittmann.tech@gmail.com'. "\r\n" .
'MIME-Version: 1.0'. "\r\n" .
 'Content-Type: text/html; charset=utf-8';

$x = mail("nate.wittmann@goldenwestpackaging.com","My test subject","Hello World", $headers);

echo var_dump($x);

 * */


// function connWhitneyUser(){
//     $congtr1 = null;
//     $server = 'localhost';
//     $username = 'whitney_prxUser';
//     $password = 'FE5XrHh5FrEn';
//     $database = 'whitney';
//     $dsn = "mysql:host=$server; dbname=$database";
//     $option = array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION);
//     try {
//             $congtr1 = new PDO($dsn, $username, $password);
//             //echo 'It worked';
//         }catch (PDOException $exc){
//             //echo $exc->getTraceAsString();
//             $message = '<p>Failed User Connection To Database</p>';
//             $_SESSION['message'] = $message;
//             //header('location: /errorDocs/500.php');
//         }
    
//     if(is_object($congtr1)){
//         return $congtr1;
//     }
//     return false;
// }

// $connection = connWhitneyUser();
// db_query('SET @x := 5,SET @y := 6;')->execute();
// db_query('SELECT * from mytable')->fetchAll();

include 'view.php'; 

echo 'test';