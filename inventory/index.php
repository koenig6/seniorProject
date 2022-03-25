<?php
session_start();

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$email = null;
$message = null;
$users = null;
$action = null;
$employees = null;
$subAction = null;
$associatedCustomer = null;
$binData = null;
$binTotals = null;
$missingItems = null;
$refreshview = false;
$ajax = false;
$componentTagType = null;

if (isset($_POST['action'])) {
 $action = $_POST['action'];
} elseif (isset($_GET['action'])) {
 $action = $_GET['action'];
}
require 'model.php';

$missingItems = getMissingItemDetails();
$userID = $_SESSION['userID'];
if(isset($missingItems)){
    for($i = 0; $i < sizeof($missingItems); $i++){
        addNewItem($missingItems[$i][0], $userID);
    }
}


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
}elseif($action == 'billablePallet') {
    $skuNumber = filterNumber($_GET['skuNumber']);
    echo buildBillablePallet($skuNumber);;
    $ajax = true;
}elseif($action == 'updatePriority') {
    $pricingListID = filterNumber($_GET['pricingListID']);
    $pricingTypeID = filterNumber($_GET['pricingTypeID']);
    if($pricingListID != 1) {
        $price = getPriceData($pricingListID, $pricingTypeID);
        echo ($price != false) ? number_format((float)$price[0], 2, '.', '') : -1;
    }else {
        echo '-2';
    }
    $ajax = true;
}elseif($action == 'getPricing') {
    $pricingListID = filterNumber($_GET['pricingListID']);
    $skuNumber = filterNumber($_GET['skuNumber']);
    echo buildPricingData($skuNumber, $pricingListID, true);
    $ajax = true;
}elseif($action == 'updatePrice') {
    $pricingTypeID = filterNumber($_GET['pricingTypeID']);
    $pricingListID = filterNumber($_GET['pricingListID']);
    $skuNumber = filterNumber($_GET['skuNumber']);
    $price = filterString($_GET['price']);
    $priority = filterString($_GET['priority']);
    $userID = $_SESSION['userID'];

    $success = updatePriorities($skuNumber, $priority, $pricingTypeID);
    
    if($pricingListID == 1) {
        $success = uploadPrice($pricingTypeID, $skuNumber, $price, $userID);
    }

    // Return Outcome
    echo $success != null ? 1 : 0;
    $ajax = true;
}elseif($action == 'updateItemPricing') {
    // Get Vars
    $skuNumber = filterNumber($_GET['skuNumber']);
    $pricingListID = filterNumber($_GET['pricingListID']);
    $pricingDesc = filterString($_GET['pricingDesc']);

    // Update Item Pricing Type
    $success = updateItemPricing($skuNumber, $pricingListID, $pricingDesc);

    // Update Billable Pallet
    updateBillable(1);

    // Return Outcome
    // echo $success != null ? '<i style="color:white;" class="bi bi-check-circle-fill"></i>' : '<i style="color:#c70300;" class="bi bi-x-circle-fill"></i>';
    echo $pricingDesc;
    $ajax = true;
}elseif($action == 'resetPricing') {
    $skuNumber = filterNumber($_GET['skuNumber']);
    $success = resetPricing($skuNumber);
    echo $success != null ? 1 : 0;
    $ajax = true;
}elseif($action == 'searchItemsx') {

    $inventorySearchHTML = buildInventorySearch();
    
}elseif ($action == 'searchItems'){
    
    if(isset($_GET['itemSearch'])){
        $searchTerm = filterString($_GET['itemSearch']);
    }
    if(isset($_GET['foresite'])){
        $foresite = filterNumber($_GET['foresite']);
    }
    
    $searchMenuHTML = buildSearchMenu($searchTerm, $foresite);
    
   $ajax = true;
   echo $searchMenuHTML;
    
}elseif($action == 'testPrint') {

    $ajax = true;

}elseif ($action == 'editIteS'){

    $itemDetails = null;
        $paperColor = getPaperColor();
        
        $userID = $_SESSION['userID'];
        $skuNumber = $_GET['skuNumber'];
        $itemDetails = getItemData($skuNumber);
        
        if($itemDetails == null){
            $newItem = addNewItem($skuNumber, $userID);
            
            if(!$newItem){
            $message = 'Error in collecting data2.';
            }
        }
        
        $action = 'searchItems';
        $subAction = 'editIteS';

        $editHTML = buildEditMenu($itemDetails, $message, $paperColor);
        $ajax = true;
        echo $editHTML;

}elseif($action == 'openNotesMenu') {

    $skuNumber = filterNumber($_GET["skuNumber"]);

    $html = buildNotesMenu($skuNumber);

    echo $html;

    $ajax = true;

}elseif($action == 'saveItemNotes') {
    
    $notes = filterString($_GET["notes"]);
    $skuNumber = filterNumber($_GET["skuNumber"]);

    $update = updateItemNotes($skuNumber, $notes);

    if($update != false) {
        echo 1;
    }

    $ajax = true;

}elseif($action == 'skuHistory') {

    $skuNumber = filterNumber($_GET["skuNumber"]);

    // $html = buildSkuHistory();

    $html = 'This is the Sku History!' . $skuNumber;

    echo $html;

    $ajax = true;

}elseif($action == 'updateApproval') {

    $workOrderNumber = filterString($_GET["woNo"]);
    $approval = filterNumber($_GET["approval"]);

    // $success = updateApprovalStatus($workOrderNumber, $approval) != false ? 1 : 0;
    $success = updateApprovalStatus($workOrderNumber, $approval);

    echo $success;

    $ajax = true;

}elseif($action == 'searchResultSkus') {

    $itemSearch = filterString($_GET["itemSearch"]);

    $html = buildSkuSearchResultsList($itemSearch);

    echo $html;

    $ajax = true;

}elseif($action == 'flagItem') {

    $hold = filterString($_GET["checked"]);
    $poiId = filterString($_GET["poiId"]);
    $poNum = filterString($_GET["poNum"]);

    $success = flagPoiItem($hold, $poiId);

    $orderData = getPOData($poNum);

    $tranArr[0] = $success;

    $tranArr[1] = buildPOItems($orderData);

    $data = json_encode($tranArr);
    echo $data;
    $ajax = true;

}elseif($action == 'noHolds') {

    $lotCode = filterString($_GET["lotCode"]);

    $html = buildNoHolds($lotCode);

    echo $html;

    $ajax = true;

}elseif($action == 'holdAllItems') {
    $lotCode = filterString($_GET["lotCode"]);
    
    $holdCnt = holdAllItems($lotCode);

    $html = buildHoldResultes($lotCode, $holdCnt);

    echo $html;

    $ajax = true;
}elseif($action == 'releaseHold'){

    $lotCode = filterString($_GET["lotCode"]);
    $results = filterNumber($_GET["results"]);
    $release = filterNumber($_GET["release"]);

    if($release) {

        $releaseCnt = releaseAllItems($lotCode);

        $html = buildReleaseResults($lotCode, $releaseCnt);

        echo $html;

    }else {
        if($results) {
            $html = 'results';
        }else {
            if($lotCode != 'false') {
                $held = getHeldItemCnt($lotCode)[0];
                $free = getFreeItemCnt($lotCode)[0];
                if($held == 0) {
                    $html = 'noholds';
                }else {
                    $html = buildHoldList($lotCode, $held, $free);
                }
            }else {
                $html = buildReleaseHoldInput();
            }    
        }
    
        echo $html;
    }

    $ajax = true;

}elseif($action == 'viewItem'){
    
    $skuNumber = $_GET["skuNumber"];
    if(isset($_GET["itemSearch"])) {
        $itemSearch = filterString($_GET["itemSearch"]);
    }else {
        $itemSearch = false;
    }
    $viewItemHTML = buildItemView($skuNumber, $itemSearch);

    //  echo $itemSearch;
    
}elseif($action == 'addNewItem'){

    $customerID = filterNumber($_GET["customerID"]);
    $partNumber = filterString($_GET["partNumber"]);
    $description1 = filterString($_GET["description1"]);
    $description2 = filterString($_GET["description2"]);
    $description3 = filterString($_GET["description3"]);
    $description4 = filterString($_GET["description4"]);
    $description5 = filterString($_GET["description5"]);
    $PUT = filterString($_GET["PUT"]);
    $PFCTR = filterString($_GET["PFCTR"]);
    $timeStudy = filterNumber($_GET["timeStudy"]);
    $paperColor = filterNumber($_GET["paperColor"]);
    $palletQty = filterNumber($_GET["palletQty"]);
    $customerRefNumber = filterNumber($_GET["customerRefNumber"]);
    $stockingUnit = filterString($_GET["stockingUnit"]);
    $stockingRatio = filterNumber($_GET["stockingRatio"]);
    $organic = filterNumber($_GET["organic"]);
    $kosher = filterNumber($_GET["kosher"]);
    $alergen = filterNumber($_GET["alergen"]);
    // $billablePallet = filterNumber($_GET["billablePallet"]);
    $rackCharge = filterNumber($_GET["rackCharge"]);
    $kit = filterNumber($_GET["kit"]);
    $component = filterNumber($_GET["component"]);
    $poRequired = filterNumber($_GET["poRequired"]);
    $batchRequired = filterNumber($_GET["batchRequired"]);
    $palletNumberRequired = filterNumber($_GET["palletNumberRequired"]);
    $cats = filterString($_GET["cats"]);
    $notStackable = filterNumber($_GET["notStackable"]);
    $consumable = filterNumber($_GET["consumable"]);
    $caseQty = filterNumber($_GET["caseQty"]);
    $palletPos = filterNumber($_GET["palletPos"]);

    $userID = $_SESSION['userID'];
    
    $newSkuNumber = advanceSku()[0];
    
    // $newItem = insertItem($newSkuNumber, $partNumber, $desc1, $desc2, $desc3, $desc4, $desc5, $pricingUnit, $inventoryUnit);

    $newItem = insertItemDetailed($newSkuNumber
                                , $timeStudy
                                , $paperColor
                                , $palletQty
                                , $customerRefNumber
                                , $stockingUnit
                                , $stockingRatio
                                , $organic
                                , $kosher
                                , $alergen
                                , $rackCharge
                                , $kit
                                , $component
                                , $poRequired
                                , $batchRequired
                                , $palletNumberRequired
                                , $cats
                                , $notStackable
                                , $consumable
                                , $caseQty
                                , $palletPos);

    $newItemf = insertItem($newSkuNumber
                         , $partNumber
                         , $description1
                         , $description2
                         , $description3
                         , $description4
                         , $description5
                         , $PUT
                         , $cats
                         , $PFCTR
                         , $stockingUnit);
    $addCust = addAssociatedCustomer($customerID, $newSkuNumber, $userID);
    
    $tranArr = null;
    $tranArr[0] = $newSkuNumber;
    
    if($newItem != false && $addCust != false && $newItemf != false){
        updateSku($newSkuNumber);
        $tranArr[1] = buildItemView($newSkuNumber);
    }else{
        $tranArr[1] = 0;
    }
    
    // echo $customerID . ', ' . $partNumber . ', ' . $description1 . ', ' . $description2 . ', ' . $description3 . ', ' . $description4 . ', ' . $description5 . ', ' . $PUT . ', ' . $PFCTR . ', ' . $timeStudy . ', ' . $paperColor . ', ' . $palletQty . ', ' . $customerRefNumber . ', ' . $stockingUnit . ', ' . $stockingRatio . ', ' . $organic . ', ' . $kosher . ', ' . $alergen . ', ' . $billablePallet . ', ' . $rackCharge . ', ' . $kit . ', ' . $component . ', ' . $poRequired . ', ' . $batchRequired . ', ' . $palletNumberRequired . ', ' . $cats . ', ' . $notStackable . ', ' . $consumable . ', ' . $caseQty . ', ' . $palletPos;
    
    $data = json_encode($tranArr);
    echo $data;

    $ajax = true;
    
}elseif($action == 'newCustomer'){
    
    $skuNumber = $_GET["skuNumber"];
    
    $newCustomerHTML = buildNewCustomer($skuNumber);
    
    echo $newCustomerHTML;
    $ajax = true;
    
}elseif($action == 'changeSelection'){
    
    $menu = filterNumber($_GET["menu"]);

    $skuNumber = filterNumber($_GET["skuNumber"]);

    $type = filterNumber($_GET["type"]);

    $start = filterString($_GET["start"]);
    $end = filterString($_GET["end"]);

    // $start = '2002-11-27';
    // $end = '2002-11-30';

    if ($start == "") {
        $start = date('m-d-Y', strtotime('-30 days'));
    }

    if($end == "") {
        $end = date('m-d-Y');
    }

    if($menu == "1" ) {
        $html = buildOpenWO($type, $skuNumber, $start, $end, $menu);
    }elseif($menu == "2") {
        $html = buildOpenPO($type, $skuNumber, $start, $end, $menu);
    }elseif($menu == "3") {
        $html = buildOpenShipments($type, $start, $end, $menu, $skuNumber);
    }elseif($menu == "4"){
        $html = buildOpenBinLocations($type, $skuNumber, $start, $end, $menu);
    }else {
        $html = "No Menu Found";
    }

    // $html = $menu . ' | ' . $skuNumber . ' | ' . $type . ' | ' . $start . ' | ' . $end;
    
    echo $html;
    $ajax = true;
    
}elseif($action == 'openHistorical') {
    $serial  = filterNumber($_GET['serial']);

    $html = buildHistoricalChart($serial);

    echo $html;
    $ajax = true;
}elseif($action == 'getShipTos'){
    // $message;
    
    
    $customerID = filterString($_GET['customerID']);
    
//    echo $customerID;
    
    $shipOptionBoxHTML = buildShipToOptionBox($customerID);
    echo $shipOptionBoxHTML;
    $ajax = true;
}elseif($action == 'newVendor'){
    
    $skuNumber = $_GET["skuNumber"];
    $associatedVendor = getAssociatedVendor($skuNumber);
    $vendors = getVendors();
    
}elseif($action == 'viewItem' || $action == 'editIteV' || $action == 'newCustomer' || $action =='newVendor'){
    $itemDetails = null;
    
    $userID = $_SESSION['userID'];
    
    if($action == 'newCustomer' || $action == 'newVendor'){
        $skuNumber = filterNumber($_GET['skuNumber']);
    }else{
        $skuNumber = $_GET['skuNumber'];
    }
    
    $itemDetails = getItemDetails($skuNumber);
    if($itemDetails == null){
        $newItem = addNewItem($skuNumber, $userID);
        
        if(!$newItem){
            $message = 'Error in collecting data3.';
        }
    }
    
    $itemData = getItemData($skuNumber);
    
    //Get component Tag Type
    $componentTagType = getConstant('compTagType')['constantValue'];
    
    if($componentTagType == 2 && $itemData[15] == 0){
        $associatedVendor = getAssociatedVendor($skuNumber);
    }else{
        $associatedCustomer = getAssociatedCustomer($skuNumber);
    }
    
    $quantityAvailable = ($itemData[10] - $itemData[11]);
    $binTotals = currentInventoryTotals($skuNumber);
    $binData = getInventoryData($skuNumber);
    if(!isset($itemData)){
        $message = 'Error: Unable to access selected item.';
        $action = 'searchItems';
    }
    
    if($action == 'editIteV'){
        $paperColor = getPaperColor();
        
        $itemDetails = getItemDetails($skuNumber);
        
        if(!isset($itemDetails)){
            $message = 'Error in collecting data4.';
        }
        
        $action = 'viewItem';
        $subAction = 'editIteV';
    }elseif($action == 'newCustomer'){
        $customers = getCustomers();
        
        $action = 'viewItem';
        $subAction = 'newCustomer';
    }elseif($action == 'newVendor'){
        $vendors = getVendors();
        
        $action = 'viewItem';
        $subAction = 'newVendor';
    }else{
        $action = 'viewItem';
        $subAction = null;
    }
    $action = 'viewItem';
}elseif($action == 'viewImage'){
    $skuNumber = filterNumber($_GET['skuNumber']);
    
    //Get component Tag Type
    $componentTagType = getConstant('componentTagType')['constantValue'];
    
    if($componentTagType == 0){
        $associatedCustomer = getAssociatedCustomer($skuNumber);
    }else{
        $associatedVendor = getAssociatedVendor($skuNumber);
    }
    
    $refreshview = true;
    
    $subAction = 'viewImage';
}elseif($_SESSION['securityLevel'] < 2){
    header('location: /logIn/index.php');
    $_SESSION['message'] = 'Access Denied';
}elseif($action == 'updateItem'){

    $skuNumber = filterString($_GET["skuNumber"]);
    $partNumber = filterString($_GET["partNumber"]);
    $desc1 = filterString($_GET["desc1"]);
    $desc2 = filterString($_GET["desc2"]);
    $desc3 = filterString($_GET["desc3"]);
    $desc4 = filterString($_GET["desc4"]);
    $desc5 = filterString($_GET["desc5"]);
    $pricingUnit = filterString($_GET["pricingUnit"]);
    $pricingFactor = filterString($_GET["pricingFactor"]);
    $timeStudy = filterNumber($_GET["timeStudy"]);
    $paperColorID = filterString($_GET["labelColor"]);
    $palletQty = filterString($_GET["qtyPerPallet"]);
    $customerRefNumber = filterString($_GET["customerRefNumber"]);
    $stockingUnit = filterString($_GET["stockingUnit"]);
    $stockingRatio = filterString($_GET["stockingRatio"]);
    $organic = filterString($_GET["organic"]);
    $kosher = filterString($_GET["kosher"]);
    $alergen = filterString($_GET["alergen"]);
    // $billablePallet = filterString($_GET["billable"]);
    $rackCharge = filterString($_GET["charge"]);
    $kit = filterString($_GET["kit"]);
    $component = filterString($_GET["comp"]);
    $poRequired = filterString($_GET["poReq"]);
    $batchRequired = filterString($_GET["batchReq"]);
    $palletNumberRequired = filterString($_GET["pltNoReq"]);
    $cats = filterString($_GET["cats"]);
    $notStackable = filterString($_GET["notStackable"]);
    $consumable = filterString($_GET["consumable"]);
    $palletPosition = filterString($_GET["palletPosition"]);
    $caseQty = filterString($_GET["caseQty"]);

    $inDetails = filterNumber($_GET["inDetails"]);
    
    $bulkCharge = null;
    $caseCharge = null;
    $modifiedBy = $_SESSION['userID'];

    $updateResult = updateItem($skuNumber
                            , $paperColorID
                            , $palletQty
                            , $kit
                            , $component
                            , $rackCharge
                            , $bulkCharge
                            , $caseCharge
                            , $poRequired
                            , $batchRequired
                            , $modifiedBy
                            , $timeStudy
                            , $palletNumberRequired
                            , $customerRefNumber
                            , $stockingUnit
                            , $stockingRatio
                            , $organic
                            , $kosher
                            , $alergen
                            , $notStackable
                            , $consumable
                            , $caseQty
                            , $palletPosition);
    
    $updateResultF = updateItemf($skuNumber
                                , $partNumber
                                , $desc1
                                , $desc2
                                , $desc3
                                , $desc4
                                , $desc5
                                , $pricingUnit
                                , $pricingFactor
                                , $cats);
    
    // Find out the result, notify client
    if ($updateResult || $updateResultF) {
    //  $message = 'The update was successful.';
     $tranArr[0] = 1;
    } else {
    //  $message = 'Sorry, the update failed.';
     $tranArr[0] = 0;
    }

    // //Setup Data for view Item
    // $itemData = getItemData($skuNumber);
    // $quantityAvailable = ($itemData[10] - $itemData[11]);
    // //Set action to view items
    // $action = 'viewItem';
    // $subAction = null;
   
    // $viewItemHTML = buildItemView($skuNumber);

    // echo $skuNumber . ', ' . $partNumber . ', ' . $desc1 . ', ' . $desc2 . ', ' . $desc3 . ', ' . $desc4 . ', ' . $pricingUnit . ', ' . $pricingFactor . ', ' . $timeStudy . 
    // ', ' . $labelColor . ', ' . $qtyPerPallet . ', ' . $customerRefNumber . ', ' . $stockingUnit . ', ' . $stockingRatio . ', ' . $organic . ', ' . $kosher . ', ' .
    //  $alergen . ', ' . $billable . ', ' . $charge . ', ' . $kit . ', ' . $comp . ', ' . $poReq . ', ' . $batchReq . ', ' . $pltNoReq . ', ' . $cats;
    // echo $skuNumber . ', ' . $partNumber . ', ' . $desc1 . ', ' . $desc2 . ', ' . $desc3 . ', ' . $desc4 . ', ' . $pricingUnit . ', ' . $pricingFactor . ', ' . $timeStudy . ', ' . $labelColor . ', ' . $qtyPerPallet . ', ' . $customerRefNumber . ', ' . $stockingUnit . ', ' . $stockingRatio . ', ' . $organic . ', ' . $kosher . ', ' . $alergen . ', ' . $billable . ', ' . $charge . ', ' . $kit . ', ' . $comp . ', ' . $poReq . ', ' . $batchReq . ', ' . $pltNoReq . ', ' . $cats . ', ' . $notStackable . ', ' . $consumable . ', ' . $palletPosition . ', ' . $caseQty;

    $tranArr[1] = $inDetails ? buildItemView($skuNumber) : null;

    $data = json_encode($tranArr);

    // $data = $updateResult . ', ' . $updateResultF;

    echo $data;

    $ajax = true;
    
}elseif($action == 'exit'){
    header('Location: /logIn/index.php?action=mainMenu');
}elseif($action == 'returnToProduction'){
    header('Location: /production/index.php?action=production');
}elseif($action == 'submitNewCustomer'){
    $message = null;
    $customerID = null;
    $customerID = filterNumber($_GET['customerID']);
    $skuNumber = filterNumber($_GET['skuNumber']);
    $userID = $_SESSION['userID'];
    
    $validate = validateAssociatedCustomer($customerID, $skuNumber);

   
    if($validate[0]){
        
        $customers = getCustomers();
        $subAction = 'newCustomer';
        
        $message = 'Customer has already been assigned.';
    }else{
    
        $addCustomer = addAssociatedCustomer($customerID, $skuNumber, $userID);
        
        if(!$addCustomer){
            $message = 1;
        }
        
    }
    
    if(!$message){
        $viewItemHTML = buildItemView($skuNumber);
        echo $viewItemHTML;
    }else{
        echo $message;
    }
    
    $ajax = true;
    
    
}elseif($action == 'submitNewVendor'){
    $vendorID = filterNumber($_POST['vendorID']);
    $skuNumber = filterNumber($_POST['skuNumber']);
    $userID = $_SESSION['userID'];
    
    $validate = validateAssociatedVendor($vendorID, $skuNumber);

    if($validate[0]){
        $vendors = getVendors();
        $subAction = 'newVendor';
        
        $message = 'Vendor has already been assigned.';
    }else{
    
        $addVendor = addAssociatedVendor($vendorID, $skuNumber, $userID);
        
        if(!$addVendor){
            $message = 'Error: Failed to add Vendor.';
        }
        
    }
    
    $refreshview = true;
    
}elseif($action == 'deleteCustomer'){
    
    $associatedCustomerID = filterNumber($_GET['associatedCustomerID']);
    $skuNumber = filterNumber($_GET['skuNumber']);
    $deleteAC = deleteAssociatedCustomer($associatedCustomerID);
    
    if (!$deleteAC){
        $message = 'Customer Delete Failed';
    }
    
    $refreshview = true;
    
}elseif($action == 'deleteVendor'){
    
    $associatedVendorID = filterNumber($_GET['associatedVendorID']);
    $skuNumber = filterNumber($_GET['skuNumber']);
    $deleteAC = deleteAssociatedVendor($associatedVendorID);
    
    if (!$deleteAC){
        $message = 'Customer Delete Failed';
    }
    
    
    $refreshview = true;
    
}elseif($action == 'viewInventoryDetails'){
    $sku = filterNumber($_GET['skuNumber']);
    $inventoryData = detailedInventoryData($sku, 0);
    
    $inventoryDetailsTable = buildCurrentTable($inventoryData, $sku);
    
    
    echo $inventoryDetailsTable;
    $ajax = true;
}elseif($action == 'viewWODetailsComp'){
    $sku = filterNumber($_GET['skuNumber']);
    $woData = getDetailedWODataComp($sku, 0);
    
    $woDetailsTableComp = buildWODetailsTableComp($woData);
    
    
    echo $woDetailsTableComp;
    $ajax = true;
}elseif($action == 'viewWODetailsKit'){
    $sku = filterNumber($_GET['skuNumber']);
    $woData = getDetailedWODataKit($sku, 0);
    
    $woDetailsTableKit = buildWODetailsTableKit($woData);
    
    
    echo $woDetailsTableKit;
    $ajax = true;
}elseif($action == 'viewPODetails'){
    $sku = filterNumber($_GET['skuNumber']);
    $poData = getDetailedPOData($sku, 0);
    
    $poDetailsTable = buildPODetailsTable($poData);
    
    echo $poDetailsTable;
    $ajax = true;
}elseif($action == 'addItem'){
 
    $message;
    $addItemHTML = buildAddItemMenu($message);
    
    echo $addItemHTML;
    $ajax = true;
}elseif($action == 'addItemData'){
    $message;
    
    
    //insertItem()
    //$addItemHTML = buildAddItemMenu($message);
    
    echo $addItemHTML;
    $ajax = true;
}elseif($action == 'editItemDetail'){
    $message;
    $editItemDetailHTML = buildEditItemDetailMenu($message);
    
//    echo 'alert("I am an alert box")';
    
    echo $buildEditItemDetailMenu;
    $ajax = true;
}elseif($action == 'purchaseOrderMenu'){
    $message;
    $tranArr = null;
    
    $tranArr = buildPurchaseOrderMenu(NULL);
    
    $ajax = false;
}elseif($action == 'addPOrder'){
    $message;
    $tranArr[0] = null;
    
    $customerAddressID = filterNumber($_GET['customerAddress']);
    $shipToAddressID = filterString($_GET['ShipToAddress']);
    $orderDate = filterString($_GET['orderDate']);
    $requiredDate = filterString($_GET['requiredDate']);
    $shipVia = filterNumber($_GET['shipVia']);
    $notes = filterString($_GET['notes']);
    $ref = filterString($_GET['ref']);

    $poNumber = insertPOHeader($customerAddressID, $shipToAddressID, $orderDate, $requiredDate, $shipVia, $notes, $userID, $ref);
    
    if($poNumber){

    }else{

    }
    
    $orderData = getPOData($poNumber);
    $tranArr = buildPurchaseOrderMenu($orderData);
    $data = json_encode($tranArr);
    echo $data;
    $ajax = true;
}elseif($action == 'addPOItem'){
    $message;
            
    
    $orderItemNo = filterNumber($_GET['orderItemNo']);
    $orderItemQty = filterNumber($_GET['orderItemQty']);
    $lotCode = filterString($_GET['lotCode']);
    $palletNumber = filterString($_GET['palletNumber']);
    $poNo = filterString($_GET['poNo']);
    $hold = (filterString($_GET['hold']) == 1 ? true : false);
    $lineNo = 1;
    
    $itemAdded = addPOItem($poNo, $orderItemNo, $orderItemQty, $lineNo, $lotCode, $palletNumber, $hold);
    $orderData = getPOData($poNo);
    $htmlData = buildPOItems($orderData);
    $ajax = true;
    echo $htmlData;
}elseif($action == 'deletePOItem'){
    
//    $deleteOrder = deleteOrderItems($orderNumber);
//    $deleteOrder = deleteOrderHeader($orderNumber);
//    
//    $pickPackOrders = getPickPackOrders($customerID, $status);
//    $smallWorldMenu = buildPickPackMenu($pickPackOrders, $status);
    $purchaseOrderItemsID = filterNumber($_GET['purchaseOrderItemsID']);
    $poNo = filterNumber($_GET['poNo']);
    $orderData = getPOData($poNo);
    $delete = deletePOItem($purchaseOrderItemsID);
    $htmlData = buildPOItems($orderData);
    $ajax = true;
    echo $htmlData;
}elseif($action == 'updatePOItem') {
    $purchaseOrderItemsID = filterNumber($_GET['purchaseOrderItemsID']);
    $orderedQty = filterNumber($_GET['orderedQty']);
    $lotNum = filterNumber($_GET['lotNum']);
    $pltNum = filterNumber($_GET['pltNum']);
    $hold = filterNumber($_GET['hold']);

    $update = updatePOItem($purchaseOrderItemsID, $orderedQty, $lotNum, $pltNum, $hold);

    echo $update;
    // echo $orderedQty . ', ' . $lotNum . ', ' . $pltNum . ', ' . $hold;

    $ajax = true;
}elseif($action == 'openPO'){
    $message;
    $poNumber = filterNumber($_GET['poNumber']);
    $orderData = getPOData($poNumber);

    
    $tranArr = buildPurchaseOrderMenu($orderData, true);
    $data = json_encode($tranArr);

    
    echo $data;
    $ajax = true;
}elseif($action == 'completedPOrders') {

    $search = filterString($_GET['search']);
    $customerID = filterNumber($_GET['customer']);
    $startdate = filterString($_GET['startdate']);
    $enddate = filterString($_GET['enddate']);

    if($customerID == null && $search == null && $startdate == null && $enddate == null) {
        $poData = getPOList(1);
    }else {
        $completed = 1;
        $customerID = $customerID = null ? false : $customerID;
        $poData = getSearchedPO($search, $customerID, $startdate, $enddate, $completed);
    }

    if($poData != false || $poData != null) {
        $data = rebuildPOList($poData);
    }else {
        $data = 'error'; 
    }

    echo $data;

    $ajax = true;

}elseif($action == 'poSearch') {
    $search = filterString($_GET['search']);
    $customerID = filterNumber($_GET['customer']);
    $startdate = filterString($_GET['startdate']);
    $enddate = filterString($_GET['enddate']);
    $complete = filterNumber($_GET['complete']);
    
    if($customerID == null && $search == null && $startdate == null && $enddate == null && $complete == 0) {
        $poData = getPOList(1, $complete);
    }else {
        $customerID = $customerID = null ? false : $customerID;
        $poData = getSearchedPO($search, $customerID, $startdate, $enddate, $complete);
    }

    if(sizeof($poData) == 1) {
        $data = $poData[0][0];
    }else {
        if($poData != false || $poData != null) {
            $data = rebuildPOList($poData);
        }else {
            $data = 'error'; 
        }
    }

    $tranArr = null;
    $tranArr[0] = sizeof($poData);
    $tranArr[1] = $data;

    $data = json_encode($tranArr);
    echo $data;
    $ajax = true;
}elseif($action == 'clearPOSearch') {

    $html = clearPOSearch();

    echo $html;

    $ajax = true;

}elseif($action == 'editPO'){
    $message;

    $ajax = true;
}elseif($action == 'deletePO') {

    $poNo = filterNumber($_GET['poNo']);

    $r = deletePO($poNo);

    echo $r;

    if(!$r) {

    }else {

    }

}elseif($action == 'saveChangesPO') {
    $customerAddress = filterString($_GET['customerAddress']);
    $ShipToAddress = filterString($_GET['ShipToAddress']);
    $poNo = filterNumber($_GET['poNo']);
    $orderDate = filterString($_GET['orderDate']);
    $requiredDate = filterString($_GET['requiredDate']);
    $refNo = filterString($_GET['ref']);
    $shipVia = filterNumber($_GET['shipVia']);
    $notes = filterString($_GET['notes']);

    // $echo = $poNo . ' | ' . $orderDate . ' | ' . $requiredDate . ' | ' . $refNo . ' | ' . $shipVia . ' | ' . $notes;
    
    $update = updatePO($customerAddress, $ShipToAddress, $poNo, $orderDate, $requiredDate, $refNo, $shipVia, $notes);

    echo 1;

    $ajax = true;

}elseif($action == 'addBOM'){
    $skuNumber = filterNumber($_GET['skuNumber']);

    $message;
    $validBomItem = true;
    // $validChildSku = true;

    $validChildSku = validateBOMChildSku($skuNumber);
    
    if(!$validBomItem) {
        $htmlData = -1;
    }elseif(!$validChildSku){
        $htmlData = -2;
    }else { 
//        Nothing Failed-->
        $parentSku = filterNumber($_GET['parentSku']);
        $qtyPer = filterNumber($_GET['qtyPer']);
        $nextSeq = getNextSeq($parentSku);
        $itemAdded = addBOMData($skuNumber, $parentSku, $qtyPer, $nextSeq[0] + 1);
        $orderData = getBOMData($parentSku);
        $htmlData = buildBOM($orderData, $parentSku);
    }
    
    $ajax = true;
    
    echo $htmlData;
}elseif($action == 'deleteBom'){

        $parentSku = filterNumber($_GET['parentSku']);
        $childSku = filterNumber($_GET['childSku']);
        $itemAdded = deleteBOMItem($parentSku, $childSku);
        $orderData = getBOMData($parentSku);
        $htmlData = buildBOM($orderData, $parentSku);
    
    $ajax = true;
    
    echo $htmlData;
}else{
//    $searchTerm = null;
//    $searchMenuHTML = buildSearchMenu($searchTerm);

}

if ($refreshview){
    //Get component Tag Type
    $componentTagType = getConstant('componentTagType')['constantValue'];
    
    //Get Item Data
    $itemData = getItemData($skuNumber);
    
    //Calculate quantity available from Item Data
    $quantityAvailable = ($itemData[10] - $itemData[11]);
    
    //Get current Bin Totals
    $binTotals = currentInventoryTotals($skuNumber);
    
    //Get component Tag Type
    $componentTagType = getConstant('compTagType')['constantValue'];
    
    //Determine what to display (Vendor or Customer)
    if($componentTagType == 2 && $itemData[15] == 0){
        $associatedVendor = getAssociatedVendor($skuNumber);
    }else{
        $associatedCustomer = getAssociatedCustomer($skuNumber);
    }
    
    //Get inventory Data
    $binData = getInventoryData($skuNumber);
    
    //Set action to view Item
    $action = 'viewItem';
}else{
    $searchTerm = null;
    
    $searchMenuHTML = buildSearchMenu($searchTerm);
}

if(!$ajax){
    include 'view.php';
}

function buildCurrentTable($inventoryData, $sku){
    $table = '
    <div id="printThis">
        <h1>Currently In-Stock
            <a class="printBtn" onclick="printReport2()"><i class="bi bi-printer"></i></a>
        </h1>
        <h2 class="printOnly">Sku#: ' . $sku . ' | ' . $_SESSION['firstName'] . ' | ' .  date('h:i a') . '</h2>
        ';
    
    $table .= '<table class="printTable">
                    <tr>
                        <th>Tags / Serial#</th>
                        <th>State</th>
                        <th>Bin</th>
                        <th>PO#</th>
                        <th>LOT#</th>
                        <th>Exp</th>
                        <th>Plt#</th>
                        <th>WO</th>
                        <th>Qty</th>
                        <th>Age</th>
                    </tr>';
    
                for($i = 0; $i < sizeof($inventoryData); $i++){
                    if($inventoryData[$i][6] != null && $inventoryData[$i][6] != '0000-00-00'){
                        $expDate = date('m-d-Y', strtotime($inventoryData[$i][6]));
                    }else{
                        $expDate = null;
                    }
                    $age = dateDifference(date('Y-m-d'), date('Y-m-d', strtotime($inventoryData[$i][3])));

                    $table .= '<tr>
                                <td>
                                    ' . ($inventoryData[$i][21] == 1 ? 'H' : "") . '
                                    ' . ($inventoryData[$i][20] == 1 ? 'Q' : "") . '
                                    ' . ($inventoryData[$i][20] == 1 || $inventoryData[$i][21] == 1 ? ' / ' : "") . '
                                    ' . ($inventoryData[$i][0] == null ? '*' :  $inventoryData[$i][0]) . '
                                </td>
                                <td>' . ($inventoryData[$i][19] == null ? '*' :  $inventoryData[$i][19]) . '</td>
                                <td>' . ($inventoryData[$i][16] == null ? '*' :  $inventoryData[$i][16]) . '</td>
                                <td>' . ($inventoryData[$i][4] == null ? '*' :  $inventoryData[$i][4]) . '</td>
                                <td>' . ($inventoryData[$i][5] == null ? '*' :  $inventoryData[$i][5]) . '</td>
                                <td>' . ($expDate == null ? '*' :  $expDate) . '</td>
                                <td>' . ($inventoryData[$i][7] == null ? '*' :  $inventoryData[$i][7]) . '</td>
                                <td>' . ($inventoryData[$i][18] == null ? '*' :  $inventoryData[$i][18]) . '</td>
                                <td>' . ($inventoryData[$i][17] == null ? '*' :  $inventoryData[$i][17]) . '</td>
                                <td>' . $age . '</td>
                            </tr>';
                        }
                        $table .= '</table></div>';
    return $table;
}

function buildEditMenu($itemDetails, $message, $paperColor){
    $editHTML;

    $categories = getProductCodes();
    // $categories = getCategories();

    $timeStudyInSecs = $itemDetails[40];

    $timeStudyH = floor($timeStudyInSecs / 3600);
    $timeStudyM = floor(($timeStudyInSecs / 60) % 60);
    $timeStudyS = $timeStudyInSecs % 60;

    // $timeStudyH = floor($timeStudyInSecs/3600);
    // $timeStudyM = (($timeStudyInSecs/3600)-$timeStudyH)*60;

    $editHTML = '
    <div class="itemDetails">
        <h1>Edit Item Details</h1>';

        if($message){
            $editHTML .= '<h2>' . $message . '</h2>';
        }

$editHTML .= '<div class="grid">
            <div class="unit half">

                <div class="editdetail skupart">
                    <label for="skuNumber">SKU#:</label>
                    <input type="text" name="skuNumber" id="skuNumber" value="' . $itemDetails[0] . '" readonly="readonly">
                </div>
                <div class="editdetail skupart">
                    <label for="">PART#:</label>
                    <input type="text" name="partNumber" id="partNumber" value="' . $itemDetails[1] . '">
                </div>

                <div class="editdetail">
                    <label for="">DESC1:</label>
                    <input class="desc" type="text" name="description1" id="description1" value="' . $itemDetails[2] . '">
                </div>
                <div class="editdetail">
                    <label for="">DESC2:</label>
                    <input class="desc" type="text" name="description2" id="description2" value="' . $itemDetails[3] . '">
                </div>
                <div class="editdetail">
                    <label for="">DESC3:</label>
                    <input class="desc" type="text" name="description3" id="description3" value="' . $itemDetails[4] . '">
                </div>
                <div class="editdetail">
                    <label for="">DESC4:</label>
                    <input class="desc" type="text" name="description4" id="description4" value="' . $itemDetails[5] . '">
                </div>
                <div class="editdetail">
                    <label for="">DESC5:</label>
                    <input class="desc" type="text" name="description5" id="description5" value="' . $itemDetails[6] . '">
                </div>

                <div class="grid subInputs">
                    <div class="unit half">
                        <div class="editdetail">
                            <label for="">PUT:</label>
                            <input class="" type="text" name="PUT" id="PUT" value="' . $itemDetails[7] . '">
                        </div>
                        <div class="editdetail">
                            <label for="">PFCTR:</label>
                            <input class="" type="number" name="PFCTR" id="PFCTR" value="' . $itemDetails[9] . '">
                        </div>
                        <div class="editdetail">
                            <label for="">Pallet Position:</label>
                            <input class="" type="text" step="any" name="palletPos" id="palletPos" value="' . $itemDetails[38] . '">
                        </div>
                        <div class="editdetail">
                            <label for="">Paper Color:</label>
                            <select id="paperColor" name="paperColor" required>';

                                if(isset($itemDetails[39]) || $itemDetails[39] != null) {
                                    $editHTML .= '<option disabled value="' . $itemDetails[39] . '" selected>' . $itemDetails[13] . '</option>';
                                }else {
                                    $editHTML .= '<option disabled>Select Color...</option>';
                                }

                                for($i = 0; $i < sizeof($paperColor); $i++){
                                    $editHTML .= '<option value=' . $paperColor[$i][0];
                                    $editHTML .=  '>' . $paperColor[$i][1] . '</option>';
                                }

                $editHTML .= '</select>
                        </div>
                    </div>
                    <div class="unit half">
                        <div class="editdetail">
                            <label for="">Pallet Qty:</label>
                            <input class="" type="text" name="palletQty" id="palletQty" value="' . $itemDetails[14] . '">
                        </div>
                        <div class="editdetail">
                            <label for="">Cust Ref#:</label>
                            <input class="" type="text" name="customerRefNumber" id="customerRefNumber" value="' . $itemDetails[28] . '">
                        </div>
                        <div class="editdetail">
                            <label for="">Stock Unit:</label>
                            <input class="" type="text" name="stockingUnit" id="stockingUnit" value="' . $itemDetails[29] . '">
                        </div>
                        <div class="editdetail">
                            <label for="">Stock Ratio:</label>
                            <input class="" type="text" name="stockingRatio" id="stockingRatio" value="' . $itemDetails[30] . '">
                        </div>
                    </div>
                    <div class="editdetail timestudy">
                        <label for="">Time Study (H:M:S):</label>
                        <input type="number" id="timestudyH" style="text-align:right" placeholder="Hour" value="' . $timeStudyH .'">
                        <span>:</span>
                        <input type="number" id="timestudyM" step="15" max="45" min="0" placeholder="Min" value="' . (float)$timeStudyM .'">
                        <span>:</span>
                        <input type="number" id="timestudyS" step="15" max="45" min="0" placeholder="Sec" value="' . (float)$timeStudyS .'">
                    </div>
                </div>

            </div>

            <div class="unit half">

                <div class="switches editSwitches">
                    <div class="toggleFilter' . ( (float)$itemDetails[31] == 1 ? ' tfActive' : '' ) . '" onclick="toggleFilter(`organic`)" id="organictf"><i class="bi bi-check2"></i><p>Organic</p><input class="hide" id="organic" value="' . (float)$itemDetails[31] . '" disabled=""></div>
                    <div class="toggleFilter' . ( (float)$itemDetails[32] == 1 ? ' tfActive' : '' ) . '" onclick="toggleFilter(`kosher`)" id="koshertf"><i class="bi bi-check2"></i><p>Kosher</p><input class="hide" id="kosher" value="' . (float)$itemDetails[32] . '" disabled=""></div>    
                    <div class="toggleFilter' . ( (float)$itemDetails[33] == 1 ? ' tfActive' : '' ) . '" onclick="toggleFilter(`alergen`)" id="alergentf"><i class="bi bi-check2"></i><p>Alergen</p><input class="hide" id="alergen" value="' . (float)$itemDetails[33] . '" disabled=""></div>    
                    <div class="toggleFilter' . ( (float)$itemDetails[36] == 1 ? ' tfActive' : '' ) . '" onclick="toggleFilter(`consumable`)" id="consumabletf"><i class="bi bi-check2"></i><p>Consumable</p><input class="hide" id="consumable" value="' . (float)$itemDetails[36] . '" disabled=""></div>    
                    <div class="toggleFilter' . ( (float)$itemDetails[15] == 1 ? ' tfActive' : '' ) . '" onclick="toggleFilter(`kit`)" id="kittf"><i class="bi bi-check2"></i><p>Kit</p><input class="hide" id="kit" value="' . (float)$itemDetails[15] . '" disabled=""></div>    
                    <div class="toggleFilter' . ( (float)$itemDetails[16] == 1 ? ' tfActive' : '' ) . '" onclick="toggleFilter(`component`)" id="componenttf"><i class="bi bi-check2"></i><p>Component</p><input class="hide" id="component" value="' . (float)$itemDetails[16] . '" disabled=""></div>    
                    <div class="toggleFilter' . ( (float)$itemDetails[17] == 1 ? ' tfActive' : '' ) . '" onclick="toggleFilter(`poReq`)" id="poReqtf"><i class="bi bi-check2"></i><p>poReq</p><input class="hide" id="poReq" value="' . (float)$itemDetails[17] . '" disabled=""></div>    
                    <div class="toggleFilter' . ( (float)$itemDetails[18] == 1 ? ' tfActive' : '' ) . '" onclick="toggleFilter(`batchReq`)" id="batchReqtf"><i class="bi bi-check2"></i><p>batchReq</p><input class="hide" id="batchReq" value="' . (float)$itemDetails[18] . '" disabled=""></div>    
                    <div class="toggleFilter' . ( (float)$itemDetails[35] == 1 ? ' tfActive' : '' ) . '" onclick="toggleFilter(`notStackable`)" id="notStackabletf"><i class="bi bi-check2"></i><p>notStackable</p><input class="hide" id="notStackable" value="' . (float)$itemDetails[35] . '" disabled=""></div>     
                    <div class="toggleFilter' . ( (float)$itemDetails[27] == 1 ? ' tfActive' : '' ) . '" onclick="toggleFilter(`palletNumberRequired`)" id="palletNumberRequiredtf"><i class="bi bi-check2"></i><p>Plt# Req</p><input class="hide" id="palletNumberRequired" value="' . (float)$itemDetails[27] . '" disabled=""></div>     
                </div>

                <div class="rightInputs">
                    <div class="editdetail skupart">
                        <label for="">Rack Charge:</label>
                        <input class="" type="number" step="any" name="rackCharge" placeholder="Enter Rack Charge" id="rackCharge" value="' . $itemDetails[20] . '">
                    </div>
                    <div class="editdetail skupart">
                        <label for="">Case Qty:</label>
                        <input class="" type="text" step="any" name="caseQty" id="caseQty" value="' . $itemDetails[37] . '">
                    </div>
                </div>

                <div class="editdetail cats">
                    <label style="text-align: center !important" for="">Categories:</label>
                    <select id="cats" class="categorySelect" name="cats">';

                                if(isset($itemDetails[34]) || $itemDetails[34] != null) {
                                    $editHTML .= '<option disabled value="' . $itemDetails[34] . '" selected>' . $itemDetails[34] . '</option>';
                                }else {
                                    $editHTML .= '<option disabled>Select Cats...</option>';
                                }

                                for($i = 0; $i < sizeof($categories); $i++){
                                    $editHTML .= '<option value=' . $categories[$i][0] . '>' . $categories[$i][0] . ' | ' . $categories[$i][1] . '</option>';
                                }

                $editHTML .= '</select>
                </div>

                <div class="container">
                    <a class="bs-btn btn-blue" onclick="updateItem()">Update</a>
                </div>

            </div>

        </div>
    </div>
    ';

    return $editHTML;

    // <button class="btn btn-outline-light" onclick="updateItem()">Submit</button>
    // <button type="submit" name="action" id="action" value="updateItem"><span>i</span></button>
}

function buildAddItemMenu($message){
    $addItemHTML;

    $customers = getCustomers();
    $paperColor = getPaperColor();
    $productCodes = getProductCodes();
    $forceBillable = getConstant('forceBillable');

    $addItemHTML = '
    <div class="itemDetails">
        <h1>Add Item</h1>';

    if($message){
        $addItemHTML .= '<h2>' . $message . '</h2>';
    }

$addItemHTML .= '
            <div class="grid">
            <div class="unit half">
                <input class="hide" id="forceBillable" value="' . $forceBillable['constantValue'] . '">
                <div class="editdetail selectCustomerAdd">
                    <select id="customerID" name="customerID" required>
                        <option value="" disabled selected>Select Customer</option>';
                        for($i = 0; $i < sizeof($customers); $i++){
                            $addItemHTML .= '<option value=' . $customers[$i][0] . '>' . $customers[$i][1] . ' - ' . $customers[$i][2] . '</option>';
                        }
                    $addItemHTML .= '</select>
                </div>

                <div class="editdetail skupart" style="margin-left:1.8em !important;">
                    <label for="">PART#:</label>
                    <input type="text" name="partNumber" id="partNumber" value="">
                </div>

                <div class="editdetail">
                    <label for="">DESC1:</label>
                    <input class="desc" type="text" name="description1" id="description1" value="">
                </div>
                <div class="editdetail">
                    <label for="">DESC2:</label>
                    <input class="desc" type="text" name="description2" id="description2" value="">
                </div>
                <div class="editdetail">
                    <label for="">DESC3:</label>
                    <input class="desc" type="text" name="description3" id="description3" value="">
                </div>
                <div class="editdetail">
                    <label for="">DESC4:</label>
                    <input class="desc" type="text" name="description4" id="description4" value="">
                </div>
                <div class="editdetail">
                    <label for="">DESC5:</label>
                    <input class="desc" type="text" name="description5" id="description5" value="">
                </div>

                <div class="grid subInputs">
                    <div class="unit half">
                        <div class="editdetail">
                            <label for="">PUT:</label>
                            <input class="" type="text" name="PUT" id="PUT" value="">
                        </div>
                        <div class="editdetail">
                            <label for="">PFCTR:</label>
                            <input class="" type="number" name="PFCTR" id="PFCTR" value="">
                        </div>
                        <div class="editdetail">
                            <label for="">Pallet Position:</label>
                            <input class="" type="text" step="any" name="palletPos" id="palletPos" value="">
                        </div>
                        <div class="editdetail">
                            <label for="">Paper Color:</label>
                            <select id="paperColor" name="paperColor" required>
                                <option disabled>Select Color...</option>';

                                for($i = 0; $i < sizeof($paperColor); $i++){
                                    $addItemHTML .= '<option value=' . $paperColor[$i][0];
                                    $addItemHTML .=  '>' . $paperColor[$i][1] . '</option>';
                                }

                $addItemHTML .= '</select>
                        </div>
                    </div>
                    <div class="unit half">
                        <div class="editdetail">
                            <label for="">Pallet Qty:</label>
                            <input class="" type="text" name="palletQty" id="palletQty" value="">
                        </div>
                        <div class="editdetail">
                            <label for="">Cust Ref#:</label>
                            <input class="" type="text" name="customerRefNumber" id="customerRefNumber" value="">
                        </div>
                        <div class="editdetail">
                            <label for="">Stock Unit:</label>
                            <input class="" type="text" name="stockingUnit" id="stockingUnit" value="">
                        </div>
                        <div class="editdetail">
                            <label for="">Stock Ratio:</label>
                            <input class="" type="text" name="stockingRatio" id="stockingRatio" value="">
                        </div>
                    </div>
                </div>

                <div class="editdetail timestudy">
                    <label for="">Time Study (H:M:S):</label>
                    <input id="timestudyH" type="number" style="text-align:right" placeholder="Hour">
                    <span>:</span>
                    <input id="timestudyM" type="number" step="15" max="45" min="0" placeholder="Min">
                    <span>:</span>
                    <input id="timestudyS" type="number" step="15" max="45" min="0" placeholder="Sec">
                </div>

            </div>

            <div class="unit half">

                <div class="switches editSwitches">
                    <div class="toggleFilter" onclick="toggleFilter(`organic`)" id="organictf"><i class="bi bi-check2"></i><p>Organic</p><input class="hide" id="organic" value="0" disabled=""></div>
                    <div class="toggleFilter" onclick="toggleFilter(`kosher`)" id="koshertf"><i class="bi bi-check2"></i><p>Kosher</p><input class="hide" id="kosher" value="0" disabled=""></div>    
                    <div class="toggleFilter" onclick="toggleFilter(`alergen`)" id="alergentf"><i class="bi bi-check2"></i><p>Alergen</p><input class="hide" id="alergen" value="0" disabled=""></div>    
                    <div class="toggleFilter" onclick="toggleFilter(`kit`)" id="kittf"><i class="bi bi-check2"></i><p>Kit</p><input class="hide" id="kit" value="0" disabled=""></div>    
                    <div class="toggleFilter" onclick="toggleFilter(`consumable`)" id="consumabletf"><i class="bi bi-check2"></i><p>Consumable</p><input class="hide" id="consumable" value="0" disabled=""></div>    
                    <div class="toggleFilter" onclick="toggleFilter(`component`)" id="componenttf"><i class="bi bi-check2"></i><p>Component</p><input class="hide" id="component" value="0" disabled=""></div>    
                    <div class="toggleFilter" onclick="toggleFilter(`poReq`)" id="poReqtf"><i class="bi bi-check2"></i><p>poReq</p><input class="hide" id="poReq" value="0" disabled=""></div>    
                    <div class="toggleFilter" onclick="toggleFilter(`batchReq`)" id="batchReqtf"><i class="bi bi-check2"></i><p>batchReq</p><input class="hide" id="batchReq" value="0" disabled=""></div>    
                    <div class="toggleFilter" onclick="toggleFilter(`notStackable`)" id="notStackabletf"><i class="bi bi-check2"></i><p>notStackable</p><input class="hide" id="notStackable" value="0" disabled=""></div>     
                    <div class="toggleFilter" onclick="toggleFilter(`palletNumberRequired`)" id="palletNumberRequiredtf"><i class="bi bi-check2"></i><p>Plt# Req</p><input class="hide" id="palletNumberRequired" value="0" disabled=""></div>     
                </div>

                <div class="rightInputs">
                    <div class="editdetail skupart">
                        <label for="">Rack Charge:</label>
                        <input class="" type="number" step="any" name="rackCharge" placeholder="Enter Rack Charge" id="rackCharge" value="">
                    </div>
                    <div class="editdetail skupart">
                        <label for="">Case Qty:</label>
                        <input class="" type="text" step="any" name="caseQty" id="caseQty" value="">
                    </div>
                </div>

                <div class="editdetail cats">
                    <label style="text-align: center !important" for="">Categories:</label>
                    <select id="cats" name="cats" class="categorySelect">
                        <option disabled>Select Cats...</option>';

                        for($i = 0; $i < sizeof($productCodes); $i++){
                            $addItemHTML .= '<option value=' . $productCodes[$i][0] . '>' . $productCodes[$i][0] . ' | ' . $productCodes[$i][1] . '</option>';
                        }

                $addItemHTML .= '</select>
                </div>

                <div class="container" id="loading">
                    <a class="bs-btn btn-blue" onclick="addNewItem()">Add Item</a>
                </div>

            </div>

        </div>
    </div>
    ';

    return $addItemHTML;

    // <button class="btn btn-outline-light" onclick="updateItem()">Submit</button>
    // <button type="submit" name="action" id="action" value="updateItem"><span>i</span></button>

}


function buildEditItemDetailMenu($message){
    $editItemDetailHTML;
    
    // $editItemDetailHTML  = '<a href="#" onclick="closeOverlay()" id="exit"><span>x</span></a>';
    
    $editItemDetailHTML .= '<form id="dataEntryForm">
    <h1>Edit Item Details</h1>';
        if($message){
            $editItemDetailHTML .= '<h2>' . $message . '</h2>';
        }

    $editItemDetailHTML .= '<ul>
          
        <div class="grid">
            <div class="unit half">
                <li>
                    <label for="partNumber">Part Number</label>
                    <input type="number" name="partNumber" placeholder="Enter Part Number" 
                        id="partNumber" value="My Default">
                </li>
                <li>
                    <label for="DESC1">Desc. 1</label>
                    <input type="text" name="DESC1" placeholder="Enter Description" 
                        id="DESC1" value="My Default">
                </li>
                <li>
                    <label for="DESC2">Desc. 2</label>
                    <input type="text" name="DESC2" placeholder="Enter Description" 
                        id="DESC2" value="My Default">
                </li>
                <li>
                    <label for="DESC3">Desc. 3</label>
                    <input type="text" name="DESC3" placeholder="Enter Description" 
                        id="DESC3" value="My Default">
                </li>
                <li>
                    <label for="DESC4">Desc. 4</label>
                    <input type="text" name="DESC4" placeholder="Enter Description" 
                        id="DESC4" value="My Default">
                </li>
                <li>
                    <label for="DESC5">Desc. 5</label>
                    <input type="text" name="DESC5" placeholder="Enter Description" 
                        id="DESC5" value="My Default">
                </li>
                <li>
                    <label for="paperColor">Label Color:</label>
                    <select name="paperColor" required>
                        <option value="" disabled selected>Select Color</option>
                        <option value="1">This is option 1</option>
                    </select>
                </li>
            </div>
            <div class="unit half">
                <li>
                <label for="palletQty">Pallet Qty:</label>
                <input type="number" name="palletQty" placeholder="Enter Pallet Quantity" 
                    id="palletQty" value="My Default">
                </li>  
                <li>
                    <label for="paperColor">Label Color:</label>
                    <select name="paperColor" required>
                        <option value="" disabled selected>Select Color</option>
                        <option value="1">This is option 1</option>
                    </select>
                </li>
            </div>
        </div>
    </ul>
    <a class="bs-btn btn-blue" value="updateItem()"><span>i</span></a>
</form>';

    return $editItemDetailHTML;


}

function buildSearchMenu($searchTerm, $foresite = null){
    $message = null;
    $searchMenuHTML;
    $items = itemSearch($searchTerm, $foresite);
    
    if(!$items){
        $message = 'No search results found';
    }
        // $searchMenuHTML  = '<a href="/logIn/index.php?action=mainMenu" id="exit"><span>x</span></a>';
    
        $searchMenuHTML = '<h1>Search Inventory</h1><input class="hide" id="currentPage" value="search">';
        
        if($message){ $searchMenuHTML .= '<li><h3>' . $message . '</h3></li>';}
        
        $searchMenuHTML .= 
        '
            <div class="grid">
                <div class="unit whole align-center">
                    <label for="itemSearch">Search:</label>';

                    if($searchTerm){
                        $searchMenuHTML .= '<input type="text" name="itemSearch" value="' . $searchTerm . '" placeholder="Enter search term" onkeyup="enterSubmitSearch(event);" id="itemSearch" >';
                    }else {
                        $searchMenuHTML .= '<input type="text" name="itemSearch" placeholder="Enter search term" onkeyup="enterSubmitSearch(event);" id="itemSearch" >';
                    }

                    $searchMenuHTML .= '
                    <a href="#" onclick="searchInventory(); return false;" title="Search"><span class="viewportSpan">s</span></a>
                    <a href="#" onclick="addItem(); return false;" title="Add Item"><span class="viewportSpan">+</span></a>

                    <div class="foresiteToggle">
                        <div class="onoffswitch">
                            <input type="checkbox" name="onoffswitch" id="myonoffswitch" class="onoffswitch-checkbox" tabindex="0" onchange="filterForesite(this)" ' . ( $foresite == 1 ? 'checked' : '' ) . '>
                            <label class="onoffswitch-label" for="myonoffswitch"></label>
                        </div>
                        <h2 class="onoffswitchLabel">Include Foresite</h2>
                    </div>
                </div>
            </div>


        <table style="margin-left: auto;">
            <tr>
                <th>SKU#</th>
                <th>Company</th>
                <th>Part#</th>
                <th>Description 1</th>
                <th>Description 2</th>
                <th>Qty (BIN/ERP)</th>
                <th>Options</th>
            </tr>';

        for($i = 0; $i < count($items); $i++){
            $searchMenuHTML .= '
            <tr id="skuLine_' . $items[$i][0] . '">
                <td>' . $items[$i][0] . '</td>
                <td>' . $items[$i][1] . '</td>
                <td>' . $items[$i][2] . '</td>
                <td>' . $items[$i][3] . '</td>
                <td>' . $items[$i][4] . '</td>
                <td>' . $items[$i][6] . '/' . (float)$items[$i][7] . '</td>
                <td style="display: flex; justify-content: center;">';
                
                if((getSkuType($items[$i][0])[0] == 1)) {
                    $searchMenuHTML .= '<a href="#" onclick="printShipping(' . $items[$i][0] . ', false)" title="Print"><span>h</span></a>';
                }

                $searchMenuHTML .= '
                    <a href="#" onclick="viewItem(' . $items[$i][0] . ')" title="View Item"><span>N</span></a>';
                $searchMenuHTML .= $_SESSION['securityLevel'] >=3 ? '<a href="#" onclick="openEditMenu(' . $items[$i][0] . ')" title="Edit Item"><span>p</span></a></td>' : '</td>';
            $searchMenuHTML .= '</tr>';
        }

        $searchMenuHTML .= '</table>';
        
    return $searchMenuHTML;

   
}

function buildItemView($skuNumber, $itemSearch = false){

    $message = null;
    $itemViewMenuHTML = null;
    
    $userID = $_SESSION['userID'];
    $itemDetails = getItemDetails($skuNumber);
    $itemData = getItemData($skuNumber);
    $bomData = getBOMData($skuNumber);

    $timeStudyInSecs = $itemData[40];

    $timeStudyH = floor($timeStudyInSecs / 3600);
    $timeStudyM = floor(($timeStudyInSecs / 60) % 60);
    $timeStudyS = $timeStudyInSecs % 60;

    // $timeStudyH = floor($timeStudyInSecs/3600);
    // $timeStudyM = (($timeStudyInSecs/3600)-$timeStudyH)*60;

    //Get component Tag Type
    //$componentTagType = getConstant('compTagType')['constantValue'];
    $componentTagType = 1;
    
    if($componentTagType == 2 /*&& $itemData[15] == 0*/){
        $associatedVendor = getAssociatedVendor($skuNumber);
    }else{
        $associatedCustomer = getAssociatedCustomer($skuNumber);
    }
    
    $quantityAvailable = ($itemData[10] - $itemData[11]);
    $binTotals = currentInventoryTotals($skuNumber);
    $binData = getInventoryData($skuNumber);
    
    if(!isset($itemData)){
        $message = 'Error: Unable to access selected item.';
        $action = 'searchItems';
    }
    
    if($itemDetails == null){
        $newItem = addNewItem($skuNumber, $userID);
        
        if(!$newItem){
            $message = 'Error in collecting data3.';
        }
    }
    

    
//        if(isset($_SESSION['return'])){
//            
//            if($_SESSION['return'] == 'returnToProduction'){
//                    $itemViewMenuHTML = '<a href= "/production/index.php" id="exit"><span>x</span></a>';
//            }
//        }else{
//            $itemViewMenuHTML .= '<a href= "/inventory/index.php" id="exit"><span>x</span></a>';
//        }
                    

               
                
        if($message){$itemViewMenuHTML .= '<li><h3>' . $message . '</h3></li>';}

            $itemViewMenuHTML .= '<div class="grid" id="itemView">';

            if(!isset($_SESSION['return'])) {
                $_SESSION['return'] = '';
            }

            $itemViewMenuHTML .= '
            <div class="unit half">                    
                <div class="itemDetails">
                    <h1>
                    <a';

                    // Back Button

                        if($_SESSION['return'] == 'returnToProduction'){
                            $itemViewMenuHTML .= ' href="/production/index.php?action=productionSchedule" ';
                        }else {
                            $itemViewMenuHTML .= ' href="#" onclick="searchInventory(`' . $itemSearch . '`)" ';
                        }
        
            $itemViewMenuHTML .= 'class="headerBtn"><i class="bi bi-arrow-left"></i></a>

                            Sku Information
                            ' . ( $_SESSION['securityLevel'] > 2 ? '<a href="#" onclick="openEditMenu(' . $itemData[0] . ')" title="Edit Item" class="headerBtn"><i class="bi bi-pencil"></i></a>' : '' ) . '
                            <a href="#" onclick="openNotesMenu(' . $itemData[0] . ')" title="Notes" class="headerBtn"><i class="bi bi-journal"></i></a>
                            <a href="#" onclick="skuHistory(' . $itemData[0] . ')" title="Open Item History" class="headerBtn"><i class="bi bi-clock-history"></i></a>
                            <a href="#" onclick="billablePallet(' . $itemData[0] . ')" title="Open Billable Pallet" class="headerBtn"><i class="bi bi-currency-dollar"></i></a>
                            <input class="hide" id="currentPage" value="details">
                        </h1>';

                        // <!-- Text Inputs -->
                        $itemViewMenuHTML .= '
                        <div class="detail">
                            <label for="">SKU#:</label>
                            <input type="text" id="skuNumber" value="' . $itemData[0] .'" disabled>
                        </div>
                        <div class="detail">
                            <label for="">Part#:</label>
                            <input type="text" value="' . $itemData[1] .'" disabled>
                        </div>
                        <div class="detail">
                            <label for="">DESC 1:</label>
                            <input class="desc" type="text" value="' . $itemData[2] .'" disabled>
                        </div>
                        <div class="detail">
                            <label for="">DESC 2:</label>
                            <input class="desc" type="text" value="' . $itemData[3] .'" disabled>
                        </div>
                        <div class="detail">
                            <label for="">DESC 3:</label>
                            <input class="desc" type="text" value="' . $itemData[4] .'" disabled>
                        </div>
                        <div class="detail">
                            <label for="">DESC 4:</label>
                            <input class="desc" type="text" value="' . $itemData[5] .'" disabled>
                        </div>
                        <div class="detail">
                            <label for="">DESC 5:</label>
                            <input class="desc" type="text" value="' . $itemData[6] .'" disabled>
                        </div>';

                        // <!-- Inputs -->
                        $itemViewMenuHTML .= '
                        <div class="grid inputs2">
                            <div class="unit half">
                                <div class="detail">
                                    <label for="">PUT:</label>
                                    <input type="text"  value="' . $itemData[7] .'" disabled>
                                </div>
                                <div class="detail">
                                    <label for="">PFCTR:</label>
                                    <input type="text"  value="' . $itemData[9] .'" disabled>
                                </div>
                                <div class="detail timestudy">
                                    <label for="">Time Study (H:M:S):</label>
                                    <input type="number" placeholder="Hour" value="' . (float)$timeStudyH .'" disabled>
                                    <span>:</span>
                                    <input type="number" step="15" max="45" min="00" placeholder="Min" value="' . (float)$timeStudyM .'" disabled>
                                    <span>:</span>
                                    <input type="number" step="15" max="45" min="00" placeholder="Sec" value="' . (float)$timeStudyS .'" disabled>
                                </div>
                                <div class="detail">
                                    <label for="">Paper Color:</label>
                                    <input type="text"  value="' . $itemData[13] .'" disabled>
                                </div>
                            </div>
                            <div class="unit half">
                                <div class="detail">
                                    <label for="">Pallet Qty:</label>
                                    <input type="text"  value="' . $itemData[14] .'" disabled>
                                </div>
                                <div class="detail">
                                    <label for="">Cust Ref#:</label>
                                    <input type="text"  value="' . $itemData[28] .'" disabled>
                                </div>
                                <div class="detail">
                                    <label for="">Stock Unit:</label>
                                    <input type="text"  value="' . $itemData[29] .'" disabled>
                                </div>
                                <div class="detail">
                                    <label for="">Stock Ratio:</label>
                                    <input type="text"  value="' . $itemData[30] .'" disabled>
                                </div>
                            </div>
                        </div>';

                        // <!-- Booleans -->
                        $itemViewMenuHTML .= '
                        <div class="switches">
                            <div class="toggleFilter tfDisabled' . ( (float)$itemData[31] == 1 ? ' tfActive' : '' ) . '" id="organictf"><i class="bi bi-check2"></i><p>Organic</p><input class="hide" id="organic" value="' . (float)$itemData[31] .'" disabled></div>
                            <div class="toggleFilter tfDisabled' . ( (float)$itemData[32] == 1 ? ' tfActive' : '' ) . '" id="koshertf"><i class="bi bi-check2"></i><p>Kosher</p><input class="hide" id="kosher" value="' . (float)$itemData[32] .'" disabled></div>    
                            <div class="toggleFilter tfDisabled' . ( (float)$itemData[33] == 1 ? ' tfActive' : '' ) . '" id="alergentf"><i class="bi bi-check2"></i><p>Alergen</p><input class="hide" id="alergen" value="' . (float)$itemData[33] .'" disabled></div>    
                            <div class="toggleFilter tfDisabled' . ( (float)$itemData[36] == 1 ? ' tfActive' : '' ) . '" id="consumabletf"><i class="bi bi-check2"></i><p>Consumable</p><input class="hide" id="consumable" value="' . (float)$itemData[36] .'" disabled></div>    
                            <div class="toggleFilter tfDisabled' . ( (float)$itemData[15] == 1 ? ' tfActive' : '' ) . '" id="kittf"><i class="bi bi-check2"></i><p>Kit</p><input class="hide" id="kit" value="' . (float)$itemData[15] .'" disabled></div>    
                            <div class="toggleFilter tfDisabled' . ( (float)$itemData[16] == 1 ? ' tfActive' : '' ) . '" id="componenttf"><i class="bi bi-check2"></i><p>Component</p><input class="hide" id="component" value="' . (float)$itemData[16] .'" disabled></div>    
                            <div class="toggleFilter tfDisabled' . ( (float)$itemData[17] == 1 ? ' tfActive' : '' ) . '" id="poReqtf"><i class="bi bi-check2"></i><p>poReq</p><input class="hide" id="poReq" value="' . (float)$itemData[17] .'" disabled></div>    
                            <div class="toggleFilter tfDisabled' . ( (float)$itemData[19] == 1 ? ' tfActive' : '' ) . '" id="billablePallettf"><i class="bi bi-check2"></i><p>billablePallet</p><input class="hide" id="billablePallet" value="' . (float)$itemData[19] .'" disabled></div>    
                            <div class="toggleFilter tfDisabled' . ( (float)$itemData[18] == 1 ? ' tfActive' : '' ) . '" id="batchReqtf"><i class="bi bi-check2"></i><p>batchReq</p><input class="hide" id="batchReq" value="' . (float)$itemData[18] .'" disabled></div>    
                            <div class="toggleFilter tfDisabled' . ( (float)$itemData[35] == 1 ? ' tfActive' : '' ) . '" id="notStackabletf"><i class="bi bi-check2"></i><p>notStackable</p><input class="hide" id="notStackable" value="' . (float)$itemData[35] .'" disabled></div>
                            <div class="toggleFilter tfDisabled' . ( (float)$itemData[27] == 1 ? ' tfActive' : '' ) . '" id="palletNumberRequiredtf"><i class="bi bi-check2"></i><p>Plt# Req</p><input class="hide" id="palletNumberRequired" value="' . (float)$itemData[27] . '" disabled=""></div>     
        
                        </div>';

                        // <!-- More Text Inputs -->
                        $itemViewMenuHTML .= '
                        <div class="container">
                            <div>
                                <label for="">Rack Charge:</label>
                                <input class="desc" type="text" value="' . $itemData[20] .'" disabled>
                            </div>
                            <div>
                                <label for="">Case Qty:</label>
                                <input class="desc" type="text" value="' . $itemData[37] .'" disabled>
                            </div>
                            <div>
                                <label for="">Pallet Position:</label>
                                <input class="desc" type="text" value="' . $itemData[38] .'" disabled>
                            </div>
                        </div>

                        <div class="cats">
                            <label for="">Categories:</label>
                            <input type="text" value="' . $itemData[34] .'" disabled>
                        </div>';

                        // Associated Customers
                            
                        $itemViewMenuHTML .= '<div class="division"  id="associatedCustomer">';

                        if($_SESSION['securityLevel'] > 1){ 
                            if($componentTagType == 2){
                                $itemViewMenuHTML .= '<h1 class="invAsCust">Associated Vendor
                                <a href="#" onclick="newVendor(' . $itemData[0] . ')"><span>+</span></a></h1>';
                            }else{
                                $itemViewMenuHTML .= '<h1 class="invAsCust">Associated Customer
                                <a href="#" onclick="newCustomer(' . $itemData[0] . ')"><span id="skuInfoBtnBlue">+</span></a>';
                            }
                        }
                                                      
                        $itemViewMenuHTML .= '
                        <div class="grid" id ="skuInfoCustomerListBox">
                            <div class="unit three-fifths scrollListHeader">Customer</div>
                                <div class="unit one-fifth scrollListHeader">City</div>
                                    <br><hr>';
                        
                        if($componentTagType == 2 && $itemData[15] == 0){
                            for($i = 0; $i < count($associatedVendor); $i++){
                                $itemViewMenuHTML .= 
                                        '<div class="unit three-fifths">' . $associatedVendor[$i][2] . '</div>
                                            <div class="unit one-fifth">' . $associatedVendor[$i][3] . '</div>';
                                if($_SESSION['securityLevel'] > 2){ 
                                    $itemViewMenuHTML .= '
                                    <div class="unit one-fifth">
                                        <a href= "/inventory/index.php?action=deleteVendor&associatedVendorID=' . $associatedVendor[$i][0] . '&skuNumber=' . $itemData[0] . '">
                                            <span>x</span>
                                        </a>
                                    </div>';
                                }else{
                                    $itemViewMenuHTML .= '<div class="unit one-fifth"></div>';
                                }
                            }
                        }else{
                            for($i = 0; $i < count($associatedCustomer); $i++){
                                $itemViewMenuHTML .= 
                                        '<div class="unit three-fifths">' . $associatedCustomer[$i][2] . '</div>
                                            <div class="unit one-fifth">' . $associatedCustomer[$i][3] . '</div>';
                                if($_SESSION['securityLevel'] > 2){ 
                                    $itemViewMenuHTML .= 
                                    '<div class="unit one-fifth">
                                        <a href= "/inventory/index.php?action=deleteCustomer&associatedCustomerID=' . $associatedCustomer[$i][0] . '&skuNumber=' . $itemData[0] . '">
                                            <span id="skuInfoBtnRed">x</span>
                                        </a>
                                    </div>';
                        
                                }else{
                                    $itemViewMenuHTML .= '<div class="unit one-fifth"></div>';
                                }
                            }
                        }
                        
                        $itemViewMenuHTML .= '</div>';

                        // End Associated Customers
                        
                        $itemViewMenuHTML .= '</div>
                    </div>
                </div>';

                
//                            <ul>
//                                <li>
//                                    ';
//'<div class="division">';
//                                        $filePath = $_SERVER['DOCUMENT_ROOT']. '/image/' . $itemData[0] . '.JPG';
//                                        if (file_exists($filePath)){
//                                            $itemViewMenuHTML .= '<a href= "/inventory/index.php?action=viewImage&skuNumber=' . $itemData[0] . '">'
//                                                . '<img src="/image/' . $itemData[0] . '.JPG">'
//                                                   . '</a>';
//
//                                       }else{
//                                          $itemViewMenuHTML .= '<img src="/image/noImage.png">'; 
//                                        }
//'</div>';
//                                                       '</li>
//                                <li>                 
                                
                                    $itemViewMenuHTML .=       
                                            
                                        
                                    '<div class="unit half align-center">
                                    <div class="gridPanel">
                                        <div class="division"  id="billOfMaterials">
                                            <h1>Bill of Material
                                                <a href="#" onclick="buildNewBomMenu(' . $skuNumber . ')"><span id="skuInfoBtnBlue">+</span></a>
                                            </h1>
                                                <div class="grid" id ="overflowScroll">

                                                    <div class="unit one-fifth">Sku#</div>
                                                    <div class="unit two-fifths">Description</div>
                                                    <div class="unit one-fifth">Qty Per</div>

                                                    <input class="hide" id="skuExists" value="';
                                                    for($i = 0; $i < count($bomData); $i++){
                                                        $itemViewMenuHTML .= $bomData[$i][1] . ' ';
                                                    }
                                                    
                                                    $itemViewMenuHTML .= '">

                                                    <br>

                                                    <hr>';

                                                    for($i = 0; $i < count($bomData); $i++){
                                                        $itemViewMenuHTML .=  
                                                        '<div class="grid">
                                                        <div class="unit one-fifth">' . $bomData[$i][1] . '</div>
                                                        <div class="unit two-fifths">' . $bomData[$i][2] . '</div>
                                                        <div class="unit one-fifth">' . (Float)$bomData[$i][4] . '</div>
                                                        <div class="unit one-fifth">
                                                            <a href="#" onclick="openBomDetails()"><span id="skuInfoBtnBlue">N</span></a>
                                                            <a href="#" onclick="deleteBomItem('. $bomData[$i][0] . ', ' . $bomData[$i][1] . ')"><span id="skuInfoBtnRed">x</span></a>
                                                        </div></div>';
                                                    }
                                    $itemViewMenuHTML .=
                                                    '<hr>
                                                  <div class="selectionPanel" id="newBOM"></div>
                                                  <div id="errorMessage"></div>
                                                </div>
                                    </div>
                                        <div class="division"  id="binLocations">';

                                    //Builds Header

                                    if($itemData[16] == 1 && $itemData[15] == 1) {
                                        $type = 0;
                                    }elseif($itemData[16] == 1) {
                                        $type = 1;
                                    }elseif($itemData[15] == 1) {
                                        $type = 2;
                                    }else {
                                        $type = null;
                                    }

                                    $html = buildOpenBinLocations($type, $skuNumber, "2020-04-01", "2020-05-30", 4);   
                                    
                                    $itemViewMenuHTML .= $html;
                                    
    return $itemViewMenuHTML;
}

function buildNewCustomer($skuNumber){
    $message = null;
    $newCustomerHTML = Null;
            
    $associatedCustomer = getAssociatedCustomer($skuNumber);
    $customers = getCustomers();
        $newCustomerHTML .= '<a href="#" onclick="closeOverlay()" id="exit"><span>x</span></a>';
        $newCustomerHTML .=
            '<h1>Add Customer</h1>
            <div class="grid">
            <div class="unit two-thirds">
            <ul>';
        
            if($message){$newCustomerHTML .= '<li><h2>' . $message . '</h2></li>';}
                
            $newCustomerHTML .=
                '<li>
                    <label for="customerID">Customer:</label>
                    <select name="customerID" id="customerID" required>
                        <option value="" disabled selected>Select customer</option>';

                            for($i = 0; $i < sizeof($customers); $i++){
                                $newCustomerHTML .= '<option value=' . $customers[$i][0] . '>' . $customers[$i][1] . ' - ' . $customers[$i][2] . '</option>';
                            }
                            
            $newCustomerHTML .=
                    '</select>
                    <a class="invBtn" href="#" onclick="addCustomer(' . $skuNumber . ')"><span>v</span></a>
                </li>
            </ul>
            </div>
            </div>';

    return $newCustomerHTML;
}

// function advanceSku(){
    
//     $currentSku = getConstant("itemSequence")["constantValue"];

//     $currentSku + 1;
    
//     return $currentSku;
// }

function buildPurchaseOrderMenu($orderData = NULL, $openPO = false){
    $tranArr = null;
    $type = 1;
    $message;

    // Build Order List
    $tranArr[0] = !$openPO ? buildPOList($type) : null;

    // Build Order Info
    $tranArr[1] = buildPOHeader($orderData);

    // Build Order Details
    $tranArr[2] = buildPODetails($orderData);

    // Build Order Line Items
    $tranArr[3] = buildPOItems($orderData);

    return $tranArr;
}


//Build Order Info #1
function buildPOHeader($orderData){
    $html;
    
    $html = '
    <h2>Order Info ' 
    . ( $orderData ? 
    ( $orderData[10] == 0 
    ? '<a href="#" id="deletePOBtn" onclick="confirmDeletePO(' .  $orderData[0] . ')" title="Delete PO"><span class="viewportRedBtn">X</span></a>' : '') : '' ) . '</h2';

    if($orderData) {
        $html .= '
        <div>
            <h2>PONumber: ' . $orderData[0] . '</h2>
            <h2>Status: ' . ($orderData[10] == 1 ? 'Completed' : 'Not Completed') . '</h2>
        </div>
        ';
    }
    
    return $html;
}

// Order Details #2
function buildPODetails($orderData){

    // Customer Address
    $customerList = getCustomers();

    $html = '
    <div class="poDetailsLi">
        <h2>Customer Address</h2>
        <select id="customerAddress" onchange="customerToAddressChange(value);" required ' . ($orderData ? 'disabled' : '') . '>
            <option value="' . (!$orderData ? 'null' : $orderData[1]) . '" disabled selected>' . (!$orderData ?  'Select Ship To Address' : $orderData[8]) . '</option>';

        for($i = 0; $i < sizeof($customerList); $i++){
            $html .= '<option value=' . $customerList[$i][0] . '>' . $customerList[$i][1] . ( $customerList[$i][2] == 'NULL' || $customerList[$i][2] == NULL ? "" : ' - ' . $customerList[$i][2] ) . '</option>';               
        }

        $html .= '
        </select>
    </div>';

    // Ship To Address
    if(!$orderData) {
        $html .= '
        <div id="shipToAddressBox" class="poDetailsLi">
            <h2>Ship To ID</h2>
            <input id="shipToAddress" type="number" disabled>
        </div>
        ';
    }else {
        $customerShipTo = getShipTo($orderData[1]);
        
        $html .= '
        <div id="shipToAddressBox" class="poDetailsLi">
            <h2>Ship To ID</h2>
            <select id="shipToAddress" name="ShipToAddress" onchange="shipToChange(value, `' . $orderData[8] . '`);" required disabled>
                <option value="' . $orderData[2] . '" selected><p>' . $orderData[9] . '</p></option>';
            
                for($i = 0; $i < sizeof($customerShipTo); $i++){
                    $html .= '<option value=' . $customerShipTo[$i][1] . ' onchange="customerSelectChange()">' . $customerShipTo[$i][2] . '</option>';               
                }

            $html .= '</select>
        </div>';
    }

    // order date, required date, reference#, shipVia, notes
    $html .= '
    <div class="poDetailsLi">
        <h2>Order Date</h2>
        <input id="orderDate" value="' . ($orderData ? date('Y-m-d', strtotime($orderData[3])) : '') . '" 
        class="POBox" name="orderDate" type="date" style="width: 80% !important;" onkeydown="return false" ' . ($orderData ? 'disabled' : '') . '>
    </div>
    <div class="poDetailsLi">
        <h2>Required Date</h2>
        <input id="requiredDate" value="' . ($orderData ? date('Y-m-d', strtotime($orderData[4])) : '') . '"
        class="POBox" name="requiredDate" type="date" style="width: 80% !important;" onkeydown="return false" ' . ($orderData ? 'disabled' : '') . '>
    </div>
    <div class="poDetailsLi">
        <h2>Reference#</h2>
        <input id="refNum" type="text" value="' . ($orderData ? $orderData[7] : '') . '" ' . ($orderData ? 'disabled' : '') . '>
    </div>
    <div class="poDetailsLi">
        <h2>Ship Via</h2>
        <select id="shipVia" class="shipViaDrop" name="shipVia" required ' . ($orderData ? 'disabled' : '') . '>';

            if($orderData) {
                if($orderData[5] == 1) {
                    $html .= '<option disabled selected>Our Truck</option>';
                }elseif($orderData[5] == 2) {
                    $html .= '<option disabled selected>Common Carrier</option>';
                }else {
                    $html .= '<option disabled selected>UPS</option>';
                }
            }

            $html .= 
            '<option value="1">Our Truck</option>
            <option value="2">Common Carrier</option>
            <option value="3">UPS</option>
        </select>
    </div>
    <div class="poDetailsLi">
        <h2>Notes</h2>
        <input id="notes" type="text" ' . ($orderData ? $orderData[6] : '') . ' placeholder="Enter Notes..." ' . ($orderData ? 'disabled' : '') . '>
    </div>
    ';
            
    // Edit / Add Buttons
    if($orderData) {
        if($orderData[10] == 0) {
            $html .= '<div class="poDetailsLi">
            <a href="#" id="addPOBtn" onclick="editPO(\'' . $orderData[0] . '\')" title=""><span class="viewportBlueBtn">p</span></a>
            </div>';
        }
    }else {
        $html .= '
        <div id="loading">
            <a href="#" id="addPOBtn" onclick="addPOrder()" title="Add PO"><span class="viewportGreenBtn">v</span></a>
        </div>';
    }

    return $html;
}

//Build Order Items #3
function buildPOItems($orderData){
    $html = null;

    if($orderData == null) {
        $orderNumber = null;
    }else {
        $orderNumber = $orderData[0];
    }

    $orderItems = getOrderItem($orderNumber);
     
    if($orderData){
        
    $html = '
    <table class="poListTable">
        <tr>
            <th style="width: 5vw;">Status</th>
            <th>Sku#</th>
            <th>Desc</th>
            <th>Receipt Qty</th>
            <th>Ordered Qty</th>
            <th>Lot#</th>
            <th>Plt#</th>
            <th>Hold</th>
            <th style="width: 6vw;"></th>
        </tr>';

    for($i = 0; $i < sizeof($orderItems); $i++) {
        $html .= '
        <tr>
            <td>' . ($orderItems[$i][8] == 1 ? 'C' : '') . '</td>
            <td>' . $orderItems[$i][1] . '</td>
            <td>' . $orderItems[$i][4] . '</td>
            <td>' . $orderItems[$i][10] . '</td>
            <td id="orderedQty_' . $i . '">' . $orderItems[$i][2] . '</td>
            <td id="lotNum_' . $i . '">' . $orderItems[$i][7] . '</td>
            <td id="pltNum_' . $i . '">' . $orderItems[$i][6] . '</td>
            <td id="hold_' . $i . '">
                <input style="width: 15px; height: 15px;" onchange="flagItem(' . $orderItems[$i][5] . ', this, ' . $orderNumber . ', ' . $orderItems[$i][1] .', ' . (isset($orderData[10]) ? $orderData[10] : false) . ')" type="checkbox" id="holdFlag"' . ($orderItems[$i][9] == 1 ? 'checked' : '') . '>
            </td>
            <td class="orderLiBtns" id="liBtns_' . $i . '">
                <a id="editItem" href="#" onclick="editPOItem(' . $i . ', ' . $orderNumber . ', ' . $orderItems[$i][5] . ')" title="Edit Item"><span class="viewportBlueBtn" > p </span></a>
                <a id="deleteItem" href="#" onclick="deletePOItem(' . $orderItems[$i][5] . ', ' . $orderNumber . ', ' . (isset($orderData[10]) ? $orderData[10] : false) . ')" title="Delete Item"><span class="viewportRedBtn" > x </span></a>
            </td>
        </tr>';
    }


    $html .= '</table>';
    if($orderData[10] == 0) {
        $html .= '
        <div class="selectionPanel" id="newPOItemMenu">
            <ul>
                <li>Add Item <a href="#" onclick="buildNewPOItemMenu(' . $orderNumber . ')" title="newPOrderItem"><span> + </span></a></li>
            </ul>
        </div>';
    }
    
    }else{
        $html = null;
    }
    
       
    return $html;
}

//Build Current Order Items #4
function buildPOList($type){
    $html = null;
    
    $poData = getPOList($type);

    $customers = getCustomers();

    $html = '
    <div class="orderList">

        <h5>
            Order List
            <a href="/inventory/index.php?action=purchaseOrderMenu" title="Add Order"><i class="bi bi-plus-lg"></i></a>
        </h5>
        <a href="#" id="jumpBtn" onclick="jumpUp(`poList`);"><span>!</span></a>

        <div class="orderFilters">
            <div class="">
                <input type="text" id="searchPOBox" onkeyup="enterSubmitPOSearch(event);" placeholder="Search...">
                <select style="width: 7vw !important" name="customer" id="customerSelectInput">
                    <option value="null" selected>Select Customer...</option>';

                    for($i = 0; $i < sizeof($customers); $i++){
                        $html .= '<option value="' . $customers[$i][0] . '">' . $customers[$i][1] . ' - ' . $customers[$i][2] . '</option>';
                    }


                $html .= '</select>
            </div>

            <div class="dateFilter">
                <p>Receipt Date Range</p>
                <input type="date" id="startdate">
                <input type="date" id="enddate">
            </div>

            <a href="#" title="Clear Search" onclick="clearPOSearch()"><i class="bi bi-arrow-counterclockwise"></i></a>
            <a href="#" title="Search" onclick="poSearch()"><i class="bi bi-search"></i></a>
            
            <div class="headerCnt">
                <p id="poListCnt" class="orderCnt">' . sizeof($poData) . ' Items</p>
                <p id="poCloseOpenIcon">Open Orders<a href="#" title="Closed Orders" onclick="togglePOCloseOpen()"><i class="bi bi-circle"></i></a></p>
                <input id="closeOpenStatus" value="0" class="hide">
            </div>

            <div class="ordersHeader">
                <p>Order#</p>
                <p>Req Date</p>
            </div>

            <div class="orders" id="poList" onscroll="checkPOScroll();">';

            for($i = 0; $i < sizeof($poData); $i++){
                $html .= '<hr><p href="#" class="orderItem" onclick="openPO(' . $poData[$i][0] . ')">' . $poData[$i][0] . ' | ' . $poData[$i][1] . '</p>';
            }

            $html .= '</div>
            </div>
        </div>';
            
            // <div id="completedPOBox">
            //     <a href="#">
            //     <span onclick="openCPO()">!</span>
            //     </a>
            //     <p>Completed</p>
            // </div>

    return $html;

}

//Build Order Items #4
function buildBOM($bomData, $parentSku){
    $html = null;
//    $orderItems = getOrderItem($orderNumber);

             $html .= '<h1>Bill of Material
                            <a href="#" onclick="buildNewBomMenu(' . $parentSku . ')"><span id="skuInfoBtnBlue">+</span></a>
                        </h1>
                            <div class="grid" id ="overflowScroll">

                                <div class="unit one-fifth">Sku#</div>
                                <div class="unit two-fifths">Description</div>
                                <div class="unit one-fifth">Qty Per</div>

                                <input class="hide" id="skuExists" value="';
                                for($i = 0; $i < count($bomData); $i++){
                                    $html .= $bomData[$i][1] . ' ';
                                }
                                
                                $html .= '">

                                <br>
                                <hr>';

                                for($i = 0; $i < count($bomData); $i++){
                                    $html .=  
                                    '<div class="grid">
                                    <div class="unit one-fifth">' . $bomData[$i][1] . '</div>
                                    <div class="unit two-fifths">' . $bomData[$i][2] . '</div>
                                    <div class="unit one-fifth">' . (Float)$bomData[$i][4] . '</div>
                                    <div class="unit one-fifth">
                                        <a href="#" onclick="openBomItemDetails()"><span id="skuInfoBtnBlue">N</span></a>
                                        <a href="#" onclick="deleteBomItem('. $parentSku . ', ' . $bomData[$i][1] . ')"><span id="skuInfoBtnRed">x</span></a>
                                    </div>';
                                }
            $html .= '<div class="selectionPanel" id="newBOM"></div></div><hr>';
    
       
    return $html;
}


function buildShipToOptionBox($customerID){
    
    $shipOptionBoxHTML = null;
    
    $customerShipTo = getShipTo($customerID);

    $shipOptionBoxHTML = '
    <h2>Ship To ID</h2>
    <select id="shipToAddress" name="ShipToAddress" onchange="shipToChange(value, `' . $customerID . '`);" required>
        <option value="null" selected>Select Ship To</option>';
        
        for($i = 0; $i < sizeof($customerShipTo); $i++){
            $shipOptionBoxHTML .= '<option value=' . $customerShipTo[$i][1] . ' onchange="customerSelectChange()">' . $customerShipTo[$i][2] . '</option>';               
        }

    $shipOptionBoxHTML .= '</select>';
            
    return $shipOptionBoxHTML;

}






function buildToggleMenuHeader($title, $start, $end, $skuNumber, $type, $menu, $pltCnt = 0) {
    $html = null;

    if($type == 0){
        $text = "both";
    }elseif($type == 1) {
        $text = "comp";

        $QPR = getQPR($skuNumber);  
        $QPM = getCountTotals($skuNumber);

        if(empty($QPR)) {
            $QPR = '0';
        }else {
            $QPR = $QPR[1];
        }

        if(empty($QPM)) {
            $QPM = '0';
            $QAV = '0';
        }else {
            $QPM = $QPM[1];
            $QAV = $QPM[2] - $QPM[1] - $QPM[3];
        }

        $QOH = $QPM[2] == null ? '0' : $QPM[2];


    }elseif($type == 2) {
        $text = "kit";

        $QPM = getQPMKit($skuNumber);
        $QPM = empty($QPM) || $QPM == null ? '0' : $QPM[0];

        $QAV = "0";
        $QPR = "0";
        $QOH = $QPM;
    }

    $html = '

    <div class="grid">
        <div id="binLocationDateRange" class="unit two-fifths" disabled>
        <input value="' . $text . '" class="hide">
        <input value="' . $start . '" id="start" title="Start Date" type="date" class="binLocInput">
        <input value="' . $end . '" id="end" title="End Date" type="date" class="binLocInput"> 
        <a href="#" id="dateSelSubmit" title="Submit Date Range" onclick="changeSelection()"><span>i</span></a>

        </div>
        
        <div id="headerTitle" class="unit one-third"> <h1>' . $title . '</h1></div>
    
        <div id="binLocationTB" class="unit one-quarter">
            <a title="Open Bin Locations" href="#" onclick="changeSelection(4);"><span>#</span></a>
            <a title="Open WO" href="#" onclick="changeSelection(1);"><span>C</span></a>
            <a title="Open PO" href="#" onclick="changeSelection(2);"><span>g</span></a>
            <a title="Open Shipments" href="#" onclick="changeSelection(3);"><span>W</span></a>
        </div>
    </div>
    
    <div class="grid" id="binLocCnts">
        <div class="unit one-fifth">QPR: ' . $QPR . '</div>
        <div class="unit one-fifth">QOH: ' . $QOH . '</div>
        <div class="unit one-fifth">QAV: ' . $QAV . '</div>
        <div class="unit one-fifth">QPM: ' . $QPM . '</div>
        <div class="unit one-fifth">Plt Cnt: ' . $pltCnt . '</div>
    </div>';
    return $html;
}

function buildOpenWO($type, $skuNumber, $start, $end, $menu) {

    $html = null;

    if($type == 1) {
        $woData = getWODataComp($skuNumber, $start, $end);
        $title ="Work Order <a href='#' onclick='openWODetailsComp()'><span id='skuInfoBtnBlue'>I</span></a>";
    }elseif($type == 2) {
        $woData = getWODataKit($skuNumber, $start, $end);
        $title ="Work Order <a href='#' onclick='openWODetailsKit()'><span id='skuInfoBtnBlue'>I</span></a>";
    }

    $pltCnt = count($woData);

    $header = buildToggleMenuHeader($title, $start, $end, $skuNumber, $type, $menu, $pltCnt);
    
    $check = true;

    for($i = 1; $i < count($woData); $i++){
        if($woData[$i][6] == 0) {
            $check = false;
        }
    }

    $html .= $header . '

        <div class="grid">

        <div class="unit one-fifth">Schedule Date</div>
        <div class="unit one-fifth">WO#</div>
        <div class="unit one-fifth">Actual / Required Qty</div>
        <div class="unit one-fifth">Completion Date</div>
        <div class="unit one-fifth">Approved';



        // $html .= '<a title="' . (!$check ? 'Approve' : 'Un-approve') . ' All" style="cursor: pointer;" onclick="updateApproval(`';
        
        // $arr = array();

        // if(!$check) {
        //     for($i = 0; $i < count($woData); $i++){
        //         if($woData[$i][1] != null && $woData[$i][6] != 1) {
        //             array_push($arr, $woData[$i][1]);
        //         }
        //     }
        // }else {
        //     for($i = 0; $i < count($woData); $i++){
        //         if($woData[$i][1] != null && $woData[$i][6] != 0) {
        //             array_push($arr, $woData[$i][1]);
        //         }
        //     }
        // }

        // $html .= implode(', ', $arr) . '`, 1)"><span>' . (!$check ? '$' : '0') . '</span></a>';

        $html .= '</div>
        
        <hr><div class="overflowScroll" id="binLoclist">';

        for($i = 0; $i < count($woData); $i++){
            $html .=  
            '<div>
            <div class="unit one-fifth">' . $woData[$i][0] . '</div>
            <div class="unit one-fifth">' . $woData[$i][1] . '</div>
            <div class="unit one-fifth">' . $woData[$i][3] . '</div>
            <div class="unit one-fifth">' . $woData[$i][4] . '</div>';

            if($type == 2) {
                $html .= '<div class="unit one-fifth"><input type="checkbox" style="width: 16%; height: 1.2em;" id="approval" onchange="updateApproval(' . $woData[$i][1] . ')" ' . ($woData[$i][6] == 1 ? 'checked' : '') . '></div>';
            }else {
                $html .= '<div class="unit one-fifth"></div>';
            }
            
            $html .= '</div>';
        }
        $html .= '
        </div>
        </div>
        </div> ';

    return $html;
};

function buildOpenPO($type, $skuNumber, $start, $end, $menu){

    $html = null;

    if($type = 0){ //Both

    }elseif($type = 1) { //Comp

    }elseif($type = 2) {  //Kit

    }

    $poData = getPODataKit($skuNumber, $start, $end);

    $pltCnt = count($poData);

    $header = buildToggleMenuHeader("Purchase Order <a href='#' onclick='openPODetails()'><span id='skuInfoBtnBlue'>I</span></a>", $start, $end, $skuNumber, $type, $menu, $pltCnt);

    $html = $header . '

    <div class="grid" id="overflowScroll">
    
    <div class="unit one-fifth">Schedule Receipt Date</div>
    <div class="unit one-fifth">PO#</div>
    <div class="unit one-fifth">Qty Received / Qty Ordered</div>
    <div class="unit one-fifth">Receipt Date</div>
    <div class="unit one-fifth">Completed</div>
    <hr><div class="overflowScroll" id="binLoclist">';

        for($i = 0; $i < count($poData); $i++){
            $html .=  
            '<div>
            <div class="unit one-fifth">' . $poData[$i][0] . '</div>
            <div class="unit one-fifth">' . $poData[$i][1] . '</div>
            <div class="unit one-fifth">' . $poData[$i][2] . '</div>
            <div class="unit one-fifth">' . $poData[$i][3] . '</div>
            <div class="unit one-fifth"><input type="checkbox" value ="' . $poData[$i][4] . '" disabled></div>';
        
            $html .= '</div>';
        }
    $html .= '</div>';
    return $html;
};

function buildOpenShipments($type, $start, $end, $menu, $skuNumber){
    $header = buildToggleMenuHeader("Shipments", $start, $end, $skuNumber, $type, $menu);
    $html = null;
    $html = $header . '<hr><h1>This Is Shipments.</h1>';

    return $html;
};

function buildOpenBinLocations($type, $skuNumber, $start, $end, $menu) {





    error_reporting(E_ALL & ~E_NOTICE);




    $itemData = getItemData($skuNumber);
    $quantityAvailable = ($itemData[10] - $itemData[11]);
    $binTotals = currentInventoryTotals($skuNumber);
    $binData = getInventoryData($skuNumber);

    $pltCnt = count($binData);

    $header = buildToggleMenuHeader("Bin Locactions <a href='#' onclick='openInventoryDetails()'><span id='skuInfoBtnBlue'>I</span></a>", $start, $end, $skuNumber, $type, $menu, $pltCnt);
    $html = null;
    $html = $header . '



    <div class="grid">
        
        <div class="unit one-fifth">Q-S#</div>
        <div class="unit one-fifth">Bin</div>
        <div class="unit one-fifth">Qty</div>
        <div class="unit one-fifth">Received</div>
        <div class="unit one-fifth">Status</div>
        <hr><div class="overflowScroll" id="binLoclist">';

        for($i = 0; $i < count($binData); $i++){
            $html .=  
            '<div><div class="unit one-fifth"><a href="" onclick="openHistorical('  . (Float)$binData[$i][0] . ')">' . ($binData[$i][5] == 0 ? "" : "Q" . ' / ') . (Float)$binData[$i][0] . '</a></div>
            <div class="unit one-fifth">' . (!isset($binData[$i][1])?"-":$binData[$i][1]) . '</div>
            <div class="unit one-fifth">' . (!isset($binData[$i][2])?"-":$binData[$i][2]) . '</div>
            <div class="unit one-fifth">' . date('m-d-Y', strtotime($binData[$i][3])) . '</div>
            <div class="unit one-fifth">' . $binData[$i][4] . '</div></div>';
        }
        $html .=  '
        </div>
    </div>
    </div> ';

    return $html;
}



function buildWODetailsTableComp($woData) {
    
    $table = null;

    $table = '<h1>Work Order (Component)</h1>';
    

    // Needs a new table ******************************************************


    // $table .= '<table>
    //                 <tr>
    //                     <th>Quar</th>
    //                     <th>Serial#</th>
    //                     <th>State</th>
    //                     <th>Bin</th>
    //                     <th>PO#</th>
    //                     <th>LOT#</th>
    //                     <th>Exp</th>
    //                     <th>Plt#</th>
    //                     <th>WO</th>
    //                     <th>Qty</th>
    //                     <th>Age</th>
    //                 </tr>';
    
    //             for($i = 0; $i < sizeof($inventoryData); $i++){
    //                 if($inventoryData[$i][6] != null && $inventoryData[$i][6] != '0000-00-00'){
    //                     $expDate = date('m-d-Y', strtotime($inventoryData[$i][6]));
    //                 }else{
    //                     $expDate = null;
    //                 }
    //                 $age = dateDifference(date('Y-m-d'), date('Y-m-d', strtotime($inventoryData[$i][3])));

    //                 $table .= '<tr>
    //                             <td>' . ($inventoryData[$i][20] == 1 ? 'Q' : "") . '</td>
    //                             <td>' . ($inventoryData[$i][0] == null ? '*' :  $inventoryData[$i][0]) . '</td>
    //                             <td>' . ($inventoryData[$i][19] == null ? '*' :  $inventoryData[$i][19]) . '</td>
    //                             <td>' . ($inventoryData[$i][16] == null ? '*' :  $inventoryData[$i][16]) . '</td>
    //                             <td>' . ($inventoryData[$i][4] == null ? '*' :  $inventoryData[$i][4]) . '</td>
    //                             <td>' . ($inventoryData[$i][5] == null ? '*' :  $inventoryData[$i][5]) . '</td>
    //                             <td>' . ($expDate == null ? '*' :  $expDate) . '</td>
    //                             <td>' . ($inventoryData[$i][7] == null ? '*' :  $inventoryData[$i][7]) . '</td>
    //                             <td>' . ($inventoryData[$i][18] == null ? '*' :  $inventoryData[$i][18]) . '</td>
    //                             <td>' . ($inventoryData[$i][17] == null ? '*' :  $inventoryData[$i][17]) . '</td>
    //                             <td>' . $age . '</td>
    //                         </tr>';
    //                     }
    //                     $table .= '</table>';
    return $table;
}

function buildWODetailsTableKit($woData) {
    
    $table = null;

    $table = '<h1>Work Order (Kit)</h1>';
    

    // Needs a new table ******************************************************


    // $table .= '<table>
    //                 <tr>
    //                     <th>Quar</th>
    //                     <th>Serial#</th>
    //                     <th>State</th>
    //                     <th>Bin</th>
    //                     <th>PO#</th>
    //                     <th>LOT#</th>
    //                     <th>Exp</th>
    //                     <th>Plt#</th>
    //                     <th>WO</th>
    //                     <th>Qty</th>
    //                     <th>Age</th>
    //                 </tr>';
    
    //             for($i = 0; $i < sizeof($inventoryData); $i++){
    //                 if($inventoryData[$i][6] != null && $inventoryData[$i][6] != '0000-00-00'){
    //                     $expDate = date('m-d-Y', strtotime($inventoryData[$i][6]));
    //                 }else{
    //                     $expDate = null;
    //                 }
    //                 $age = dateDifference(date('Y-m-d'), date('Y-m-d', strtotime($inventoryData[$i][3])));

    //                 $table .= '<tr>
    //                             <td>' . ($inventoryData[$i][20] == 1 ? 'Q' : "") . '</td>
    //                             <td>' . ($inventoryData[$i][0] == null ? '*' :  $inventoryData[$i][0]) . '</td>
    //                             <td>' . ($inventoryData[$i][19] == null ? '*' :  $inventoryData[$i][19]) . '</td>
    //                             <td>' . ($inventoryData[$i][16] == null ? '*' :  $inventoryData[$i][16]) . '</td>
    //                             <td>' . ($inventoryData[$i][4] == null ? '*' :  $inventoryData[$i][4]) . '</td>
    //                             <td>' . ($inventoryData[$i][5] == null ? '*' :  $inventoryData[$i][5]) . '</td>
    //                             <td>' . ($expDate == null ? '*' :  $expDate) . '</td>
    //                             <td>' . ($inventoryData[$i][7] == null ? '*' :  $inventoryData[$i][7]) . '</td>
    //                             <td>' . ($inventoryData[$i][18] == null ? '*' :  $inventoryData[$i][18]) . '</td>
    //                             <td>' . ($inventoryData[$i][17] == null ? '*' :  $inventoryData[$i][17]) . '</td>
    //                             <td>' . $age . '</td>
    //                         </tr>';
    //                     }
    //                     $table .= '</table>';
    return $table;
}

function buildPODetailsTable($poData) {
    
    $table = null;

    $table = '<h1>Purchase Order</h1>';
    

    // Needs a new table ******************************************************



    // $table .= '<table>
    //                 <tr>
    //                     <th>Quar</th>
    //                     <th>Serial#</th>
    //                     <th>State</th>
    //                     <th>Bin</th>
    //                     <th>PO#</th>
    //                     <th>LOT#</th>
    //                     <th>Exp</th>
    //                     <th>Plt#</th>
    //                     <th>WO</th>
    //                     <th>Qty</th>
    //                     <th>Age</th>
    //                 </tr>';
    
    //             for($i = 0; $i < sizeof($inventoryData); $i++){
    //                 if($inventoryData[$i][6] != null && $inventoryData[$i][6] != '0000-00-00'){
    //                     $expDate = date('m-d-Y', strtotime($inventoryData[$i][6]));
    //                 }else{
    //                     $expDate = null;
    //                 }
    //                 $age = dateDifference(date('Y-m-d'), date('Y-m-d', strtotime($inventoryData[$i][3])));

    //                 $table .= '<tr>
    //                             <td>' . ($inventoryData[$i][20] == 1 ? 'Q' : "") . '</td>
    //                             <td>' . ($inventoryData[$i][0] == null ? '*' :  $inventoryData[$i][0]) . '</td>
    //                             <td>' . ($inventoryData[$i][19] == null ? '*' :  $inventoryData[$i][19]) . '</td>
    //                             <td>' . ($inventoryData[$i][16] == null ? '*' :  $inventoryData[$i][16]) . '</td>
    //                             <td>' . ($inventoryData[$i][4] == null ? '*' :  $inventoryData[$i][4]) . '</td>
    //                             <td>' . ($inventoryData[$i][5] == null ? '*' :  $inventoryData[$i][5]) . '</td>
    //                             <td>' . ($expDate == null ? '*' :  $expDate) . '</td>
    //                             <td>' . ($inventoryData[$i][7] == null ? '*' :  $inventoryData[$i][7]) . '</td>
    //                             <td>' . ($inventoryData[$i][18] == null ? '*' :  $inventoryData[$i][18]) . '</td>
    //                             <td>' . ($inventoryData[$i][17] == null ? '*' :  $inventoryData[$i][17]) . '</td>
    //                             <td>' . $age . '</td>
    //                         </tr>';
    //                     }
    //                     $table .= '</table>';
    return $table;
}

function rebuildPOList($poData) {
    $html = '';

    if(!isset($poData)) {
        $poData = getPOList(1);
    }

    for($i = 0; $i < sizeof($poData); $i++){
        $html .= '<hr><p href="#" class="orderItem" onclick="openPO(' . $poData[$i][0] . ')">' . $poData[$i][0] . ' | ' . $poData[$i][1] . '</p>';
    }
    
    return $html;

}

function buildReleaseHoldInput() {
    $html = '
    <div id="releaseHoldInput" class="releaseHoldInput">
        <h1>Search Lot Code:</h1>
        <input type="text" onkeyup="submitLotCodeSearch(event)" text-center placeholder="Enter Lot Code..." id="searchLotCode"><br>
        <a href="#" onclick="releaseHold(true)" id="buttonB">Search</a>
    </div>
    ';

    return $html;
}

function buildHoldList($lotCode, $held, $free) {

    $heldItem = getHeldItems($lotCode);

    if(!$heldItem) {
        $html = false;
    }else if(sizeof($heldItem) == 0) {
        $html = 'zero';
    }else {
        $html = '
        <div class="releaseHoldList">
            <input id="lotCode" class="hide" value="' . $lotCode . '">
            <h1>Items on Hold</h1>  
            <h1 style="margin-left: 0 !important;"><font color="blue">' . $lotCode . '</font></h1><br>
            <li>
                <div class="grid">
                    <div class="unit half">InventoryID</div>
                    <div class="unit half">SkuNumber</div>
                </div>
            </li><hr>
            <div class="releaseHoldScrollList">';
            
            for($i = 0; $i < sizeof($heldItem); $i++){
                $html .= '
                    <li>
                        <div class="grid">
                            <div class="unit half">' . $heldItem[$i][0] . '</div>
                            <div class="unit half">' . $heldItem[$i][1] . '</div>
                        </div>
                    </li>';
            }
            
            $html .= '</div>
            <br>
            <a href="#" onclick="releaseHold(null, null, true)" id="buttonB">Release ' . $held . ($held > 1 ? ' items' : ' item') . '</a>
            <br>';
            if($free != 0) {
                $html .= '<a href="#" onclick="holdAll(`' . $lotCode . '`)" id="buttonB">Place ' . $free . ($free > 1 ? ' items' : ' item') . ' on Hold</a>';
            } 
        $html .= '</div>';
    }

    return $html;
}

function buildReleaseResults($lotCode, $releaseCnt) {
    $html = '
    <div class="releaseHoldInput">
        <h1>Items Released</h1>
        <h3>' . $releaseCnt . ' items updated.</h3>
        <a href="#" onclick="closeOverlayGlobal()" id="buttonB">Ok</a><br>
        <a href="#" onclick="releaseHold()" id="buttonB">Another Lot Code</a>
    </div>
    ';
    return $html;
}

function buildHoldResultes($lotCode, $holdCnt) {
    $html = '
    <div class="releaseHoldInput">
        <h1>Items Held</h1>
        <h3>' . $holdCnt . ' items updated.</h3>
        <a href="#" onclick="closeOverlayGlobal()" id="buttonB">Ok</a><br>
        <a href="#" onclick="releaseHold()" id="buttonB">Another Lot Code</a>
    </div>
    ';
    return $html;
}

function buildNotesMenu($skuNumber) {

    $notes = getItemNotes($skuNumber);

    $notes = empty($notes) ? '' : $notes;

    $html = '<div id="itemNotesBox">
    <h1>Notes</h1>
    <input class="hide" id="skuNumber" value="' . $skuNumber . '">
    <textarea id="itemNotes" disabled>' . $notes . '</textarea>
    ';
    if($_SESSION['securityLevel'] > 2) {
        $html .= '<div id="editNotesBtnContainer"><a href="#" onclick="unlockItemNotes()" title="Edit Notes"><span id="skuInfoBtnBlue">p</span></a></div>';
    }
    $html .= '</div>';

    return $html;

}

function buildSkuSearchResultsList($itemSearch) {

    $itemList = getSkuList($itemSearch);

    $html = '';

    for($i = 0; $i < sizeof($itemList); $i++){
        $html .= '<a onclick="viewItem(' . $itemList[$i][0] . ')">' . $itemList[$i][0] . '</a>';
    }

    return $html;

}

function buildNoHolds($lotCode) {

    $free = getFreeItemCnt($lotCode)[0];

    $freeItem = getFreeItems($lotCode);

    $html = '
    <div class="releaseHoldList">
        <input id="lotCode" class="hide" value="' . $lotCode . '">
        <h1>No Items Held</h1>  
        <h1><font color="blue">' . $lotCode . '</font></h1><br>
        <li>
            <div class="grid">
                <div class="unit half">InventoryID</div>
                <div class="unit half">SkuNumber</div>
            </div>
        </li><hr>
        <div class="releaseHoldScrollList">';
        
        for($i = 0; $i < sizeof($freeItem); $i++){
            $html .= '
                <li>
                    <div class="grid">
                        <div class="unit half">' . $freeItem[$i][0] . '</div>
                        <div class="unit half">' . $freeItem[$i][1] . '</div>
                    </div>
                </li>';
        }
        
        $html .= '</div>
        <a href="#" onclick="holdAll(`' . $lotCode . '`)" id="buttonB">Place ' . $free . ($free > 1 ? ' items' : ' item') . ' on Hold</a>
    </div>
    ';

    return $html;
}

function buildBillablePallet($skuNumber) {
    $pricingList = getPricingList();
    
    // Get pricingListID from sku
    $pricingListID = getPricingListID($skuNumber);
    $pricingListID = !$pricingListID ? NULL : $pricingListID[0];
    $pricingDesc = $pricingListID == 1 ? getPricingDesc($skuNumber) : null;
    $pricingDesc = $pricingDesc == null ? null : $pricingDesc[0];

    $html = '
    <div class="billablePalletModal">
    <h1>Billable Pallet</h1>
    <select id="pricingSelect" onchange="pricingChange(' . $skuNumber . ')">
        <option value="null" selected>Select pricing...</option>';

        for ($i=0; $i < sizeof($pricingList); $i++) { 
            $html .= '
                <option value="' . $pricingList[$i][0] . '" ' . ( $pricingListID != null ? ( $pricingListID == $pricingList[$i][0] ? 'selected' : '' ) : '' ) . '>' . $pricingList[$i][1] . '</option>
            ';
        }

    $html .= '
    </select>
    <div id="loadingPHPriceReset"></div>

    <div id="pricingDescPH">
    ' . ( $pricingListID == 1 ? '<input id="pricingDesc" value="' . $pricingDesc . '" onchange="pricingDescUpdate()" class="pricingDescInput" maxlength="50" placeholder="Enter Pricing Desc...">' : '' ) . '
    </div>

    <div id="pricingDataPH">
    ';

    if($pricingListID != NULL) {
        $html .= buildPricingData($skuNumber, $pricingListID);
    }

    $html .= '
        </div>
    </div>
    ';

    return $html;
}

function buildPricingData($skuNumber = false, $pricingListID = false, $priceChange = false) {

    // Used on first load to check item for pricingListID
    $pricingListID = $pricingListID != null ? $pricingListID : getPricingListID($skuNumber);
    $pricingListID = $pricingListID == false ? $pricingListID[0] : $pricingListID;

    $html = '';

    // Get Priorities!
    $priorities = getPricingPriority($skuNumber);
    $priorities = $priorities != null ? $priorities[0] : null;
    $prices = $priorities == null && $priceChange == false ? null : getPrices($priorities, $pricingListID, ( $pricingListID == 1 ? $skuNumber : null ));

    // Get Pricing Types
    $pricing = getPricing();

    $html = '
    <input class="hide" id="originalPricingListID" value="0">
    <input class="hide" id="originalPricingDesc" value="0">
    <input class="hide" id="originalPriority1" value="0">
    <input class="hide" id="originalPriority2" value="0">
    <input class="hide" id="originalPriority3" value="0">
    <div id="priority1" class="pricingDataItem">
        <select id="pInput1" onchange="updatePriority(1, ' . $skuNumber . ')">
            <option disabled ' . ( $priorities[0] == null ? 'selected' : '' ) . '>Select Priority 1</option>';
            for ($i=0; $i < sizeof($pricing); $i++) { 
                $html .= '<option value="' . $pricing[$i][0] . '" ' . ( $pricing[$i][0] == $priorities[0] ? 'selected' : '' ) . '>' . $pricing[$i][2] . '</option>';
            }
        $html .= '
        </select>
        $<input id="priceInput1" type="number" value="' . ( $prices != null ? number_format((float)$prices[0][0], 2, '.', '') : '' ) . '" ' . ( $pricingListID == 1 ? 'onchange="priceChange(1)"' : 'disabled' ) . '>
        <div id="loadingPH1"></div>
    </div>
    <div id="priority2" class="pricingDataItem">
        <select id="pInput2" onchange="updatePriority(2, ' . $skuNumber . ')">
            <option disabled ' . ( $priorities[1] == null ? 'selected' : '' ) . '>Select Priority 2</option>';
            for ($i=0; $i < sizeof($pricing); $i++) { 
                $html .= '<option value="' . $pricing[$i][0] . '"' . ( $pricing[$i][0] == $priorities[1] ? 'selected' : '' ) . '>' . $pricing[$i][2] . '</option>';
            }
        $html .= '
        </select>
        $<input id="priceInput2" type="number" value="' . ( $priorities[1] == null ? '' : ( $prices != null ? number_format((float)$prices[1][0], 2, '.', '') : '' ) ) . '" ' . ( $pricingListID == 1 ? 'onchange="priceChange(2)"' : 'disabled' ) . '>
        <div id="loadingPH2"></div>
        <div id="clearPriorityPH2"></div>
    </div>
    <div id="priority3" class="pricingDataItem">
        <select id="pInput3" onchange="updatePriority(3, ' . $skuNumber . ')">
            <option disabled ' . ( $priorities[2] == null ? 'selected' : '' ) . '>Select Priority 3</option>';
            for ($i=0; $i < sizeof($pricing); $i++) { 
                $html .= '<option value="' . $pricing[$i][0] . '"' . ( $pricing[$i][0] == $priorities[2] ? 'selected' : '' ) . '>' . $pricing[$i][2] . '</option>';
            }
        $html .= '
        </select>
        $<input id="priceInput3" type="number" value="' . ( $priorities[1] == null ? number_format((float)$prices[1][0], 2, '.', '') : ( $prices != null ? number_format((float)$prices[2][0], 2, '.', '') : '' ) ) . '" ' . ( $pricingListID == 1 ? 'onchange="priceChange(3)"' : 'disabled' ) . '>
        <div id="loadingPH3"></div>
        <div id="clearPriorityPH3"></div>
    </div>
    ';


    $html .= '
    <a class="bs-btn btn-blue hide" id="pricingDataSubmitBtn" onclick="updatePricingData(' . $skuNumber . ')">Update</a>
    </div>';
    return $html;
}

function buildHistoricalChart($serial) {

    // $data = getHistoricalDate($serial);

    $html = $serial;

    echo $html;
}