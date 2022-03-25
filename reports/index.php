<?php
session_start();
date_default_timezone_set('America/Los_Angeles');
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$ajax = false;
$message = null;
$action = 'reports';
$subAction = null;
$reportName = null;
$date1 = null;
$date2 = null;
$employeeID = null;
$dateRange = null;
$minQty = null;
$report1 = 'Daily Shipping Report';
$report2 = 'Inventory Disrepancy Report';
$report3 = 'Pre-Billable Pallet Report';
$report4 = 'Billable Pallet Report (By Batch)';
$report5 = 'New Sku Number Report';
$report6 = 'Time Study History';
$report7 = 'Open Work Order Report';
$report8 = 'Whos In?';
$report9 = 'Job Costing Report/WIP Report';
$report10 = 'Inventory History';
$report11 = 'Forklift Activity Report';
$report12 = 'Pick Pack Report';
$report13 = 'Temp Agency Labor Audit';

if (isset($_POST['action'])) {
 $action = $_POST['action'];
} elseif (isset($_GET['action'])) {
 $action = $_GET['action'];
}

if (isset($_POST['date1'])) {
 $date1 = $_POST['date1'];
} elseif (isset($_GET['date1'])) {
 $date1 = $_GET['date1'];
}

if (isset($_POST['date2'])) {
 $date2 = $_POST['date2'];
} elseif (isset($_GET['date2'])) {
 $date2 = $_GET['date2'];
}

if (isset($_POST['lastDateRange'])) {
 $lastDateRange = $_POST['lastDateRange'];
} elseif (isset($_GET['lastDateRange'])) {
 $lastDateRange = $_GET['lastDateRange'];
}

if (isset($_POST['skuNumber'])) {
 $skuNumber = $_POST['skuNumber'];
} elseif (isset($_GET['skuNumber'])) {
 $skuNumber = $_GET['skuNumber'];
}

require 'model.php';
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
}elseif($action == 'getCustomerList') {
    $customers = getCustomers();
    $cats = getProductCodes();
    $html = '
    <select id="customerID" name="customerID" required>
    <option value="" disabled selected>Select Customer</option>
    <option value="-1">Select All Customers</option>';
        for($i = 0; $i < sizeof($customers); $i++){
            $html .= '<option value=' . $customers[$i][0] . '>' . $customers[$i][1] . '</option>';
        }
    $html .= '</select>
    <select id="catSelection" name="cats" required>
    <option value="" disabled selected>Select Product Code</option>';
        for($i = 0; $i < sizeof($cats); $i++){
            $html .= '<option value=' . $cats[$i][0] . '>' . $cats[$i][0] . '</option>';
        }
    $html .= '</select>';
    echo $html;
    $ajax = true;
}elseif($action == 'generateInv') {
    $customerID = filterString($_GET['customerID']);
    $reportType = filterNumber($_GET['reportType']);
    $hold = filterNumber($_GET['hold']);
    $quarantine = filterNumber($_GET['quarantine']);
    $skuNumber = filterNumber($_GET['skuNumber']);
    $productCode = filterString($_GET['productCode']);

    $data = buildInvReport($customerID, $reportType, $hold, $quarantine, $skuNumber, $productCode);

    echo $data;
    $ajax = true;
}elseif($action == 'report0') {
    // Inventory Report

    $i = filterNumber($_GET['i']);
    $detailed = isset($_GET['detailed']) ? filterNumber($_GET['detailed']) : false;
    $companyID = isset($_GET['companyID']) ? filterNumber($_GET['companyID']) : false;

    $hold = isset($_GET['hold']) ? filterNumber($_GET['hold']) : false;
    $quarantine = isset($_GET['quarantine']) ? filterNumber($_GET['quarantine']) : false;

    $tranArr[0] = $i == 4 ? '90vw' : false;
   
    $tranArr[1] = buildInventoryReport($i, $detailed, $companyID, $hold, $quarantine);
    
    $data = json_encode($tranArr);

    echo $data;
    $ajax = true;
}elseif($action == 'report1'){
    $subAction = 'reportSingleDate';
    $action = 'reports';
    $reportName = 'dailyShipping';
}elseif($action == 'report2'){
    $subAction = 'chooseCategory';
    $action = 'reports';
    $reportName = 'inventoryDiscrepancy';
    $categoryList = getProductCodes();
}elseif($action == 'report3'){
    $subAction = 'reportCompanySelect';
    $billableCustomers = getBillableCustomers();
    $action = 'reports';
    $reportName = 'billablePallet';
}elseif($action == 'report4'){
    $subAction = 'reportCompanySelect';
    $billableVendors = getBillableVendors();
    $billableCustomers = getBillableCustomers();
    $action = 'reports';
    $reportName = 'billablePalletBatch';
}elseif($action == 'report5'){
    $subAction = 'reportDateRange';
    $action = 'reports';
    $reportName = 'newSkuNumberReport';
}elseif($action == 'report6'){
    $subAction = 'selectSkuNumber';
    $kitSkuNumber = getKitSkuNumbers();
    $action = 'reports';
    $reportName = 'timeStudyReport';
}elseif($action == 'report7'){
    $subAction = 'reportSingleDate2';
    $action = 'reports';
    $reportName = 'openWorkOrder';
}elseif($action == 'report9'){
    $subAction = 'selectJobNumber';
    $action = 'reports';
    $reportName = 'simpleJobClockReport';
}elseif($action == 'report10'){
    $subAction = 'historyOptions';
    $action = 'reports';
    $reportName = 'inventoryHistory';
}elseif($action == 'report11'){
    $subAction = 'forkliftActivityOptions';
    $action = 'reports';
    
    //Get inputted data
    $activeUsers = getActiveUser();
    $reportName = 'forkliftActivity';
}elseif($action == 'report12'){
    $subAction = 'pickPackOptions';
    $action = 'reports';
    $reportName = 'pickPackReport';
}elseif($action == 'report13'){
    $subAction = 'reportSingleDate';
    $action = 'reports';
    $reportName = 'tempAgencyAudit';
}elseif($action == 'exportInvBakReport') {

    $batchNumber = $_SESSION['batchNumber'];

// Build Headers

    // Lot Tracing Report
    $header1 = array(array("Lot Code", $batchNumber));
    // SkuNumber Data
    $header2 = array(array("skuNumber", "partNumber", "DESC1", "DESC2", "UNIT"));
    // Receiver Header
    $header3 = array(array("purchaseOrderID", "orderDate", "requiredDate", "reference", "purchaseOrderItemsID", "skuNumber", "orderQty", "lineItem", "palletNumber", "lotCode", "complete"));
    // Receiving
    $header4 = array(array("receiptID", "date_received", "purchaseOrderItemsID", "receiptQty", "complete"));
    // Raw Inventory Units
    $header5 = array(array("inventoryID", "skuNumber", "receiptDate", "lotCode", "expirationDate", "creationDate", "receiptID", "quarantine", "combineToID", "shipFromID"));
    // Raw Inventory Units History
    $header6 = array(array("inventoryID", "receiptDate", "lotCode", "expirationDate", "active", "type", "userName", "creationDate", "value", "refNumber", "quantity"));
    // Finished Goods Units
    $header7 = array(array("inventoryID", "skuNumber", "companyID", "receiptDate", "poNumber", "lotCode", "expirationDate", "palletNumber", "salesOrder", "active", "createdBy", "creationDate", "modifiedBy", "modifiedDate", "phyInventoryUnitStatusID", "vendorID", "workOrderNumber", "receiptID", "quarantine", "combineToID", "splitFromID", "shippingID"));
    // Finished Goods Unit History
    $header8 = array(array("inventoryID", "skuNumber", "companyID", "receiptDate", "lotCode", "expirationDate", "active", "type", "userName", "creationDate", "value", "refNumber", "quantity"));
    // Shipped Finished Goods
    $header9 = array(array("ORDER_NO", "LAST_CHANGED", "TAXCODE", "VNCODE", "WHSNCODE", "CARRIER_TRUCK_CD", "CSCODE", "ITEM_NO", "SPEC_PART_NO", "SHIP_TO_NO", "CUST_PO_NO", "ORDER_DATE", "DUE_DATE", "DUE_DATE_CD", "QTY_ORDERED", "QTY_TO_MFG", "QTY_SHIPPED", "QTY_INVOICED", "MSF_INVOICED", "PLT_NO", "COMPLETION_FLG", "PRICING_METH", "USER_ID", "COMMENTS", "JOB_NUMBER", "YIELD_QTY", "PROJ_NO"));

    $reportArray = null;

// Build Data

    // Get Lot Code aka Batch Number

    $reportArray = array_merge(array(array("Lot Tracing Report")), $header1);

    // Get RecepitID from Inventory with Lot Code

    $receipts = getReceipts($batchNumber);

    if($receipts != false) {

        $reportArray = array_merge($reportArray, array(array("")), array(array("Receiving")), $header4);

        $poiIds = array();
        // Loop through distict RecepitID (s)
        for($i = 0; $i < sizeof($receipts); $i++){
        
            if($receipts[$i][0] != null) {
                $receiptData = getReceiptData($receipts[$i][0]);
                $reportArray = array_merge($reportArray, $receiptData);
                $poiIds[] = $receiptData[0][2];
            }

        }

        $reportArray = array_merge($reportArray, array(array("")), array(array("Receiver Header")), $header3);

        $poItems = array();
        for($i = 0; $i < sizeof($poiIds); $i++){
            if($poiIds[$i] != null) {
                // JOIN purchaseOrder to purchaseOrderItemsID by purchaseOrderItemsID (LOOP through multiple & seperate POItems by matching purchaseOrder)
                $poItem = getPurchaseOrdersPlusItems($poiIds[$i]);
                $poSkus[] = $poItem[0][5];
                $poItems[] = $poItem;
                $reportArray = array_merge($reportArray, $poItem);
            }
        }

        $reportArray = array_merge($reportArray, array(array("")), array(array("SkuNumber Data")), $header2);

        for($i = 0; $i < sizeof($poSkus); $i++){
            $skuData = getReportSkuData($poSkus[$i]);
            $reportArray = array_merge($reportArray, $skuData);
        }


        $reportArray = array_merge($reportArray, array(array("")), array(array("Raw Inventory Units")), $header5);
        for($i = 0; $i < sizeof($receipts); $i++){
            $rawInvUnit = getRawInv($batchNumber, $receipts[$i][0]);
            $reportArray = array_merge($reportArray, $rawInvUnit);
        }

        $reportArray = array_merge($reportArray, array(array("")), array(array("Raw Inventory Units History")), $header6);
        $rawInvHistory = getRawInvHistory($batchNumber);

        $woNumbers = array();
        for($i = 0; $i < sizeof($rawInvHistory); $i++){
            if(!in_array($rawInvHistory[$i][9], $woNumbers)) {
                $woNumbers[] = $rawInvHistory[$i][9];
            }
            
        }
        $reportArray = array_merge($reportArray, $rawInvHistory);
        

        $reportArray = array_merge($reportArray, array(array("")), array(array("Finished Goods Units")), $header7);

        $fgLotCodes = array();
        for($i = 0; $i < sizeof($woNumbers); $i++){
            if($woNumbers[$i] != null) {
                $finishedGoods = getFinishedGoods($woNumbers[$i]);
                $reportArray = array_merge($reportArray, $finishedGoods);
                // Get workOrderNumber from Inventory History (Existing Report)
                $fgLotCodes[] = $finishedGoods[0][5];
            }
        }

        // Get workOrderItems with workOrderNumber

        $reportArray = array_merge($reportArray, array(array("")), array(array("Finished Goods Unit History")), $header8);
        for($i = 0; $i < sizeof($fgLotCodes); $i++){
            if($fgLotCodes[$i] != null) {
                $finishedGoodHistory = getFinishedGoodHistory($fgLotCodes[$i]);
                $reportArray = array_merge($reportArray, $finishedGoodHistory);
            }
        }

        // $reportArray = array_merge($reportArray, array(array("")), array(array("Shipped Finished Goods")), $header9);
        // Get Shipped Finished Goods...? with workOrderNumber

    }


    // var_dump($fgLotCodes);

    //convert to csv
    download_send_headers("lotTracing_export_" . $batchNumber . ".csv");
    echo array2csv($reportArray);
    die();

}elseif($action == 'exportInvBakReportDetailed') {

// Build Header
    $header = array(array("inventoryID", "companyName", "skuNumber", "DESC1", "DESC2", "binName", "stockingUnit", "Qty", "batchNumber", "palletNumber", "receiptDate", "lastStatusChange", "stateName", "age", "quarantine", "reason"));
    
    $reportData = $_SESSION['reportData'];

    $reportArray = array_merge($header, $reportData);

    //convert to csv
    download_send_headers("invHistoryDetailed_export_" . date("Y-m-d") . ".csv");
    echo array2csv($reportArray);
    die();

}elseif($action == 'exportInvBakReportDiscrepancy') {

    // Build Header
    $header = array(array("skuNumber", "DESC", "stockingUnit", "partNumber", "custRefNumber", "customerName", "palletCnt", "Lot Code", "BIN", "ERP", "Discrepancy"));
    
    $reportData = $_SESSION['reportData'];

    $reportArray = array_merge($header, $reportData);

    //convert to csv
    download_send_headers("invDiscrepancyReport_export_" . date("Y-m-d") . ".csv");
    echo array2csv($reportArray);
    die();

}elseif($action == 'exportInvBakReportSummary') {

    // Build Header
    $header = array(array("skuNumber", "DESC", "stockingUnit", "partNumber", "custRefNumber", "companyName", "palletCnt", "Qty", "subTotal", "batchNumber", "expDate", "palletNumber", "Note", "poNumber", "age", "reason"));

    $reportData = $_SESSION['reportData']; 

    $reportArray = array_merge($header, $reportData);

    //convert to csv
    download_send_headers("invHistorySummary_export_" . date("Y-m-d") . ".csv");
    echo array2csv($reportArray);
    die();

}elseif($action == 'dailyShipping'){
    //status for shipped pallets
    $status = 3;
    
    //get report data
    $action = 'reports';
    $subAction = 'dailyShipping';
    $reportData = getDailyShippingReport($date1, $status);
    $reportSummary = getDailyShippingSummary($date1, $status);

}elseif($action == ''){
    //status for shipped pallets
    $status = 3;
    
    //get report data
    $action = 'reports';
    $subAction = 'dailyShipping';
    $reportData = getDailyShippingReport($date1, $status);
    $reportSummary = getDailyShippingSummary($date1, $status);

}elseif($action == 'customReportList') {
    
    $html = buildReportList();

    echo $html;

    $ajax = true;

}elseif($action == 'updateAutoReportVars') {

    $id = filterNumber($_GET['id']);

    $html = buildCustomReportVars($id);

    echo $html;

    $ajax = true;

}elseif($action == 'buildCustomReport') {

    $id = filterNumber($_GET['id']);
    $varData = json_decode($_GET['vars']);
    $varNames = explode(',', filterString($_GET['varNames']));;

    $sql = getCustomQuerySql($id);

    if(!$sql) {
        $data = false;
        $data2 = false;
    }else {
        $data = $sql[0] == null ? false : buildCustomReport($sql[0], $varData, $varNames);
        $data2 = $sql[1] == null ? false : buildCustomReport($sql[1], $varData, $varNames);
        $_SESSION['reportData1'] = $data != null ?  $data : null;
        $_SESSION['reportData2'] = $data2 != null ?  $data2 : null;
    }

    $html = !$data2 ? array_to_table($data) : array_to_table($data, $data2);

    echo $html;

    $ajax = true;

}elseif($action == 'billablePallet'){
    //status for shipped pallets
    $customerID = filterNumber($_POST['customerID']);
    
    //get report data
    $action = 'reports';
    $subAction = 'billablePallet';
    $reportData = getBillablePalletReport($customerID);
    $reportSkuTotals = getBillableSkuTotals($customerID);
}elseif($action == 'billablePalletBatch'){
    //status for shipped pallets
    $customerID = filterNumber($_POST['customerID']);
    
    //get report data
    $action = 'reports';
    $subAction = 'billablePalletBatch';
    $reportData = getBillablePalletBatchReport($customerID);
    $reportExportData = getBillablePalletBatchExport($customerID);
    $reportExportData2 = getBillablePalletBatchExport2($customerID);
    $reportSkuTotals = getBillableSkuBatchTotals($customerID);
    $reportSubTotals = getBillableSubTotals($customerID);
    $reportTotals = getBillableTotals($customerID);
    
    $_SESSION['reportData'] = null;
    $_SESSION['reportData'] = $reportExportData2;
}elseif($action == 'newSkuNumberReport'){
    //get report data
    $dateRange1 = filterString($_POST['date1']);
    $dateRange2 = filterString($_POST['date2']);
    $action = 'reports';
    $subAction = 'newSkuNumberReport';
    $reportData = getNewSkuNumberData($dateRange1, $dateRange2);
}elseif($action == 'inventoryDiscrepancy'){
    $includeZeros = false;
    $category = array_values($_POST);
    if($category[0] == 'includeZeros'){
        $includeZeros = true;
    }
    
    //get report data
    $action = 'reports';
    $subAction = 'inventoryDiscrepancy';
    $reportData = getInventoryDiscrepancy($category, $includeZeros);
}elseif($action == 'inventoryDiscrepancyCustomerOwned'){
    //get report data
    $action = 'reports';
    $subAction = 'inventoryDiscrepancyCustomerOwned';
    $reportData = getInventoryDiscrepancyCust();
}elseif($action == 'openWorkOrder'){
    //get report data
    $action = 'reports';
    $subAction = 'openWO';
    $workOrderOnly = null;
    
    if(isset($_POST['openWorkOrders'])){
        $workOrderOnly = $_POST['openWorkOrders'];
    }
    
    $date1 = $_POST['date1'];
    $date2 = $_POST['date2'];
    
    if(!$date1){
        $date1 = '1980-1-1';
    }
    
    if(!$date2){
        $date2 = '2100-1-1';
    }
    
    if($workOrderOnly){
        $reportData = getOpenWO($date1, $date2, true);
    }else{
        $reportData = getOpenWO($date1, $date2, false);
    }
    
    $_SESSION['reportData'] = null;
    $_SESSION['reportData'] = $reportData;

}elseif($action == 'report8'){
    //get report data
    $action = 'reports';
    $subAction = 'whosIn';
    $reportData = getWhosIn(false);
    $temps = false;
}elseif($action == 'report8temps') {
    //get report data
    $action = 'reports';
    $subAction = 'whosIn';
    $reportData = getWhosIn(true);
    $temps = true;
}elseif($action == 'timeStudyReport'){
    $dateRange = 0;
    $date1 = null;
    $date2 = null;
    //Get inputted data
    $crewLeaders = getCrewLeaders();
    
    //Get SKU data
    $skuData = getSkuData($skuNumber);
    
    //Create Return String
    $_SESSION['returnStr'] = '<a href="/reports/index.php?action=' . $action . '&skuNumber=' . $skuNumber . '" title="Go Back to Reports" id="exit"><span>x</span></a>';
    
    //get report data
    $action = 'reports';
    $subAction = 'timeStudyReport';
    $reportData = getTimeStudyData($skuNumber);
    $reportTotals = getTimeStudyTotals($skuNumber);
    
}elseif($action == 'filterReport' || $action == 'activeWO' || $action == 'deactiveWO'){
    
    if(isset($_GET['workOrderNumber'])){
        $workOrderNumber = filterNumber($_GET['workOrderNumber']);
    }
    
    //Toggle Active
    if($action == 'activeWO'){
        $updateActive = activateWO($workOrderNumber);
    }elseif($action == 'deactiveWO'){
        $updateActive = deactivateWO($workOrderNumber);
    }
    
    //Get filter values
    if(isset($_POST['dateRange'])){
        $dateRange = filterNumber($_POST['dateRange']);
    }else{
        $dateRange = 0;
    }
    
    $skuData = getSkuData($skuNumber);
    //$date1 = filterString($_POST['date1']);
    //$date2 = filterString($_POST['date2']);
    //$lastDateRange = filterNumber($_POST['lastDateRange']);

    //Get Filtered Crew Leader
    if(isset($_POST['crewLeader'])){
        $employeeID = filterNumber($_POST['crewLeader']);
    }else{
        $employeeID = null;
    }
    
    //Get Filtered Quantity
    if(isset($_POST['minQty'])){
        $minQty = filterNumber($_POST['minQty']);
    }else{
        $minQty = null;
    }
    
    //Get filter dates values
    if($lastDateRange == $dateRange){
        $dateRange = -1;
    }elseif($dateRange > 0 && $lastDateRange != $dateRange){
        $date1 = date('Y-m-d', strtotime(' -' . $dateRange . ' months'));
        $date2 = date('Y-m-d');
    }elseif($dateRange == 0 && $lastDateRange != $dateRange){
        $date1 = null;
        $date2 = null;
    }elseif($dateRange == -1 && $lastDateRange != $dateRange){
        $date1 = date('Y-m-d');
        $date2 = null;
    }
    
    //Get inputted data
    $crewLeaders = getCrewLeaders();
    

    //Create Return String
    $_SESSION['returnStr'] = '<a href="/reports/index.php?action=' . $action . '&skuNumber=' . $skuNumber . '" title="Go Back to Reports" id="exit"><span>x</span></a>';
    
    //get report data
    $action = 'reports';
    $subAction = 'timeStudyReport';
    $reportData = getTimeStudyData($skuNumber, $date1, $date2, $employeeID, $minQty);
    $reportTotals = getTimeStudyTotals($skuNumber, $date1, $date2, $employeeID, $minQty);

}elseif($action == 'openJobReport'){
    $jobNumber = null;
    $wip = null;
    $jobNumber = filterString($_POST['jobNumber']);
    $date1 = filterString($_POST['date1']);
    $date2 = filterString($_POST['date2']);
    if(isset($_POST['wipReport'])){
        $wip = $_POST['wipReport'];
    }
    

    if($jobNumber){
        //get Single report data
        $action = 'reports';
        $subAction = 'simpleJobClockReport';
        $jobPunchData = getJobClockPunches($jobNumber);
        $deptPunchTotals = getDeptTotals($jobNumber);
        $jobPunchTotal = getJobTotalHours($jobNumber);
        $materialUsage = getMaterialUsage($jobNumber);
    }elseif($date1 && $date2 && !$wip){
        //get Summary report data
        $action = 'reports';
        $subAction = 'simpleJobClockSummary';

        $departmentTotals = getDepartmentTotals($date1, $date2);
        $workOrderTotal = getWorkOrderTotal($date1, $date2);
        
        $_SESSION['reportData'] = null;
        $_SESSION['reportData'] = $departmentTotals;
        
    }elseif($date1 && $date2 && $wip){
        //get Summary report data
        $action = 'reports';
        $subAction = 'wipReport';

        $missingPunches = getMissingPunches($date1, $date2);
        $wipHours = getWipHours($date1, $date2);
        $eusageWIP = getEusageWIP($date1, $date2);
        
        $_SESSION['reportData1'] = null;
        $_SESSION['reportData2'] = null;
        $_SESSION['reportData3'] = null;
        $_SESSION['reportData1'] = $missingPunches;
        $_SESSION['reportData2'] = $wipHours;
        $_SESSION['reportData3'] = $eusageWIP;
        
    }
    
}elseif($action == 'history'){
    $skuNumber = filterNumber($_POST['skuNumber']);
    $serialNumber = filterNumber($_POST['serialNumber']);
    $batchNumber = filterString($_POST['batchNumber']);
    if($skuNumber > 0){
        $historyData = getInventoryHistoryBySKU($skuNumber);
        $headerTitle = " By SKU: " . $skuNumber;
    }else if($serialNumber > 0){
        $historyData = getInventoryHistoryBySerial($serialNumber);
        $headerTitle = " By Serial Number: " . $serialNumber;
    }else if(isset($batchNumber)){
        $historyData = getInventoryHistoryByBatch($batchNumber);
        $headerTitle = " By Batch Number " . $batchNumber;
    }
    $_SESSION['reportData'] = null;
    $_SESSION['reportData'] = $historyData;
    $_SESSION['batchNumber'] = $batchNumber;

    $subAction = 'inventoryHistoryBySku';
    $action = 'reports';
}elseif($action == 'pickPackHistory'){
    $lotCode = filterString($_POST['lotCode']);
    $searchOrders = filterString($_POST['searchOrders']);
    $date1 = filterString($_POST['date1']);
    $date2 = filterString($_POST['date2']);
    
    if($lotCode){
        $lotCodeData = getLotCodeData($lotCode);
        $headerTitle = " Lot Code Report: " . $lotCode;
        $subAction = 'pickPackHistoryByLot';
    }else if($searchOrders){
        $orderData = getPickPackOrder($searchOrders);
        $headerTitle = " Pick Pack Order: " . $searchOrders;
        $subAction = 'pickPackHistoryByOrder';
    }else if($date1 && $date2){
        $orderData = getPickPackRange($date1, $date2);
        $headerTitle = " Pick Pack Orders: " . $date1 . " To " . $date2;
        $subAction = 'pickPackHistoryByDate';
    }
    
    
    $action = 'reports';
}elseif($action == 'exportReport10'){
    $reportData = $_SESSION['reportData'];
    $columnHeaders = array(array("inventoryID", "skuNumber", "companyID", "receiptDate", "batchNumber", "expirationDate", "active", "type", "userName", "creationDate", "value"));
    
    $reportArray = array_merge($columnHeaders, $reportData);
    //convert to csv
    download_send_headers("report10_export_" . date("Y-m-d") . ".csv");
    echo array2csv($reportArray);
    die();
}elseif($action == 'exportReport4'){
    $reportData = $_SESSION['reportData'];
    $columnHeaders = array(array("sku_Number", "DESC1", "Unit", "part_Number", "company_Name", "pallet_Count", "inventory_Quantity", "sub_Total", "batch_Number", "expiration_Date", "pallet_Number", "Notes"));
    
    $reportArray = array_merge($columnHeaders, $reportData);
    //convert to csv
    download_send_headers("report4_export_" . date("Y-m-d") . ".csv");
    echo array2csv($reportArray);
    die();
}elseif($action == 'exportReport7'){
    
    $reportData = $_SESSION['reportData'];
    
    $columnHeaders = array(array("Work_Order_#", "SKU#", "DESC", "Schedule_Date", "Qty", "Time_Est", "Hot"));
    
    $reportArray = array_merge($columnHeaders, $reportData);
    //convert to csv
    download_send_headers("Open_WO_Report_" . date("Y-m-d") . ".csv");
    echo array2csv($reportArray);
    die();
    
}elseif($action == 'exportReport7'){
    
    $reportData = $_SESSION['reportData'];
    
    $columnHeaders = array(array("Work_Order_#", "SKU#", "DESC", "Schedule_Date", "Qty", "Time_Est", "Hot"));
    
    $reportArray = array_merge($columnHeaders, $reportData);
    //convert to csv
    download_send_headers("Open_WO_Report_" . date("Y-m-d") . ".csv");
    echo array2csv($reportArray);
    die();
    
}elseif($action == 'exportReport9'){
    
    $reportData = $_SESSION['reportData'];
    
    $columnHeaders = array(array("Job_#", "Job_Title", "Total_Hrs", "Prototyping", "Setup", "Processing", "Assembly", "Stain-Paint", "Sanding", "PackOut", "Ship-Rec", "Crating", "Other"));
    
    $reportArray = array_merge($columnHeaders, $reportData);
    //convert to csv
    download_send_headers("Job_Clock_Summary_" . date("Y-m-d") . ".csv");
    echo array2csv($reportArray);
    die();
    
}elseif($action == 'exportReport12b'){
    
    $reportData1 = $_SESSION['reportData1'];
    $reportData2 = $_SESSION['reportData2'];
    $reportData3 = $_SESSION['reportData3'];
    
    $columnHeaders1 = array(array("Job_#", "Start", "End"));
    $columnHeaders2 = array(array("Job_#", "Description", "Total_Time"));
    $columnHeaders3 = array(array("Job_#", "SKU_#", "Description", "Unit", "Usage_Qty"));
    
    $reportData = array_merge($columnHeaders1, $reportData1);
    $reportData = array_merge($reportData, $columnHeaders2);
    $reportData = array_merge($reportData, $reportData2);
    $reportData = array_merge($reportData, $columnHeaders3);
    $reportData = array_merge($reportData, $reportData3);
    
    $_SESSION['reportData1'] = null;
    $_SESSION['reportData2'] = null;
    $_SESSION['reportData3'] = null;
    
    //convert to csv
    download_send_headers("WIP_Report_" . date("Y-m-d") . ".csv");
    echo array2csv($reportData);
    
    die();
    
}elseif($action == 'exportCustomReport'){
    
    $data1 = $_SESSION['reportData1'];
    $data2 = $_SESSION['reportData2'] == null ? false : $_SESSION['reportData2'];

    $data1 = array_merge($data1, array(array(""))); // Creates gap in between the arrays

    $_SESSION['reportData1'] = null;
    $_SESSION['reportData2'] = null;
    
    //convert to csv
    download_send_headers("customReport" . date("Y-m-d") . ".csv");
    echo array2csv($data1, $data2);
    
    die();
    
}elseif($action == 'forkliftActivity'){
    $startDate = filterString($_POST['date1']);
    $endDate = filterString($_POST['date2']);
    $userID = filterNumber($_POST['userID']);
    $reportOptions = filterNumber($_POST['reportOptions']);
    
    if($reportOptions == 1){
        $forkLiftActivitySummary = getForkLiftActivitySummary($startDate, $endDate, $userID);
        $subAction = 'forkliftActivitySummary';
    }elseif($reportOptions == 2){
        $forkLiftActivitySummary = getForkLiftActivitySummary($startDate, $endDate, $userID);
        $forkLiftActivityByDay = getForkLiftActivityByDay($startDate, $endDate, $userID);
        $subAction = 'forkliftActivityByDay';
    }
   
   $action = 'reports';
}elseif($action == 'tempAgencyAudit'){
    $subAction = 'tempAgencyAudit';
    
    $selectedDate = filterString($_POST['date1']);
    $payPeriod = getPayPeriod($selectedDate);
    $startOfWeek = $payPeriod[0];
    $endOfWeek = $payPeriod[6];
    $userID = filterNumber($_SESSION['userID']);

    //$forkLiftActivitySummary = getDescrepancyData($startOfWeek, $endOfWeek);
    $employeesWithHours = getEmployeesWithHours($startOfWeek, $endOfWeek);
    $employeesWithHoursData = getEmployeesWithHoursData($startOfWeek, $endOfWeek);
    $tempSummary = buildTimeTempSummary($employeesWithHours, $selectedDate, $endOfWeek, $employeesWithHoursData);
    
    
   
   $action = 'reports';
}elseif($action == 'autoReport') {
    $r = filterNumber($_GET['report']);

    $start = filterString($_GET['start']);
    $end = filterString($_GET['end']);
    $skuNumber = filterString($_GET['skuNumber']);
    $partNumber = filterString($_GET['partNumber']);

    $table = getInvDetailedReport();

    $_SESSION['reportData'] = $table;

    echo array_to_table($table);

}elseif($action == 'exportAutoReport') {

    $reportArray = $_SESSION['reportData'];

    // convert to csv
    download_send_headers("customReport_export_" . date("Y-m-d") . ".csv");
    echo array2csv($reportArray);
    die();
}

if(!$ajax){
    include 'view.php'; 
}

function array_to_table($data1, $data2 = false) {   
    $html = '
    <h1>Custom Report</h1>
    <br>';

    if(!empty($data1)) {
        $html .= '<a onclick="exportAutoReport()" class="bs-btn btn-blue">Export</a>';
    }else {
        $html .= '<h3 style="margin-left: 0 !important">No data found...</h3>';
    }
    
    $html .= '<div class="arrayToTableScroll">
    <table>';

    // Table header
        foreach ($data1[0] as $clave=>$fila) {
            $html .= "<th>".$clave."</th>";
        }

    // Table body
        foreach ($data1 as $fila) {
            $html .= "<tr>";
            foreach ($fila as $elemento) {
                    $html .= "<td>".$elemento."</td>";
            } 
            $html .= "</tr>";
        } 
    $html .= "</table>";

    if($data2 != false) {
        $html .= '
        <table>';

        // Table header
        foreach ($data2[0] as $clave=>$fila) {
            $html .= "<th>".$clave."</th>";
        }

        // Table body
        foreach ($data2 as $fila) {
            $html .= "<tr>";
            foreach ($fila as $elemento) {
                $html .= "<td>".$elemento."</td>";
            } 
            $html .= "</tr>";
        } 
        $html .= "</table></div>";
    }else {
        $html .= '</div>';
    }

    return $html;
}

function buildReportList() {

    $queries = getCustomQueries();
    
    $html = '
    <div class="autoReport">
      <h1>Custom Reports</h1>
      <select id="report" onchange="updateAutoReportVars();">
        <option selected disabled>Select Report...</option>';

        for($i = 0; $i < sizeof($queries); $i++) {
            $html .= '<option value="' . $queries[$i][0] . '">' .  $queries[$i][1] . '</option>';
        }

    $html .= '</select>
    <div id="vars"></div>
    </div>
    ';

    return $html;
}

function buildCustomReportVars($id) {

    $var = getCustomQueryVars($id);

    $varNames = [];
    for($i = 0; $i < sizeof($var); $i++) {
        array_push($varNames, $var[$i][2]);
    }  
    $varNames = implode(', ', $varNames);

    $html = '<input id="varCnt" value="' . sizeof($var) . '" class="hide">';
    $html .= '<input id="varNames" value="' . $varNames . '" class="hide">';

    for($i = 0; $i < sizeof($var); $i++) {
        $html .= '<div class="varLi">';
        $html .= '<label>' . $var[$i][2] . '</label><input id="varNum' . $i . '" type="';   

        if($var[$i][3] == 'string') {
            $html .= 'text';
        }elseif($var[$i][3] == 'date') {
            $html .= 'date';
        }elseif($var[$i][3] == 'boolean') {
            $html .= 'checkbox';
        }

        $html .= '"></div>';
    }

    $html .= '<a class="bs-btn btn-blue" onclick="submitCustomReport()">SUBMIT</a>';

    return $html;
}

function buildInvReport($customerID = false, $reportType, $hold, $quarantine, $skuNumber = false, $productCode = false) {
    $hold = $hold == 0 ? null : $hold;
    $quarantine = $quarantine == 0 ? null : $quarantine;
    $customerID = $customerID == -1 || !$customerID ? 0 : $customerID;

    if($reportType == 0) {
        $title = 'Inventory Summary Report';
        $reportData = getInvSummaryReport($customerID, $hold, $quarantine, $skuNumber, $productCode);
    }elseif($reportType == 1) {
        $title = 'Inventory Detailed Report';
        $reportData = getInvDetailedReport($customerID, $hold, $quarantine, $skuNumber, $productCode);
    }elseif($reportType == 2) {
        $title = 'Inventory Discrepancy Report';
        $reportData = getInvDiscrepancyReport($customerID, $hold, $quarantine, $skuNumber, $productCode);
    }

    $html = '
    <div>
    <div class="w-100">
    <h1>' . $title . '</h1>
    <a href="#" class="invRepExportBtn" onclick="exportReport(' . $reportType . ')"><i class="bi bi-box-arrow-in-down"></i></a>
    </div>
    <div class="inventoryHistoryReportScroll">
    ';

    if($reportType == 1) {
        // Detailed Report
        $html .= '
        <table>
            <tr>
                <th>inventoryID</th>
                <th>companyName</th>
                <th>skuNumber</th>

                <th>DESC1</th>
                <th>DESC2</th>

                <th>binName</th>
                <th>stockingUnit</th>
                <th>Qty</th>

                <th>batchNumber</th>
                <th>palletNumber</th>

                <th>receiptDate</th>
                <th>lastStatusChange</th>
                <th>stateName</th>

                <th>age</th>
                <th>quarantine</th>
                <th>reason</th>
            </tr>
            ';
            for($i = 0; $i < sizeof($reportData); $i++){
                $html .= '
            <tr>';
                for($j = 0; $j <  count(current($reportData)); $j++) {
                    $html .= '<td>' . $reportData[$i][$j] . '</td>';
                }
            $html .= '
            </tr>';
            }
        $html .= '
        </table>';
    }elseif($reportType == 0) {
        // Summary Report
        $html .= '
        <table>
            <tr>
                <th>skuNumber</th>
                <th>DESC</th>
                <th>stockingUnit</th>
                <td>partNumber</th>
                <th>custRefNumber</th>
                <th>companyName</th>
                <th>palletCnt</th>
                <th>Lot Code</th>
                <th>Qty</th>
                <th>subTotal</th>
            </tr>';

            for($i = 0; $i < sizeof($reportData); $i++){
                $html .= '
            <tr>';
                for($j = 0; $j <  count(current($reportData)); $j++) {
                    $html .= '<td>' . $reportData[$i][$j] . '</td>';
                }
            $html .= '
            </tr>';
            }

            $html .= '
        </table>
        ';
    }elseif($reportType == 2) {
        // Discrepancy Report
        $html .= '
        <table>
            <tr>
                <th>skuNumber</th>
                <th>DESC</th>
                <th>stockingUnit</th>
                <th>partNumber</th>
                <th>custRefNumber</th>
                <th>companyName</th>
                <th>palletCnt</th>
                <th>Lot Code</th>
                <th>BIN/ERP</th>
                <th>Discrepancy</th>
            </tr>
            ';

            for($i = 0; $i < sizeof($reportData); $i++){

                $html .= '
                <tr>
                    <td>' . $reportData[$i][0] . '</td>
                    <td>' . $reportData[$i][1] . '</td>
                    <td>' . $reportData[$i][2] . '</td>
                    <td>' . $reportData[$i][3] . '</td>
                    <td>' . $reportData[$i][4] . '</td>
                    <td>' . $reportData[$i][5] . '</td>
                    <td>' . $reportData[$i][6] . '</td>
                    <td>' . $reportData[$i][7] . '</td>
                    <td>' . $reportData[$i][8] . ' / ' . (float)$reportData[$i][9] . '</td>
                    <td>' . (float)$reportData[$i][10] . '</td>
                </tr>
                ';

            }
            
            $html .= '
        </table>
        ';
    }

    $_SESSION['reportData'] = $reportData;

    $html .= '
        </div>
    </div>';

    return $html;
}
