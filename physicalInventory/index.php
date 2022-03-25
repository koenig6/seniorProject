<?php
session_start();

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$ajax = false;
$action = null;
$subAction = null;
$employeeID = null;
$curDate = date('Y-m-d');
$heading = null;
$table1 = null;
$table2 = null;
$timeStamp = date('Y-m-d H:i:s');
$innerHTML = null;


require 'model.php';

$_SESSION['cats'] =  getConstant("physicalInventoryCat")['constantValue'];

// $invDate = getConstant('physicalInventory')['constantValue'];
$invDate = getPhyInvStatus()[0][0];

// $_SESSION['physicalInventory'] = $invDate;



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
}elseif($action == 'update'){
    
    //Get the current view to return to
    $action = filterString($_GET['view']);
    
    //Get SkuNumber and New Status
    $skuNumber = filterNumber($_GET['skuNumber']);
    $status = filterString($_GET['status']);
    
    //Get datatype from action
    $dataType = determineDataType($status);
    
    //Update SKU
    updateSkuInvStatus($dataType, $skuNumber);
    
    //Refresh selected Status
    $data = loadPhyInv($action);
    echo $data;
    
    //Set to Ajax
    $ajax = true;
}elseif($action == 'counted' || $action == 'hold' || $action == 'flagged' 
        || $action == 'recounted' || $action == 'passed' || $action == 'notcounted' || $action == 'escalated'){
    
    //Refresh selected Status
    $data = loadPhyInv($action);
    echo $data;
    
    //Set to Ajax
    $ajax = true;
}elseif($action == 'details'){
    
    //Get Sku Number Details
    $skuNumber = filterNumber($_GET['skuNumber']);
    
    //Get data for SKU details
    $skuDetails = getSkuDetails($skuNumber);
    
    //Refresh selected Status
    $data = buildPhysicalInventoryDetails($skuDetails, FALSE);
    echo $data;
    
    //Set to Ajax
    $ajax = true;
}elseif($action == 'recount' || $action == 'escalate'){
        
    if($action == 'recount'){
        //Get datatype from action
        $dataType = determineDataType('flagged');
        $subAction = 'recount';
    }else{
        $dataType = determineDataType('escalated');
        $subAction = 'esculate';
    }
    
    //Set Action
        $action = 'flagged';
    
    //Get Data
    $phyInvData = getPhyInvData($dataType);
    
    $phyInvBuild = buildRecountView($phyInvData, $action, FALSE);
    $phyInvHeaderBuild = null;

}elseif($action == 'selectRecount' || $action == 'resumeRecount'){
    //Get if audit or recount view
    $audit = filterNumber($_GET['audit']);
    
    //Get SKU Number & User ID
    $skuNumber = filterNumber($_GET['skuNumber']);
    $userID = $_SESSION['userID'];
    
    //Get datatype from action
    $dataType = determineDataType('flagged');
    
    //Get Data for individual recount
    $phyInvData = getSkuPhyInvData($skuNumber);
    
    if($action == 'selectRecount'){
        //Update user assignment
        $update = updateRecountAssignment($skuNumber, $userID);
        //Build single selected Recount
        $phyInvBuild = buildRecountView($phyInvData, $action, FALSE);
    }elseif($audit){
        $action = 'recounted';
        $phyInvBuild = buildPhysicalInventory($phyInvData, $action, TRUE);
    }else{
        $phyInvBuild = buildRecountView($phyInvData, $action, TRUE);
    }
    
    
    //Get Details Overview Data
    $recountDetailsData = getSkuDetails($skuNumber);
    
    //Build Recount Details Page
    if($audit){
        $recountDetailsBuild = buildRecountDetails($recountDetailsData, FALSE, TRUE);
    }else{
        $recountDetailsBuild = buildRecountDetails($recountDetailsData, FALSE, FALSE);
    }
    
    //Encoder to JSON for AJAX
    //set strings and encode to JSON String
    $tranArr = null;
    $tranArr[0] = $phyInvBuild;
    $tranArr[1] = $recountDetailsBuild;

    $data = json_encode($tranArr);
    echo $data;
    $ajax = true;
    
}elseif($action == 'showLots') {

    $skuNumber = filterNumber($_GET['skuNumber']);

    $html = buildLotsTable($skuNumber);

    echo $html;

    $ajax = true;

}elseif($action == 'releaseRecount'){
    //Set Action
    $action = 'flagged';
    
    //Get SKU Number
    $skuNumber = filterNumber($_GET['skuNumber']);
    
    //Update assignment to null
    $update = updateRecountAssignment($skuNumber, null);
    
    //Get Data for SKU
    $phyInvData = getSkuPhyInvData($skuNumber);
    
    $phyInvSkuPanel = buildRecountView($phyInvData, null, TRUE);
    
    echo $phyInvSkuPanel;
    
    $ajax = true;
    
}elseif($action == 'recountScan'){
    //Get Serial #
        $serial = filterNumber($_GET['serialID']);
    //Check to see if serial # is valid
        $validateSerial = getInventory($serial);
        
    if($validateSerial){
        echo 1;
    }else{
        echo 0;
    }
    
    $ajax = true;
    
}elseif($action == 'reportFilter'){

    $cats = $_GET['cats'];
    
    $r = insertCats($cats);

    $clearInventory = clearInventory();
    
    if(!$r) {
        // $message = 'Failed to Update Catagories.';
        // $message = var_dump($r);
    }else {
        // $message = 'Successfully Updated Catagories.';
    }

    // echo $cats;

//    echo $cats . " PHP";
    
    $ajax = true;
    
}elseif($action == 'RC-confirm' || $action == 'RC-found' || $action == 'RC-recount' || $action == 'RC-notFound'){
    //Get SerialID
    $serialID = $_GET['serialID'];
    
    //Get Status Code from action
    $invStatusID = determineDataType($action);
    
    //Check if new count and if new create a entry.
    if($action == 'RC-found'){
        //Get Data for Serial and insert new entry as well as set status'
        $serialData = getSerialDetails($serialID);
        $skuStatusID = getSkuData($serialData[0][2]);
        $newInsert = newPhyInvEntry($serialID, $serialData[0][4], $serialData[0][16], $_SESSION['userID'], $serialData[0][2], $invStatusID, $skuStatusID['phyInventorySkuStatusID']);
    }else{
        //Update status
        $updateCountStatus = updateCountStatus($serialID, $invStatusID);
    }
    
    //Get Data for Serial
    $serialData = getSerialDetails($serialID);
    
    //Rebuild Serial # Panel
    $detailSerialBuild = buildRecountDetails($serialData, TRUE, FALSE);
    
    //Echo Data
    echo $detailSerialBuild;
    $ajax = true;
    
}elseif($action == 'addRecount'){
    $tranArr = null;
    
    //Get Serial #
        $serialID = filterNumber($_GET['serialID']);
        
    //Get New Qty and Old Qty
        $newQty = filterNumber($_GET['newQty']);
        
    //Get UserID
        $userID = $_SESSION['userID'];
        
    //Get Serial Data and Verify there is no current recount
        $recountData = getRecount($serialID);
        $serialData = getInventory($serialID);
        
    //If No Recount Create a New Recount, If Recount update recount
        if($recountData){
            //Update recount with new recount quantity
            $updateRecount = updateRecount($serialID, $newQty, $userID);
            
            if($updateRecount){
                //Recount Exsists, Replace Recount Value
                $recountExtData = recountExtData($serialID);
                $buildRecountExtension = buildRecountExtension($recountExtData, false);
                $tranArr[0] = 1;
                $tranArr[1] = $buildRecountExtension;
            }else{
                //Echo failed Transaction
                $tranArr[0] = 2;
            }
            
        }elseif($serialData[14] == 0 || $serialData[14] == 11){
            //Error: Cannot recount with a not found status
            echo 0;
        }else{
            //Get Original Qty from current inventory recording
                $phyEntryData = getPhysicalInventoryEntry($serialID);
            //Create New ReCount
                $newRecount = insertRecount($serialID, $newQty, $phyEntryData[3], $userID);
            
            if($newRecount){
                //Get Data for Serial & Rebuild Serial # Panel
                    $serialExtData = getSerialDetails($serialID);
                    $detailSerialBuild = buildRecountDetails($serialExtData, TRUE, FALSE);
                //Get newly created data
                    $recountExtData = recountExtData($serialID);
                    $buildRecountExtension = buildRecountExtension($recountExtData, false);
                    $tranArr[0] = 0;
                    $tranArr[1] = $detailSerialBuild;
                    $tranArr[2] = $buildRecountExtension;
            }else{
                //Echo failed Transaction
                $tranArr[0] = 3;
            }
            
        }
        
    $data = json_encode($tranArr);
    echo $data;
    $ajax = true;
    
}elseif($action == 'submitRecount'){
    //Get Serial #
    $skuNumber = filterNumber($_GET['skuNumber']);
        
    //Validate recount by checking status
    $invalidCount = validateRecountSubmit($skuNumber);
    
    //Check if any not counted status
    if($invalidCount[0] > 0){
        //Throw error as count of invalid status
        echo $invalidCount[0];
        
    }else{
        //If valid change statusof Recount to complete (#4)
        //Get datatype from action
        $dataType = determineDataType('recounted');
        
        //Update user assignment
        $update = updateRecountAssignment($skuNumber, $userID);
    
        //Update SKU
        $updateSkuStatus = updateSkuInvStatus($dataType, $skuNumber);
        
        if($updateSkuStatus){
            //Completed update
            echo 0;
        }else{
            //Error
            echo -1;
        }
        
        
    }
    $ajax = true;
}elseif($action == 'useRecount'){
    $tranArr = null;
    
    //Change Action to 'recounted'
    $action = 'recounted';
    //Get Serial # & Get qty #
    $serialID = filterNumber($_GET['serialID']);
    $recountQty = filterNumber($_GET['recountQty']);
    
    //Get Inventory Data
    $inventoryData = getInventory($serialID);
    $invStatusID = $inventoryData[14];
    $skuNumber = $inventoryData[1];
    
    //Get Current Recount Data
    $recountData = getRecount($serialID);
    
    //Verify and entry exsists and make one if one doesnt
    if(!$recountData){
        //Get Serial Data
        $serialData = getSerialDetails($serialID);
        $skuStatusID = $serialData[0][16];
        $bin = $serialData[0][4];
        $qty = $serialData[0][3];
        $userID = $_SESSION['userID'];
        
        $newInsert = newPhyInvEntry($serialID, $bin, $qty, $userID, $skuNumber, $invStatusID, $skuStatusID);
    }
    
    if($invStatusID != 4){
        $tranArr[0] = 0;
    }else{
        
        //Get Current Qty
        $originalQty = $recountData[2];
        
        //Update Quantity
        $useRecount = updateQuantity($serialID, $recountQty);

        //Refresh Totals
        $skuTotals = getSkuPhyInvData($skuNumber);

        //Build SKU details
        $skuDetails = getSerialDetails($serialID);
        $buildRecountView = buildRecountDetails($skuDetails, TRUE, TRUE);

        //Build Normal View
        $phyInvBuild = buildPhysicalInventory($skuTotals, $action, TRUE);
        
        //Get newly created data
        $recountExtData = recountExtData($serialID);
        $buildRecountExtension = buildRecountExtension($recountExtData, TRUE);

        //Encoder to JSON for AJAX
        //set strings and encode to JSON String
        $tranArr = null;
        $tranArr[0] = $phyInvBuild;
        $tranArr[1] = $buildRecountView;
        $tranArr[2] = $buildRecountExtension;
        $tranArr[3] = $skuNumber;
        
    }
    
    

    $data = json_encode($tranArr);
    echo $data;
    $ajax = true;
    
}elseif($action == 'completeStatus'){
    //Get Status Change Count (not counted & not found)
    $statusChangeCount = getStatusChangeCounted($invDate);
    $statusChangeCount2 = getStatusChangeCounted2($invDate);
    //Get Bin Change Count
    $binChangeCount = getBinChangeCount($invDate);
    //Get Qty Change Count
    $qtyChangeCount = getQtyChangeCount($invDate);
    
    $tranArr = null;
    $tranArr[0] = $statusChangeCount[0];
    $tranArr[1] = $statusChangeCount2[0];
    $tranArr[2] = $binChangeCount[0];
    $tranArr[3] = $qtyChangeCount[0];
    
    $buildSummary = buildCompleteSummary($tranArr);
    $data = $buildSummary;
    echo $data;
    $ajax = true;
    
}elseif($action == 'startCorrection'){
    //Get Status Change Count (not counted & not found)
    $statusChangeCount = getStatusChangeCounted($invDate);
    $statusChangeCount2 = getStatusChangeCounted2($invDate);
    //Get Bin Change Count
    $binChangeCount = getBinChangeCount($invDate);
    //Get Qty Change Count
    $qtyChangeCount = getQtyChangeCount($invDate);
    
    $tranArr = null;
    $tranArr[0] = $statusChangeCount[0];
    $tranArr[1] = $statusChangeCount2[0];
    $tranArr[2] = $binChangeCount[0];
    $tranArr[3] = $qtyChangeCount[0];
    
    $buildSummary = buildCompleteSummary($tranArr);
    $data = $buildSummary;
    echo $data;
    $ajax = true;
}elseif($action == 'statusCorrection'){

    // Define Variables
    $errors = array();
    
    // Get Status Change Count (not counted & not found)
    $statusChangeLost = getStatusChangeLost($invDate);
    for($i = 0; $i < sizeof($statusChangeLost); $i++){
        $insertStatus = insertNewStatus($statusChangeLost[$i][0], 2, 1);
        $deactivate = deactivate($statusChangeLost[$i][0], 1);

        // Record Problems
        if($insertStatus) array_push($errors, 'ncnf');
    }
    
    // Found
    $statusChangeFound = getStatusChangeFound($invDate);
    for($i = 0; $i < sizeof($statusChangeFound); $i++){
        $insertStatus = insertNewStatus($statusChangeFound[$i][0], 4, 1);
        $activate = activate($statusChangeFound[$i][0], 1);

        // Record Problems
        if(!$insertStatus) array_push($errors, 'found');
    }

    echo implode(', ', $errors);
    $ajax = true;

}elseif($action == 'binCorrection'){

    // Define Variables
    $errors = array();
    
    // Get & Loop Through Write-ins
    $writeInList = getWriteIn($invDate);
    for($i = 0; $i < sizeof($writeInList); $i++){
        $insertStatus = insertNewBin($writeInList[$i][0], $writeInList[$i][2], null, 1);
        $activate = activate($writeInList[$i][0], 1);
        
         // Record Problems
         if(!$insertStatus) array_push($errors, $writeInList[$i][0]);
    }
    
    echo implode(', ', $errors);
    $ajax = true;
}elseif($action == 'qtyCorrection'){
    
    // Define Variables
    $errors = array();
        
    //Get Status Change Count (not counted & not found)
    $qtyChangeList = getQtyChange($invDate);
    for($i = 0; $i < sizeof($qtyChangeList); $i++){
        $insertStatus = insertNewQty($qtyChangeList[$i][0], $qtyChangeList[$i][1], null, 1);
        $activate = activate($qtyChangeList[$i][0], 1);
        
        // Record Problems
        if(!$insertStatus) array_push($errors, $qtyChangeList[$i][0]);
    }

    echo implode(', ', $errors);
    $ajax = true;


    // //Get Status Change Count (not counted & not found)
    // $qtyChangeList = getQtyChange($invDate);
    // $error = null;
    // $error[0] = 'No Errors';
    // $ecnt = 2;
    
    // //Insert all qty changes, record error if insert fails
    // for($i = 0; $i < sizeof($qtyChangeList); $i++){
    //     $insertStatus = insertNewQty($qtyChangeList[$i][0], $qtyChangeList[$i][1], null, 1);
    //     $activate = activate($qtyChangeList[$i][0], 1);
    //     if(!$insertStatus){
    //         $error[$ecnt] = $qtyChangeList[$i][0];
    //         $ecnt++;
    //     }
    // }
    
    // //Check to see if list is finished, if not set to do another cycle
    // if(sizeof($qtyChangeList) > 0){
    //     $error[1] = 0;
    // }else{
    //     $error[1] = 1;
    // }
    
    // $data = json_encode($error);
    
    // echo $data;
    // $ajax = true;
}elseif($action == 'completeInventory'){
    $complete = setInventoryComplete();
    
    echo $complete ? 1 : 0;
    $ajax = true;
}elseif($action == 'setUpInventory'){
    $clearInventory = clearInventory();
    
    if($clearInventory){
        echo 1;
    }else{
        echo 0;
    }
    
    $ajax = true;
}elseif($action == 'startInventory'){
    
    $startInvHTML = buildstartInvMenu();
    
    
    echo $startInvHTML;
    
    $ajax = true;
}elseif($action == 'exportInvOptions') {

    echo buildInvExportOptions();

    $ajax = true;

}elseif($action == 'exportInventory'){

    $selectedExport = filterNumber($_GET['option']);

    switch ($selectedExport) {
        case 1:
            break;
        case 2:
            break;
        case 3:
            break;
        case 4:
            break;
        case 5:
            break;
        default:
            $reportData = getPhysicalInventoryCat();
    }
    
    $columnHeaders = array(array("skuNumber", "Counted", "QOH2", "Discrepancy", "DDiscrepancy", "physicalInventoryStatus", "DESC1", "DESC2", "physicalInventoryStatusID", "userName", "userID"));
            
    if ($reportData && sizeof($reportData) > 0) {    
        
        $reportArray = array_merge($columnHeaders, $reportData);
        //    convert to csv
        download_send_headers("inv_report_export_" . date("Y-m-d") . ".csv");
        echo array2csv($reportArray);
        die();
        
    }else {
       echo 0;
    }
    
    $ajax = true;
}elseif($action == 'resetInventory') {
    $success = resetInventory();
    if($success == null) {
        echo false;
    }else {
        echo true;
    }
    $ajax = true;
}elseif($action == 'newBinWriteIn'){
    $myArr = null;
    $myArr[0] = 0;
    //Get Serial # & binID
    $serialID = filterNumber($_GET['serialID']);
    $binID = filterNumber($_GET['binID']);
    
    $binName = getBinName($binID);
    
    $inventoryEntry = getPhysicalInventoryEntry($serialID);
    
    if ($inventoryEntry){
        $writeInStatus = updateWriteIn($serialID, $binID);
        $myArr[0] = 1;
        $myArr[1] = $binName;
    }else{
        $myArr[1] = "Failed to update Location";
    }
    
    $data = json_encode($myArr);
    
    echo $data;
    $ajax = true;
}else{
    //Set Action
    $action = 'counted';
    
    //Get Inventory Counts
    $dataCounts = physicalInventoryCount();
    
    //Get datatype from action
    $dataType = determineDataType($action);
    
    //Get Data
    $phyInvData = getPhyInvData($dataType);
    
    $phyInvBuild = buildPhysicalInventory($phyInvData, $action, FALSE);
    $phyInvHeaderBuild = buildPhysicalInventoryHeader('counted', $dataCounts);
    
}

if(!$ajax){
    include 'view.php';
}

function loadPhyInv($action){
    //Get Inventory Counts
    $dataCounts = physicalInventoryCount();
    
    //Get datatype from action
    $dataType = determineDataType($action);
    
    //Get Data
    $phyInvData = getPhyInvData($dataType);
    
    $phyInvBuild = buildPhysicalInventory($phyInvData, $action, FALSE);
    $phyInvHeaderBuild = buildPhysicalInventoryHeader($action, $dataCounts);
    
    //set strings and encode to JSON String
    $tranArr = null;
    $tranArr[0] = $phyInvHeaderBuild;
    $tranArr[1] = $phyInvBuild;

    $data = json_encode($tranArr);
    return $data;
    
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
    }elseif($action == 'escalated'){
        $dataType = 6;
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

function getDiscrepancyLevel($value){
    //Determine Color of $ Discrepancy
        if($value == 0){
            $countDiscrepancy = 'countZero';
             $dollarDiscrepancy = 'dollarZero';
        }else if($value < 0){
            $countDiscrepancy = 'countNegative';
            if($value < -10000){
                $dollarDiscrepancy = 'dollarNegative4';
            }elseif($value < -5000){
                $dollarDiscrepancy = 'dollarNegative3';
            }elseif($value < -1000){
                $dollarDiscrepancy = 'dollarNegative2';
            }elseif($value < -100){
                $dollarDiscrepancy = 'dollarNegative1';
            }else{
                $dollarDiscrepancy = 'dollarNegative0';
            }
        }else{
            $countDiscrepancy = 'countPositive';
            if($value > 10000){
                $dollarDiscrepancy = 'dollarPositive4';
            }elseif($value > 5000){
                $dollarDiscrepancy = 'dollarPositive3';
            }elseif($value > 1000){
                $dollarDiscrepancy = 'dollarPositive2';
            }elseif($value > 100){
                $dollarDiscrepancy = 'dollarPositive1';
            }else{
                $dollarDiscrepancy = 'dollarPositive0';
            }
        }
        
        $myArr[0] = $countDiscrepancy;
        $myArr[1] = $dollarDiscrepancy;
        
        return $myArr;
}

function buildPhysicalInventory($phyInvData, $action, $innerHTML){
        $phyInvBuild = null;
    for($i = 0; $i < sizeof($phyInvData); $i++){
        
        $discrepancyLevel = getDiscrepancyLevel($phyInvData[$i][4]);
        $countDiscrepancy = $discrepancyLevel[0];
        $dollarDiscrepancy = $discrepancyLevel[1];
        
        if($phyInvData[$i][5] == 'not counted'){
            $status = 'notcounted';
        }else{
            $status = $phyInvData[$i][5];
        }
        if(!$innerHTML){
            $phyInvBuild .= '<div class="phyInventoryPanel" id="' . $phyInvData[$i][0] . '">';
        }
            $phyInvBuild .= '<div class="grid">
                                <div class="unit one-quarter ">
                                    <h1>Sku#: ' . $phyInvData[$i][0] . 
                                '</div>
                                <div class="unit three-quarters ">
                                    <div class="grid">
                                        <div class="unit one-third align-center">
                                            <div class="grid">
                                                <div class="unit whole align-center">
                                                    Status
                                                </div>
                                            </div>
                                            <div class="grid">
                                                <div class="unit whole align-center">
                                                    <div class="' . $status .'">' . $phyInvData[$i][5] . '</div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="unit one-third align-left">
                                            <div class="grid">
                                                <div class="unit half align-center">
                                                    Counted <a href="#" onclick="showLots(' . $phyInvData[$i][0] . ')" title="Show Lots"><span style="font-size: 1.5em" id="skuInfoBtnBlue">I</span></a>
                                                </div>
                                                <div class="unit half align-center">
                                                    QOH
                                                </div>
                                            </div>
                                            <div class="grid">
                                                <div class="unit half align-center">
                                                    <div>' . (!$phyInvData[$i][1] ? 0 : $phyInvData[$i][1])  . '</div>
                                                </div>
                                                <div class="unit half align-center">
                                                    <div>' . (!$phyInvData[$i][2] ? 0 : $phyInvData[$i][2]) . '</div>
                                                </div>
                                            </div>
                                            <div class="lotsPopup" id="lP_' . $phyInvData[$i][0] . '"></div>
                                        </div>

                                        <div class="unit one-third align-center">
                                            <div class="grid">
                                                <div class="unit half align-center">
                                                    Discrepancy
                                                </div>
                                                <div class="unit half align-center">
                                                    $Discrepancy
                                                </div>
                                            </div>
                                            <div class="grid">
                                                <div class="unit half align-center">
                                                    <div id="' . $countDiscrepancy .'">' . (float)$phyInvData[$i][3] . '</div>
                                                </div>
                                                <div class="unit half align-center">
                                                    <div id="' . $dollarDiscrepancy .'">$ ' . (float)$phyInvData[$i][4] . '</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>';
            $phyInvBuild .= '<div class="grid">
                                <div class="unit two-fifths ">
                                    DESC1: ' . $phyInvData[$i][6] . '</br>
                                    DESC2: ' . $phyInvData[$i][7] .
                                '</div>
                                <div class="unit two-fifths ">
                                    RECOUNTED BY: ' . $phyInvData[$i][9] . '
                                </div>
                                <div class="unit one-fifth ">
                                    <div class="grid">';
                                        
                                    If($action != 'flagged' && $action != 'recounted' && $action != 'escalated'){
                                        $phyInvBuild .=
                                            '<div class="unit one-quarter align-center">
                                                <a href="" onclick="phyInvDetails(' . $phyInvData[$i][0] . '); return false;" title="Goto Details">
                                                    <span>i</span>
                                                </a>
                                            </div>
                                            <div class="unit one-quarter align-center">
                                                <a href="" onclick="updateStatus(\'' . $action . '\', \'flagged\', ' . $phyInvData[$i][0] . '); return false;" title="Flag SKU#">
                                                    <span>*</span>
                                                </a>
                                            </div>';
                                    }elseif($action == 'recounted' || $action == 'escalated'){
                                        $phyInvBuild .= 
                                            '<div class="unit one-quarter align-center">
                                                <a href="" onclick="resumeRecount(' . $phyInvData[$i][0] . ', 1); return false;" title="Goto Details">
                                                    <span>i</span>
                                                </a>
                                            </div>
                                            <div class="unit one-quarter align-center">
                                                <a href="" onclick="updateStatus(\'' . $action . '\', \'escalated\', ' . $phyInvData[$i][0] . '); return false;" title="Escalate SKU#">
                                                    <span>f</span>
                                                </a>
                                            </div>';
                                    }else{
                                        $phyInvBuild .= 
                                            '<div class="unit one-quarter align-center">
                                                <a href="" onclick="phyInvDetails(' . $phyInvData[$i][0] . '); return false;" title="Goto Details">
                                                    <span>i</span>
                                                </a>
                                            </div>
                                            <div class="unit one-quarter align-center"></div>';
                                    }
                                    
                                    If($action != 'hold'){
                                        $phyInvBuild .=
                                            '<div class="unit one-quarter align-center">
                                                <a href="" onclick="updateStatus(\'' . $action . '\', \'hold\', ' . $phyInvData[$i][0] . '); return false;" title="Hold SKU#">
                                                    <span>%</span>
                                                </a>
                                            </div>';
                                    }else{
                                        $phyInvBuild .= 
                                            '<div class="unit one-quarter align-center"></div>';
                                    }
                                    
                                    If($action != 'passed'){
                                        $phyInvBuild .=
                                            '<div class="unit one-quarter align-center">
                                                <a href="" onclick="updateStatus(\'' . $action . '\', \'passed\', ' . $phyInvData[$i][0] . '); return false;" title="Pass SKU#">
                                                    <span>$</span>
                                                </a>
                                            </div>';
                                    }else{
                                        $phyInvBuild .= 
                                            '<div class="unit one-quarter align-center"></div>';
                                    }
                                        
                    $phyInvBuild .=        
                                    '</div>
                                </div>
                            </div>';
        if(!$innerHTML){
            $phyInvBuild .= '</div>';
        }
    }
    return $phyInvBuild;
}

function buildPhysicalInventoryHeader($action, $dataCounts){
    $phyInvHeaderBuild = null;
    $phyInvHeaderBuild .= '<nav class="phyInventoryNav">
                            <button type="button" '
                            . ($action == 'counted' ? 'id="selected"' : 'id="notselected"') . 
                                ' onclick="phyInvNav(\'counted\')" title="Production Schedule"><span>q</span>Counted<div class="invCount">' . (isset($dataCounts[1][1]) ? $dataCounts[1][1] : 0) . '</div></button>
                                <button type="button" '
                            . ($action == 'hold' ? 'id="selected"' : 'id="notselected"') . 
                                ' onclick="phyInvNav(\'hold\')" title="Hold View"><span>%</span>On Hold<div class="invCount">' . (isset($dataCounts[2][1]) ? $dataCounts[2][1] : 0) . '</div></button>
                                <button type="button" '
                            . ($action == 'flagged' ? 'id="selected"' : 'id="notselected"') . 
                                ' onclick="phyInvNav(\'flagged\')" title="Flagged View"><span>*</span>Flagged<div class="invCount">' . (isset($dataCounts[3][1]) ? $dataCounts[3][1] : 0) . '</div></button>
                                <button type="button" '
                            . ($action == 'recounted' ? 'id="selected"' : 'id="notselected"') . 
                                ' onclick="phyInvNav(\'recounted\')" title="ReCount View"><span>n</span>Re-Counted<div class="invCount">' . (isset($dataCounts[4][1]) ? $dataCounts[4][1] : 0)  . '</div></button>
                                <button type="button" '
                            . ($action == 'passed' ? 'id="selected"' : 'id="notselected"') . 
                            ' onclick="phyInvNav(\'passed\')" title="Passed View"><span>v</span>Passed<div class="invCount">' . (isset($dataCounts[5][1]) ? $dataCounts[5][1] : 0) . '</div></button>
                                <button type="button" ' 
                            . ($action == 'notcounted' ? 'id="selected"' : 'id="notselected"') . 
                            ' onclick="phyInvNav(\'notcounted\')" title="Not Counted View"><span>x</span>Not Counted<div class="invCount">' . (isset($dataCounts[0][1]) ? $dataCounts[0][1] : 0) . '</div></button>
                                <button type="button" ' 
                            . ($action == 'escalated' ? 'id="selected"' : 'id="notselected"') . 
                            ' onclick="phyInvNav(\'escalated\')" title="Escalated View"><span>x</span>Escalated<div class="invCount">' . (isset($dataCounts[6][1]) ? $dataCounts[6][1] : 0) . '</div></button>
                        </nav>';

    return $phyInvHeaderBuild;
}


function buildPhysicalInventoryDetails($skuDetails, $innerHTML){
    $phyInvDetails = null;
    if(!$innerHTML){
        $phyInvDetails .= '<div class="overlay anim-scale-up" id="overlay">
                            <div class="grid">
                                <div class="unit one-third">
                                    <h1>Inventory Details</h1>
                                </div>
                                <div class="unit one-third align-center">
                                </div>
                                <div class="unit one-third align-right">
                                    <a href="" onclick="phyInvDetailsClose(); return false;" title="Hold SKU#" id="exit"><span>x</span></a>
                                </div>
                            </div>
                            <div class="phyInventoryPanel" id="phyInventoryPanel"></div>
                            <div id="phyInventoryDetailSection">';
    }
    //<a href="" onclick="phyInvSkuDetails(); return false;" title="Goto Details"><span>i</span></a>
    for($i = 0; $i < sizeof($skuDetails); $i++){
        if($skuDetails[$i][9]){
            $writeIn = $skuDetails[$i][9];
            $binlocation = "-";
            $writeInStatus = 'writeInTrue';
            $orgLocStatus = 'origStatFalse';
            
        }elseif($skuDetails[$i][5]){
            $binlocation = $skuDetails[$i][5];
            $writeIn = "-";
            $writeInStatus = 'writeInFalse';
            $orgLocStatus = 'origStatTrue';
        }else{
            $writeIn = "-";
            $binlocation = "-";
            $writeInStatus = 'writeInFalse';
            $orgLocStatus = 'origStatFalse';
        }
        
        if($skuDetails[$i][12] == 0 || $skuDetails[$i][12] == 11){
            $currentCount = $skuDetails[$i][16];
        }else{
            $currentCount = $skuDetails[$i][3];
        }
        
        if($skuDetails[$i][11] == 'not counted'){
            $status = 'notcounted';
        }else{
            $status = $skuDetails[$i][11];
        }
        if(!$innerHTML){
            $phyInvDetails .= '<div class="phyInventoryDetailPanel" id="' . $skuDetails[$i][0] . '">';
        }
                $phyInvDetails .='<div class="grid">
                                    <div class="unit one-third">
                                        <div class="grid">
                                            <div class="unit whole">
                                                Serial#: ' . $skuDetails[$i][0] .
                                            '</div>
                                            <div class="unit whole">
                                            Lot Code: ' . $skuDetails[$i][17] .
                                            '</div>
                                        </div>
                                        <div class="grid">
                                            <div class="unit whole">
                                                Counted: ' . $currentCount .
                                            '</div>
                                        </div>
                                        <div class="grid">
                                            <div class="unit whole">
                                                Counted By: ' . $skuDetails[$i][7] .
                                            '</div>
                                        </div>
                                    </div>
                                    <div class="unit one-third" align-center>
                                        <div class="grid">
                                            <div class="unit half align-center">
                                                Orig Loc:
                                            </div>
                                            <div class="unit half align-center">
                                                Write In:
                                            </div>
                                        </div>
                                        <div class="grid">
                                            <div class="unit half align-center">
                                                <div class="' . $orgLocStatus . '">'
                                                    . $binlocation .
                                                '</div>
                                            </div>
                                            <div class="unit half align-center">
                                                <div class="' . $writeInStatus . '">'
                                                    . $writeIn .
                                                '</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="unit one-third align-center">
                                        <div class="grid">
                                            <div class="unit half">
                                                Cur Status:
                                            </div>
                                            <div class="unit half">
                                                PhyInv Status:
                                            </div>
                                        </div>
                                        <div class="grid">
                                            <div class="unit half">
                                                <div>'
                                                    . $skuDetails[$i][6] .
                                                '</div>
                                            </div>
                                            <div class="unit half">
                                                <div class="'. $status .'">'
                                                    . $skuDetails[$i][11] .
                                                '</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                </div>';
                            
    }
    if(!$innerHTML){
                                $phyInvDetails .= '</div></div>';
                            } 

    return $phyInvDetails;
}

function buildRecountDetails($skuDetails, $innerHTML, $useRecount){
    $phyInvDetails = null;
    if(!$innerHTML){
        $phyInvDetails .= '<div class="overlay anim-scale-up" id="overlay">
                            <div class="grid">
                                <div class="unit two-fifths">
                                    <h1>Inventory Details</h1>
                                </div>
                                <div class="unit one-fifth align-center">
                                    <label for="recountScan">Scan To Search</label>
                                    <input type="text" name="recoundScan" id="recountScan" onkeydown="Javascript: if (event.keyCode==13) recountScan();">
                                    <div id=errorMessage></div>
                                </div>
                                <div class="unit one-fifth align-right">
                                    <button onclick=submitRecount(' . $skuDetails[0][2] . ')>Submit</button>
                                </div>
                                <div class="unit one-fifth align-right">
                                    <a href="" onclick="phyInvDetailsClose(); return false;" title="Hold SKU#" id="exit"><span>x</span></a>
                                </div>
                            </div>
                            <div class="phyInventoryPanel" id="phyInventoryPanel"></div>
                            <div id="phyInventoryDetailSection">';
    }
    //<a href="" onclick="phyInvSkuDetails(); return false;" title="Goto Details"><span>i</span></a>
    for($i = 0; $i < sizeof($skuDetails); $i++){
        if($skuDetails[$i][9]){
            $writeIn = $skuDetails[$i][9];
            $binlocation = "-";
            $writeInStatus = 'writeInTrue';
            $orgLocStatus = 'origStatFalse';
            
        }elseif($skuDetails[$i][5]){
            $binlocation = $skuDetails[$i][5];
            $writeIn = "-";
            $writeInStatus = 'writeInFalse';
            $orgLocStatus = 'origStatTrue';
        }else{
            $writeIn = "-";
            $binlocation = "-";
            $writeInStatus = 'writeInFalse';
            $orgLocStatus = 'origStatFalse';
        }
        
        if($skuDetails[$i][14] != $skuDetails[$i][3]){
            $originalQty = FALSE;
        }else{
            $originalQty = TRUE;
        }
        
        if($skuDetails[$i][12] == 0 || $skuDetails[$i][12] == 11){
            $currentCount = $skuDetails[$i][16];
        }else{
            $currentCount = $skuDetails[$i][3];
        }
        
        if($skuDetails[$i][11] == 'not counted'){
            $status = 'notcounted';
        }else{
            $status = $skuDetails[$i][11];
        }
        if(!$innerHTML){
            $phyInvDetails .= '<div class="phyInventoryDetailPanel" tabindex="' . $i . '" id="' . $skuDetails[$i][0] . '">';
        }
                $phyInvDetails .='<div class="grid">
                                    <div class="unit one-quarter">
                                        <div class="grid">
                                            <div class="unit whole">
                                                Serial#: ' . $skuDetails[$i][0] .
                                            '</div>
                                            <div class="unit whole">
                                            Lot Code: ' . $skuDetails[$i][17] .
                                        '</div>
                                        </div>
                                        <div class="grid">
                                            <div class="unit whole">
                                                Counted: ' . $currentCount .
                                            '</div>
                                        </div>
                                        <div class="grid">
                                            <div class="unit whole">
                                                Counted By: ' . $skuDetails[$i][7] .
                                            '</div>
                                        </div>
                                    </div>
                                    <div class="unit one-quarter" align-center>
                                        <div class="grid">
                                            <div class="unit half align-center">
                                                Orig Loc:
                                            </div>
                                            <div class="unit half align-center">
                                                Write In:
                                            </div>
                                        </div>
                                        <div class="grid">
                                            <div class="unit half align-center">
                                                <div class="' . $orgLocStatus . '">'
                                                    . $binlocation .
                                                '</div>
                                            </div>
                                            <div class="unit half align-center">
                                                <div class="' . $writeInStatus . '" id="wi' . $skuDetails[$i][0] . '">'
                                                    . $writeIn .
                                                '</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="unit one-quarter align-center">
                                        <div class="grid">
                                            <div class="unit half">
                                                Cur Status:
                                            </div>
                                            <div class="unit half">
                                                PhyInv Status:
                                            </div>
                                        </div>
                                        <div class="grid">
                                            <div class="unit half">
                                                <div>'
                                                    . $skuDetails[$i][6] .
                                                '</div>
                                            </div>
                                            <div class="unit half">
                                                <div class="'. $status .'">'
                                                    . $skuDetails[$i][11] .
                                                '</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="unit one-quarter align-center">
                                        <div class="grid">
                                            <div class="unit one-third">';
                if ($skuDetails[$i][11] == 'counted' && $skuDetails[$i][1]){ 
                    $phyInvDetails .= '<button onclick="reCountAction(' . $skuDetails[$i][0] . ', \'RC-confirm\')" title="Confirm" ><span>v</span></button>';
                }else{
                    $phyInvDetails .= '<button onclick="reCountAction(' . $skuDetails[$i][0] . ', \'RC-found\')" title="Confirm" ><span>v</span></button>';
                }
                        $phyInvDetails .= '</div>
                                            <div class="unit one-third">
                                                <button onclick="reCountAction(' . $skuDetails[$i][0] . ', \'RC-notFound\')" title="Not found"><span>X</span></button>
                                            </div>
                                            <div class="unit one-third">';
                            if($skuDetails[$i][14]){
                            $phyInvDetails .= '<button onclick="useRecount(' . $skuDetails[$i][0] . ', ' . $skuDetails[$i][14] . ')" title="Use Recount" ><span>G</span></button>';
                            }
                            $phyInvDetails .= '</div>
                                        </div>
                                        <div class="grid">
                                            <div class="unit half">
                                                <div></div>
                                            </div>
                                            <div class="unit half">
                                                <div></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="grid">
                                    <div class="unit half">
                                        <div id="qtyField' . $skuDetails[$i][0] . '">
                                                    <label for="newQty' . $skuDetails[$i][0] . '" class="hide">New Qty</label>
                                                    <input id="newQty' . $skuDetails[$i][0] . '" class="hide" onkeydown="Javascript: if(event.keyCode==13) addRecount(' . $skuDetails[$i][0] . ');"></input>
                                        </div>';
                $phyInvDetails .= '</div>
                                    <div class="unit half">
                                        <div id="binField' . $skuDetails[$i][0] . '">
                                            <label for="newBin' .  $skuDetails[$i][0] . '">Scan New Bin</label>
                                            <input id="newBin' .  $skuDetails[$i][0] . '" onkeydown="Javascript: if(event.keyCode==13) newBinWriteIn(' .  $skuDetails[$i][0] . ');"></input>
                                        </div>
                                    </div>';
            $phyInvDetails .= '</div>
                            <div id="recountExt' . $skuDetails[$i][0] . '">';
                                if($skuDetails[$i][14]){
                                    $phyInvDetails .=
                                    '<div class="recountExt">
                                        <div class="grid">
                                            <div class="unit one-third ">';
                            if($originalQty){
                                $phyInvDetails .= 'Used Recount Data
                                            </div>
                                            <div class="unit one-third ">
                                                Org-Count: ' . $skuDetails[$i][13];
                            }else{
                                $phyInvDetails .= 'New Recount Data
                                            </div>
                                            <div class="unit one-third ">
                                                Re-Count: ' . $skuDetails[$i][14];
                            }
                        $phyInvDetails .= '</div>
                                            <div class="unit one-third ">
                                                Re-Counted By: ' . $skuDetails[$i][15]  .
                                            '</div>
                                        </div>
                                    </div>';
                            }
                                    
            $phyInvDetails .= '</div>
                    </div>';
                                

    }
    if(!$innerHTML){
        $phyInvDetails .= '</div></div>';
    }

    return $phyInvDetails;
}



function buildRecountView($phyInvData, $action, $innerHTML){
        $phyInvBuild = null;
    for($i = 0; $i < sizeof($phyInvData); $i++){
        //Determine Color of $ Discrepancy
        $discrepancyLevel = getDiscrepancyLevel($phyInvData[$i][4]);
        $countDiscrepancy = $discrepancyLevel[0];
        $dollarDiscrepancy = $discrepancyLevel[1];
        
        if($phyInvData[$i][5] == 'not counted'){
            $status = 'notcounted';
        }else{
            $status = $phyInvData[$i][5];
        }
        //Check if recount has been assigned
        if($phyInvData[$i][10] && $action != 'resumeRecount' && $action != 'recounted' && $action != 'escalated'){
            $phyInvBuild .= '<div class="selectedRecountPanel anim-scale-up" id="' . $phyInvData[$i][0] . '">
                            <div class="grid">
                                <div class="unit one-quarter align-center">
                                    <div class="grid">
                                        <div class="unit whole align-center">
                                            SKU#
                                        </div>
                                    </div>
                                    <div class="grid">
                                        <div class="unit whole align-center">
                                            <div class="' . $status .'">' . $phyInvData[$i][0] . '</div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="unit one-quarter align-center">
                                    <div class="grid">
                                        <div class="unit whole align-center">
                                            $Discrepancy
                                        </div>
                                    </div>
                                    <div class="grid">
                                        <div class="unit whole align-center">
                                            <div id="' . $dollarDiscrepancy . '">$ ' . (float)$phyInvData[$i][4] . '</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="unit one-quarter align-left">
                                    <div class="grid">
                                        <div class="unit whole align-center">
                                            Reserved By
                                        </div>
                                    </div>
                                    <div class="grid">
                                        <div class="unit whole align-center">
                                            <div>' . $phyInvData[$i][9] . '</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="unit one-quarter align-right">
                                    <div class="grid">
                                        <div class="unit whole align-center">
                                            Options
                                        </div>
                                    </div>
                                    <div class="grid">
                                        <div class="unit half align-center">
                                            <a href="" onclick="resumeRecount(' . $phyInvData[$i][0] . ', 0); return false;" title="Resume ReCount">
                                                <span>i</span>
                                            </a>
                                        </div>
                                        <div class="unit half align-center">
                                            <a href="" onclick="releaseRecount(' . $phyInvData[$i][0] . '); return false;" title="Release ReCount">
                                                <span>M</span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>';
        }else{
            if(!$innerHTML){
                $phyInvBuild .= '<div class="phyInventoryPanel" id="' . $phyInvData[$i][0] . '">';
            }
                $phyInvBuild .= '<div class="grid">
                                    <div class="unit one-quarter ">
                                        <h1>Sku#: ' . $phyInvData[$i][0] . 
                                    '</h1></div>
                                    <div class="unit three-quarters ">
                                        <div class="grid">
                                            <div class="unit one-third align-center">
                                                <div class="grid">
                                                    <div class="unit whole align-center">
                                                        Status
                                                    </div>
                                                </div>
                                                <div class="grid">
                                                    <div class="unit whole align-center">
                                                        <div class="' . $status .'">' . $phyInvData[$i][5] . '</div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="unit one-third align-left">
                                                <div class="grid">
                                                    <div class="unit half align-center">
                                                        Counted
                                                    </div>
                                                    <div class="unit half align-center">
                                                        QOH
                                                    </div>
                                                </div>
                                                <div class="grid">
                                                    <div class="unit half align-center">
                                                        <div>' . (!$phyInvData[$i][1] ? 0 : $phyInvData[$i][1])  . '</div>
                                                    </div>
                                                    <div class="unit half align-center">
                                                        <div>' . $phyInvData[$i][2] . '</div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="unit one-third align-center">
                                                <div class="grid">
                                                    <div class="unit half align-center">
                                                        Discrepancy
                                                    </div>
                                                    <div class="unit half align-center">
                                                        $Discrepancy
                                                    </div>
                                                </div>
                                                <div class="grid">
                                                    <div class="unit half align-center">
                                                        <div id="' . $countDiscrepancy .'">' . (float)$phyInvData[$i][3] . '</div>
                                                    </div>
                                                    <div class="unit half align-center">
                                                        <div id="' . $dollarDiscrepancy .'">$ ' . (float)$phyInvData[$i][4] . '</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="grid">
                                    <div class="unit two-fifths ">
                                        DESC1: ' . $phyInvData[$i][6] . '</br>
                                        DESC2: ' . $phyInvData[$i][7] .
                                    '</div>
                                    <div class="unit two-fifths ">
                                    RECOUNTED BY: ' . $phyInvData[$i][9] . '
                                    </div>
                                    <div class="unit one-fifth ">
                                        <div class="grid">
                                        <div class="unit whole align-right">
                                            <a href="" onclick="selectRecount(' . $phyInvData[$i][0] . ', false); return false;" title="Select Re-Count">
                                                <span>1</span>
                                            </a>
                                        </div>
                                        ';

                        $phyInvBuild .=        
                                        '</div>
                                    </div>
                                </div>';
                        if(!$innerHTML){
                            $phyInvBuild .= '</div>';
                        }
                            
        }
    }
    return $phyInvBuild;
}


function buildRecountExtension($recountData, $originalQty){
    $phyInvHeaderBuild = null;
    
    $phyInvHeaderBuild .= '<div class="recountExt">
                                <div class="grid">
                                    <div class="unit one-third ">';
                    if($originalQty){
                        $phyInvHeaderBuild .= 'Used Recount Data
                                    </div>
                                    <div class="unit one-third ">
                                        Org-Count: ' . $recountData[6];
                    }else{
                        $phyInvHeaderBuild .= 'New Recount Data
                                    </div>
                                    <div class="unit one-third ">
                                        Re-Count: ' . $recountData[7];
                    }
             $phyInvHeaderBuild .= '</div>
                                    <div class="unit one-third ">
                                        Re-Counted By: ' . $recountData[9] .
                                    '</div>
                                </div>
                            </div>';

    return $phyInvHeaderBuild;
}


function buildCompleteSummary($tranArr){
    $totalStatusChange = (int)$tranArr[0] + (int)$tranArr[1];

    $html = '
    <div class="compInvModal">
        <h1>Complete Inventory</h1>
        
        <p id="statusChange">Status Change: ' . $totalStatusChange . '</p>
        <p class="InvCompletionError" id="statusChangeError">Errors: </p>
        
        <p id="WriteIn">Write-In Update: ' . $tranArr[2] . '</p>
        <p class="InvCompletionError" id="WriteInError">Errors: </p>

        <p id="QtyChange">Qty Change</p>
        <p class="InvCompletionError" id="QtyChangeError">Errors: </p>

        <div class="progress-bar">
            <progress id="completionProgress" value="0" max="3"></progress>
            <p id="completionProgress-label">0%</p>
        </div>

        <div id="submitButton">
            <a onclick="correction()" class="bs-btn btn-blue">Complete</a>
        </div>
    </div>
    ';

    return $html;

    // $phyComplete = '<div class="overlay anim-scale-up" id="overlay" style="width: 15em; min-height: 18em;">
    //                     <a href="" onclick="phyInvDetailsClose(); return false;" title="Exit" id="exit"><span>x</span></a>
    //                     <div class="grid" style="margin-left: 10%">
    //                         <div class="unit whole">
    //                             <h1 class="text-center compInvTitle">Complete Inventory</h1>
    //                         </div>
    //                         <div class="unit whole">
    //                             <div>Status Change: ' . $totalStatusChange . '</div><div id="statusChange">: </div>
    //                             <div id="statusChangeError">Errors:</div>
    //                         </div>
    //                         <div class="unit whole">
    //                             <div>Write-In Update: ' . $tranArr[2] . '</div><div id="WriteIn">:</div>
    //                             <div id="WriteInError">Errors:</div>
    //                         </div>
    //                         <div class="unit whole">';
    //                             // <div>Qty Change: ' . $tranArr[3] . '</div><div id="QtyChange">:</div>
    //                             // <div id="QtyChangeError">Errors:</div>
    //                     $phyComplete .= '
                        
    //                     </div>
    //                     </div>
    //                     <div id="submitButton" class="unit whole"><button onclick="correction()" style="margin-left: 25%" title="Start Corrections">Start Corrections</button></div></div>';
    // return $phyComplete;
}



function buildstartInvMenu() {
    
    
    $startInvHTML;
    
    $billableCustomers = getBillableCustomers();
    
    $categoryList = getCategoryList();

    $startInvHTML = '


    <form id="reportFilterForm" method="post" action="." id="selectionForm">
    <a href="/physicalInventory/index.php" id="exit"><span>x</span></a>
    <h1>Discrepancy Report Filters</h1>
    <h3>Starting a new inventory will clear previous inventory data. Are you sure you want to proceed? Press the Submit button to
      continue or press X in the top right corner to exit.</h3>
    <div class="reportSelection">

        <div class="discrepancyFilter">
            <div id="checkBoxBtn">
                <h2>Select Category <a href="#" id="invertChecks" onclick="checkAll();">Check-All</a></h2>
            </div>
            <select id="customerFilter"name="customerID" required>
                <option value="" disabled selected>Select Customer</option>';
                    for($i = 0; $i < sizeof($billableCustomers); $i++){
                        $startInvHTML .= '<option value=' . $billableCustomers[$i][0] . '>' . $billableCustomers[$i][1] . '</option>';
                    }
                $startInvHTML .= '</select>
        </div>


        <div id="catsList" class="grid">';

    //     <div class="grid">
    //     <div id="checkBoxBtn" class="unit half">
    //         <h2>Select Category</h2>
    //         <a href="#" id="invertChecks" onclick="checkAll();">Check-All</a>
    //     </div>
    //     <div class="unit half">
    //         <select id="customerFilter"name="customerID" required>
    //         <option value="" disabled selected>Select Customer</option>';
    //             for($i = 0; $i < sizeof($billableCustomers); $i++){
    //                 $startInvHTML .= '<option value=' . $billableCustomers[$i][0] . '>' . $billableCustomers[$i][1] . '</option>';
    //             }
    //         $startInvHTML .= '</select>
    //     </div>
    // </div>
       
        for($i = 0; $i < 5; $i++) {
             $startInvHTML .= '<div class="unit one-fifth">';
             for($j = 0; $j < ((sizeof($categoryList) / 5)); $j++) {
                 $x = $j + ($i * (floor(sizeof($categoryList) / 5) + 1));
                 //Echo every cycle a new option box
                 if(isset($categoryList[$x][0])){
                     $startInvHTML .= '<label for="' . $categoryList[$x][0] . '">' . $categoryList[$x][0] . '</label>
                            <input type="checkbox" name="filter" value="' . $categoryList[$x][0] . '" id="' . $categoryList[$x][0] . '"><br>';
                }
            }
            $startInvHTML .=  '</div>';
        }
    
        
    
    
    
        
          $startInvHTML .= '</div></div><a class="bs-btn btn-blue" class="reportFilterSubmitButton" onclick="reportFilter();">Submit</a>
        
    
</form>';
          
    return $startInvHTML;
}

function buildLotsTable($skuNumber) {


    $lotData = getBatchInfo($skuNumber);

    $html = '
    <div class="overlay anim-scale-up" style="width: 15vw; max-height: 45vh; overflow: hidden;">
    <div class="grid">
        <div class="unit whole">
            <a href="javascript:;" onclick="closeOverlay()" id="exit"><span>x</span></a>
        </div>
        
        <h1 style="font-size: 1.2em; text-align: center; margin-left: 0px">Sku#: ' . $skuNumber . '</h1>

        <div id="lotTableScroll" class="unit whole">

                <table id="lotsTable">
                    
                    <tr>
                        <th>Lot Code</th>
                        <th>Counted</th>
                        <th>QOH</th>
                        <th>Discrepancy</th>
                    </tr>';

                for($i = 0; $i < sizeof($lotData); $i++){
                    $html .= '
                    <tr>
                        <td>' . $lotData[$i][0] . '</td>
                        <td>' . $lotData[$i][1] . '</td>
                        <td>' . $lotData[$i][2] . '</td>
                        <td>' . $lotData[$i][3] . '</td>
                    </tr>
                    ';
                }

            $html .= '
                </table>
            </div>
        </div>
    </div>';


    return $html;

}

function buildInvExportOptions() {
    $html = '
    <div class="invExportOptions">
        <h1>Inventory Report</h1>
        <select name="exportOptions" id="exports">
            <option value="null" selected>Select Export Type...</option>
            <option value="1">Export 1</option>
            <option value="2">Export 2</option>
            <option value="3">Export 3</option>
            <option value="4">Export 4</option>
            <option value="5">Export 5</option>
        </select>
        <a onclick="exportInventory(1)" class="bs-btn btn-blue">Export</a>
    </div>
    ';
    return $html;
}