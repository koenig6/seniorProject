<?php
session_start();

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$message = null;
$action = 'tags';
$subAction = null;
if (isset($_POST['action'])) {
 $action = $_POST['action'];
} elseif (isset($_GET['action'])) {
 $action = $_GET['action'];
}

require 'model.php';




if($_SESSION['securityLevel'] < 1){
    header('location: /logIn/index.php');
    $_SESSION['message'] = 'Access Denied';
}elseif(timedOut()){
    header('location: /logIn/index.php');
}elseif($_SESSION['loggedIn'] != true){
    header('location: /logIn/index.php');
}elseif($action == 'logOut'){
    logOut();
    header('location: /logIn/index.php');
}elseif($action == 'newTag'){
    $subAction = 'newTag';
    $action = 'tags';
}elseif($action == 'newTagEx'){
    //Validation
    
    $skuNumber = filterNumber($_POST['skuNumber']);
    
    $associatedCustomer = getAssociatedCustomer($skuNumber);
    $skuData = getSkuData($skuNumber);


    if(!isset($skuData['skuNumber'])){
        $message = 'Sku# \'' . $skuNumber . '\' did not exsist. Try again.';
        $action = 'tags';
        $subAction = 'newTag';
        
    }else{
        $subAction = 'newTagEx';
        $action = 'tags';
    }
}elseif($action == 'createTag'){
    $poNumber = null;
    $batchNumber = null;
    $expDate = null;
    $skuNumber = filterNumber($_POST['skuNumber']);
    $companyID = filterNumber($_POST['associatedCompanyID']);
    $receiptDate = date('Y-m-d', strtotime(filterString($_POST['receiptDate'])));
    $palletQty = filterNumber($_POST['palletQty']);
    $palletCount = filterNumber($_POST['palletCount']);
    $partial = filterNumber($_POST['partial']);
    $userID = $_SESSION['userID'];
    
    if(isset($_POST['poNumber'])){
        $poNumber = filterString($_POST['poNumber']);
    }
    if(isset($_POST['batchNumber'])){
        $batchNumber = filterString($_POST['batchNumber']);
    }
    if(isset($_POST['expDate'])){
        $expDate = filterString($_POST['expDate']);
        $expDate = date('Y-m-d', strtotime($expDate));
    }
    echo 'batch: ' . $batchNumber;
    $serialNumber = createNewInventory(
                                        $skuNumber
                                        , $companyID
                                        , $userID
                                        , $receiptDate
                                        , $palletQty
                                        , $poNumber
                                        , $batchNumber
                                        , $expDate);
    
    $tagData = getTagData($serialNumber);
    
    $action = 'tags';
    $subAction = 'printTag';
    
    
}
include 'view.php';