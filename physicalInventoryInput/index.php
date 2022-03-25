<?php
session_start();

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
$action = null;
$ajax = false;
$employeeID = null;
$curDate = date('Y-m-d');
$heading = null;
$table1 = null;
$table2 = null;
$timeStamp = date('Y-m-d H:i:s');
$serialID = null;

require 'model.php';

$_SESSION['cats'] = getConstant("physicalInventoryCat")['constantValue'];

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
}elseif($action == 'scanTag'){
    //Setup JSON sting
    $tranArr = null;

    // Get Warned Status
    $warned = filterNumber($_GET['warned']);

    //Get Serial #
    $serial = filterString($_GET['serial']);

    if(is_numeric($serial)) {
        // Number
    }else {
        // String
        $serial = translateAltLocID($serial);
    }

    // Ensure $serial is an int
    $serial = intval($serial);  

    //Check to see if serial # is valid
    $validateSerial = getInventory($serial);

    if(!$validateSerial || $validateSerial == null) {
        $validateAltLocID = translateAltLocID($serial);  // Double Check to see its not altLocationID
        if($validateAltLocID != false || $validateAltLocID != null) {
            $serial = $validateAltLocID;
        }
    }
    
    //Check to see if serial # is valid
    $validateSerial = getInventory($serial);

    //Check to see if serial has been scanned already and show bin.
    $serialData = getSerialData($serial);

    // Get Status
    $itemStatus = getInvItemStatus($serial);
    
    // Check to see if serial is deactivated
    if($itemStatus[0] != 2 || $warned) {
        
        //Check to see if serial is a bin location.By getting range.
        $firstBin = $_SESSION['firstBin'];
        $lastBin = $_SESSION['lastBin'];
            

        //Load Get Bin Location Or Throw Error
            if($validateSerial){

                //Get tag sku data    
                $skuData = getSkuData($validateSerial[1]);
                $catCode = $skuData['CAT'];
                $customerID = $validateSerial[2];
                $validateCatCust = validateCatCust($serial);


                if($serialData){
                    //Send duplication error code with bin location
                    $tranArr[0] = 2;
                    if(isset($serialData[5])){
                        $tranArr[1] = $serialData[5];
                    }else{
                        $tranArr[1] = $serialData[3];
                    }
                }else if(!$validateCatCust){
                    //Send wrong CAT Code error code with CAT Code
                    $tranArr[0] = 4;

                    $tranArr[1] = "Error: CAT CODE: $catCode OR CustomerID: $customerID INVALID" ;

                }else{
                    //Check to see if valid Serial is a Bin Location. Just in case.
                    if($serial <= $lastBin && $serial >= $firstBin){
                        //Serial Number is invalid, Check to see if a bin location
                        $tranArr[0] = 3;
                    }else{
                        //Transmit success and return to tagScan
                    $tranArr[0] = 0;
                    $tranArr[1] = '<input type="text" name="scan" id="scan" class="scanBin" onfocusout="setFocusToTextBox()" onkeydown="Javascript: if (event.keyCode==13) binScan(' . $serial . ');">';
                    }
                    
                }
                
            }else{
                //Serial Number is invalid, Check to see if a bin location
                if($serial <= $lastBin && $serial >= $firstBin){
                    //Scanned number is a binlocation send error code
                    $tranArr[0] = 3;
                }else{
                    //Send error code for invalid serial #
                    $tranArr[0] = 1;
                }
            }

        $tranArr[2] = $warned ? true : false;
            
        $data = json_encode($tranArr);
        echo $data;
        
    }else {

        if($_SESSION['securityLevel'] < 4) {
            // Send an error message & stop
            echo -1;
        }elseif($_SESSION['securityLevel'] >= 4) {
            // Warn Authorized User
            echo -2;
        }

    }

    $ajax = true;
}elseif($action == 'scanBin'){
    //Set StatusID for counted status
    $statusID = determineDataType('counted');
    
    
    //Create transfer array
        $tranArr = null;
    
    //Get Bin and Serial
        $bin = filterString($_GET['bin']);
    //Check to see if serial is a bin location.By getting range.
        $firstBin = $_SESSION['firstBin'];
        $lastBin = $_SESSION['lastBin'];

    // Validate Bin

        if(substr($bin, 0 , 5) == 'LSSLP'){
            $bin = 'LSSLP+' . substr($bin, 6, 100);
        }

        if(is_numeric($bin)) {
            // Number
        }else {
            // String
            $bin = translateAltLocID($bin);
        }
        
    //Check binlocation is valid
    if($bin <= $lastBin && $bin >= $firstBin){
        
        //Get Data for new entry
        $serial = filterNumber($_GET['serial']);
        $serialData = getInventoryData($serial);
        $user = $_SESSION['userID'];
        
        //Check if binlocation matches original
        if($bin != $serialData[1]){
            //Insert new entry as write in for binlocation
            $newPhyInvEntry = newPhyInvEntry($serial, $bin, $serialData[2], $user, $serialData[3], $statusID, $statusID);
        }else{
            //Insert new entry
            $newPhyInvEntry = newPhyInvEntry($serial, null, $serialData[2], $user, $serialData[3], $statusID, $statusID);
        }
        
        if($newPhyInvEntry){
            //Send success code
            $tranArr[0] = 0;
        }else{
            //Send Failed Transaction Code
            $tranArr[0] = 2;
        }

        $tranArr[0] = 0;
    }else{
        //Send back Error for invalid bin location
        $tranArr[0] = 1;
        $tranArr[1] = $bin;
    }
    
    $data = json_encode($tranArr);
    echo $data;
    $ajax = true;
    
}
if(!$ajax){
    include 'view.php';
}

function determineDataType($action){
    $dataType = null;
    if($action == 'counted'){
        $dataType = 1;
    }elseif($action == 'hold'){
        $dataType = 2;
    }elseif($action == 'flagged'){
        $dataType = 3;
    }elseif($action == 'recounted'){
        $dataType = 4;
    }elseif($action == 'passed'){
        $dataType = 5;
    }elseif($action == 'notcounted'){
        $dataType = 0;
    }elseif($action == 'RC-notFound'){
        $dataType = 11;
    }elseif($action == 'RC-found'){
        $dataType = 12;
    }elseif($action == 'RC-confirm'){
        $dataType = 13;
    }elseif($action == 'RC-recount'){
        $dataType = 14;
    }else{
        $dataType = 1;
    }
    return $dataType;
}
