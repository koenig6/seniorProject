<?php
session_start();

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$curDate = date('Y-m-d');
$action = 'customMenu';
$customMenu = null;
$smallWorldMenu = null;
$ajax = null;
$message = null;
$currentShipmentGroup = null;
$shipmentGroupID = null;



require 'model.php';



//Get Action
if (isset($_POST['action'])) {
 $action = $_POST['action'];
} elseif (isset($_GET['action'])) {
 $action = $_GET['action'];
}



if($action == 'smallWorldMenu' || $action == 'reloadOrders' || $action == 'uploadHeader'){
    if(isset($_GET['shipmentGroupID'])){
        $shipmentGroupID = filterNumber($_GET['shipmentGroupID']);
        $currentShipmentGroup = getShipmentGroup($shipmentGroupID);
    }else if(isset($_SESSION['customCustomerID'])) {
        $currentShipmentGroup = $_SESSION['currentShipmentGroup'];
    }else{
        $currentShipmentGroup = getShipmentGroup(6);
    }
    
    $customerID = $currentShipmentGroup[0][1];
    $_SESSION['customCustomerID'] = $currentShipmentGroup[0][1];
    $_SESSION['currentShipmentGroup'] = $currentShipmentGroup;
    $displayPO = true;

    // echo $customerID;
    
}elseif($action == 'moduleModal') {

    // TODO This should be put in a more global spot at some point

    $module = filterString($_GET['module']);

    $html = include $_SERVER['DOCUMENT_ROOT'] . '/module/' . $module . '.php';

    return $html;

    $ajax = true;

}elseif($action == 'caveManMenu'){
    // $_SESSION['customCustomerID'] = 0;
    // $customerID = 0;


    if(isset($_GET['shipmentGroupID'])){
        $shipmentGroupID = $_GET['shipmentGroupID'];
        $currentShipmentGroup = getShipmentGroup($shipmentGroupID);
    }else{
        $currentShipmentGroup = getShipmentGroup(6);
    }

    $customerID = 0;
    $_SESSION['customCustomerID'] = 0;
    $_SESSION['currentShipmentGroup'] = $currentShipmentGroup;
    $displayPO = true;

    $action = 'smallWorldMenu';
    
}

if(isset($_SESSION['customCustomerID'])){
    $customerID = $_SESSION['customCustomerID'];
}

if($_SESSION['securityLevel'] < 3){
    header('location: /logIn/index.php');
    $_SESSION['message'] = 'Access Denied';
}elseif(timedOut()){
    header('location: /logIn/index.php');
}elseif($_SESSION['loggedIn'] != true){
    header('location: /logIn/index.php');
}elseif($action == 'logOut'){
    logOut();
    header('location: /logIn/index.php');
}elseif($action == 'smallWorldMenu'){

    // $shipmentGroupOptions = getShipmentGroup();
    $pickPackOrders = getPickPackOrders($_SESSION['currentShipmentGroup'][0][1], 1, $_SESSION['currentShipmentGroup'][0][0]);
    $smallWorldMenu = buildPickPackMenu($pickPackOrders, 1);

}elseif($action == 'reloadOrders'){
    $status = filterNumber($_GET['status']);
    $pickPackOrders = getPickPackOrders($_SESSION['currentShipmentGroup'][0][1], $status, $_SESSION['currentShipmentGroup'][0][0]);
    $smallWorldMenu = buildPickPackMenu($pickPackOrders, $status);
    $ajax = true;
    echo $smallWorldMenu;
}elseif($action == 'deleteOrder'){
    $status = 1;
    $orderNumber = filterString($_GET['orderNumber']);
    
    $deleteOrder2 = deleteItemsShipping($orderNumber);
    $deleteOrder = deleteOrderItems($orderNumber);
    $deleteOrder1 = deleteOrderHeader($orderNumber);

    if($deleteOrder1 == null) {
        $tranArr[0] = true;
        $tranArr[1] = null;
    }else {
        $pickPackOrders = getPickPackOrders($_SESSION['currentShipmentGroup'][0][1], 1, $_SESSION['currentShipmentGroup'][0][0]);
        $tranArr[0] = false;
        $tranArr[1] = buildPickPackMenu($pickPackOrders, 1);
    }
    
    // $tranArr[0] = $orderNumber;

    $data = json_encode($tranArr);
    echo $data;

    $ajax = true;
}elseif($action == 'returnToPending') {

    // Variables
    $orderNumber = filterString($_GET['orderNumber']);

    // Update Order Status
    $updateStatus = setStatus($orderNumber, 2);

    // Get Item Data
    $pickpackitems = getPickPackItems($orderNumber, $customerID);

    for($i = 0; $i < sizeof($pickpackitems); $i++){  // Loop through all pickpackitems and do the following

        $skuNumber = !$pickpackitems[$i][18] ? getPONo($pickpackitems[$i][3]) : $pickpackitems[$i][18];
        $shipping = getShippingData($pickpackitems[$i][0], $pickpackitems[$i][1]);
        $shipped = countShippedQty($shipping[0], $skuNumber);

        updateInventoryShipped($shipping[0], 1);  // Active = 1

        $pickedpallets = availableInventory($skuNumber, $pickpackitems[$i][0], true, $shipping[0]);  // GEt inventory units SELECT inventoryID from inventory where shippingID = :shippingID

        for($j = 0; $j < sizeof($pickedpallets); $j++){  // Loop through inventory units 
            insertNewStatus($pickedpallets[$j][0], 3, $_SESSION['userID']);  //CALL insert to inventoryStatus to 3
        }

    }

}elseif($action == 'setOrder'){
    $orderNumber = filterString($_GET['orderNumber']);
    $status = filterString($_GET['status']);
    $error = false;
    
    $ajax = true;
    $tranArr = null;
    
    if($status == 2){
        //Check If all are assigned
        $pickPackItem = getPickPackItems($orderNumber, $customerID);
        for($i = 0; $i < sizeof($pickPackItem); $i++){
            if($pickPackItem[$i][16] < 0){
                $error = true;
                break;
            }
        }
    }
    
    if(!$error){
            //If current status 1 then reload status 1
            $headerOrder = getPickPackHeader($orderNumber);
            $lastStatus = $headerOrder[23];
            
            //set new status
            $updateStatus = setStatus($orderNumber, $status);

            if($status == 1) {
                $tranArr[3] = 'New';
            }else if($status == 2) {
                $tranArr[3] = 'Pending';
            }else if($status == 3) {
                $tranArr[3] = 'Pulled';
            }else if($status == 4) {
                $tranArr[3] = 'Completed';
            }

            // If last status is 1 set status to one so it will stay on 1 
            if($lastStatus == 1){
                $status = 1;
            }
            
            //Get new list based on status and build refreshed list 
            $pickPackOrders = getPickPackOrders($_SESSION['currentShipmentGroup'][0][1], $lastStatus, $_SESSION['currentShipmentGroup'][0][0]);
            $orderSelectMenu = buildOrdersListBox($pickPackOrders, $lastStatus);

            // $pickPackOrders = getPickPackOrders($customerID, $status);
            // $orderSelectMenu = buildOrderSelect($pickPackOrders, $status);

            //Send list HTML to array
            $tranArr[0] = $orderSelectMenu;
            
            if($status == 1){
                //Setup & build pick pack header because order has been cancelled
                $orderNumber = filterString($_GET['orderNumber']);

                $pickPackHeaderView = buildPickPackHeader($headerOrder);

                //Setup & build pick pack items because order has been cancelled
                $pickPackItem = getPickPackItems($orderNumber, $customerID);
                $assignedLots = getAssignedLots($orderNumber);
                $pickPackItemsView = buildPickPackItems($pickPackItem, $orderNumber, $assignedLots);

                $tranArr[1] = $pickPackHeaderView;
                $tranArr[2] = $pickPackItemsView;
            }

    }else{
        $tranArr[0] = 0;
    }
    $data = json_encode($tranArr);
    echo $data;
    
}elseif($action == 'uploadHeader'){
    $tranArr = null;
    
    //Setup & build pick pack header
    $orderNumber = filterString($_GET['orderNumber']);
    // $headerOrder = getPickPackHeader($orderNumber);
    $tranArr = buildCustomView($orderNumber, $_SESSION['customCustomerID']);
    
    $ajax = true;
    // $tranArr[9] = $customerID;
    $data = json_encode($tranArr);

    echo $data;
    
}elseif($action == 'addBatch'){

        //Setup & build pick pack header
        $batch = filterString($_GET['batch']);
        $qty = filterString($_GET['qty']);
        $orderQty = filterString($_GET['orderQty']);
        $orderNumber = filterString($_GET['orderNumber']);
        $pickPackID = filterString($_GET['pickPackID']);
        $picked = filterString($_GET['bal']);

        $pickPackID2 = $pickPackID;
        
        $userID = $_SESSION['userID'];
        $bal = $orderQty - $picked;

        if($bal > 0 && $qty > 0){
            if ($qty <= $bal){
                $assignedQty = $qty;
            }elseif ($qty > $bal){
                $assignedQty = $bal;
            }

            $addBatch = insertBatch($batch, $assignedQty, $orderNumber, $pickPackID, $userID);
            $_SESSION['bal'] = $picked + $assignedQty;
        }
    
    
    //Refresh Batch List
    //Setup & build pick pack header
    $itemNumber = $_SESSION['itemNumber'];
    $pickPackID = $_SESSION['pickPackID'];
    $orderNumber = $_SESSION['orderNumber'];
    $orderQty = $_SESSION['orderQty'];
    $bal = $_SESSION['bal'];
    
    // if(!$_SESSION['currentShipmentGroup'][0][4]){
    //     $sku = $itemNumber;
    //     $batchList = getItemBatch($sku);
    // }elseif($_SESSION['currentShipmentGroup'][0][4] || $_SESSION['currentShipmentGroup'][0][0] == 1){
    //     $sku = getSkuNumber($itemNumber);
    //     $batchList = getItemBatch($sku[0]);
    // }

    $batchList = getItemBatch($itemNumber);
    
    $batchListView = buildBatchList($batchList, $itemNumber, $orderNumber, $pickPackID, $orderQty, $bal, $customerID);
    
    //Setup & build pick pack items
    $pickPackItem = getPickPackItems($orderNumber, $customerID);
    $assignedLots = getAssignedLots($orderNumber);
    $orderNumber = filterString($_GET['orderNumber']);
    $pickPackItemsView = buildPickPackItems($pickPackItem, $orderNumber, $assignedLots);
    
    //Update Ship Qty
    $updateBatch = updateShippedQty($pickPackID2, $_SESSION['bal']);
    
    $ajax = true;
    
    $tranArr = null;
    $tranArr[0] = $pickPackItemsView;
    $tranArr[1] = $batchListView;

    $data = json_encode($tranArr);

    echo $data;
    
}elseif($action == 'lookupItem') {

    $html = buildItemLookup();

    echo $html;

    $ajax = true;

}elseif($action == 'cancelDeletion') {

    $orderNumber = filterString($_GET['orderNumber']);

    $headerOrder = getPickPackHeader($orderNumber);
    $pickPackHeaderView = buildPickPackHeader($headerOrder);

    echo $pickPackHeaderView;

    $ajax = true;

}elseif($action == 'updateAssignment'){
    //Update with new qty
    $pickPackLotAssignID = filterString($_GET['pickpacklotassignID']);
    $newQty = filterString($_GET['newQty']);
    $updateQty = updateAssignmentQty($pickPackLotAssignID, $newQty);
        
    //Refresh Batch List
    //Setup & build pick pack header
    $itemNumber = $_SESSION['itemNumber'];

    
    $pickPackID = $_SESSION['pickPackID'];
    $orderNumber = $_SESSION['orderNumber'];
    $orderQty = $_SESSION['orderQty'];
    $bal = $_SESSION['bal'];
    
    // if(!$_SESSION['currentShipmentGroup'][0][4]){
    //     $sku = $itemNumber;
    //     $batchList = getItemBatch($sku);
    // }elseif($_SESSION['currentShipmentGroup'][0][4] || $_SESSION['currentShipmentGroup'][0][0] == 1){
    //     $sku = getSkuNumber($itemNumber);
    //     $batchList = getItemBatch($sku[0]);
    // }

    $batchList = getItemBatch($itemNumber);
    
    $batchListView = buildBatchList($batchList, $itemNumber, $orderNumber, $pickPackID, $orderQty, $bal, $customerID);
    
    //Setup & build pick pack items
    $pickPackItem = getPickPackItems($orderNumber, $customerID);
    $assignedLots = getAssignedLots($orderNumber);
    $orderNumber = filterString($_GET['orderNumber']);
    $pickPackItemsView = buildPickPackItems($pickPackItem, $orderNumber, $assignedLots);
    

    $ajax = true;
    
    $tranArr = null;
    $tranArr[0] = $pickPackItemsView;
    $tranArr[1] = $batchListView;

    $data = json_encode($tranArr);

    echo $data;
}elseif($action == 'updateLineItem'){
    //Update with new qty
    $orderQty = filterString($_GET['orderQty']);
    $backOrder = filterString($_GET['backOrder']);
    $pickPackItemID = filterString($_GET['pickPackItemID']);
    $newCaseCount = $_SESSION['orderQty'] == 0 || $_SESSION['totalCaseQty'] == 0 ? 0 : $orderQty / ($_SESSION['orderQty'] / $_SESSION['totalCaseQty']);
    
    $updateQty = updateLineItem($pickPackItemID, $backOrder, $orderQty, $newCaseCount);
        
    //Refresh Batch List
    //Setup & build pick pack header
    $itemNumber = $_SESSION['itemNumber'];
    $pickPackID = $_SESSION['pickPackID'];
    $orderNumber = $_SESSION['orderNumber'];
    $orderQty = $_SESSION['orderQty'];
    $bal = $_SESSION['bal'];
    
    // if(!$_SESSION['currentShipmentGroup'][0][4]){
    //     $sku = $itemNumber;
    //     $batchList = getItemBatch($sku);
    // }elseif($_SESSION['currentShipmentGroup'][0][4] || $_SESSION['currentShipmentGroup'][0][0] == 1){
    //     $sku = getSkuNumber($itemNumber);
    //     $batchList = getItemBatch($sku[0]);
    // }
    
    $batchList = getItemBatch($itemNumber);

    $batchListView = buildBatchList($batchList, $itemNumber, $orderNumber, $pickPackID, $orderQty, $bal, $customerID);
    
    //Setup & build pick pack items
    $pickPackItem = getPickPackItems($orderNumber, $customerID);
    $assignedLots = getAssignedLots($orderNumber);
    //$orderNumber = filterString($_GET['orderNumber']);
    $pickPackItemsView = buildPickPackItems($pickPackItem, $orderNumber, $assignedLots);
    

    $ajax = true;
    
    $tranArr = null;
    $tranArr[0] = $pickPackItemsView;
    $tranArr[1] = $batchListView;

    $data = json_encode($tranArr);

    echo $data;
}elseif($action == 'deleteBatch'){
    //Setup & build pick pack header
    $lotAssignID = filterString($_GET['lotAssignID']);
    $orderNumber = filterString($_GET['orderNumber']);
    $userID = $_SESSION['userID'];
    $addBatch = deleteBatch($lotAssignID);
    
    //Setup & build pick pack items
    $pickPackItem = getPickPackItems($orderNumber, $customerID);
    $assignedLots = getAssignedLots($orderNumber);
    $pickPackItemsView = buildPickPackItems($pickPackItem, $orderNumber, $assignedLots);
    
    //Refresh Batch list
    $itemNumber = filterString($_GET['itemNumber']);
    $pickPackID = filterString($_GET['pickPackID']);
    $orderQty = filterString($_GET['orderQty']);
    $assignedQty = filterString($_GET['assignedQty']);
    $bal = filterString($_GET['bal']);
    
    // if(!$_SESSION['currentShipmentGroup'][0][4]){
    //     $sku = $itemNumber;
    //     $batchList = getItemBatch($sku);
    // }elseif($_SESSION['currentShipmentGroup'][0][4] || $_SESSION['currentShipmentGroup'][0][0] == 1){
    //     $sku = getSkuNumber($itemNumber);
    //     $batchList = getItemBatch($sku[0]);
    // }

    $batchList = getItemBatch($itemNumber);
    
    $newQty = $bal - $assignedQty;
    
    $batchListView = buildBatchList($batchList, $itemNumber, $orderNumber, $pickPackID, $orderQty, $newQty, $customerID);
    
    //Update Ship Qty
    $updateBatch = updateShippedQty($pickPackID, $newQty);
   
    $_SESSION['itemNumber'] = $itemNumber;
    $_SESSION['orderNumber'] = $orderNumber;
    $_SESSION['pickPackID'] = $pickPackID;
    $_SESSION['orderQty'] = $orderQty;
    $_SESSION['bal'] = $newQty;
    
    
    $ajax = true;
    $tranArr = null;
    $tranArr[0] = $pickPackItemsView;
    $tranArr[1] = $batchListView;

    $data = json_encode($tranArr);

    echo $data;
}elseif($action == 'uploadBatch'){
    
    //Setup & build pick pack header
    $itemNumber = filterString($_GET['itemNumber']);
    $pickPackID = filterString($_GET['pickPackID']);
    $orderNumber = filterString($_GET['orderNumber']);
    $orderQty = filterString($_GET['orderQty']);
    $bal = filterString($_GET['bal']);
    
    /*
    if(!$_SESSION['currentShipmentGroup'][0][4]){
        $sku = $itemNumber;
        $batchList = getItemBatch($sku, $customerID);
    }elseif($_SESSION['currentShipmentGroup'][0][4] || $_SESSION['currentShipmentGroup'][0][0] == 1){
        $sku = getSkuNumber($itemNumber);
        $batchList = getItemBatch($sku, $customerID);
    } */

    $batchList = getItemBatch($itemNumber, $customerID);
    
    $batchListView = buildBatchList($batchList, $itemNumber, $orderNumber, $pickPackID, $orderQty, $bal, $customerID);
   
    $_SESSION['itemNumber'] = $itemNumber;
    $_SESSION['orderNumber'] = $orderNumber;
    $_SESSION['pickPackID'] = $pickPackID;
    $_SESSION['orderQty'] = $orderQty;
    $_SESSION['bal'] = $bal;

    $ajax = true;
    $tranArr = null;
    $tranArr[0] = $batchListView;

    $data = json_encode($tranArr);

    echo $data;
    
}elseif($action == 'editAssignment'){
    //Setup & build pick pack header
    $lotAssignID = filterString($_GET['lotAssignID']);
    $pickPackID = filterString($_GET['pickPackID']);
    $bal = filterString($_GET['bal']);
    
    //Setup & build pick pack items
    $pickPackItem = getPickPackItem($pickPackID, $customerID);
    $assignedLots = getPickPackLotAssign($lotAssignID);
    $buildPickPackAssignEditHTML = buildPickPackAssignEdit($pickPackItem, $assignedLots);
    
    $_SESSION['itemNumber'] = $pickPackItem[4];
    $_SESSION['orderNumber'] = $pickPackItem[2];
    $_SESSION['pickPackID'] = $pickPackItem[1];
    $_SESSION['orderQty'] = $pickPackItem[10];
    $_SESSION['bal'] = $bal;

    $ajax = true;
    $tranArr = null;
    $tranArr[0] = $buildPickPackAssignEditHTML;

    $data = json_encode($tranArr);

    echo $data;
}elseif($action == 'editLineItem'){
    //Setup & build pick pack header
    $pickPackID = filterString($_GET['pickPackID']);
    $orderQty = filterString($_GET['orderQty']);
    $bal = filterString($_GET['bal']);
    
    //Setup & build pick pack items
    $pickPackItem = getPickPackItem($pickPackID, $customerID);
    $buildLineItemEditHTML = buildLineItemEdit($pickPackItem, $bal);
    
    $_SESSION['itemNumber'] = $pickPackItem[4];
    $_SESSION['orderNumber'] = $pickPackItem[2];
    $_SESSION['pickPackID'] = $pickPackItem[1];
    $_SESSION['orderQty'] = $pickPackItem[10];
    $_SESSION['bal'] = $bal;
    $_SESSION['totalCaseQty'] = $pickPackItem[12];

    $ajax = true;
    $tranArr = null;
    $tranArr[0] = $buildLineItemEditHTML;

    $data = json_encode($tranArr);

    echo $data;
}elseif($action == 'viewCurrentUsage'){
    
    //Setup & build pick pack header
    $lotCode = filterString($_GET['lotcode']);
    
    //Setup & build pick pack items
    $lotCodeData = getLotCodeData($lotCode);
    $lotCodeOverlayHTML = buildLotCodeOverlay($lotCodeData, $lotCode);

    $ajax = true;
    
    $tranArr = null;
    $tranArr[0] = $lotCodeOverlayHTML;

    $data = json_encode($tranArr);
    
    echo $data;
}elseif($action == 'selectOrder'){
    
    //Setup & build pick pack header
    $orderNumber = filterString($_GET['orderNumber']);
    
    $headerOrder = getPickPackHeader($orderNumber);
    
    if(!$headerOrder){
        $headerOrder = getPickPackHeaderPO($orderNumber);
        if($headerOrder){
            $orderNumber = $headerOrder[1];
        }
    }
    
    $pickPackHeaderView = buildPickPackHeader($headerOrder);
    
    //Setup & build pick pack items
    $pickPackItem = getPickPackItems($orderNumber, $customerID);
    $assignedLots = getAssignedLots($orderNumber);
    $pickPackItemsView = buildPickPackItems($pickPackItem, $orderNumber, $assignedLots);
    
    $_SESSION['orderNumber'] = $orderNumber;
    
    $ajax = true;
    $tranArr = null;
    $tranArr[0] = $pickPackHeaderView;
    $tranArr[1] = $pickPackItemsView;

    $data = json_encode($tranArr);
    
    echo $data;
}elseif($action == 'searchOrders') {

    $status = filterNumber($_GET['status']);
    $search = filterString($_GET['search']);
    $start = filterString($_GET['start']);
    $end = filterString($_GET['end']);

    $html = rebuildOrderList($status, $search, $start, $end);

    echo $html;

    $ajax = true;

}elseif($action == 'newOrder'){
    // $message;
    $newOrderHTML = buildnewOrderMenu($message);
    
    echo $newOrderHTML;
    $ajax = true;
}elseif($action == 'editOrder') {

    $soNumber = filterString($_GET['soNumber']);
    $orderDate = filterString($_GET['orderDate']);
    $shipDate = filterString($_GET['shipDate']);

    $poNumber = filterString($_GET['poNumber']);
    $custNumber = filterString($_GET['custNumber']);
    $shipVia = filterString($_GET['shipVia']);

    $custName = filterString($_GET['custName']);
    $addr1 = filterString($_GET['addr1']);
    $addr2 = filterString($_GET['addr2']);

    $values = array($soNumber, $orderDate, $shipDate, $poNumber, $custNumber, $shipVia, $custName, $addr1, $addr2);

    $html = buildPickPackHeader(null, $values);

    echo $html;

    $ajax = true;
}elseif($action == 'changeOrder') {

    $customerSONumber = filterString($_GET['soNumber']);
    $orderDate = filterString($_GET['orderDate']);
    $shipDate = filterString($_GET['shipDate']);

    $poNumber = filterString($_GET['poNumber']);
    $customerNumber = filterString($_GET['custNumber']);
    $shipVia = filterString($_GET['shipVia']);

    // $custName = filterString($_GET['custName']);
    // $addr1 = filterString($_GET['addr1']);
    // $addr2 = filterString($_GET['addr2']);
    
    if($customerNumber == null || $customerNumber == 0) {
        $customerNumber = '*';
    }

    $success = editOrder($orderDate, $shipDate, $poNumber, $customerNumber, $shipVia, $customerSONumber);
    
    echo $success;

    $ajax = true;

}elseif($action == 'addOrder'){
    // $message;
    //$newOrderHTML = buildnewOrderMenu($message);
    $customerSONumber = getNextPPH()[0];
    $customerSONumber = 't' . strval($customerSONumber);
    $shipmentGroupID = filterString($_GET['shipmentGroupID']);
    $pickPackCompanyID = getShipmentGroup($shipmentGroupID)[0][1];
    $orderDate = filterString($_GET['orderDate']);
    $shipDate = filterString($_GET['shipDate']);
    $customerPO = filterString($_GET['customerPO']);
    $shipVia = filterString($_GET['shipVia']);
    $customerComment = filterString($_GET['customerComment']);
    $shipToCustomerID = filterString($_GET['shipToCustomerID']);
    $shipToID = filterString($_GET['shipToID']);
    $userID = $_SESSION['userID'];

    $currentShipmentGroup = getShipmentGroup($shipmentGroupID);

    // $customerInfo = getCustomerInfo($shipToCustomerID);
    $customerInfo = getShipTo($shipToCustomerID, $shipToID);

    $customerName = $customerInfo[0][2];
    $shipToPhone = $customerInfo[0][10];
    $shipToAddressLine1 = $customerInfo[0][3];
    $shipToAddressLine2 = $customerInfo[0][4];
    $shipToAddressLine3 = $customerInfo[0][5];
    $shipToCity = $customerInfo[0][6];
    $shipToState = $customerInfo[0][7];
    $country = $customerInfo[0][9];
    $customerNumber = $customerInfo[0][0];

    // $r = 'customerSONumber = ' . $customerSONumber . '| pickPackCompanyID: ' . $pickPackCompanyID . '| shipmentGroupID: ' . $shipmentGroupID . '| customerPO: ' . $customerPO . '| shipVia: ' . $shipVia . '| shipToCustomerID: ' . $shipToCustomerID . '| shipToID: ' . $shipToID . '| orderDate: ' . $orderDate;
    
    $newID = addOrder($customerSONumber
                    , $pickPackCompanyID
                    , $orderDate
                    , $shipDate
                    , $customerPO
                    , $shipVia
                    , $customerComment
                    , $customerName
                    , $shipToPhone
                    , $shipToAddressLine1
                    , $shipToAddressLine2
                    , $shipToAddressLine3
                    , $shipToCity
                    , $shipToState
                    , $country
                    , $userID
                    , $shipmentGroupID
                    , $shipToCustomerID
                    , $shipToID
                    , $customerNumber);
    
    // $newID = addOrderSlim(
    //         $customerSONumber
    //         ,$pickPackCompanyID
    //         , $orderDate
    //         , $shipDate
    //         , $customerPO
    //         , $shipVia
    //         , $customerComment
    //         , $shipmentGroupID
    //         , $shipToCustomerID
    //         , $shipToID);
    
    $orderNumber = getOrderSONumber($newID)[0];
    $tranArr = buildCustomView($orderNumber, $customerID, $currentShipmentGroup);
    
    $ajax = true;
    $data = json_encode($tranArr);

    echo $data;

    // echo ' | customerSONumber: ' . $customerSONumber . ' | shipmentGroupID: ' . $shipmentGroupID . ' | pickPackCompanyID: ' . $pickPackCompanyID . ' | orderDate:' . $orderDate . ' | shipDate:' . $shipDate . ' | customerPO: ' . $customerPO . ' | shipVia: ' . $shipVia . ' | customerComment: ' . $customerComment . ' | shipToCustomerID: ' . $shipToCustomerID . ' | shipToID: ' . $shipToID . ' | userID: ' . $userID;
}elseif($action == 'updateOrderItem') {

    $pickPackItemsID = filterString($_GET['pickPackItemsID']);
    $qty = filterNumber($_GET['qty']);
    $notes = filterString($_GET['notes']);

    $return = updateOrderItem($pickPackItemsID, $notes, $qty);

    if($return == null || $return == false) {
        $data = false;
    }else {
        $data = true;
    }

    echo $data;    
    $ajax = true;

}elseif($action == 'newOrderItem'){  
    
    //$newOrderItemHTML = buildNewOrderItem();

    // $number = filterString($_GET['number']);
    // $type = filterString($_GET['type']);

    $array = explode(',', $_GET['skuPart']);

    $number = $array[0];
    $type = $array[1];

    $SONo = filterString($_GET['SONo']);
    $customerID = filterString($_GET['customerID']);

    $notes = filterString($_GET['notes']);
    $qty = filterString($_GET['qty']);

    // echo $notes . ' | ' . $qty;
    // echo var_dump($array);
    // echo 'type: ' . $type . ' | number: ' . $number . ' | SONo: ' . $SONo . ' | notes: ' . $notes . ' | qty: ' . $qty . ' | customerID: ' . $customerID;  

    $pickPackHeader = getPickPackHeader($SONo);

    // if($pickPackHeader[25] != 6) {
    // }
    // echo $type;

    //Check if SKU exsists
    if($type == 'Part') {
        $skuData = getSkuData($number);
    }elseif($type == 'Sku') {
        $skuData = getSkuDataP($number);
    }
   
    
    if($skuData != false || $qty > 0){
        
        $tranArr[0] = true;  

        if($type == 'Part') {
            $addedItem = insertNewItem($SONo, $qty, $number, $pickPackHeader[2], $notes);
            $skuNumber = true;
        }elseif($type == 'Sku') {
            $skuNumber = getSkuNo($number);
            $skuNumber = !$skuNumber ? false : $skuNumber[0];
            if($skuNumber != false) {
                $addedItem = insertNewItem($SONo, $qty, $skuNumber, $pickPackHeader[2], $notes);
            }else {
                $addedItem = false;
            }
        }
    
        if(!$addedItem) {
            $message = "Item Insert Failed.";
            $tranArr[0] = false;
        }else if(!$skuNumber) {
            $message = "No Sku# found with entered Part#";
            $tranArr[0] = false;
        }else{
            $tranArr[0] = true;
        }
    
    }else{
        $message = "New item failed, check sku/part and quantity.";
        
        $tranArr[0] = false;
    }

    if(isset($message)) { $tranArr[1] = $message; }

    $data = json_encode($tranArr);
    echo $data;
    
    // echo var_dump($addedItem);

    // echo '|' . $skuNumber . ' \ ' . $qty . ' \ ' . $SONo . ' \ ' . $pickPackHeader[2] . ' | notes: ' . $notes;
    $ajax = true;
}elseif($action == 'addNewOrderItem'){
    // $message;
    
    
    echo "Hello";
    
    $ajax = true;
}elseif($action == 'deleteOrderItem'){

    $pickPackItemID = filterString($_GET['pickPackItemID']);
    $orderNumber = filterString($_GET['orderNumber']);

    $deleteOrderItem = deleteOrderItem($pickPackItemID);
    
    $tranArr = buildCustomView($orderNumber, $customerID);
    $tranArr[4] = $deleteOrderItem ? 1 : 0; // deletion success
    
    $ajax = true;
    $data = json_encode($tranArr);

    echo $data;
    $ajax = true;
}elseif($action == 'customerChange'){
    // $message;
    
    $selectedCustomerID = filterString($_GET['value']);
    
    // echo "Fire2" + $selectedCustomerID;
    
    $ajax = true;
}elseif($action == 'getShipTos'){
    // $message;
    
    $customerID = filterString($_GET['customerID']);
    
    
    $shipOptionBoxHTML = buildShipToOptionBox($customerID);
    echo $shipOptionBoxHTML;
    $ajax = true;
}elseif($action == 'assignCustomer') {

    // Get Variables
    $pickPackHeaderID = filterNumber($_GET['pickPackHeaderID']);
    $orderCustomerID = filterNumber($_GET['orderCustomerID']);
    $pickPackCustomerID = getShipmentGroup($orderCustomerID)[0][1];

    // Update SONumber
    echo updatePOCustomer($pickPackHeaderID, $orderCustomerID, $pickPackCustomerID) != null ? 1 : 0;

    $ajax = true;
}elseif($action == 'reassignOrder') {

    // Get Variables
    $pickPackHeaderID = filterNumber($_GET['pickPackHeaderID']);

    // Build Reassignment ShipmentGroup Menu.
    echo buildAssignShipmentGroup($pickPackHeaderID);

    $ajax = true;
}elseif($action == 'reloadShipTo'){
    // $message;
    
    $customerID = filterString($_GET['customerID']);
    $shipToNo = filterString($_GET['shipToNo']);
    
    
    $buildShipToDetailsHTML = buildShipToDetails($customerID, $shipToNo);
    echo $buildShipToDetailsHTML;
    $ajax = true;
}else{
    $action = 'custom';
    $customMenu = buildCustomMenu($message);
}

if(!$ajax){
    include 'view.php';
}

function buildCustomView($orderNumber, $customerID, $currentShipmentGroup = null){
    //Setup & build pick pack header
    
    $customerID = $_SESSION['currentShipmentGroup'][0][1];

    if($currentShipmentGroup != null) {
        $currentShipmentGroup = $currentShipmentGroup;
    }else {
        $currentShipmentGroup = $_SESSION['currentShipmentGroup'];
    }

    $headerOrder = getPickPackHeader($orderNumber);
    $pickPackHeaderView = buildPickPackHeader($headerOrder);
    
    //Setup & build pick pack items
    $pickPackItem = getPickPackItems($orderNumber, $customerID);
    $assignedLots = getAssignedLots($orderNumber);
    $pickPackItemsView = buildPickPackItems($pickPackItem, $orderNumber, $assignedLots);
    
    //Build Order List
    $orders = getPickPackOrders($currentShipmentGroup[0][1], 1, $currentShipmentGroup[0][0]);
    $orderListBox = buildOrdersListBox($orders, 1, $currentShipmentGroup);

    $_SESSION['orderNumber'] = $orderNumber;
    
    $tranArr = null;
    $tranArr[0] = $pickPackHeaderView;
    $tranArr[1] = $pickPackItemsView;
    $tranArr[2] = $orderListBox;
    $tranArr[3] = $customerID;
    
    return $tranArr;
}

function buildCustomMenu($message){
    $customMenu = '<a href= "/logIn/index.php?action=mainMenu" id="exit"><span>x</span></a>
                <form method="post" action="." id="mainMenu">
                    <h1>Custom Menu</h1>';
                    if($message){
                        echo '<li><h3>' . $message . '</h3></li>';
                    }
    $customMenu .= '<ul>
                        <li><button type="submit" name="action" id="action" value="smallWorldMenu" title="Small World"><span>@</span></button>
                        <li><button type="submit" name="action" id="action" value="caveManMenu" title="Small World"><span>A</span></button>
                    </ul>
            </form>';
        return $customMenu;   
    
}

function buildOrderSelectOverlay(){
    $orderSelect = include $_SERVER['DOCUMENT_ROOT'] . '/module/reportOrderSelect.php';
    
    return $smallWorldMenu;   
    
}

function buildPickPackAssignEdit($pickPackItem, $assignedLots){
    $customMenu = '<div class="overlay">
                    <a href="javascript:;" onclick="openInventoryDetails()" id="exit"><span>x</span></a>
                    <h1>Edit Assignment</h1>
                        <form>
                            <ul>
                                <li>
                                    <h3 id="errorMessage"></h3>
                                </li>
                                <li>
                                    <label for="customerPO">Customer PO :</label>
                                    <div class="readOnly">' . $pickPackItem[0] . '</div>
                                </li>
                                <li>
                                    <label for="customerSONumber">Customer SO#:</label>
                                    <div class="readOnly" id="customerSONumber">' . $pickPackItem[2] . '</div>
                                </li>
                                <li>
                                    <label for="itemNumber">Item Number :</label>
                                    <div class="readOnly">' . $pickPackItem[4] . '</div>
                                </li>
                                <li>
                                    <label for="description">Description :</label>
                                    <div class="readOnly">' . $pickPackItem[5] . '</div>
                                </li>
                                <li>
                                    <label for="batchNumber">Batch # :</label>
                                    <div class="readOnly">' . $pickPackItem[2] . '</div>
                                </li>
                            </ul>
                        </form>';
    $customMenu .= '<ul>
                        <li>
                            <label for="assignedQty">Assigned Qty :</label>
                            <input type="text" name="assignedQty" id="assignedQty" value="' . $assignedLots[4] . '">
                        </li>
                        <li>
                            <button onclick="updateAssignment(' . $assignedLots[0] . ')" id="dataEntryForm" name="action"title="New Item"><span>e</span></button>
                        </li>
                    </ul>';
        
    return $customMenu;  
    
}

function buildLineItemEdit($pickPackItem, $bal){
    $customMenu = '<div class="overlay">
                    <a href="javascript:;" onclick="closeOverlay()" id="exit"><span>x</span></a>
                    <h1>Edit Line Item</h1>
                        <form>
                            <ul>
                                <li>
                                    <h3 id="errorMessage"></h3>
                                </li>
                                <li>
                                    <label for="customerPO">Customer PO :</label>
                                    <div class="readOnly">' . $pickPackItem[0] . '</div>
                                </li>
                                <li>
                                    <label for="customerSONumber">Customer SO# :</label>
                                    <div class="readOnly" id="customerSONumber">' . $pickPackItem[2] . '</div>
                                </li>
                                <li>
                                    <label for="itemNumber">Item Number :</label>
                                    <div class="readOnly">' . $pickPackItem[4] . '</div>
                                </li>
                                <li>
                                    <label for="description">Description :</label>
                                    <div class="readOnly">' . $pickPackItem[5] . '</div>
                                </li>
                                <li>
                                    <label for="orderQtyBal">Order Qty/ Bal :</label>
                                    <div class="readOnly">' . $bal . '/' . $pickPackItem[10] . '</div>
                                </li>
                                <li>
                                    <label for="orderQty">Order Qty :</label>
                                    <input type="text" name="orderQty" id="orderQty" value="' . $pickPackItem[10] . '">
                                </li>
                                <li>
                                    <label for="backOrder">Back Order Qty :</label>
                                    <input type="text" name="backOrder" id="backOrder" value="' . $pickPackItem[13] . '">
                                </li>
                                <li>
                                    <label for="caseCnt">Case Count :</label>
                                    <div class="readOnly" id="customerSONumber">' . ($pickPackItem[12] == 0 ? 0 : $pickPackItem[10] / $pickPackItem[12]) . '</div>
                                </li>
                                <li>
                                    <button type="button" onclick="updateLineItem(' . $pickPackItem[1] . ')" id="dataEntryForm" name="action"title="New Item"><span>e</span></button>
                                </li>
                            </ul>
                        </form>';
        
    return $customMenu;  
    
}

function buildPickPackMenu($orders, $status) {
//    $customerID = getCustomerID();

            $smallWorldMenu = '
                <div id="errorMessage"></div>
                <div class="grid">
                <div class="unit one-fifth">
                <section id="customLAside">';
    
            $smallWorldMenu .= buildOrdersListBox($orders, $status);
            
            $smallWorldMenu .= '</section></div>
                            <div class="unit four-fifths">
                                    <div class="unit whole">
                                        <section id="customHeader">
                                            <h2>Order Header</h2>
                                        </section>
                                    </div>
                                    <div class="unit two-thirds">
                                        <section id="customMain">
                                            <h2>Order Items</h2>
                                        </section>
                                    </div>
                                    <div class="unit one-third">
                                        <section id="customRAside">
                                            <h2>Lots</h2>
                                        </section>
                                    </div>
                            </div>
                        </div>';
    return $smallWorldMenu;   
    
}

function buildOrderSelect($orders, $status){
    $smallWorldMenu = '
                                    <div>
                                        <a href="javascript:;" onclick="reloadOrders(1)" title="New Orders"><span> G </span></a>
                                        <a href="javascript:;" onclick="reloadOrders(2)" title="Pending Orders"><span> t </span></a>
                                        <a href="javascript:;" onclick="reloadOrders(3)" title="Pulled Orders"><span> # </span></a>
                                        <a href="javascript:;" onclick="reloadOrders(4)" title="Completed Orders"><span> g </span></a>
                                    </div>';
                if($status == 1){
                    $smallWorldMenu .= '<h2>New Orders</h2>';
                }elseif($status == 2){
                    $smallWorldMenu .= '<h2>Pending Orders</h2>';
                }elseif($status == 3){
                    $smallWorldMenu .= '<h2>Pulled Orders</h2>';
                }elseif($status == 4){
                    $smallWorldMenu .= '<h2>Completed Orders</h2>';
                }
                                    
                $smallWorldMenu .= '<hr><ul>';
                        for($i=0; $i < sizeof($orders); $i++){
                            $smallWorldMenu .= '<li>' . $orders[$i][5] . ' <a href="javascript:;" onclick="uploadHeader(\'' . $orders[$i][1] . '\')"><span> i</span></a>';
                        }
            $smallWorldMenu .= '</ul>';
            
            
    return $smallWorldMenu;   
  
}

function buildBatchList($batchList, $itemID, $orderNumber, $pickPackID, $orderQty, $bal, $customerID){
    
    $batchListView = 
        '<h2>Available Lots - ITEM/SKU:' .  $itemID . '</h2>';
                    
                    
                    if($_SESSION['currentShipmentGroup'][0][4]){
                        
    $batchListView .= '<p>| REC DATE | LOT | QTY |</p>
                        <hr>
                        <div class="grid">';
                        for($i=0; $i < sizeof($batchList); $i++){
                        $batchListView .= '<div class="unit whole">' . date("n-d-y", strtotime($batchList[$i][4])) . ', ' . 
                                    '<a href="javascript:;" onclick="viewCurrentUsage(' . $batchList[$i][0] . ')">' .
                                    $batchList[$i][0] . '</a>'
                                    . ', ' . $batchList[$i][1] . ' plts = ' . $batchList[$i][2]  . '(' . $batchList[$i][3] . ')' .
                                    '<a href="javascript:;" onclick="addBatch(\'' . $batchList[$i][0] .
                                    '\', ' . $batchList[$i][3] . ', \'' . $orderNumber . '\', ' . $pickPackID . ', ' . $orderQty . ', ' . $bal . ')"><span> +</span></a></div>';
                        }
                    }elseif(!$_SESSION['currentShipmentGroup'][0][4]){
    $batchListView .= '<p>| EXP DATE | LOT | QTY |</p>
                        <hr>
                        <div class="grid">';
                        for($i=0; $i < sizeof($batchList); $i++){
                        $batchListView .= '<div class="unit whole">' . date("n-d-y", strtotime($batchList[$i][5])) . ', ' . $batchList[$i][0] . ', ' . $batchList[$i][1] . ' plts = ' . $batchList[$i][2]  . '(' . $batchList[$i][3] . ')' .
                                    '<a href="javascript:;" onclick="addBatch(\'' . $batchList[$i][0] .
                                    '\', ' . $batchList[$i][3] . ', \'' . $orderNumber . '\', ' . $pickPackID . ', ' . $orderQty . ', ' . $bal . ')"><span> +</span></a></div>';
                        }
                    }
                            
        $batchListView .= '</div>';

    return $batchListView;
}


function buildPickPackHeader($orderData, $edit = false){
    $smallWorldMenu = null;


    // if($orderData[23] == 1){
    //     $smallWorldMenu .= '<h2>New Orders</h2>';
    //     $statusName = '<h2>New</h2>';
    // }elseif($orderData[23] == 2){
    //     $smallWorldMenu .= '<h2>Pending Orders</h2>';
    //     $statusName = '<h2>Pending</h2>';
    // }elseif($orderData[23] == 3){
    //     $smallWorldMenu .= '<h2>Pulled Orders</h2>';
    //     $statusName = '<h2>Pulled</h2>';
    // }elseif($orderData[23] == 4){
    //     $smallWorldMenu .= '<h2>Completed Orders</h2>';
    //     $statusName = '<h2>Completed</h2>';
    // }else {
    //     $statusName = null;
    // }

    if($edit != false) {
        $orderData[1] = $edit[0];
        $smallWorldMenu = '
                <h2>Order Header
                <a href="javascript:;" onclick="changeOrder(\'' . $edit[0] . '\')"><span> v</span></a>
                <a href="javascript:;" onclick="uploadHeader(\'' . $edit[0] . '\')"><span> X</span></a>
                </h2>';
    }else {    
    

        $smallWorldMenu = '<h2>';

        if($orderData[23] == 1){
            $smallWorldMenu .=      
            'Order Header
            <a href="javascript:;" onclick="setOrder(\'' . $orderData[1] . '\', 2)"><span> $</span></a>
            <span style="font: bold 1em/2em Arial, Helvetica; color: red;" id="deleteOrder">
            <a href="javascript:;" onclick="confirmDelete(\'' . $orderData[1] . '\')"><span> 8</span></a>
            <a href="javascript:;" onclick="editOrder(\'' . $orderData[1] . '\')"><span> p</span></a>';;
        }elseif ($orderData[23] == 2) {
            $smallWorldMenu .= 'Order Header
                                <a href="javascript:;" onclick="setOrder(\'' . $orderData[1] . '\', 3)"><span> $</span></a>
                                <a href="javascript:;" onclick="setOrder(\'' . $orderData[1] . '\', 1)"><span> 2</span></a>';
        }elseif ($orderData[23] == 3){
            $smallWorldMenu .= 'Order Header
                                <a href="javascript:;" onclick="setOrder(\'' . $orderData[1] . '\', 4)"><span> $</span></a>
                                <a href="javascript:;" onclick="returnToPending(\'' . $orderData[1] . '\')"><span> 2</span></a>';
                                // <a href="javascript:;" onclick="setOrder(\'' . $orderData[1] . '\', 2)"><span> 2</span></a></h2>';
        }elseif ($orderData[23] == 3){
            $smallWorldMenu .= 'Order Header<a href="javascript:;" onclick="setOrder(\'' . $orderData[1] . '\', 3)"><span> $</span></a>';
        }elseif ($orderData[23] == 4){
            $smallWorldMenu .= 'Order Completed.<a href="javascript:;" onclick="setOrder(\'' . $orderData[1] . '\', 3)"><span> 2</span></a>';
        }else{
            $smallWorldMenu .= 'Order Not Found';
        }

        $_SESSION['currentShipmentGroup'][0][0] == 12 && $smallWorldMenu .= '<a title="Re-Assign" onclick="reassignOrder(\'' . $orderData[0] . '\')"><i class="bi bi-arrow-clockwise"></i></a>';

        $smallWorldMenu .= '</h2>';
        // $smallWorldMenu .= 'Status: ' . $statusName;
    }

    

    if($edit != false) {
        $smallWorldMenu .= '
        <div class="grid orderEdit">

        <div class="unit one-quarter">
            <ul>
                <li>SO Number: ' . $edit[0] . '
                <li>Order Date: <input id="orderDate" type="date" value="' . $edit[1] . '">
                <li>Ship Date: <input id="shipDate" type="date" value="' . $edit[2] . '">
            </ul>
        </div>
        <div class="unit one-quarter">
            <ul>
                <li>PO Number: <input id="poNumber" value="' . $edit[3] . '">
                <li>Cust Num: <input id="custNumber" value="' . $edit[4] . '">
                <li>Ship Via: <input id="shipVia" value="' . $edit[5] . '">
            </ul>
        </div>
        <div class="unit half">
            <ul>
                <li>Cust Name: ' . $edit[6] . '
                <li>Address 1: ' . $edit[7] . '
                <li>Address 2: ' . $edit[8] . '
            </ul>
        </div>
        </div>

        </div>';
    }else {
        $smallWorldMenu .= '<div class="grid">

        <input class="hide" type="text" id="soNumber" value="' . $orderData[1] . '">
        <input class="hide" type="text" id="orderDate" value="' . $orderData[3] . '">
        <input class="hide" type="text" id="shipDate" value="' . $orderData[4] . '">
        <input class="hide" type="text" id="poNumber" value="' . $orderData[5] . '">
        <input class="hide" type="text" id="custNumber" value="' . ($orderData[6] == null ? "*" : $orderData[6]) . '">
        <input class="hide" type="text" id="shipVia" value="' . $orderData[7] . '">
        <input class="hide" type="text" id="custName" value="' . $orderData[16] . '">
        <input class="hide" type="text" id="addr1" value="' . $orderData[17] . '">
        <input class="hide" type="text" id="addr2" value="' . $orderData[18] . '">

        <div class="unit one-quarter">
            <ul>
                <li>SO Number: ' . $orderData[1] .
                '<li>Order Date: ' . $orderData[3] .
                '<li>Ship Date: ' . $orderData[4] .
            '</ul>
        </div>
        <div class="unit one-quarter">
            <ul>
                <li>PO Number: ' . $orderData[5] .
                '<li>Cust Num: ' . $orderData[6] .
                '<li>Ship Via: ' . $orderData[7] .
            '</ul>
        </div>
        <div class="unit half">
            <ul>
                <li>Cust Name: ' . $orderData[16] .
                '<li>Address 1: ' . $orderData[17] .
                '<li>Address 2: ' . $orderData[18] .
            '</ul>
        </div>
        </div>';
    }


    return $smallWorldMenu;   
    
}

function buildPickPackItems($items, $orderNumber, $assignedLots){
    $buildItems = '<h2>Order Items</h2>
                        <div id="orderItemsGrid" class="grid">
                        
                        <table>
                        <th>part#</th>
                        <th>sku#</th>
                        <th>Desc</th>
                        <th></th>
                        ';
                        for($i=0; $i < sizeof($items); $i++){

                            $buildItems .= 
                            '<tr id="lineItem_' . $i . '">
                            <td>' . $items[$i][19] . '</td>
                            <td>' . $items[$i][18] . '</td>
                            <td>' . $items[$i][4] . '</td>
                            <td>

                            <a href="javascript:;" onclick="editLineItem(' . $items[$i][0] . ', ' . $items[$i][9] .  ', ' . $items[$i][16] . ')"> (';

                            if($items[$i][12] > 0){
                                $buildItems .= '*';
                            }

                            $buildItems .= $items[$i][16] . ' of ' . $items[$i][9] . ')' . 
                            '<a id="deleteOrderItem" href="javascript:;" onclick="deleteOrderItemConfirm(' . $items[$i][0] . ', \'' .  $items[$i][1] . '\')">
                            <span>  8</span>
                            </a>';

                            $buildItems .= "
                            <a href='#' onclick='editOrderItem(`{$items[$i][19]}`, {$items[$i][18]}, {$items[$i][0]}, `{$items[$i][1]}`, `{$items[$i][20]}`, {$items[$i][9]}, {$i})' title='Edit Notes'><span> p </span></a>
                            ";
                            
                            $buildItems .= '
                            <a href="javascript:;" onclick="uploadBatch(' . $items[$i][0] . ', \'' . $items[$i][18] . '\', \'' . $orderNumber . '\', ' . $items[$i][9] .  ', ' . $items[$i][16] . ')">
                            <span> ></span>
                            </a>
                            <ul>';

                            $buildItems .= '</td>
                            </tr>
                            <tr id="attachLi_' . $i . '">';

                            $buildItems .= '<td colspan="1"><ul id="orderItemBatchList">';

                            for($j=0; $j <sizeof($assignedLots); $j++){
                               

                                if($assignedLots[$j][3] == $items[$i][3]){

                                    $buildItems .= '<li><a href="javascript:;" onclick="deleteBatch(' . $assignedLots[$j][0] .  ', \'' 
                                            . $orderNumber .  '\', '  . $items[$i][0] . ', \'' . $items[$i][18] . '\', ' . $items[$i][9] .  ', ' 
                                            . $items[$i][16] .  ', ' . $assignedLots[$j][4] . ')"><span>X </span></a>'
                                            . '<a href="javascript:;" onclick="editAssignment(' . $assignedLots[$j][0] .  ', ' 
                                            . $items[$i][0] .  ', ' . $items[$i][16] . ')"><span>p </span></a>'
                                            . 'Batch#: ' . $assignedLots[$j][2] . ', qty: ' . $assignedLots[$j][4] . '</li>';

                                }

                            }

                        $buildItems .= '</ul></td>';

                        $buildItems .= '
                        <td colspan="3">
                        <input id="ppiID_' . $i . '" value="' . $items[$i][0] . '" class="hide">
                        Notes: ' . ($items[$i][20] == null ? "" : $items[$i][20]) . '
                        </td></tr>';
                        }
                $buildItems .= '</table></div>';
            $buildItems .= '<div class="selectionPanel" id="newItem"><li>Add Item <a href="#" onclick="buildNewItemMenu(\'' . $orderNumber . '\')" title="newOrderItem"><span> + </span></a></li></div>';
        $buildItems .= '</div>';
    return $buildItems;   
    
}

function buildNewOrderItem(){

            $buildItems = '<div class="whole">
                                <div class="selectionPanel"><ul><li>';
                                    
            $buildItems .= '    </div>
                            </div>';

}


function buildLotCodeOverlay($lotCodeData, $lotcode){
    
    $overlay = '<div class="overlay">
                    <a href="javascript:;" onclick="closeOverlay()" id="exit"><span>x</span></a>
                    <h1>Lot Code Usage: ' . $lotcode . '</h1>
            <table>
                    <tr>
                        <th>Status</th>
                        <th>Customer SO #</th>
                        <th>Customer PO #</th>
                        <th>Customer Name</th>
                        <th>Location</th>
                        <th>Quantity</th>
                    </tr>';

                for($i = 0; $i < count($lotCodeData); $i++){
                    if($lotCodeData[$i][0] == 2 || $lotCodeData[$i][0] == 1){
                        $overlay .= '<tr>
                                <td>' . ($lotCodeData[$i][0] == 1 ? "New Order" : "Pending") . '</td>
                                <td>' . $lotCodeData[$i][1] . '</td>
                                <td>' . $lotCodeData[$i][2] . '</td>
                                <td>' . $lotCodeData[$i][4] . '</td>
                                <td>' . $lotCodeData[$i][5] . '</td>
                                <td>' . $lotCodeData[$i][6] . '</td>';

                        $overlay .= '</tr>';
                    }

                }

         $overlay .= '</tbody>
    </table>';
         
    if ($lotCodeData[0][0] != 1 && $lotCodeData[0][0] != 2){
        $overlay .= "<h2>No Active Assignments</h2>";
    }     
    
    return $overlay;  
    
    
}


function buildnewOrderMenu($message){
    
    $html;
    $customerList = getCustomers();
    $shipmentGroupOptions = getShipmentGroup();
    $currentDateTime = date('Y-m-d');
     
    $html = '
    <h1>New Order</h1>';
    if($message){
        $newOrderHTML .= '<h2>' . $message . '</h2>';
    }
    $html .= '<div class="newOrderModal">
        <div class="half">

            <div class="orderInput">
                <label for="">Customer:</label>
                <select name="" id="orderCustomerID">
                    <option value="" selected disabled>Select Customer...</option>';
                    for($i = 0; $i < sizeof($shipmentGroupOptions); $i++){
                        if($_SESSION['currentShipmentGroup'][0][0] == $shipmentGroupOptions[$i][0]){
                            $html .= '<option value=' . $shipmentGroupOptions[$i][0] . ' selected="selected">' . $shipmentGroupOptions[$i][2] . ' - ' . $shipmentGroupOptions[$i][3] . '</option>';
                        }else{
                            $html .= '<option value=' . $shipmentGroupOptions[$i][0] . '>' . $shipmentGroupOptions[$i][2] . ' - ' . $shipmentGroupOptions[$i][3] . '</option>';
                        }
                    }
                $html .= '</select>
            </div>

            <div class="orderInput">
                <label for="">Order Date:</label>
                <input id="orderDate" type="date" value= ' . $currentDateTime . '>
            </div>
            
            <div class="orderInput">
                <label for="">Ship Date:</label>
                <input id="shipDate" type="date" value= ' . $currentDateTime . '>
            </div>

            <div class="orderInput">
                <label for="">Customer PO:</label>
                <input id="customerPO" type="text">
            </div>

            <div class="orderInput">
                <label for="">Ship Via:</label>
                <input id="shipVia" type="text">
            </div>

            <div class="orderInput">
                <label for="">Customer Comment:</label>
                <input id="customerComment" type="text" class="custComment">
            </div>
            
        </div>
        <div class="half">

            <div class="orderInput">
                <label for="">Customer Name: </label>
                <select name="" id="newOrderCustomerIDBox" onchange="customerSelectChange(value);" required>
                    <option value="" selected disabled>Select Customer...</option>';
                    for($i = 0; $i < sizeof($customerList); $i++){
                        $html .= '<option value=' . $customerList[$i][0] . ' onchange="customerSelectChange()">' . $customerList[$i][1] . '</option>';               
                    }
                $html .= '</select>
            </div>

            <div class="orderInput">
                <label for="">Ship To:</label>
                <select name="" id="shipTo" disabled>
                    <option value="" selected disabled>Select Ship To...</option>
                </select>
            </div>
            
            <div id="shipToInfo">
            
                <div class="orderInput">
                    <label for="">Phone:</label>
                    <input type="text" id="shipToPhone" disabled>
                </div>

                <div class="orderInput">
                    <label for="">Address 1:</label>
                    <input type="text" id="shipToAddressLine1" disabled>
                </div>

                <div class="orderInput">
                    <label for="">Address 2:</label>
                    <input type="text" id="shipToAddressLine2" disabled>
                </div>

                <div class="orderInput">
                    <label for="">Address 3:</label>
                    <input type="text" id="shipToAddressLine3" disabled>
                </div>

                <div class="orderInput">
                    <label for="">City:</label>
                    <input type="text" id="shipToCity" disabled>
                </div>

                <div class="orderInput">
                    <label for="">State:</label>
                    <input type="text" id="shipToState" disabled>
                </div>

                <div class="orderInput">
                    <label for="">Country:</label>
                    <input type="text" id="country" disabled>
                </div>

            </div>
        </div>
    </div>
    <button class="bs-btn btn-blue" onclick="addOrder()">Add Order</button>';

    return $html;

}





function buildnewOrderItemMenu($message){
    $newOrderItemHTML;

     
     
    $newOrderItemHTML = '<div class="overlay">
                    <a href="#" onclick="closeOverlay()" id="exit"><span>x</span></a>        
                    <form method="post" action="." id="dataEntryForm">
    <h1>New Order Item</h1>';
        if($message){
            $newOrderItemHTML .= '<h2>' . $message . '</h2>';
        }
$currentDateTime = date('Y-m-d');
    $newOrderItemHTML .= '<ul>
          
        <div class="grid">
            <div class="unit half">
                <li>
                    <label for="orderDate">Order Date</label>
                    <input type="date" name="orderDate" placeholder="Enter Order Date" 
                        id="orderDate" value= ' . $currentDateTime . '>
                </li>
                <li>
                    <label for="orderDate">Order Date</label>
                    <input type="date" name="orderDate" placeholder="Enter Order Date" 
                        id="orderDate" value= ' . $currentDateTime . '>
                </li>
                <li>
                    <label for="orderDate">Order Date</label>
                    <input type="date" name="orderDate" placeholder="Enter Order Date" 
                        id="orderDate" value= ' . $currentDateTime . '>
                </li>
            </div>
            

            <div class="unit half">
                <li>
                    <label for="customerName">Customer Name</label>
                    <input type="text" name="customerName" placeholder="Enter Customer Name"
                        id="customerName" value="">
                </li>
                <li>
                    <label for="orderDate">Order Date</label>
                    <input type="date" name="orderDate" placeholder="Enter Order Date" 
                        id="orderDate" value= ' . $currentDateTime . '>
                </li>
                <li>
                    <label for="orderDate">Order Date</label>
                    <input type="date" name="orderDate" placeholder="Enter Order Date" 
                        id="orderDate" value= ' . $currentDateTime . '>
                </li>
            </div>
        </div>
    </ul>
    <div>
    <a href="#" onclick="addOrderItem()" title="Add Order Item"><span>i</span></a>
    </div>
</form></div>';

    return $newOrderItemHTML;


}


Function buildMenus($orderNumber, $customerID){
    //Setup & build pick pack header
    $headerOrder = getPickPackHeader($orderNumber);
    $pickPackHeaderView = buildPickPackHeader($headerOrder);
    
    //Setup & build pick pack items
    $pickPackItem = getPickPackItems($orderNumber, $customerID);
    $assignedLots = getAssignedLots($orderNumber);
    $pickPackItemsView = buildPickPackItems($pickPackItem, $orderNumber, $assignedLots);
    
    $_SESSION['orderNumber'] = $orderNumber;
    
    $ajax = true;
    $tranArr = null;
    $tranArr[0] = $pickPackHeaderView;
    $tranArr[1] = $pickPackItemsView;
    
    return $tranArr;
}

Function buildShipToOptionBox($customerID){
    
    $shipOptionBoxHTML = null;
    
    
    $customerShipTo = getShipTo($customerID);
    
    
    $shipOptionBoxHTML = '
                <select name="shipToID" id="shipTo" onchange="shipToChange(value, ' . $customerID . ');" required>
                    <option value="null" selected>Select Ship To</option>';
        
    
            for($i = 0; $i < sizeof($customerShipTo); $i++){
                $shipOptionBoxHTML .= '<option value=' . $customerShipTo[$i][1] . ' onchange="customerSelectChange()">' . $customerShipTo[$i][2] . '</option>';               
            }

    
            $shipOptionBoxHTML .= '</select>';
    
            
            return $shipOptionBoxHTML;
}

Function buildShipToDetails($customerID, $shipToNo) {
    $buildShipToDetailsHTML;
    
    $getShipDetails = getShipTo($customerID, $shipToNo);
    
    
    $buildShipToDetailsHTML = '
                    <div class="orderInput">
                        <label for="">Phone:</label>
                        <input type="text" id="shipToPhone" value="' . ($getShipDetails[0][11] == "NULL" ? "" : $getShipDetails[0][11]) . '" disabled disabled>
                    </div>

                    <div class="orderInput">
                        <label for="">Address 1:</label>
                        <input type="text" id="shipToAddressLine1" value="' . ($getShipDetails[0][3] == "NULL" ? "" : $getShipDetails[0][3]) . '" disabled disabled>
                    </div>

                    <div class="orderInput">
                        <label for="">Address 2:</label>
                        <input type="text" id="shipToAddressLine2" value="' . ($getShipDetails[0][4] == "NULL" ? "" : $getShipDetails[0][4]) . '" disabled disabled>
                    </div>

                    <div class="orderInput">
                        <label for="">Address 3:</label>
                        <input type="text" id="shipToAddressLine3" value="' . ($getShipDetails[0][5] == "NULL" ? "" : $getShipDetails[0][5]) . '" disabled disabled>
                    </div>

                    <div class="orderInput">
                        <label for="">City:</label>
                        <input type="text" id="shipToCity" value="' . ($getShipDetails[0][6] == "NULL" ? "" : $getShipDetails[0][6]) . '" disabled disabled>
                    </div>

                    <div class="orderInput">
                        <label for="">State:</label>
                        <input type="text" id="shipToState" value="' . ($getShipDetails[0][7] == "NULL" ? "" : $getShipDetails[0][7]) . '" disabled disabled>
                    </div>

                    <div class="orderInput">
                        <label for="">Country:</label>
                        <input type="text" id="country" value="' . ($getShipDetails[0][9] == "NULL" ? "" : $getShipDetails[0][9]) . '" disabled disabled>
                    </div>';
    
    return $buildShipToDetailsHTML;
    
}

function buildOrdersListBox($orders, $status, $currentShipmentGroup = null) {

    if($currentShipmentGroup != null) {
        $currentShipmentGroup = $currentShipmentGroup;
    }else {
        $currentShipmentGroup = $_SESSION['currentShipmentGroup'];
    }

    $shipmentGroupOptions = getShipmentGroup();

    $smallWorldMenu = '
    <div class="orderList">
        <div class="customer">
            <select onchange="customerChange(value);" name="" id="customerID">
                <option value="" disabled>Select customer...</option>';

                for($i = 0; $i < sizeof($shipmentGroupOptions); $i++){
                    if($currentShipmentGroup[0][0] == $shipmentGroupOptions[$i][0]){
                        $smallWorldMenu .= '<option value=' . $shipmentGroupOptions[$i][0] . ' selected="selected">' . $shipmentGroupOptions[$i][2] . ' - ' . $shipmentGroupOptions[$i][3] . '</option>';
                    }else{
                        $smallWorldMenu .= '<option value=' . $shipmentGroupOptions[$i][0] . '>' . $shipmentGroupOptions[$i][2] . ' - ' . $shipmentGroupOptions[$i][3] . '</option>';
                    }
                    
                }

            $smallWorldMenu .= '</select>
        </div>
        <div class="status">
            <a href="#" onclick="reloadOrders(1)" title="New Orders" class="' . (($status == 1) ? 'optionCurrent' : '') . '"><i class="bi bi-plus-circle-dotted"></i></a>
            <a href="#" onclick="reloadOrders(2)" title="Pending Orders" class="' . (($status == 2) ? 'optionCurrent' : '') . '"><i class="bi bi-tag"></i></a>
            <a href="#" onclick="reloadOrders(3)" title="Pulled Orders" class="' . (($status == 3) ? 'optionCurrent' : '') . '"><i class="bi bi-building"></i></a>
            <a href="#" onclick="reloadOrders(4)" title="Completed Orders" class="' . (($status == 4) ? 'optionCurrent' : '') . '"><i class="bi bi-truck"></i></a>
        </div>
        <div class="ordersHeader">';

        if($status == 1){
            $smallWorldMenu .= '<h5>New Orders<a href="#" onclick="newOrder()" class="' . (($status == 4) ? 'optionCurrent' : '') . '" title="Add Order"><i class="bi bi-plus-lg"></i></a></h5>';
        }elseif($status == 2){
            $smallWorldMenu .= '<h5>Pending Orders</h5>';
        }elseif($status == 3){
            $smallWorldMenu .= '<h5>Pulled Orders</h5>';
            $smallWorldMenu .= '<input type="text" style="margin-bottom: 0.5em; text-align: center;" placeholder="Select Order" name="selectOrder" id="selectOrder" autocomplete="off" autofocus onkeydown="Javascript: if (event.keyCode==13) selectOrder();">';
        }elseif($status == 4){
            $smallWorldMenu .= '<h5>Completed Orders</h5>';
        }

            $smallWorldMenu .= '
            <input id="searchbox" onkeydown="Javascript: if (event.keyCode==13) searchOrders(' . $status . ');" placeholder="Search..."></input>
            <div class="dateFilter">
                <p>Order Date Range</p>
                <input type="date" id="startdate">
                <input type="date" id="enddate">
            </div>

            <a title="Search" onclick="clearOrderSearch(' . $status . ')" href="#"><i class="bi bi-arrow-counterclockwise"></i></a>
            <a title="Search" onclick="searchOrders(' . $status . ')" href="#"><i class="bi bi-search"></i></a>
        </div>
        <div class="orders" id="orderListBox">';

        for($i=0; $i < sizeof($orders); $i++){

            $smallWorldMenu .= '
            <hr>
            <p href="#" onclick="uploadHeader(\'' . $orders[$i][1] . '\')" class="orderItem">
                ' . ( $currentShipmentGroup[0][4] ? $orders[$i][5] : $orders[$i][1] ) . '
            </p>
            ';
            
        }
        
        $smallWorldMenu .= '</div>
        <hr>
    </div>';

    return $smallWorldMenu;

}

function buildItemLookup() {

    $html = '
    <div class="grid overlay">
        <div class="unit whole align-center">
            <a href="javascript:;" onclick="closeOverlay()" id="exit"><span>x</span></a>
            <h1 style="margin-top: 2vh; margin-bottom: 1vh">Item Lookup</h1>
        </div>
        <div class="unit whole align-center">
            Search<br>
            <input id="searchOIInput" placeholder="Enter sku# or part# or desc..." onchange="searchOI()" value="">
        </div>
        <div id="orderLookupList" class="unit whole">

        </div>
    </div>
    ';

    return $html;
}

function rebuildOrderList($status, $search, $start, $end) {

    $orders = getPickPackOrders($_SESSION['currentShipmentGroup'][0][1], $status, $_SESSION['currentShipmentGroup'][0][0], $search, $start, $end);
    $currentShipmentGroup = $_SESSION['currentShipmentGroup'];
    $shipmentGroupOptions = getShipmentGroup();

    $html = '';

    for($i=0; $i < sizeof($orders); $i++){
        if($currentShipmentGroup[0][4]){
            $html .= '<hr><p href="#" onclick="uploadHeader(\'' . $orders[$i][1] . '\')" class="orderItem">' . $orders[$i][5] . '</p>';

        }else{
            $html .= '<hr><p href="#" onclick="uploadHeader(\'' . $orders[$i][1] . '\')" class="orderItem">' . $orders[$i][1] . '</p>';
        }
    }

    return $html;
}

function buildAssignShipmentGroup($pickPackHeaderID) {

    $shipmentGroups = getShipmentGroup();

    $html = ' 
    <div class="orderReassign">
    <h1>Re-Assign Order</h1>

    <select name="customer" id="reassignCustomerID">
        <option value="null">Select Customer</option>';

        for ($i=0; $i < sizeof($shipmentGroups); $i++) { 
            $html .= '<option value="' . $shipmentGroups[$i][0] . '">' . $shipmentGroups[$i][2] . ' - ' . $shipmentGroups[$i][3] . '</option>';
        }

    $html .= '
    </select>

    <a onclick="assignCustomer(\'' . $pickPackHeaderID . '\')" class="bs-btn btn-blue">Submit</a>
    </div>
    ';

    return $html;

}