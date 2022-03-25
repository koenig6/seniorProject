<?php
session_start();

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$ajax = false;
$action = 'forklift';
$employeeID = null;
$curDate = date('Y-m-d');
$timeStamp = date('Y-m-d H:i:s');
$userID = $_SESSION['userID'];

require 'model.php';

//Get Action
if (isset($_POST['action'])) {
 $action = $_POST['action'];
} elseif (isset($_GET['action'])) {
 $action = $_GET['action'];
}

if($_SESSION['securityLevel'] < 2){
    header('location: /logIn/index.php');
    $_SESSION['message'] = 'Access Denied';
}elseif(timedOut()){
    header('location: /logIn/index.php');
}elseif($_SESSION['loggedIn'] != true){
    header('location: /logIn/index.php');
}elseif($action == 'logOut'){
    logOut();
    header('location: /logIn/index.php');
}elseif($action == 'forklift'){
    //Count of New & Picked
    $newCount = countNew();
    $pickedCount = countPicked();

    $orderCount = countTransferOrders();
}elseif($action == 'serialCheck'){
    
    $serialNumber = filterNumber($_GET['serialNumber']);

    $invData = checkInv($serialNumber);

    $tranArr = null;
    $tranArr[0] = $invData != false ? true : false;
    $tranArr[1] = $invData != false ? $invData : null;
    
    $data = json_encode($tranArr);
    echo $data;

    $ajax = true;
}elseif($action == 'split'){

    $check = null;
    $serialNumber = filterNumber($_GET['serialNumber']);
    $shippingID = filterNumber($_GET['shippingID']);

    $remainingQty = filterNumber($_GET['remainingQty']); // Old Pallet Qty
    $newQty = filterNumber($_GET['newQty']); // New Pallet Qty

    $newSerialNumber = advanceSku()[0];

    // Duplicate Pallet
    $check = duplicateInvUnit($newSerialNumber, $userID, $serialNumber);

    if(!$check) {
        die();
        echo 0; // New Pallet Creation Failed
    }

    // Update constants table with new inventoryID
    updateSku($newSerialNumber); 

    // Update Qty of Old Pallet
    $check = updateInventoryQty($serialNumber, $remainingQty);  // Sets the old pallet to the remaining qty

    if(!$check) {
        die();
        echo 1; // Old Pallet Qty Update Failed
    }

    // Update Qty of New Pallet
    $check = insertNewQty($newSerialNumber, $newQty, null, $userID);  // Sets the old pallet to the remaining qty
    
    if(!$check) {
        die();
        echo 2; // New Pallet Qty Update Failed
    }

    // Add Shipping ID to New Pallet
    updateShippingID($shippingID, $newSerialNumber);  // UPDATE inventory to add shippingID

    // Update Status to Picked
    insertNewStatus($newSerialNumber, 1, $userID);  // Insert status of picked

    echo 'New Serial: ' . $newSerialNumber;

    $ajax = true;
}

if(!$ajax){
    include 'view.php';
}

function console_log( $data ){
    echo '<script>';
    echo 'console.log('. json_encode( $data ) .')';
    echo '</script>';
}