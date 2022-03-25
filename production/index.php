<?php
session_start();

require 'model.php';
require $_SERVER['DOCUMENT_ROOT'] . '/library/library3.php';

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$ajax = false;
$searchTerm = null;
$message = null;
$action = 'production';
$subAction = null;
$workOrderNumber = null;
$missingWO = null;
$subComponent = getConstant('subComponent');
date_default_timezone_set('America/Los_Angeles');

$missingWO = getMissingWorkorderDetails();
if(!$missingWO == null){
    for($i = 0; $i < sizeof($missingWO); $i++){
        $userID = $_SESSION['userID'];
        insertMissingWO($missingWO[$i][0], $userID);
    }
}

if (isset($_SESSION['productionScheduleCurrentView'])){
    $currentView = $_SESSION['productionScheduleCurrentView'];
}

if (!isset($_SESSION['searchState'])) {
    $_SESSION['searchState'] = 0;
}

if(!isset($currentView)) {
    $currentView = null;
}

//Check if AJAX request
if(isset($_GET['ajax'])){
    $ajax = true;
}else{
    $ajax = false;
}

//Get Action from Post or Get
if (isset($_POST['action'])) {
 $action = $_POST['action'];
} elseif (isset($_GET['action'])) {
 $action = $_GET['action'];
}

//Get Work Order #
if (isset($_POST['workOrderNumber'])) {
 $workOrderNumber = filterString($_POST['workOrderNumber']);
} elseif (isset($_GET['workOrderNumber'])) {
 $workOrderNumber = filterString($_GET['workOrderNumber']);
}
//Get Message
if (isset($_POST['message'])) {
 $message = $_POST['message'];
} elseif (isset($_GET['message'])) {
 $message = $_GET['message'];
}

//Reroute action depending on date changes
if (isset($_GET['date'])) {
    $selectedDate = $_GET['date'];
}elseif (isset($_POST['selectedDate'])) {
    $selectedDate = filterString($_POST['selectedDate']);
    $action = 'productionSchedule';
}else{
    $selectedDate = date("Y-m-d");
}
//assign selected date to session
$_SESSION['selectedDate'] = $selectedDate;

//Reroute action if an attempt to updateCrewCount
if (isset($_POST['updateNotes'])) {
    $action = 'updateNotes';
}

//Reroute action if an attempt to updateCrewCount
if (isset($_POST['updateCrewCount'])) {
    $action = 'updateCrewCount';
}

//Reroute action if an attempt to updateCrewLeader
if (isset($_POST['updateCrewLeader'])) {
    $action = 'updateCrewLeader';
}

//Reroute action if an attempt to updateSchedule
if (isset($_POST['updateScheduleDate'])) {
    $action = 'updateScheduleDate';
}

if(isset($_SESSION['return']) && ($_SESSION['return'] == 'returnToProduction')){
    if(isset($_SERVER['scheduleSearchTerm'])){
        $searchTerm = $_SERVER['scheduleSearchTerm'];
        $_SERVER['scheduleSearchTerm'] = null;
    }
    if(isset($_SESSION['productionSelectedDate'])){
        $selectedDate = $_SESSION['productionSelectedDate'];
        $_SESSION['productionSelectedDate'] = null;
    }
    if(isset($_SESSION['workOrderNumber'])){
        $workOrderNumber = $_SESSION['workOrderNumber'];
        $_SESSION['workOrderNumber'] = null;
    }
    $_SESSION['return'] =  null;
    $action = 'return';
}

if(!($_SESSION['securityLevel'] >= 2)){
    header('location: /logIn/index.php');
    $_SESSION['message'] = 'Access Denied';
}elseif(timedOut()){
    if($ajax){
        echo 'timedOut';
    }else{
        header('location: /logIn/index.php');
    }
    
}elseif($_SESSION['loggedIn'] != true){
    header('location: /logIn/index.php');
}elseif($action == 'logOut'){
    logOut();
    header('location: /logIn/index.php');
}elseif($action == 'completeWO'){
    $action = 'production';
    $subAction = 'completeWO';
    
}elseif($action == 'completeWOEx'){

    if(isset($_GET['woNo'])) {
        $workOrderNumber = filterString($_GET['woNo']);
    }else {
        $workOrderNumber = filterString($_POST['workOrderNumber']);
    }
    // echo $workOrderNumber;
    // $workOrderNumber = filterString(isset($_POST['workOrderNumber']));

    $workOrderData = getWorkOrder($workOrderNumber);
    $workOrderData = !$workOrderData ? false : $workOrderData[0]; 
    
    if($workOrderData){
        $action = 'production';

        // $subAction = 'completeWOEx';
        //Get WO data
        $employeeList = getCrewLeaders();
        $timeStudyInSecs = $workOrderData[25];

        if($timeStudyInSecs != null) {
            $timeStudyH = floor($timeStudyInSecs / 3600);
            $timeStudyM = floor(($timeStudyInSecs / 60) % 60);
            $timeStudyM = $timeStudyM < 10 ? '0' . $timeStudyM : $timeStudyM;
            $timeStudyS = $timeStudyInSecs % 60;
        }else {
            $timeStudyH = false;
            $timeStudyM = false;
            $timeStudyS = false;
        }

        $html = include $_SERVER['DOCUMENT_ROOT'] . '/module/completeWOEx.php';
    }else{
        $action = 'production';
        $subAction = 'completeWO';
        $message = 'work order #: "' . $workOrderNumber . '" not found.';
    }

    return $html;
    $ajax = true;

}elseif($action == 'searchWOConfirm') {
    $searchTerm = filterString($_GET['input']);
    $completionStatus = filterNumber($_GET['completionStatus']);
    $data = buildWOConfirmationTable($searchTerm, $completionStatus);
    echo $data;
    $ajax = true;

}elseif($action == 'updateWO'){

    $workOrderNumber = filterString($_GET['workOrderNumber']);
    $crewLeader = filterNumber($_GET['crewLeader']);
    $completionDate = date('Y-m-d', strtotime(filterString($_GET['completionDate'])));
    $crewCount = filterNumber($_GET['crewCount']);
    $quantity = filterNumber($_GET['quantity']);
    $curTimeStamp = date('Y-m-d H:i:s');
    $userID = $_SESSION['userID'];
    $completionTime = filterNumber($_GET['elapsedSeconds']);
    // $hours = (filterNumber($_GET['elapsedHrs']) == null ? 0 : filterNumber($_GET['elapsedHrs']));
    // $mins = (filterNumber($_GET['elapsedMins']) == null ? 0 : filterNumber($_GET['elapsedMins']));
    // $completionTime = $hours . ':' . $mins . ':' . 00;

    // echo $workOrderNumber;
    // echo $completionTime;
    // echo $completionDate;
    // echo $crewCount;
    // echo $quantity;
    // echo $curTimeStamp;
    // echo $userID;
    // echo $workOrderNumber;
    // echo $mins;

    $updateWorkOrder = updateWorkOrder($workOrderNumber
                                        , $crewLeader
                                        , $completionDate
                                        , $crewCount
                                        , $quantity
                                        , $completionTime
                                        , $curTimeStamp
                                        , $userID);


    if($updateWorkOrder){
        // $action = 'production';
        // $subAction = null;
        // $message = '<h3 class="fadeout" id="message">Update Successful.</h3>';
    }else{
        //redirect and add message
        $action = 'production';
        $subAction = 'completeWOEx';
        // $message = '<h3 class="fadeout" id="message">Failed to complete work order.</h3>';
        
        //Get WO data
        $employeeList = getCrewLeaders();
        $workOrderNumber = filterString($_GET['workOrderNumber']);
        $workOrderData = getWorkOrder($workOrderNumber);
        $elapsedTime = $workOrderData[0][8];
        $elapsedHour = explode(':', $elapsedTime)[0];
        $elapsedMin = substr($elapsedTime, strpos($elapsedTime, ':')+ 1, 2);
        
    }
    $ajax = true;
    
    echo $updateWorkOrder;

    // echo !$workOrderData || $workOrderData == null ? 0 : 1;

    // echo $message;
}elseif($action == 'invRollForward') {
    $parentSku = filterString($_GET['parentSku']);
    $qtyPer = filterString($_GET['qtyPer']);
    $childSku = filterString($_GET['childSku']);

    $html = buildInvRollForward($parentSku, $qtyPer, $childSku);

    echo $html;

    $ajax = true;

}elseif($action == 'checkSearchType') {

    $searchTerm = filterString($_GET['searchTerm']);

    $r = checkSearchType($searchTerm);

    $tranArr = null;

    if($r != false || $r != null) {

        $tranArr[0] = 0;
        $tranArr[1] = $r[0][0];
        $tranArr[2] = $r[0][1];
        $tranArr[2] = $r[0][2];

    }else {
        $tranArr[0] = 1;
    }

    $data = json_encode($tranArr);
    echo $data;

    $ajax = true;

}elseif($action == 'changeSearchSetting') {
    $state = filterString($_GET['state']);
    $selectedDate = filterString($_GET['selectedDate']);

    if($_SESSION['woMenu'] == 1) { // Calander Menu

        if(!isset($_SESSION['woType'])) {
            $prodSchedule = getAllOpenWO(); // Error OR WO
        }elseif($_SESSION['woType'] == 1) {
            $prodSchedule = getUnscheduledWO($selectedDate); // Kit
        }elseif($_SESSION['woType'] == 0) {
            $prodSchedule = getCompWO($selectedDate); // Component
        }

        $_SESSION['woType'] = null;
    }else {

        $prodSchedule = getProdSchedule($selectedDate); // Main Schedule Menu

    }

    $_SESSION['searchState'] = $state;

    $html = buildProdItemsList($prodSchedule, $message);

    echo $html;

}elseif($action == 'sJobClock'){
    //Get Current Clocked In
        $currentCrewCount = getCurrentCrewCount();
        $currentAssignedCrew = getCurrentAssignedCrew();
        $sjcHeaderView = buildSjcHeader($currentCrewCount, $currentAssignedCrew);
        $date = date("Y-m-d");
        
    //Get current jobs
        $currentWO = getCurrentJobs();
        $workOrderListView = buildWorkOrderList($currentWO);
    
    
}elseif($action == 'addNewJob'){
    //get Job Number and other data
    $jobNumber = filterString($_GET['jobNumber']);
    $newDate = date("Y-m-d H:i:s");
    $userID = $_SESSION['userID'];
    
    //Validate workorder
    $workOrderData = getWorkOrder($jobNumber);
    
    //Send data back via AJAX
    $ajax = true;
    $tranArr = null;
    
    if($workOrderData){
        if(!$workOrderData[0][10]){
            //Schedule Job
            $sjcCurrentJobs = updateScheduleDate($newDate, $jobNumber);
            $updateCrewLeader = updateCrewLeader($userID, $jobNumber);

            //Update Current Jobs View
            $date = date("Y-m-d");
            $currentWO = getCurrentJobs();
            $workOrderListView = buildWorkOrderList($currentWO);

            //build list of punches
            $jobPunches = getJobPunches($jobNumber);
            $jobPunchesView = buildJobPunchView($jobPunches);
            $tranArr[0] = $workOrderListView;
            $tranArr[1] = $jobPunchesView;
        }else{
            $tranArr[0] = -1;
        }
    }else{
        $tranArr[0] = 0;
    }

    $data = json_encode($tranArr);

    echo $data;
}elseif($action == 'startJobTimeView'){
    //get Job Number and other data
    $jobNumber = filterString($_GET['jobNumber']);
    $departments = getDepartments();
    
    //build job select view
    $sjcCurrentJobs = buildStartJobTime($jobNumber, $departments);
    
    
    //Send data back via AJAX
    $ajax = true;
    $tranArr = null;
    $tranArr[0] = $sjcCurrentJobs;

    $data = json_encode($tranArr);

    echo $data;
}elseif($action == 'changeShift') {

    $shift = filterString($_GET['shift']);
    $workOrderNumber = filterString($_GET['workOrderNumber']);
    $selectedDate = filterString($_GET['selectedDate']);
    $reload = filterNumber($_GET['reload']);

    $shiftChange = changeShift($shift, $workOrderNumber);

    if($reload) {
        if(!$shiftChange) {
            $message = 'Shift Change Failed';
        }else  {
            $message = 'Shift Change Successful';
        }
    
        $prodSchedule = getProdSchedule($selectedDate);
        $employeeList = getCrewLeaders();
        if (!$prodSchedule){
            $message = 'No scheduled Work Orders';  
        }
        $html = buildProdItemsList($prodSchedule, $message);
    
        echo $html;
    }else {
        echo $shiftChange != null ? 1 : 0;;
    }
   
    $ajax = true;

}elseif($action == 'changePriority') {

    $priority = filterString($_GET['priority']);
    $workOrderNumber = filterString($_GET['workOrderNumber']);
    $selectedDate = filterString($_GET['selectedDate']);

    $priorityChange = changePriority($priority, $workOrderNumber);

    if(!$priorityChange) {
        $message = 'Priority Change Failed';
    }else  {
        $message = 'Priority Change Successful';
    }

    $prodSchedule = getProdSchedule($selectedDate);
    $employeeList = getCrewLeaders();
    if (!$prodSchedule){
        $message = 'No scheduled Work Orders';  
    }
    $html = buildProdItemsList($prodSchedule, $message);

    echo $html;

    $ajax = true;

}elseif($action == 'startJobTime'){
    
    //get Job Number and other data
    $jobNumber = filterString($_GET['jobNumber']);
    $department = filterNumber($_GET['department']);
    $crewCount = filterNumber($_GET['crewCount']);
    $startTime = date("Y-m-d H:i:s", strtotime($_GET['startTime']));
    $userID = $_SESSION['userID'];
    
    //insert new job punch
    $sjcCurrentJobs = insertStartTime($jobNumber, $department, $crewCount, $startTime, $userID);
    
    //build list of punches
    $jobPunches = getJobPunches($jobNumber);
    $jobPunchesView = buildJobPunchView($jobPunches);
    
    //Get current jobs
    $currentWO = getCurrentJobs();
    $workOrderListView = buildWorkOrderList($currentWO);
    
    //Get Current Job Counts
    //Get Current Clocked In
    $currentCrewCount = getCurrentCrewCount();
    $currentAssignedCrew = getCurrentAssignedCrew();
    $sjcHeaderView = buildSjcHeader($currentCrewCount, $currentAssignedCrew);
    
    //Send data back via AJAX
    $ajax = true;
    $tranArr = null;
    $tranArr[0] = $jobPunchesView;
    $tranArr[1] = $workOrderListView;
    $tranArr[2] = $sjcHeaderView[0] . ' ' . $sjcHeaderView[1] . ' ' . $sjcHeaderView[2];
    
    $data = json_encode($tranArr);

    echo $data;
}elseif($action == 'displayJobPunches'){
    $jobNumber = filterString($_GET['jobNumber']);
    //build list of punches
    $jobPunches = getJobPunches($jobNumber);
    $jobPunchesView = buildJobPunchView($jobPunches);
    
    //Send data back via AJAX
    $ajax = true;
    $tranArr = null;
    $tranArr[0] = $jobPunchesView;

    $data = json_encode($tranArr);

    echo $data;
 
}elseif($action == 'deleteJobPunch'){
    $jobPunchID = filterString($_GET['jobPunchID']);
    $jobNumber = filterString($_GET['jobNumber']);
    
    //delete job punch
    $deleteJobPunch = deleteJobPunch($jobPunchID);   
    
    //build list of punches
    $jobPunches = getJobPunches($jobNumber);
    $jobPunchesView = buildJobPunchView($jobPunches);
    
    //Get current jobs
    $currentWO = getCurrentJobs();
    $workOrderListView = buildWorkOrderList($currentWO);
    
    //Send data back via AJAX
    $ajax = true;
    $tranArr = null;
    $tranArr[0] = $jobPunchesView;
    $tranArr[1] = $workOrderListView;

    $data = json_encode($tranArr);

    echo $data;
}elseif($action == 'addStopPunch'){
    $jobPunchID = filterNumber($_GET['jobPunchID']);
    $jobNumber = filterString($_GET['jobNumber']);
    $stopPunch = filterString($_GET['stopPunch']);
    $userID = $_SESSION['userID'];
    
    //Update Punch to add stop punch
    $updateJobPunch = jobPunchOut($jobPunchID, $stopPunch, $userID);   
    
    //build list of punches
    $jobPunches = getJobPunches($jobNumber);
    $jobPunchesView = buildJobPunchView($jobPunches);
    
    //Get current jobs
    $currentWO = getCurrentJobs();
    $workOrderListView = buildWorkOrderList($currentWO);
    
    //Send data back via AJAX
    $ajax = true;
    $tranArr = null;
    $tranArr[0] = $jobPunchesView;
    $tranArr[1] = $workOrderListView;

    $data = json_encode($tranArr);

    echo $data;
}elseif($action == 'newCompleteWO'){
    
    $woNo = filterString($_GET['woNo']);
    $compQty = filterString($_GET['compQty']);
    $skuNumber = filterString($_GET['skuNumber']);
    $desc = filterString($_GET['desc']);
    
    $completeWOHTML = buildCompleteWOMenu($woNo, $compQty, $skuNumber, $desc, false);

    $ajax = true;

    echo $completeWOHTML;
    
}elseif($action == 'reviewCompleteWO'){
    
    $woNo = filterString($_GET['woNo']);
    $compQty = filterString($_GET['compQty']);
    $skuNumber = filterString($_GET['skuNumber']);
    $desc = filterString($_GET['desc']);
    
    $completeWOHTML = buildCompleteWOMenu($woNo,$compQty, $skuNumber, $desc, true);
    
    echo $completeWOHTML;
    
}elseif($action == 'submitWOConfirm') {
    
    $woNo = filterString($_GET['woNo']);
    $compQty = filterString($_GET['compQty']);

    $html = buildSubmitWOConfirm($woNo, $compQty);

    echo $html;

    $ajax = true;

}elseif($action == 'cancelWOSubmitConfirm') {

    $woNo = filterString($_GET['woNo']);
    $compQty = filterString($_GET['compQty']);

    $html = buildCancelSubmitWOConfirm($woNo, $compQty);

    echo $html;

    $ajax = true;
    
}elseif($action == 'toggleLunch'){
    $jobPunchID = filterNumber($_GET['jobPunchID']);
    $userID = $_SESSION['userID'];
    
    //Get Job Punch
    $jobPunch = getJobPunch($jobPunchID);
    $toggle = $jobPunch[6];
    
    if($toggle){
        $toggleLunch = updateLunch($jobPunchID, 0, $userID);
        $toggle = 0;
    }else{
        $toggleLunch = updateLunch($jobPunchID, 1, $userID);
        $toggle = 1;
    }
    
    //Send data back via AJAX
    $ajax = true;

    $data = $toggle;

    echo $data;
}elseif($action == 'completeJob'){
    
    $workOrderNumber = filterString($_GET['jobNumber']);
    
    //Get Work Order Data
    $workOrderData = getWorkOrder($workOrderNumber);
    
    //Validate to make sure no null punches
    //$nullPunches = getOpenJobPunches();
    
    //Get total hours
    $totalHours = getJobTotalHours($workOrderNumber)[1];
    
    //Get Crew Leader
    $workOrderData[0][7] = $_SESSION['userID'];
    
    //Set Cur Date
    $workOrderData[0][5] = date('Y-m-d');
    
    //Set full Qty
    $workOrderData[0][6] = $workOrderData[0][3];
    
    if($workOrderData){
        $action = 'production';
        $subAction = 'completeWOEx';

        //Get WO data
        $employeeList = getCrewLeaders();
        $elapsedTime = $totalHours;
        $elapsedHour = explode(':', $elapsedTime)[0];
        $elapsedMin = substr($elapsedTime, strpos($elapsedTime, ':')+ 1, 2);
    }else{
        $action = 'production';
        $subAction = 'completeWO';
        $message = 'work order #: "' . $workOrderNumber . '" not found.';
    }
    
}

if($_SESSION['securityLevel'] < 3){
    if($_SESSION['securityLevel'] == 2){
        //Skip to Display Page
    }else{
        header('location: /logIn/index.php');
    $_SESSION['message'] = 'Access Denied';
    }
}elseif($action == 'selectWorkOrder'){
    $action = 'production';
    $subAction = 'selectWorkOrder';
    
}elseif($action == 'autoReschedule') {

    // Get Auto Reschedule Constant Setting
    $auto = getConstant('prodAutoReschedule')['constantValue'];

    // Update Past Dues If Enabled
    $return = $auto ? updatePastDues() : -1;

    // Return results
    echo $return != -1 ? 'auto reschedule enabled' : 'auto reschedule not enabled';
    $ajax = true;

}elseif($action == 'productionSchedule'){

    if(isset($_GET['today'])) {
        $today = filterString($_GET['today']);
        if($today) {
            $selectedDate = date('Y-m-d');
        }
    }else {
        if(isset($_SESSION['returnDate'])){
            $selectedDate = $_SESSION['returnDate'];
            $_SESSION['returnDate'] = null;
        }
    }

    $prodSchedule = getProdSchedule($selectedDate);
    $employeeList = getCrewLeaders();
    
    if (!$prodSchedule){
        // $prodSchedule2 = getAllUnscheduledWOs();
        // if(!$prodSchedule2) {
        //     $message = 'No scheduled Work Orders';  
        // }
    }

    $prodMenu = buildProductionMenu($prodSchedule, $employeeList, $message, $selectedDate);

    $html = $prodMenu;

}elseif($action == 'prodSearch') {

    $selectedDate = filterString($_GET['search']);
    $_SESSION['returnDate'] = date('Y-m-d', strtotime($selectedDate));
    $prodSchedule = getProdSchedule($selectedDate);
    $employeeList = getCrewLeaders();

    if (!$prodSchedule){
        $prodSchedule2 = getAllUnscheduledWOs();
        if(!$prodSchedule2) {
            $message = 'No scheduled Work Orders';  
        }
    }

    $html = buildProductionMenu($prodSchedule, $employeeList, $message, $selectedDate);

    echo $html;

}elseif($action == 'pastDue'){

    if(!$selectedDate) {
        $selectedDate = filterString($_GET['searchTerm']);
    }else {
        $selectedDate = date('Y-m-d');
    }
    if($selectedDate) {
        $_SESSION['returnDate'] = date('Y-m-d', strtotime($selectedDate));
    }
    $prodSchedule = getPastDue($selectedDate);
    $employeeList = getCrewLeaders();
    
    if (!$prodSchedule){
            $message = 'No past due Work Orders';  
    }

    $html = buildProdPastDue($prodSchedule, $employeeList, $message, $selectedDate);
}elseif($action == 'pastDueSlider') {

    // if(!isset($selectedDate)) {
    //     $selectedDate = filterString($_GET['selectedDate']);
    // }else {
    $selectedDate = date('Y-m-d');
    // }
    if($selectedDate) {
        $_SESSION['returnDate'] = date('Y-m-d', strtotime($selectedDate));
    }
    $prodSchedule = getPastDue($selectedDate);
    $employeeList = getCrewLeaders();
    
    if (!$prodSchedule){
            $message = 'No past due Work Orders';  
    }

    // $html = buildProdPastDue($prodSchedule, $employeeList, $message, $selectedDate);

    $html = buildPastDueList($prodSchedule, $message);

    echo $html;

    $ajax = true;

}elseif($action == 'toggleSortPd') {

    $state = filterNumber($_GET['state']);
    $selectedDate = filterString($_GET['selectedDate']);

    $prodSchedule = getPastDue($selectedDate, $state);

    $html = buildProdItemsList($prodSchedule, null, true, $state);
    
    echo $html;
    
    $ajax = true;

}elseif($action == 'workOrder'){
        $workOrderNumber = $_POST['workOrderNumber'];
        $verifyWO = validateWorkOrderNumber($workOrderNumber);

    if($verifyWO){
        $prodSchedule = getUnscheduledWO($workOrderNumber);
        $employeeList = getCrewLeaders();
        $selectedDate = null;

        if (!$prodSchedule){
            $message = 'Work Order Unavailable';  
        }

        $html = buildScheduleWO($prodSchedule, $employeeList, $message, $workOrderNumber);

        $_SESSION['returnStr'] = '<a style="margin-right: 0.8em;" href="/logIn/index.php?action=production" id="exit"><span>x</span></a>';
        $_SESSION['scheduleSearchTerm'] = $workOrderNumber;
    }else{
        if($workOrderNumber != null){
            $message = 'Work Order ' . $_POST['workOrderNumber'] . ' Not Found';
        }
        $action = 'production';
        $subAction = 'selectWorkOrder';
    }

    
}elseif($action == 'workOrder2') {

    $workOrderNumber = filterString($_GET['workOrderNumber']);

    $verifyWO = getWOScheduledDate($workOrderNumber);

    if(!$verifyWO) {
        echo 0;
    }else if($verifyWO[0] == null) {
        echo -1;
    }else {
        echo $verifyWO[0];
    }

    $ajax = true;

}elseif($action == 'undoDate') {
    $workOrderNumber = filterString($_GET['workOrderNumber']);
    $selectedDate = $_SESSION['returnDate'];

    //Validate Work Order
    $workOrderData = getWorkOrder($workOrderNumber);

    if($workOrderData[0][10]){
        $message = 'Error: Unable to Schedule, work Order has been completed';
    }else{
        //Update the crew leader
        $update = updateScheduleDate($selectedDate, $workOrderNumber);
        if (!$update){
                $message = 'Schedule Date Failed For: ' . $workOrderNumber;  
        }
    }

    $prodSchedule = getProdSchedule($selectedDate);
    $employeeList = getCrewLeaders();
    if ($update) {
        $message .= '<p>WO#: ' . $workOrderNumber . ' was rescheduled back to ' . $selectedDate . '</p>';
    }
    $html = buildProductionMenu($prodSchedule, $employeeList, $message, $selectedDate);
    
 

    echo $html;

    $ajax = true;


}elseif($action == 'productionNotes') {

    $workOrderNumber = filterString($_GET['workOrderNumber']);
    $notes = filterString($_GET['notes']);

    $html = buildProductionNotes($workOrderNumber, $notes);

    echo $html;
    $ajax = true;

}elseif($action == 'postProdMessage') {
    $workOrderNumber = filterString($_GET['workOrderNumber']);
    $message = filterString($_GET['message']);

    $username = getLoginName($_SESSION['userID']);

    $update = insertWorkOrderDialogue($_SESSION['userID'], $workOrderNumber, $message);

    echo $update ? $username : 0;

    $ajax = true;
}elseif($action == 'updateScheduleDate'){

    $searchMenu = filterString($_GET['searchMenu']);

    $completeStatus = filterString($_GET['completeStatus']);

    $workOrderNumber = filterString($_GET['workOrderNumber']);

    $approved = getApprovalStatus($workOrderNumber)[0];
    
    // Checks if the Item is completed
    if($completeStatus == 1) {
        // Doesn't allow the reschedule if completed
        $html = 0;
        // $html = '<h3 class="fadeout longError" id="message">You cannot reschedule completed work orders!</h3>';
    }else if($approved == 0) {
        // Doesn't allow the reschedule if not approved
        $html = 1;
        // $html = '<h3 class="fadeout longError" id="message">You cannot reschedule work orders that are not approved!</h3>';
    }else {
        // $_SESSION['returnDate'] = null;
        $oldDate = filterString($_GET['oldDate']);
        //Get new date and work order
        $date = filterString($_GET['newDate']);
        $newDate = date('Y-m-d', strtotime($date));
        

        //Validate Work Order
        $workOrderData = getWorkOrder($workOrderNumber);

        if($workOrderData[0][10]){
            $message = 'Error: Unable to Schedule, work Order has been completed';
        }else{
            //Update the crew leader
            $update = updateScheduleDate($newDate, $workOrderNumber);
            if (!$update){
                    $message = 'Schedule Date Failed For: ' . $workOrderNumber;  
            }
        }
        $selectedDate = isset($_SESSION['returnDate']) ? $_SESSION['returnDate'] : null;
        $prodSchedule = getProdSchedule($selectedDate);
        $employeeList = getCrewLeaders();
        // $html = buildProductionMenu($prodSchedule, $employeeList, $message, $selectedDate);
        $_SESSION['goBackProd'] = $oldDate;

        $html = '
        <td colspan="3" style="text-align: center">
            <p>WO#: ' . $workOrderNumber . ' was moved to ' . $newDate . '</p>
            <div id="indirectShiftChange_' . $workOrderNumber . '">
                Move to:
                <button onclick="changeShift(1, \'' . $workOrderNumber . '\', 0, 0)">Shift 1</button>
                <button onclick="changeShift(0, \'' . $workOrderNumber . '\', 0, 0)">Shift 2</button>
            </div>
        </td>
        <td class="scheduleActions" colspan="2" style="text-align:center">
            <a href="#" onclick="goToWorkOrder(true, ' . $workOrderNumber . ')" title="GoTo Work Order"><span>G</span></a>
            <a href="#" onclick="undoDate(' . $oldDate . ', ' . $workOrderNumber . ')" title="Undo"><span>n</span></a>
        </td>
        ';
       

    }

    echo $html;

    $ajax = true;



    // INSERT ROW HTML
    

    // $tranArr = null;

    // $tranArr[0] = '<p>WO#: ' . $workOrderNumber . ' was moved to ' . $newDate . '</p>';
    // $tranArr[1] = '
    //     <a href="#" onclick="prodScheduleSearch(\'' . $newDate . '\')" title="GoTo Work Order"><span>G</span></a>
    //     <a href="#" onclick="undoDate(' . $oldDate . ', ' . $workOrderNumber . ')" title="Undo"><span>n</span></a>
    // ';

    // $data = json_encode($tranArr);
    // echo $data;



    // echo $newDate;
    
    // $returnBtn = buildReturnLine();
    // echo $newDate;
    //Select the new data depending on where the action took place
    // if($currentView == 'productionSchedule'){
        
    //     $selectedDate = $_SESSION['returnDate'];
    //     $prodSchedule = getProdSchedule($selectedDate);
    //     $employeeList = getCrewLeaders();
    //     $action = 'productionSchedule';
    //     if (!$prodSchedule){
    //         $message = 'No scheduled Work Orders';  
    //     }
    // }else if($currentView == 'scheduleWO'){
    //     if (isset($_SESSION['scheduleSearchTerm'])){
    //         $searchTerm = $_SESSION['scheduleSearchTerm'];
    //     }else{
    //         $searchTerm = null;
    //     }
        
    //     $prodSchedule = getUnscheduledWO($searchTerm);
    //     $employeeList = getCrewLeaders();
    //     if (!$prodSchedule){
    //             $message = 'No scheduled Work Orders';  
    //     }
    //     $action = 'scheduleWO';
    // }else if($currentView == 'pastDue'){
    //     $prodSchedule = getPastDue();
    //     $employeeList = getCrewLeaders();
    
    //     if (!$prodSchedule){
    //             $message = 'No past due Work Orders';  
    //     }
    //     $action = 'pastDue';
    // }else if($currentView == 'workOrder'){
    //     if($message){
    //         $selectedDate = filterString($_POST['selectedDate']);
    //     }else{
    //         $selectedDate = filterString($_POST['updateScheduleDate']);
    //     }
    //     $selectedDate = filterString($_POST['updateScheduleDate']);
    //     $prodSchedule = getProdSchedule($selectedDate);
    //     $employeeList = getCrewLeaders();
    //     $action = 'productionSchedule';
    //     if (!$prodSchedule){
    //         $message = 'No scheduled Work Orders';  
    //     }
    // }
}elseif($action == 'updateCrewLeader'){
    
    //Get crew leader id and Work Order Number
    $crewLeader = filterNumber($_POST['updateCrewLeader']);
    $workOrderNumber = filterString($_POST['workOrderNumber']);
    
    //Update the crew leader
    $update = updateCrewLeader($crewLeader, $workOrderNumber);
    if (!$update){
            $message = 'Crew Leader Change Failed';  
    }
    
    //Select the new data depending on where the action took place
    if($currentView == 'productionSchedule'){
        
        $selectedDate = filterString($_POST['selectedDate']);
        $prodSchedule = getProdSchedule($selectedDate);
        $employeeList = getCrewLeaders();
        $action = 'productionSchedule';
        if (!$prodSchedule){
            $message = 'No scheduled Work Orders';  
        }
    }else if($currentView == 'scheduleWO'){
        if (isset($_SESSION['scheduleSearchTerm'])){
            $searchTerm = $_SESSION['scheduleSearchTerm'];
        }else{
            $searchTerm = null;
        }
        
        $prodSchedule = getUnscheduledWO($searchTerm);
        $employeeList = getCrewLeaders();
        if (!$prodSchedule){
                $message = 'No scheduled Work Orders';  
        }
        $action = 'scheduleWO';
    }else if($currentView == 'pastDue'){
        $prodSchedule = getPastDue();
        $employeeList = getCrewLeaders();
    
        if (!$prodSchedule){
                $message = 'No past due Work Orders';  
        }
        $action = 'pastDue';
    }else if($currentView == 'workOrder'){
        $workOrderNumber = filterString($_POST['workOrderNumber']);
        $employeeList = getCrewLeaders();
    
        $prodSchedule = getWorkOrder($workOrderNumber);
        $selectedDate = null;
        $action = 'workOrder';
    }
}elseif($action == 'updateLaborCnt') {

    // Get crew count and Work Order Number
    $crewCount = filterNumber($_GET['cnt']);
    $workOrderNumber = filterString($_GET['workOrderNumber']);

    // Update the crew count
    $update = updateCrewCount($crewCount, $workOrderNumber);
   
    echo !$update ? 0 : 1;

    $ajax = true;

}elseif($action == 'updateCrewCount'){

    //Get crew count and Work Order Number
    $crewCount = filterNumber($_POST['updateCrewCount']);
    $workOrderNumber = filterString($_POST['workOrderNumber']);
    
    //Update the crew count
    $update = updateCrewCount($crewCount, $workOrderNumber);
    if (!$update){
            $message = 'Crew Count Change Failed';  
    }
    
    //Select the new data depending on where the action took place
    if($currentView == 'productionSchedule'){
        if(isset($_POST['selectedDate'])){
            $selectedDate = filterString($_POST['selectedDate']);
        }
        $prodSchedule = getProdSchedule($selectedDate);
        $employeeList = getCrewLeaders();
        $action = 'productionSchedule';
        if (!$prodSchedule){
            $message = 'No scheduled Work Orders';  
        }
    }else if($currentView == 'scheduleWO'){
        if (isset($_SESSION['scheduleSearchTerm'])){
            $searchTerm = $_SESSION['scheduleSearchTerm'];
        }else{
            $searchTerm = null;
        }
        
        $prodSchedule = getUnscheduledWO($searchTerm);
        $employeeList = getCrewLeaders();
        if (!$prodSchedule){
                $message = 'No scheduled Work Orders';  
        }
        $action = 'scheduleWO';
    }else if($currentView == 'pastDue'){
        $prodSchedule = getPastDue();
        $employeeList = getCrewLeaders();
    
        if (!$prodSchedule){
                $message = 'No past due Work Orders';  
        }
        $action = 'pastDue';
    }else if($currentView == 'workOrder'){
        $workOrderNumber = filterString($_POST['workOrderNumber']);
        $employeeList = getCrewLeaders();
    
        $prodSchedule = getWorkOrder($workOrderNumber);
        $selectedDate = null;
        $action = 'workOrder';
    }
    
}elseif($action == 'updateNotes'){

    //Get crew count and Work Order Number
    $notes = filterString($_POST['updateNotes']);
    $workOrderNumber = filterString($_POST['workOrderNumber']);
    
    //Update the crew count
    $update = updateNotes($notes, $workOrderNumber);
    if (!$update){
            $message = 'Notes Change Failed';  
    }
    
    //Select the new data depending on where the action took place
    if($currentView == 'productionSchedule'){
        if(isset($_POST['selectedDate'])){
            $selectedDate = filterString($_POST['selectedDate']);
        }
        $prodSchedule = getProdSchedule($selectedDate);
        $employeeList = getCrewLeaders();
        $action = 'productionSchedule';
        if (!$prodSchedule){
            $message = 'No scheduled Work Orders';  
        }
    }else if($currentView == 'scheduleWO'){
        if (isset($_SESSION['scheduleSearchTerm'])){
            $searchTerm = $_SESSION['scheduleSearchTerm'];
        }else{
            $searchTerm = null;
        }
        
        $prodSchedule = getUnscheduledWO($searchTerm);
        $employeeList = getCrewLeaders();
        if (!$prodSchedule){
                $message = 'No scheduled Work Orders';  
        }
        $action = 'scheduleWO';
    }else if($currentView == 'pastDue'){
        $prodSchedule = getPastDue();
        $employeeList = getCrewLeaders();
    
        if (!$prodSchedule){
                $message = 'No past due Work Orders';  
        }
        $action = 'pastDue';
    }else if($currentView == 'workOrder'){
        $workOrderNumber = filterString($_POST['workOrderNumber']);
        $employeeList = getCrewLeaders();
    
        $prodSchedule = getWorkOrder($workOrderNumber);
        $selectedDate = null;
        $action = 'workOrder';
    }
 
}elseif($action == 'scheduleWO' || $action == 'searchClick'){

    // Show Toggle
    $_SESSION['showToggle'] = $action == 'searchClick' ? true : ( isset($_SESSION['showToggle']) ? $_SESSION['showToggle'] : null );

    // Get Search Term
    $searchTerm = isset($_GET['searchTerm']) ? filterString($_GET['searchTerm']) : null;

// Kit | Comp Detection

    // $type = getSkuType($searchTerm);

    // if($type == false || $type == null) {
        
    //     $prodSchedule = getAllOpenWO($searchTerm); // No Sku Found or WO#
    //     $_SESSION['woType'] = null;
    //     // $message = 'NULL or WO#';
    // }elseif($type[0] == 0 && $type[1] == 1) {
        
    //     $prodSchedule = getCompWO($searchTerm); // Component
    //     $_SESSION['woType'] = 0;
    //     // $message = 'Comp';
    // }elseif($type[0] == 1 && $type[1] == 0) {
        
    //     $prodSchedule = getUnscheduledWO($searchTerm); // Kit
    //     $_SESSION['woType'] = 1;
    //     // $message = 'Kit';
    // }elseif($type[0] == 1 && $type[1] == 1) {
    //     $message = 'Both';
    //     // Both checked
    // }

    $prodSchedule = getAllOpenWO($searchTerm);

    // View Logic
    if(isset($_GET['searchTerm'])){
        // There is a search Term.
        $searchTerm = filterString($_GET['searchTerm']);
        $_SESSION['scheduleSearchTerm'] = $searchTerm;
        $searchS = 1;
        $_SESSION['goBackProd'];
    }elseif(isset($_SESSION['scheduleSearchTerm'])){
        // There was a re-navigation here. No search term.
        $searchTerm = $_SESSION['scheduleSearchTerm'];
        $searchS = 0;
        $_SESSION['goBackProd'];
    }else{
        $searchTerm = null;
        $searchS = 0;
    }



    // $prodSchedule = getUnscheduledWO($searchTerm);
    $employeeList = getCrewLeaders();

    if (!isset($prodSchedule) || $prodSchedule == null){
        $message = 'No Work Order Found';  

        // Defaults Search Results if Null to destroy errors
        // $prodSchedule = getUnscheduledWO(0);
    }    

    if($action == 'searchClick') {
        $pastDue = false;
    }else {
        $pastDue = $_SESSION['woMenu'];
    }

    if($searchS == true) {
        $_SESSION['woMenu'] = 1;
        $transArr[0] = buildProdItemsList($prodSchedule, $message, $pastDue, null, $searchTerm);

        $transArr[1] = '
                <div class="onoffswitch">
                    <input type="checkbox" name="onoffswitch" id="myonoffswitch" class="onoffswitch-checkbox" tabindex="0" onchange="changeSearchSetting(this)">
                    <label class="onoffswitch-label" for="myonoffswitch"></label>
                </div>
                <h2 class="onoffswitchLabel">Toggle MRP</h2>';

        $data = json_encode($transArr);
        echo $data;
        $ajax = true;
    }elseif ($searchS == false) {
        $html = buildScheduleWO($prodSchedule, $employeeList, $message, $searchTerm);
    }
    // $html = var_dump($prodSchedule);
    
}elseif($action == 'viewItem'){
    $_SESSION['return'] = 'returnToProduction';
    $_SESSION['productionScheduleCurrentView'] = filterString($_GET['currentView']);
    if(isset($searchTerm)){
        $_SERVER['scheduleSearchTerm'] = $searchTerm;
    }
    $_SESSION['productionSelectedDate'] = $selectedDate;
    
    // header('Location: /inventory/index.php?action=viewItem' . filterNumber($_GET['skuNumber']));
    
    $ajax = true;
}elseif($action == 'exit'){
    $_SESSION['scheduleSearchTerm'] = null;
    $_SESSION['productionScheduleCurrentView'] = null;
    header('Location: /logIn/index.php?action=production');
}elseif($action == 'return'){
    //Select the new data depending on where the action took place
    if($currentView == 'productionSchedule'){
        if(isset($_SESSION['returnDate'])) {
            $selectedDate = $_SESSION['returnDate'];
        }
        $prodSchedule = getProdSchedule($selectedDate);
        $action = 'productionSchedule';
        if (!$prodSchedule){
            $message = 'No scheduled Work Orders';  
        }
    }else if($currentView == 'scheduleWO'){
        if (isset($_SESSION['scheduleSearchTerm'])){
            $searchTerm = $_SESSION['scheduleSearchTerm'];
        }else{
            $searchTerm = null;
        }
        
        $prodSchedule = getUnscheduledWO($searchTerm);
        if (!$prodSchedule){
                $message = 'No scheduled Work Orders';  
        }
        $action = 'scheduleWO';
    }else if($currentView == 'pastDue'){
        $prodSchedule = getPastDue();
    
        if (!$prodSchedule){
                $message = 'No past due Work Orders';  
        }
        $action = 'pastDue';
    }else if($currentView == 'workOrder'){
    
        $prodSchedule = getWorkOrder($workOrderNumber);
        $selectedDate = null;
        $action = 'workOrder';
    }

    // Builds Production Menu on Return
    $employeeList = getCrewLeaders();

    $prodMenu = buildProductionMenu($prodSchedule, $employeeList, $message, $selectedDate);

    $html = $prodMenu;

    
}elseif($action == 'hot'){
    $hot = filterNumber($_GET['hot']);
    $updateHot = null;
    $workOrderNumber = filterString($_GET['workOrderNumber']);
    $selectedDate = filterString($_GET['selectedDate']);
    $complete = filterString($_GET['complete']);

    // if($complete == 1) {
    //     // Doesn't allow the hot if completed
    //     $message = '<h3 class="fadeout longError" id="message">You cannot reschedule completed work orders!</h3>';
    //     $prodSchedule = getWorkOrder($workOrderNumber);
        
    // }else {

    if($hot == 1){
        $updateHot = updateHot(0, $workOrderNumber);
    }else{
        $updateHot = updateHot(1, $workOrderNumber);
    }
    
    if (!$updateHot){
            $message = 'Hot Update Failed';  
    }
    
    if($selectedDate == null || ''){
        $workOrderNumber = filterString($_GET['workOrderNumber']);
        $employeeList = getCrewLeaders();
        $prodSchedule = getWorkOrder($workOrderNumber);
        $selectedDate = null;
        $action = 'workOrder';
        $message = 'Invalid Date.';
        $html = buildProdItemsList($prodSchedule, $message);
    }else{
        $prodSchedule = getProdSchedule($selectedDate);
        $employeeList = getCrewLeaders();
        if (!$prodSchedule){
            $message = 'No scheduled Work Orders';  
        }
        $html = buildProdItemsList($prodSchedule, $message);
    }

    echo $html;

    $ajax = true;

}elseif($action == 'deleteWorkOrder') {

    $workOrderNumber = filterString($_GET['woNo']);
    $selectedDate = filterString($_GET['selectedDate']);

    $yes = true;

    if($yes && $_SESSION['securityLevel'] > 3) {
        deleteWorkOrder($workOrderNumber);
    }else {
        $message = 'You cannot delete work orders at this time.';
    }
    

    // if($selectedDate == null){
    //     $workOrderNumber = filterString($_GET['workOrderNumber']);
    //     $employeeList = getCrewLeaders();
    
    //     $prodSchedule = getWorkOrder($workOrderNumber);
    //     $selectedDate = null;
    //     $action = 'workOrder';
    //     $message = 'No scheduled Work Orders';
    // }else{
    //     $prodSchedule = getProdSchedule($selectedDate);
    //     $employeeList = getCrewLeaders();
    //     if (!$prodSchedule){
    //         $message = 'Refresh Successful!';  
    //         $message = 'No scheduled Work Orders';
    //     }
    //     $html = buildProdItemsList($prodSchedule, $message);
    //     echo $html;
    // }

}elseif($action == 'printWorkOrder'){
    $workOrderNumber = filterString($_GET['workOrderNumber']);
    $selectedDate = filterString($_GET['selectedDate']);

    $cutSheetData = getSubComponent($workOrderNumber);
    
    if(!$cutSheetData){
        $prodSchedule = getProdSchedule($selectedDate);
        $employeeList = getCrewLeaders();
        $message = 'Cut Sheet Does not exsist';
        $html = buildProdItemsList($prodSchedule, $message);
        echo $html;
    }else{
        $subAction = 'printCutSheet';
    }

    // if($selectedDate == null){
    //     $workOrderNumber = filterString($_GET['workOrderNumber']);
    //     $employeeList = getCrewLeaders();
    
    //     $prodSchedule = getWorkOrder($workOrderNumber);
    //     $selectedDate = null;
    //     $action = 'workOrder';
    // }else{
    //     $prodSchedule = getProdSchedule($selectedDate);
    //     $employeeList = getCrewLeaders();
    //     if (!$prodSchedule){
    //         $message = 'No scheduled Work Orders';  
    //     }
    //     $html = buildProdItemsList($prodSchedule, $selectedDate, $message);
    //     echo $html;
    // }


}elseif($action == 'complete'){
    
    // Get Variables
    $workOrderNumber = filterString($_GET['woNo']);
    $compQty = filterString($_GET['compQty']);
    $selectedDate = filterString($_GET['selectedDate']);
    
    // Get User's GroupID
    $userGroupID = getUserGroupID($_SESSION['userID']);

    // Create Array
    $tranArr = null;
    $tranArr[0] = 0; // No Errors by Default
    
    // Complete WO if authorized
    if($_SESSION['securityLevel'] > 3) {
        $updateComplete = null;
        // Set complete to 2 ( Awaiting Confirmation )
        $updateComplete = completeWO(2, $workOrderNumber);
        $message = $updateComplete != null ? null : 'Failed to Complete WO';
    }else {
        // Trigger Unauthorized Error Message
        $tranArr[0] = 1;
    }
    
    // Rebuild Production Menu
    if($selectedDate == null){
        $workOrderNumber = filterString($_GET['workOrderNumber']);
        $employeeList = getCrewLeaders();
    
        $prodSchedule = getWorkOrder($workOrderNumber);
        $selectedDate = null;
        $action = 'workOrder';
    }else{
        $prodSchedule = getProdSchedule($selectedDate);
        $employeeList = getCrewLeaders();
        if (!$prodSchedule){
            $message = 'No scheduled Work Orders';  
        }
        $html = buildProdItemsList($prodSchedule, $message);
    }
    $tranArr[1] = $html;
    
    // Echo Array
    $data = json_encode($tranArr);
    echo $data;
    $ajax = true;

}elseif($action == 'workOrderRundown') {

    $workOrderNumber = filterString($_GET['workOrderNumber']);
    $skuNumber = filterNumber($_GET['skuNumber']);
    $compQty = filterNumber($_GET['compQty']);
    $desc = filterString($_GET['desc']);
    $completeStatus = filterNumber($_GET['completeStatus']);

    echo buildWORundown($workOrderNumber, $compQty, $skuNumber, $desc, $completeStatus);
    $ajax = true;

}elseif($action == 'setCompletionStatus') {
    // Get Variables
    $workOrderNumber = filterString($_GET['workOrderNumber']);
    $complete = filterString($_GET['complete']);

    // Get User's GroupID
    $userGroupID = getUserGroupID($_SESSION['userID']);
    
    // Check if user if authorized
    if( ( $userGroupID == 2 && $_SESSION['securityLevel'] > 3 ) || $_SESSION['securityLevel'] >= 5 ) {
    // Reject WO
        $return = completeWO($complete, $workOrderNumber);
        echo $return != null ? 1 : 0;
    }else {
        // User not authorized
        echo 2;
    }
    $ajax = true;
}elseif($action == 'confirmWOCompletion') {

    // Get Variables
    $workOrderNumber = filterString($_GET['workOrderNumber']);
    $woImperfection = filterNumber($_GET['woImperfection']);
    $compQty = filterNumber($_GET['compQty']);
    $warned = true;

    // Get User's GroupID
    $userGroupID = getUserGroupID($_SESSION['userID']);

    // Get Minimum Authorized User Level
    // $minSecurityLevel = getConstant('woApproval');

    // Check if user if authorized
    if( ( $userGroupID == 2 && $_SESSION['securityLevel'] > 3 ) || $_SESSION['securityLevel'] >= 5 ) {
        // Set complete to 1 ( Confirmed )
        $updateComplete = completeWO(1, $workOrderNumber);

        // Get WO Data
        $woData = getWorkOrderData($workOrderNumber, $compQty);
        
        $includeConsumable = getConstant('includeConsumable')['constantValue'];
    
        // ERP Adjustments
        for ($i = 0; $i < sizeof($woData); $i++) {
            if($woData[$i][10]) {
                if(!$includeConsumable) continue;
            }
            $quantity = ($woData[$i][5] - $woData[$i][6]) * 1;
            if ($quantity != 0){
                $log = insertERPAdjustLog($woData[$i][1], $quantity, $workOrderNumber, $woData[$i][1], NULL);
            }
        }
        echo $updateComplete != null ? 1 : 0;
    }else {
        // User not authorized
        echo 2;
    }

    $ajax = true;

}elseif($action == 'woConfirmationMenu') {

    $html = buildWOConfirmationMenu();

}elseif($action == 'woRejectsMenu') {

    $html = buildWORejectsMenu();

}elseif($action == 'editCompletion'){
    $editedWO = filterString($_GET['workOrderNumber']);
    $subAction = 'editCompletion';
    
    if($selectedDate == null){
        $workOrderNumber = filterString($_GET['workOrderNumber']);
        $employeeList = getCrewLeaders();
    
        $prodSchedule = getWorkOrder($workOrderNumber);
        $selectedDate = null;
        $action = 'workOrder';
    }else{
        $prodSchedule = getProdSchedule($selectedDate);
        $employeeList = getCrewLeaders();
        $action = 'productionSchedule';
        if (!$prodSchedule){
            $message = 'No scheduled Work Orders';  
        }
    }
}// Employee Scheduler ---------------------------------------
elseif($action == 'employeeScheduler'){
    $listUnscheduledEmployees = getUnscheduledEmployees($selectedDate);
    $listScheduledEmployees = getScheduledEmployees($selectedDate);
    $listShiftsID = getShifts($selectedDate);
    
    $scheduleTable = buildScheduleTables($listUnscheduledEmployees, $listScheduledEmployees);
    
}elseif($action == 'addEmployeeToSchedule' || $action == 'removeEmployeeToSchedule'){
    //Get data to schedule employee
    $employeeID = filterNumber($_GET['employeeID']);
    $shiftID = filterNumber($_GET['shiftID']);
    $scheduleDate = filterString($_GET['scheduleDate']);
    $userID = $_SESSION['userID'];
    
    //Insert New Schedule
    if($action == 'addEmployeeToSchedule'){
        $updateEmployeeSchedule = addEmployeeToSchedule($employeeID, $shiftID, $scheduleDate, $userID);
    }elseif($action == 'removeEmployeeToSchedule'){
        $updateEmployeeSchedule = removeEmployeeFromSchedule($employeeID, $scheduleDate);
    }
    
    if(!$updateEmployeeSchedule){
        $message = 'Unable to add Employee';
    }
    //Query New schedule data
    $listUnscheduledEmployees = getUnscheduledEmployees($scheduleDate);
    $listScheduledEmployees = getScheduledEmployees($scheduleDate);
    $listShiftsID = getShifts();
    $scheduleCount = getScheduleCount($selectedDate);
    
    $scheduleTable = buildScheduleTables($listUnscheduledEmployees, $listScheduledEmployees);
    
    //echo out for AJAX
    echo $scheduleTable;
    
    //Set to AJAX
    $ajax = true;
}elseif($action == 'autoEmployeeSchedule'){
    
    $lastDate = getLastScheduleDate();
    
    $lastSchedule = null;
    $userID = $_SESSION['userID'];
    $deleteCurrent = deleteSchedule($selectedDate);
    
    if($lastDate == $selectedDate){
        //Clear current Schedule
        $deleteCurrent = deleteSchedule($selectedDate);
        $lastDate = date('Y-m-d', strtotime("$lastDate -1 day"));
    }
    //Get last schedule
    $lastSchedule = getLastSchedule($lastDate);
    
    //Copy last schedule to current schedule
    for($i = 0; $i < sizeof($lastSchedule); $i++){
            $scheduleEmployee = addEmployeeToSchedule(
                    $lastSchedule[$i][0], 
                    $lastSchedule[$i][1], 
                    $selectedDate, 
                    $userID);
        }
    
    //Rebuild Schedule
    $listUnscheduledEmployees = getUnscheduledEmployees($selectedDate);
    $listScheduledEmployees = getScheduledEmployees($selectedDate);
    $listShiftsID = getShifts($selectedDate);
    $scheduleTable = buildScheduleTables($listUnscheduledEmployees, $listScheduledEmployees);
    
    $action = 'employeeScheduler';
    
}elseif($action == 'updateCompletionData'){
    $workOrderNumber = filterString($_GET['workOrderNumber']);
    
    $selectedDate = filterString($_GET['selectedDate']);
    if(!$selectedDate){
        $selectedDate = null;
    }else{
        $selectedDate = date('Y-m-d', strtotime($selectedDate));
    }
    
    $completionDate = filterString($_GET['completionDate']);
    if(!$completionDate){
        $completionDate = null;
    }else{
        $completionDate = date('Y-m-d', strtotime($completionDate));
    }
    
    $completionQty = filterNumber($_GET['completionQty']);
    $EditElapsedHrs = filterNumber($_GET['EditElapsedHrs']);
    $EditElapsedMins = filterNumber($_GET['EditElapsedMins']);
    
    $hours = ($EditElapsedHrs == null ? 0 : $EditElapsedHrs);
    $mins = ($EditElapsedMins == null ? 0 : $EditElapsedMins);
    $completionTime = $hours . ':' . $mins . ':' . 00;
    
    $curTimeStamp = date('Y-m-d H:i:s');
    $userID = $_SESSION['userID'];
    
    $update = updateCompletionData($workOrderNumber
                        , $completionDate
                        , $completionQty
                        , $completionTime
                        , $curTimeStamp
                        , $userID);
    
    $ajax = true;
}elseif($action == 'delete' && $_SESSION['securityLevel'] > 3){
    //Verify if workorder is complete. If complete
    $workOrderNumber = filterString($_GET['workOrderNumber']);
    $workOrderData = getWorkOrder($workOrderNumber);

    if(($workOrderData[0][5] != '0000-00-00' && $workOrderData[0][5] != Null)
     || ($workOrderData[0][6] != 0 && $workOrderData[0][6] != Null)
     || ($workOrderData[0][8] != '00:00:00' && $workOrderData[0][8] != Null )){
        $message = 'Unable to Delete Work Order: ';  
        if(isset($workOrderData[0][5])){
            $message .= 'Usage Date is set - ';
        }
        if(isset($workOrderData[0][6])){
            $message .= 'Completion Quantity is set - ';
        }
        if(isset($workOrderData[0][8])){
            $message .= 'Completion Time is set';
        }
    }else{
        $deleteWorkOrder = deleteWorkOrder($workOrderNumber);

        if($deleteWorkOrder){
           $message = 'Deleted WO# ' . $workOrderNumber;  
        }else{
            $message = 'Deleted Failed'; 
        }
    }
    
    if($selectedDate == null){
        $workOrderNumber = filterString($_GET['workOrderNumber']);
        $employeeList = getCrewLeaders();
    
        $prodSchedule = getWorkOrder($workOrderNumber);
        $selectedDate = null;
        $action = 'workOrder';
    }else{
        $prodSchedule = getProdSchedule($selectedDate);
        $employeeList = getCrewLeaders();
        $action = 'productionSchedule';
    }
   
}elseif($action == 'getServerTime'){
    $currentDate = date("Y-m-d\TH:i");
    
    echo $currentDate;
    
    $ajax = true;
}elseif($action == 'jobClockSummary'){
    header('Location: /jobClock/index.php?action=jobClockSummary');


}elseif($action == 'jobClockSummaryCopy'){
    header('Location: /jobClock/indexCopy.php?action=jobClockSummaryCopy');







}elseif($action == 'buildRundown') {

    $workOrderNumber = filterString($_GET['workOrderNumber']);
    $skuNumber = filterNumber($_GET['skuNumber']);

    $html = buildWOSkuRundownMenu($workOrderNumber, $skuNumber);

    echo $html;

    $ajax = true;

}elseif($action == 'statusOH') {
    
    $complete = filterString($_GET['complete']);
    $parentSku = filterString($_GET['parentSku']);
    $compQty = filterString($_GET['compQty']);
    $childSku = filterString($_GET['childSku']);


    $html = buildStatusOH($parentSku, $childSku, $compQty);

    echo $html;
    
}

if(!$ajax){
   include 'view.php'; 
}


function buildScheduleTables($listUnscheduledEmployees, $listScheduledEmployees){
    
    
    //Rebuild Schedule Tables
    $scheduleTable = '<div class="grid">
                        <div class="unit three-fifths align-center">
                            <h2>Choose Employee To Schedule</h2>
                            <table>';
    
    
    for($i = 0; $i < sizeof($listUnscheduledEmployees); $i++){
        $scheduleTable .= '<tr>
                            <td>' . $listUnscheduledEmployees[$i][1] . '</td>
                            <td>' . $listUnscheduledEmployees[$i][2] . '</td>
                            <td>' . $listUnscheduledEmployees[$i][3] . '</td>
                            <td><a href="javascript:;" onclick="modifyEmployeeSchedule(' . $listUnscheduledEmployees[$i][0] . ', true)"
                                title="Schedule Employee"><span>></span></a></td>
                            </tr>';
    }
    
    $scheduleTable .=        '</table>
                        </div>
                        <div class="unit two-fifths align-center">
                            <h2>Scheduled Employees</h2>
                            <table>';
    
    for($i = 0; $i < sizeof($listScheduledEmployees); $i++){
        $scheduleTable .= '  <tr>
                                <td>' . $listScheduledEmployees[$i][1] . '</td>
                                <td>' . $listScheduledEmployees[$i][2] . '</td>
                                <td><a href="javascript:;" onclick="modifyEmployeeSchedule(' . $listScheduledEmployees[$i][0] . ', false)"
                                title="Schedule Employee"><span>O</span></a></td>
                                </tr>';
    }
    
    $scheduleTable .= '     </table>
                        </div>
                    </div>';
    
    return $scheduleTable;
}

function buildSjcHeader($currentCrewCount, $currentAssignedCrew){

    $currentCrewCount = !$currentCrewCount ? 0 : $currentCrewCount[0];
    $currentAssignedCrew = !$currentAssignedCrew ? 0 : $currentAssignedCrew[0];

    $sjcHeaderView[0] = '<h2>Current Total Crew Count: Clocked In ' . $currentCrewCount . ' / Assigned ' . $currentAssignedCrew . '</h2>';
    
    $sjcHeaderView[1] = '<button onclick="addNewJobInput()" title="Add New Job">Add New Job</button>';
    
    $sjcHeaderView[2] = '<button onclick="displayJobPunches(0)" title="Display General Jobs">General Jobs</button>';
    
    return $sjcHeaderView;
}


function buildWorkOrderList($currentWO){
    $workOrderList = null;
    
        for($i = 0; $i < sizeof($currentWO); $i++){
            $workOrderList .= '<div class="division-one">
                                    <div class="grid">
                                        <div class="unit half">'
                                        . $currentWO[$i][0] . ' - ' . $currentWO[$i][1] .
                                        '</div>
                                        <div class="unit half">
                                            <button onclick="startJobTimeView(\'' . $currentWO[$i][0] . '\')" title="Start Job">Start</button>
                                            <button onclick="completeJob(\'' . $currentWO[$i][0] . '\')" title="Complete Job">Finish</button>
                                            <button onclick="displayJobPunches(\'' . $currentWO[$i][0] . '\')" title="Display Punches">Crew: ' . $currentWO[$i][2] . '</button>
                                        </div>
                                    </div>
                                </div>';
        }
    
    return $workOrderList;
}

function buildJobPunchView($jobPunches){
    if($jobPunches){
    
    $jobPunchList = '<h2>Job Punches: ' . $jobPunches[0][1] . '</h2>';
    
        for($i = 0; $i < sizeof($jobPunches); $i++){
            $jobPunchList .= '<div class="division-one">
                                    <div class="grid">
                                        <div class="unit whole">
                                            <a href="javascript:;" onclick="deleteJobPunch(' . $jobPunches[$i][0] . ', \'' . $jobPunches[0][1] . '\')"
                                                title="Delete Job Punch"><span>x</span></a> '
                                            . $jobPunches[$i][2] . ' / ' . $jobPunches[$i][5] . ' - (' . $jobPunches[$i][7] . ')
                                        </div>
                                        <div class="unit whole">
                                            <div id="stopJob' . $jobPunches[$i][0]  . '">';
                        if($jobPunches[$i][4] == null){
                            $jobPunchList .= date("m/d/Y h:i A", strtotime($jobPunches[$i][3])) . ' - <button onclick="stopJobPunchView(' . $jobPunches[$i][0] . ', \'' . date("m/d/Y h:i A", strtotime($jobPunches[$i][3])) . '\', \'' . $jobPunches[0][1] . '\')" title="Clock Out">Stop</button>
                                            </div>
                                        </div>';
                        }else{
                                $jobPunchList .= date("m/d/Y h:i A", strtotime($jobPunches[$i][3])) . ' - ' . date("m/d/Y h:i A", strtotime($jobPunches[$i][4])) .
                                              '<a href="javascript:;" onclick="stopJobPunchView(' . $jobPunches[$i][0] . ', \'' . $jobPunches[$i][3] . '\', \'' . $jobPunches[0][1] . '\')" title="Clock Out"><span> p</span>
                                              <a href="javascript:;" id="' . $jobPunches[$i][0] . '" onclick="toggleLunch(' . $jobPunches[$i][0] . ', ' . $jobPunches[$i][6] . ')" title="Toggle Lunch"' . 
                                              ($jobPunches[$i][6] == 1 ? 'class="iconToggled"' : 'class=""') . '><span> b</span>
                                            </div>
                                        </div>';
                                }
                                        
                 $jobPunchList .= ' </div>
                                </div>';
        }
    }else{
        $jobPunchList = '<h2>Job Punches</h2>';
    }
    
    return $jobPunchList;
}


function buildStartJobTime($jobNumber, $departments){
    $startJobTimeView = null;
    
    $currentDate = date("Y-m-d\TH:i");
    
            $startJobTimeView .= '<div class="overlay">
                <form method="post" action="." id="dataEntryForm">
                <a href="/production/index.php?action=sJobClock" id="exit"><span>x</span></a>
                <h1>Assign Crew to Job</h1>
                <div class="grid">
                <div class="unit two-thirds">
                    <ul>
                        <li><h2>Job#: ' . $jobNumber . '</h2></li>
                        <li>
                            <label for="department">Select Department:</label>
                            <select name="department" id="department" required>
                                <option value="" disabled selected>Select Department</option>';
                                    for($i = 0; $i < sizeof($departments); $i++){
                                        $startJobTimeView .=  '<option value=' . $departments[$i][0] . '>' . $departments[$i][1] . '</option>';
                                    }
                            $startJobTimeView .= '</select></li>
                        <li><label for="crewCount">Enter Crew Count:</label>
                            <input type="text" id="crewCount" name="crewCount" placeholder="Enter Crew Count" required></li>
                        <li><label for="startTime">Enter Start Time:</label>
                            <input type="datetime-local" id="startTime" name="startTime" value="' . $currentDate .  '"></li>
                    </ul>
                </div>
                </div>
                <button type="button" onclick="startJobTime(\'' . $jobNumber . '\'); return false;"><span>i</span></button>
                </form></div>';
        
        
    
    return $startJobTimeView;
}


function buildCompleteWOMenu($woNo, $compQty, $skuNumber, $desc, $review){

        // $workOrderData = getWorkOrderData($woNo, $compQty);      
        $workOrderData = getUsageData($woNo, $compQty, $skuNumber);      
        $includeConsumable = getConstant('includeConsumable')['constantValue'];
        $includeConsumable = $includeConsumable ? $includeConsumable : false;
                
        $completeWOHTML;

        $completeWOHTML = '
        <input hidden name="woNo" id="woNo" value="' . $woNo . '"></input>
        <input hidden name="compQty" id="compQty" value="' . $compQty .'"></input>
        <h1>Complete WO Menu</h1>

        <div class="completeWOHeader">
            <p>WO#: ' . $woNo . '</p>
            <p>SKU#: ' . $skuNumber . '</p>
            <p>DESC: ' . $desc . '</p>
        </div>

        <div class="completeWOTableOverflow">
        <table class="completeWOTable">
            <tr>
                <th>CompItem#</th>
                <th>DESC1</th>
                <th>Unit</th>
                <th># in Set</th>
                <th>Estimate Usage</th>
                <th>Actual Usage</th>
                <th>Adjustment</th>
                <th></th>
            </tr>
        ';
            

            for($i = 0; $i < sizeof($workOrderData); $i++){
                if(!$workOrderData[$i][7]) {
                    $completeWOHTML .= '
                    <tr>
                        <td>' . $workOrderData[$i][0] . '</td>
                        <td>' . $workOrderData[$i][1] . '</td>
                        <td>' . $workOrderData[$i][2] . '</td>
                        <td>' . $workOrderData[$i][3] . '</td>
                        <td>' . $workOrderData[$i][4] . '</td>
                        <td>' . $workOrderData[$i][5] . '</td>
                        <td>' . $workOrderData[$i][6] . '</td>
                        <td style="min-width: 2em !important"><a class="woSkuRundownBtn" onclick="skuRundown(' . $woNo . ', `' . $workOrderData[$i][0] . '`)"><i class="bi bi-card-list"></i></a></td>
                    </tr>
                    ';
                }
            }

            
            $completeWOHTML .= '
            </table>
            </div>'; 
            
            $completeWOHTML .= '
            <h3 style="margin-bottom: 0">Consumable</h3>
            <div class="completeWOTableOverflow">
            <table class="completeWOTable">
            <tr>
                <th>CompItem#</th>
                <th>DESC1</th>
                <th>Unit</th>
                <th># in Set</th>
                <th>Estimate Usage</th>';
                if($includeConsumable) {
                    $completeWOHTML .= '
                    <th>Actual Usage</th>
                    <th>Adjustment</th>
                    ';
                }

            $completeWOHTML .= '
                <th></th>
            </tr>';

            for($i = 0; $i < sizeof($workOrderData); $i++){
                if($workOrderData[$i][7]) {
                    $completeWOHTML .= '
                    <tr>
                        <td>' . $workOrderData[$i][0] . '</td>
                        <td>' . $workOrderData[$i][1] . '</td>
                        <td>' . $workOrderData[$i][2] . '</td>
                        <td>' . $workOrderData[$i][3] . '</td>
                        <td>' . $workOrderData[$i][4] . '</td>';
                        if($includeConsumable){
                            $completeWOHTML .= '
                            <td>' . $workOrderData[$i][5] . '</td>
                            <td>' . $workOrderData[$i][6] . '</td>
                            ';
                        }
                    $completeWOHTML .= '
                        <td style="min-width: 2em !important"><a class="woSkuRundownBtn" onclick="skuRundown(' . $woNo . ', `' . $workOrderData[$i][0] . '`)"><i class="bi bi-card-list"></i></a></td>
                    </tr>
                    ';
                }
            }


            $completeWOHTML .= '
            </table>
            </div>
            ';

            $completeWOHTML .= '
            <div id="skuRundown"></div>

            <div id="confirmFooter">';
            
            if (!$review) {
                $completeWOHTML .= buildCancelSubmitWOConfirm($woNo, $compQty);
            }        
            // $completeWOHTML .= '<button type="submit" name="action" id="action" value="complete"><span>i</span></button>';

            $completeWOHTML .= '</div>';

            return $completeWOHTML;
}


function buildProductionMenu($prodSchedule, $employeeList, $message, $selectedDate) {
    $html = null;

    $pastDue = getPastDue($selectedDate);

        $html = '
        <div id="productionItems">
        <h1>Production Schedule</h1>
            <div class="grid">
                <div id="prodHeader" class="unit one-fifth">
                    <a href="#" onclick="prodToday()" title="Today"><span class="viewportBlueBtn">j</span></a>
                    <a href="/production/index.php?action=scheduleWO" title="Schedule WO"><span class="viewportBlueBtn">K</span></a>
                    <a href="#" onclick="goToWorkOrder(0)" title="Go To WO"><span class="viewportBlueBtn">G</span></a>
                </div>
                <div class="unit two-fifths">      
                    <input id="sttInput" class="searchTerm sttInput" onchange="checkSearchType()" placeholder="Schedule WO# to today...">  
                    <input class="searchTerm" type="date" name="selectedDate" id="selectedDate" value="' . $selectedDate . '" onchange="prodScheduleSearch(value)">
                </div>
                <div class="unit one-fifth">
                    <div class="onoffswitch">
                        <input type="checkbox" name="onoffswitch" id="myonoffswitch" class="onoffswitch-checkbox" tabindex="0" onchange="changeSearchSetting(this)">
                        <label class="onoffswitch-label" for="myonoffswitch"></label>
                    </div> 
                    <h2 class="onoffswitchLabel">Toggle MRP</h2>
                </div>
                <div class="unit one-fifth">
                    <a href="#" id="buttonB" onclick="pastDueSlider()">Past Due (' . (sizeof($pastDue) == null ? 0 : sizeof($pastDue)) . ')</a><input value="0" class="hide" id="pdSlideStatus">
                </div>
            </div>
            <div id="reScheduleNav"></div>
            
            <div id="prodList" class="grid">';

        $_SESSION['woMenu'] = 0;
        $html .= buildProdItemsList($prodSchedule, $message);

        if(isset($_SESSION['goBackProd'])) {
        }else {
            $_SESSION['goBackProd'] = null;
        }
        if($_SESSION['goBackProd'] == $selectedDate) {   
        }else {
            if(isset($_SESSION['goBackProd'])) {
                $html .= '
                <div id="tempProdOptions">
                    <a href="#" onclick="prodScheduleSearch(\'' . $_SESSION['goBackProd'] . '\')" title="Go Back to ' . $_SESSION['goBackProd'] . '"><span>n</span></a>
                </div>
                ';
                $_SESSION['goBackProd'] = null;
            }
        }

        $html .='
        </div>
        
        <div class="pdContainer">
            <div id="pastDueSlider">
                <div id="exitBtn">
                    <a href="#" style="font-size: 1em; margin: 10px" onclick="pdCloseSlider()"><i class="bi bi-x-circle"></i></a>
                </div>
                
                <div id="pdSliderContent"></div>
            </div>
            <a href="#" id="jumpBtn" onclick="jumpUp(`pdList`);"><span>!</span></a>
        </div>
        ';

        // <a href="#" title="Close"><span style="font-size: 1.8em" class="viewportWhiteBtn" onclick="pdCloseSlider()">`</span></a>

        return $html; 
}

function buildProdItemsList($prodSchedule, $message, $pastDue = false, $state = null, $searchTerm = null) {

    $message = !$message ? null : $message;
    $prodSchedule = $_SESSION['searchState'] == 1 ? appendAvailability($prodSchedule) : $prodSchedule;

    $html = '';

    $html .= !$pastDue ? '<div id="return"><h3 class="fadeout" id="message">' . $message . '</h3></div>' : '';

    $html .= buildWOList($prodSchedule, false, $pastDue, $state);
    
    // Unscheduled
    $unscheduled = buildWOList(getAllUnscheduledWOs($searchTerm), true, $pastDue, $state);
    if($unscheduled != '') {
        $html .= '
        ' . ( $pastDue ? '<h2 class="shiftTitle">Unscheduled</h2>' : '' ) . '
        <table id="filterTable" class="productionScheduleTable" ' . ( $pastDue ? 'class="pastDueTable"' : '') . '>';

        $html .= buildItemListHeader($pastDue, $state, 'table3');
        $html .= $unscheduled;

        $html .= ' 
        </table>';
    }

    $_SESSION['searchState'] = 0;

    return $html;
}

function buildItemListHeader($pastDue, $state, $table = false) {


    // $id = [];
    // $count = 0;
    // for ($i=0; $i < 17; $i++) {
    //     if($i == 5 && $_SESSION['woMenu'] != 0) {
    //         continue;
    //     }
    //     array_push($id, $count);
    //     $count++;
    // }

    // $id = range(0, 15);
    // if($_SESSION['woMenu'] == 0) {
    //     [ $a, $b, $c, $d, $e, $f, $g, $h, $i, $j, $k, $l, $m, $n, $o, $p ] = $id;
    // }else {
    //     [ $a, $b, $c, $d, $e, $g, $h, $i, $j, $k, $l, $m, $n, $o, $p ] = $id;
    // }

    // $id = range(0, ($_SESSION['woMenu'] == 0 ? 15 : 14));



    // $html = '
    // <tr>     
    //     <th onclick="sortTable('.$a.', \'' . $table . '\')" class="min">Schedule Date<div id="sortDir' . $table .$a.'"></div>' . ( $pastDue ? '<a id="pdDescToggle" onclick="pdDescAscToggle(' . ( $state ? 0 : 1 ) . ')"><i class="bi-chevron-double-' . ( $state ? 'down' : 'up' ) . ' viewportBlueBtn"></i></a>' : '') . '</th>
    //     <th onclick="sortTable('.$b.', \'' . $table . '\')" class="min">Product Code<div id="sortDir' . $table .$b.'"></div></th>
    //     <th onclick="sortTable('.$c.', \'' . $table . '\')" class="min">WO#<div id="sortDir' . $table .$c.'"></div></th>
    //     <th onclick="sortTable('.$d.', \'' . $table . '\')" class="min">User<div id="sortDir' . $table .$d.'"></div></th>
    //     <th onclick="sortTable('.$e.', \'' . $table . '\')" class="min">Sku#<div id="sortDir' . $table .$e.'"></div></th>
    //     ' . ( $_SESSION['woMenu'] == 0 ? '<th onclick="sortTable('.$f.', \'' . $table . '\')" class="min">Priority</th><div id="sortDir' . $table .$f.'"></div>' : '' ) . '
    //     <th onclick="sortTable('.$g.', \'' . $table . '\')" style="width: 15vw">Customer<div id="sortDir' . $table .$g.'"></div></th>
    //     <th onclick="sortTable('.$h.', \'' . $table . '\')" style="width: 18vw">Description<div id="sortDir' . $table .$h.'"></div></th>
    //     <th onclick="sortTable('.$i.', \'' . $table . '\')" class="min">Qty Comp/Req<div id="sortDir' . $table .$i.'"></div></th>
    //     <th onclick="sortTable('.$j.', \'' . $table . '\')" class="min">Notes<div id="sortDir' . $table .$j.'"></div></th>
    //     <th onclick="sortTable('.$k.', \'' . $table . '\')" class="min">Laborer Cnt<div id="sortDir' . $table .$k.'"></div></th>
    //     <th onclick="sortTable('.$l.', \'' . $table . '\')" class="min">Req Pro Date<div id="sortDir' . $table .$l.'"></div></th>
    //     <th onclick="sortTable('.$m.', \'' . $table . '\')" class="min">Time Study (H:M:S)<div id="sortDir' . $table .$m.'"></div></th>
    //     <th onclick="sortTable('.$n.', \'' . $table . '\')" class="min">Est Labor Hours<div id="sortDir' . $table .$n.'"></div></th>
    //     <th onclick="sortTable('.$o.', \'' . $table . '\')" style="width: 8vw"><div id="sortDir' . $table .$o.'"></div></th>
    // </tr>
    // ';
    $html = '
    <tr>     
        <th class="min">Schedule Date' . ( $pastDue ? '<a id="pdDescToggle" onclick="pdDescAscToggle(' . ( $state ? 0 : 1 ) . ')"><i class="bi-chevron-double-' . ( $state ? 'down' : 'up' ) . ' viewportBlueBtn"></i></a>' : '') . '</th>
        <th class="min">Product Code</th>
        <th class="min">WO#</th>
        <th class="min">User</th>
        <th class="min">Sku#</th>
        ' . ( $_SESSION['woMenu'] == 0 ? '<th class="min">Priority</th>' : '' ) . '
        <th style="width: 15vw">Customer</th>
        <th style="width: 18vw">Description</th>
        <th class="min">Qty Comp/Req</th>
        <th class="min">Notes</th>
        <th class="min">Laborer Cnt</th>
        <th class="min">Req Pro Date</th>
        <th class="min">Time Study (H:M:S)</th>
        <th class="min">Est Labor Hours</th>
        <th style="width: 8vw"></th>
    </tr>
    ';
    return $html;
}

function buildWOList($prodSchedule, $unscheduled, $pastDue = false, $state) {
    
    $html = '';
    $build = '';
    $shift1 = '';
    $shift2 = '';

    for($i = 0; $i < count($prodSchedule); $i++) {

        $timeStudyInSecs = isset($prodSchedule[$i][26]) ? $prodSchedule[$i][26] : null;
        if($timeStudyInSecs != null) {
            $timeStudyH = floor($timeStudyInSecs / 3600);
            $timeStudyM = floor(($timeStudyInSecs / 60) % 60);
            $timeStudyM = $timeStudyM < 10 ? '0' . $timeStudyM : $timeStudyM;
            $timeStudyS = $timeStudyInSecs % 60;
        }else {
            $timeStudyH = false;
            $timeStudyM = false;
            $timeStudyS = false;
        }

        $trigger = $prodSchedule[$i][21] == 1 || $pastDue ? 1 : 0;

        $complete = $prodSchedule[$i][10];
        switch($complete) {
            case 0:
                $complete = false;
                break;
            case 1:
                $complete = 'completed';
                break;
            case 2:
                $complete = 'confirmation';
                break;
            case 3:
                $complete = 'rejected';
                break;
        }

        $scheduleDate = $prodSchedule[$i][11];

        $estLaborInSecs = ( $complete ? (int)$prodSchedule[$i][18] : (int)$prodSchedule[$i][17] );

        $hour = floor($estLaborInSecs / 3600);
        $min = floor(($estLaborInSecs / 60) % 60);
        $min = $min < 10 ? '0' . $min : $min;
        $sec = $estLaborInSecs % 60;

        $estLabor = ( !$hour ? '00' : $hour ) . ':' . ( !$min ? '00' : $min ) . ':' . (!$sec ? '00' : $sec ) . '';

        $build .= '
        <tr id="woNum_' . $prodSchedule[$i][0] . '" class="' . ($complete ? $complete : '') . ( $prodSchedule[$i][13] == 1 ? 'hot' : '') . '">

            <td id="ProdMenuList">' . ( $_SESSION['woMenu'] == 0 && !$complete && $scheduleDate != null && $pastDue == false ? '<a href="#" class="changeShiftBtn" onclick="changeShift(\'' . ( $prodSchedule[$i][21] == 1 ? 0 : 1 ) . '\', \'' . $prodSchedule[$i][0] . '\', \'' . $complete . '\', 1)" title="Move to Shift ' . ( $prodSchedule[$i][21] == 1 ? 2 : 1 ) . '"><i class="bi bi-arrow-' . ( $prodSchedule[$i][21] == 1 ? 'down' : 'up' ) . '"></i></a>' : '' ) . 
            '<input id="' . $prodSchedule[$i][0] . 'i" type="date" onchange="updateScheduleDate(value, \'' . $prodSchedule[$i][0] . '\', this, ' . $prodSchedule[$i][10] . ', 0)" value="' . $prodSchedule[$i][11] . '"></td>
            <td title="' . $prodSchedule[$i][27] . '">' . $prodSchedule[$i][25] . '</td>
            <td id="woNo">' . $prodSchedule[$i][0] . '</td>

            <td>' . $prodSchedule[$i][28] . '</td>

            <td>' . $prodSchedule[$i][1] . '</td>
            ' . ( $_SESSION['woMenu'] == 0 ? '<td><input id="changePriorityInput" min="0" max="3" type="number" value="' . (isset($prodSchedule[$i][22]) ? $prodSchedule[$i][22] : "-" ) . '" href="#" onchange="changePriority(this, ' . $prodSchedule[$i][0] . ')" title="Change Priority"></td>' : '' ) . '
            <td>' . $prodSchedule[$i][24] . '</td>
            <td>' . $prodSchedule[$i][14] . '</td>
            <td>' . ((float)$prodSchedule[$i][6] == null ? "0" : (float)$prodSchedule[$i][6]) . ' / ' . ((float)$prodSchedule[$i][3] == null ? "0" : (float)$prodSchedule[$i][3]) . '</td>';

            $notes = trim($prodSchedule[$i][4]);
            $notes = !empty($notes) && $notes != 'NULL' && $notes != 'null' ? 1 : 0;
            $build .= '
            <td class="prodListNotes"><a onclick="showProdNotes(' . $prodSchedule[$i][0] . ', `'. ( !$notes ? '' : trim($prodSchedule[$i][4]) ) . '`)"><i class="bi bi-journal-text ' . ( $notes ? 'blueIcon' : '' ) . '"></i></td>
            <td>
                <input class="crewCntInput" onchange="updateLaborCnt(' . $prodSchedule[$i][0] . ')" id="crewCnt_' . $prodSchedule[$i][0] . '" min="0" type="number" value="' . (isset($prodSchedule[$i][12]) ? $prodSchedule[$i][12] : 0 ) . '" href="#">
                <div id="crewCntBox_' . $prodSchedule[$i][0] . '"></div>
            </td>
            <td>' . date_format(date_create($prodSchedule[$i][2]), "m/d/Y") . '</td>

            <td style="text-align:center">' . ( !$timeStudyH ? '00' : $timeStudyH ) . ':' . ( !$timeStudyM ? '00' : $timeStudyM ) . ':' . ( !$timeStudyS ? '00' : $timeStudyS ) . '</td>
            <td style="text-align:center">' . $estLabor . '</td>

            <td class="workOrderActions" style="text-align:center">
                <a href="#" title="View" onclick="viewProdItem(\'' . $prodSchedule[$i][1] . '\', \'productionSchedule\')"><span class="viewportBlueBtn">N</span></a>';
                $complete = $complete ? 1 : 0;
                $build .= '
                <a href="#" title="' . ( $complete ? 'View' : 'Complete' ) . '" onclick="' . ( $complete ? 'reviewCompleteWO' : 'completeProdItem' ) . '(\'' . $prodSchedule[$i][0] . '\', \'' . $prodSchedule[$i][6] . '\', \'' . $prodSchedule[$i][1] . '\', \'' . $prodSchedule[$i][14] . '\')"><span class="viewportGreenBtn">v</span></a>
                ';
                // <a href="#" title="Print" onclick="printWorkOrder(\'' . $prodSchedule[$i][0] . '\')"><span class="viewportBlueBtn">h</span></a>
                $build .= '
                <a href="#" title="Edit" onclick="editProdItem(\'' . $prodSchedule[$i][0] . '\')"><span class="viewportBlueBtn">p</span></a>
                <a href="#" title="Delete" onclick="deleteWorkOrder(\'' . $prodSchedule[$i][0] . '\', \'' . (float)$prodSchedule[$i][6] . '\', \'' . ( $complete ? 1 : 0 ) . '\')"><i class="bi bi-trash viewportRedBtn"></i></a>';
                if($_SESSION['searchState'] == 1) {
                    if($complete) {
                        $build .= '<a href="#"  class="roundBtnGray"></a>';
                    }elseif(!$prodSchedule[$i][19]) {
                        $build .= '<a href="#" onclick="statusOH(' . $prodSchedule[$i][0] . ', \'' . $prodSchedule[$i][1] .'\', \'' . $prodSchedule[$i][3] . '\', 1)" class="roundBtnGreen"></a>';
                    }else {
                        $build .= '<a href="#" onclick="statusOH(' . $prodSchedule[$i][0] . ', \'' . $prodSchedule[$i][1] .'\', \'' . $prodSchedule[$i][3] . '\', 0)" class="roundBtnRed"></a>';
                    }
                }
            $build .= '
            </td>
        </tr>
        ';

        // If Unscheduled, Don't build Shift 1 & Shift 2
        if(!$unscheduled) {
            // Shift 1
            if($prodSchedule[$i][21] == 1) {
                $shift1 .= $build;
            }
            // Shift 2
            if($prodSchedule[$i][21] == 0) {
                $shift2 .= $build;
            }
            // Reset Build
            $build = '';
        }
    }

    // If Unscheduled, Don't build Shift 1 & Shift 2
    if(!$unscheduled) {
        if($shift1 != '') {
            $html .= '
            ' . ( !$pastDue ? '<h2 class="shiftTitle">Shift 1</h2>' : '' ) . '
            <table id="filterTable" class="productionScheduleTable" ' . ( $pastDue ? 'class="pastDueTable"' : '') . '>';
    
            $html .= buildItemListHeader($pastDue, $state, 'filterTable');
            $html .= $shift1;
    
            $html .= ' 
            </table>';
        }
    
        if($shift2 != '') {
            $html .= '
            ' . ( !$pastDue ? '<h2 class="shiftTitle">Shift 2</h2>' : '' ) . '
            <table id="filterTable2" class="productionScheduleTable" ' . ( $pastDue ? 'class="pastDueTable"' : '') . '>';
    
            $html .= buildItemListHeader($pastDue, $state, 'filterTable2');
            $html .= $shift2;
    
            $html .= ' 
            </table>';
        }
    }else {
        $html = $build;
    }

    return $html;
}


function buildStatusOH($parentSku, $childSku, $compQty) {

    $workOrderData = getWorkOrderData($parentSku, $compQty);      
            
    $workOrderData = appendAvailabilityBOM($workOrderData, $childSku);

    $html;

    $html = '<form method="post" action="." id="dataEntryForm">
    <input hidden name="woNo" id="woNo" value="' . $parentSku . '"></input>
    <input hidden name="compQty" id="compQty" value="' . $compQty .'"></input>
    <h1>BOM Data</h1>
    <table>
        <th id="woNo_' . $parentSku . '">WO#</th>
        <th>CompItem#</th>
        <th>DESC1</th>
        <th>Unit</th>
        <th># in Set</th>
        <th>Usage Est</th>
        <th>QAV/QOH</th>
        <th>Bal</th>
        <th></th>';
        


    for($i = 0; $i < sizeof($workOrderData); $i++){
            $html .= '<tr>
                                <td>' . $workOrderData[$i][0] . '</td>
                                <td>' . $workOrderData[$i][1] . '</td>
                                <td>' . $workOrderData[$i][2] . '</td>
                                <td>' . $workOrderData[$i][3] . '</td>
                                <td>' . $workOrderData[$i][4] . '</td>
                                <td>' . $workOrderData[$i][5] . '</td>
                                <td>' . $workOrderData[$i][7] . '</td>
                                <td>' . $workOrderData[$i][8] . '</td>
                                <td>';
                                if($workOrderData[$i][9] == false) {
                                    $html .= '<a href="#" onclick="openInvRollForward(' . $parentSku . ', `' . $workOrderData[$i][4] . '`, `' . $workOrderData[$i][1] . '`, `' . $workOrderData[$i][0] . '`, `' . $compQty . '`)" class="roundBtnGreen"></a>';
                                }else {
                                    $html .= '<a href="#" onclick="openInvRollForward(' . $parentSku . ', `' . $workOrderData[$i][4] . '`, `' . $workOrderData[$i][1] . '`, `' . $workOrderData[$i][0] . '`, `' . $compQty . '`)" class="roundBtnRed"></a>';
                                }
                                $html .= '</td></tr>';
                                $html .= '';
        }
        
        
        
        $html .= '</table>';
            
        
        // if (!$review) {
        //     // $completeWOHTML .= '<button type="submit" name="action" id="action" value="complete"><span>i</span></button>';
        //     $completeWOHTML .= '<a href="#" id="buttonA" onclick="completeWOSubmit(' . $workOrderNumber . ', \'' . $compQty . '\')"><span>i</span></a>';
        // }
        
        $html .= '</form>';


    return $html;

}

function buildScheduleWO($prodSchedule, $employeeList, $message, $searchTerm) { 

    $html = '
    <div id="productionItems">
    <h1>Schedule WO</h1>
        <div class="grid">
            <div id="prodHeader" class="unit one-third">
                <a href="/production/index.php?action=productionSchedule" title="Current"><span class="viewportBlueBtn">j</span></a>
                <a href="#" onclick="goToWorkOrder(0)" title="Go To WO"><span class="viewportBlueBtn">G</span></a> 
            </div>
            <div class="unit one-third">
                <label for="searchTerm">Search</label>
                <input type="search" class="searchTerm workOrderSearchInput" name="selectedDate" id="selectedDate" value="' . $searchTerm . '" onkeyup="enterSubmitProdSearch(event);" placeholder="Enter Search Term">
                <a href="#" title="Search" id="prodSearchBtn" onclick="submitProdSearchSku();"><span id="searchBtn">s</span></a>
                <div id="searchList"></div>
                <input id="searchListInput" class="hide" value="">
            </div>
            <div id="showToggle" class="unit one-third">
            </div>
        </div>

        <div id="reScheduleNav"></div>

        <div id="prodList" class="grid">
    ';

    $_SESSION['woMenu'] = 1;

    $html .= buildProdItemsList($prodSchedule, $message);

    if(isset($_SESSION['goBackProd'])) {
    }else {
        $_SESSION['goBackProd'] = null;
    }
    if($_SESSION['goBackProd'] == $searchTerm) {   
    }else {
        if(isset($_SESSION['goBackProd'])) {
            $html .= '
            <div id="tempProdOptions">
                <a href="#" onclick="submitProdSearchSku(0, \'' . $_SESSION['goBackProd'] . '\')" title="Go Back to ' . $_SESSION['goBackProd'] . '"><span>n</span></a>
            </div>
            ';
            $_SESSION['goBackProd'] = null;
        }
    }

    $html .='
    </p>';

    return $html;

}

function buildProdPastDue($prodSchedule, $employeeList, $message, $selectedDate) {

    $html = '
            <div id="productionItems">
            <h1>Past Due Schedule WO</h1>
                <nav id="production">
                    <div class="grid">
                    <div class="unit one-third">
                        <a href="/production/index.php?action=productionSchedule" title="Current"><span>j</span></a>
                        <a href="/production/index.php?action=scheduleWO" title="Schedule"><span>K</span></a>
                    </div>
                        
                    <div class="unit one-third">

                                <input type="date" class="searchTerm" name="selectedDate" id="selectedDate" value="' . ($selectedDate == null ? "" : $selectedDate) . '" onchange="pastDue(value)">

                    </div>
                    <div class="unit one-third">
                    </div>
                    </div>
                </nav>
            </form>
    ';

    $html .= buildProdItemsList($prodSchedule, $message);

    $html .= '</div>';

    return $html;
}
function buildPastDueList($prodSchedule, $message) {

    $html = '
    <h1 style="margin-left: 0%;">Past Due WOs</h1>

    <div id="pdList" class="pdList" onscroll="checkPdScroll();">
    ';

    if($prodSchedule == false || $prodSchedule == null) {
        $html .= 'No Past Due WOs Found.';
    }else {
        $html .= buildProdItemsList($prodSchedule, $message, true);
    }

    $html .= '</div>';

    return $html;

}

function buildSubmitWOConfirm($woNo, $compQty) {
    $html = null;

    $html .= '
    <div style="margin-bottom: 0.5vh">
        <h4 id="confirmWOText">Are you sure you want to complete WO#: ' . $woNo . '?</h4>
        <button href="#" class="bs-btn btn-red" onclick="cancelWOSubmitConfirm(\'' . $woNo . '\', \'' . $compQty . '\')">Cancel</button>
        <button href="#" class="bs-btn btn-green" onclick="completeWOSubmit(\'' . $woNo . '\', \'' . $compQty . '\')">Confirm</button>
    </div>
    ';

    return $html;

}

function buildCancelSubmitWOConfirm($woNo, $compQty) {
    $html = null;

    $html .= '<a class="bs-btn btn-blue" onclick="submitWOConfirm(\'' . $woNo . '\', \'' . $compQty . '\')">Complete <i class="bi bi-check2"></i></a>';

    return $html;

}

function buildInvRollForward($parentSku, $qtyPer, $childSku) {

    // Get WO Data
    $workOrderData = getWorkOrderData($parentSku, $qtyPer);      

    // Get QOH
    $qohArr = getQOH($childSku);

    if(isset($qohArr[0][0])) {
       $qoh = $qohArr[0][0]; 
    }else {
        $qoh = 0;
    }
    if($qoh < 0 || $qoh == null){
        $qoh = 0;
    }

    // Get Today's Date
    $today = date("Y/m/d");

    // invRollForward($parentSku, $qtyPer, $qoh, $childSku)
    $compData = invRollForward($parentSku, $qtyPer, $qoh, $childSku);

    $html;
    
    if(!$workOrderData) {$message = 'No Work Order Data.';}

    if(!isset($message)) {$message = '';}

    $html = '
      <h1>Comp Data</h1>
      <h3 class="fadeout">' . $message . '</h3>
      <table>
        <th>Date</th>
        <th>Reference</th>
        <th>Qty</th>
        <th>Bal</th>
        <tr>
            <td>' . $today . '</td>
            <td></td>
            <td></td>
            <td>' . $qoh . '</td>
        </tr>
        ';
        
    // $html .= var_dump($workOrderData);
        
    for($i = 0; $i < sizeof($compData); $i++){
        $html .= '<tr>
                        <td>' . $compData[$i][0] . '</td>
                        <td>' . $compData[$i][1] . '</td>
                        <td>' . $compData[$i][2] . '</td>
                        <td>' . $compData[$i][3] . '</td>
                    </tr>';
    }
        
        
        
        $html .= '</table>';
    
    
      return $html;
    
    }

    function buildWOSkuRundownMenu($workOrderNumber, $skuNumber) {
        $data = getWOSkuRundownData($workOrderNumber, $skuNumber);

        if($data) {
            $html = '
        
            <h1 style="margin: auto;">' . $skuNumber . '</h1>
            <div class="eUsageTableOverflow">
            <table class="woSkuRundownTable">
    
                <tr>
                    <th>Usage Date</th>
                    <th>InventoryID</th>
                    <th>Lot Code</th>
                    <th>Lot Totals</th>
                </tr>';
    
            for($i = 0; $i < sizeof($data); $i++) {
                $html .= '
                <tr>
                    <td>' . $data[$i][14] . '</td>
                    <td>' . $data[$i][2] . '</td>
                    <td>' . $data[$i][12] . '</td>
                    <td>' . $data[$i][10] . '</td>
                </tr>';
            }
                
    
            $html .= '</table>
            </div>';
    
            return $html;
        }else {
            return 'No eUsage Data for sku#: ' . $skuNumber;
        }

    }



function buildProductionNotes($workOrderNumber, $notes) {
    
    $dialogue = getWorkOrderDialogue($workOrderNumber);

    $html = '
    <div class="productionNotes">
        <h3>' . $workOrderNumber . '</h3>
        <p class="notes">' . ($notes == 'undefined' ? 'No Notes...' : $notes) . '</p>
        <hr>
        <div class="productionDialogue" id="productionDialogue">';

        // $html .= var_dump($dialogue);

        if($dialogue == null) {
            $html .= 'No Dialogue...';
        }else {
            for($i = 0; $i < sizeof($dialogue); $i++){
                $html .= '
                <div class="dialogueLi">
                    <p>' . $dialogue[$i][2] . ' @ ' . $dialogue[$i][3] . '</p>
                    <div class="message">' . $dialogue[$i][1] . '</div>
                </div>
                ';
            }
        }

        $html .= '<div id="end"></div></div></div>';
    $html .= '
    <div class="postDialogue">
        <a href="#" title="Refresh" onclick="showProdNotes(' . $workOrderNumber . ', `' . $notes . '`)"><i class="bi bi-arrow-counterclockwise"></i></a>
        <input onkeyup="countCharacters()" id="message" maxlength="254" placeholder="Enter Note...">
        <button class="bs-btn btn-blue" onclick="postComment(' . $workOrderNumber . ', `' . $notes . '`)">Post</button>
    </div>
    <div id="charCnt">
        <p id="current">0</p>
        <p>/ 254</p>
    </div>';

    return $html;
}

function buildWOConfirmationMenu() {

    $html = '
    <div class="woConfirmMenu">
    <h1>WO Confirmation</h1>
    <input class="hide" id="completionStatus" value="1">
    <div class="woConfirmHeader">
        <input id="searchWOInput" onkeydown="Javascript: if (event.keyCode==13) searchWOConfirm(2);" placeholder="Search...">
        <a onclick="searchWOConfirm(2)"><i class="bi bi-search"></i></a>
        <a onclick="searchWOConfirm(2, 1)"><i class="bi bi-arrow-clockwise"></i></a>
    </div>
    <div id="completedWOPH">
    ' . buildWOConfirmationTable(null, 2) . '
    </div>
    </div>
    ';

    return $html;

}

function buildWORejectsMenu() {

    $html = '
    <div class="woConfirmMenu">
    <h1>WO Rejects</h1>
    <input class="hide" id="completionStatus" value="3">
    <div class="woConfirmHeader">
        <input id="searchWOInput" onkeydown="Javascript: if (event.keyCode==13) searchWOConfirm(3);" placeholder="Search...">
        <a onclick="searchWOConfirm(3)"><i class="bi bi-search"></i></a>
        <a onclick="searchWOConfirm(3, 1)"><i class="bi bi-arrow-clockwise"></i></a>
    </div>
    <div id="completedWOPH">
    ' . buildWOConfirmationTable(null, 3) . '
    </div>
    </div>
    ';

    return $html;

}

function buildWOConfirmationTable($searchTerm = null, $completeStatus) {

    $completedOrders = getWOAwaitingConfirmation($searchTerm, $completeStatus);

    $html = '
    <table>
        <tr>
            <th class="min">Usage Date</th>
            <th class="min">Product Code</th>
            <th class="min">WO#</th>
            <th class="min">Sku#</th>
            <th class="min">Priority</th>
            <th>Customer</th>
            <th>Description</th>
            <th class="min">Qty Comp/Req</th>
            <th>Notes</th>
            <th class="min">Crew Cnt</th>
            <th class="min">Req Pro Date</th>
            <th class="min">Time Study (H:M:S)</th>
            <th class="min">Est Labor Hours</th>
            <th></th>
        </tr>';

    for($i = 0; $i < sizeof($completedOrders); $i++){

        $timeStudyInSecs = isset($completedOrders[$i][12]) ? $completedOrders[$i][12] : null;
        if($timeStudyInSecs != null) {
            $timeStudyH = floor($timeStudyInSecs / 3600);
            $timeStudyM = floor(($timeStudyInSecs / 60) % 60);
            $timeStudyM = $timeStudyM < 10 ? '0' . $timeStudyM : $timeStudyM;
            $timeStudyS = $timeStudyInSecs % 60;
        }else {
            $timeStudyH = $timeStudyM = $timeStudyS = '00';
        }
    
        $estLaborInSecs = ( $completedOrders[$i][15] ? (int)$completedOrders[$i][14] : (int)$completedOrders[$i][13] );
        $hour = floor($estLaborInSecs / 3600);
        $min = floor(($estLaborInSecs / 60) % 60);
        $min = $min < 10 ? '0' . $min : $min;
        $sec = $estLaborInSecs % 60;
        $estLabor = ( !$hour ? '00' : $hour ) . ':' . ( !$min ? '00' : $min ) . ':' . (!$sec ? '00' : $sec ) . '';

        $html .= '
        <tr>
            <td>' . $completedOrders[$i][0] . '</td>
            <td title="' . $completedOrders[$i][16] . '">' . $completedOrders[$i][1] . '</td>
            <td>' . $completedOrders[$i][2] . '</td>
            <td>' . $completedOrders[$i][3] . '</td>
            <td>' . $completedOrders[$i][4] . '</td>
            <td>' . $completedOrders[$i][5] . '</td>
            <td>' . $completedOrders[$i][6] . '</td>
            <td>' . $completedOrders[$i][7] . ' / ' . $completedOrders[$i][8] . '</td>
            <td>' . $completedOrders[$i][9] . '</td>
            <td>' . $completedOrders[$i][10] . '</td>
            <td>' . $completedOrders[$i][11] . '</td>
            <td>' . $timeStudyH . ':' . $timeStudyM . ':' . $timeStudyS . '</td>
            <td>' . $estLabor . '</td>';
            $html .= '
            <td><a href="#" onclick="workOrderRundown(\'' . $completedOrders[$i][2] . '\', \'' . $completedOrders[$i][7] . '\', \'' . $completedOrders[$i][3] . '\', \'' . $completedOrders[$i][6] . '\', \'' . $completeStatus . '\')"><i class="bi bi-check-circle"></i></a></td>
        </tr>
        ';

    }

    $html .= '
    </table>
    ';

    return $html;
}

function buildWORundown($workOrderNumber, $compQty, $skuNumber, $desc, $completeStatus) {
    
    // Get Data
    $workOrderData = getUsageData($workOrderNumber, $compQty, $skuNumber);      

    // Define imperfect work order trigger
    $imperfection = false;

    // Build Modal
    $html = '
    <div class="woRundownMenu">
    <h1>WO Completion Confirmation</h1>
    <div class="completeWOHeader">
        <p>WO#: ' . $workOrderNumber . '</p>
        <p>SKU#: ' . $skuNumber . '</p>
        <p>DESC: ' . $desc . '</p>
    </div>
    <table style="margin-bottom: 10px">
    <tr>
        <th>CompItem#</th>
        <th>DESC1</th>
        <th>Unit</th>
        <th># in Set</th>
        <th>Estimate Usage</th>
        <th>Actual Usage</th>
        <th>Adjustment</th>
        <th></th>
        <th></th>
    </tr>
    ';

    for($i = 0; $i < sizeof($workOrderData); $i++){
        $html .= '
        <tr>
            <td>' . $workOrderData[$i][0] . '</td>
            <td>' . $workOrderData[$i][1] . '</td>
            <td>' . $workOrderData[$i][2] . '</td>
            <td>' . $workOrderData[$i][3] . '</td>
            <td>' . $workOrderData[$i][4] . '</td>
            <td>' . $workOrderData[$i][5] . '</td>
            <td>' . $workOrderData[$i][6] . '</td>
            <td style="min-width: 3em !important; ">
                <a class="woSkuRundownBtn" onclick="skuRundown(' . $workOrderNumber . ', `' . $workOrderData[$i][0] . '`)"><i class="bi bi-card-list"></i></a>
            </td>
            <td>' . $workOrderData[$i][8] . '</td>
        </tr>
        ';
        if($workOrderData[$i][4] > $workOrderData[$i][5]) {
            $imperfection = true;
        }
    }

    $html .= '
    </table>

    <div id="skuRundown"></div>';

    if($completeStatus == 3) {
        $html .= '
        <button onclick="setCompletionStatus(\'' . $workOrderNumber . '\', 0, \'' . $completeStatus . '\')" class="bs-btn btn-blue">Push Back</button>
        <button onclick="confirmCompletion(\'' . $workOrderNumber . '\', \'' . $imperfection . '\', \'' . $compQty . '\', \'' . $completeStatus . '\')" class="bs-btn btn-green">Complete</button>
        ';
    }else {
        $html .= '
        <button onclick="setCompletionStatus(\'' . $workOrderNumber . '\', 3, \'' . $completeStatus . '\')" class="bs-btn btn-red">Reject</button>
        <button onclick="confirmCompletion(\'' . $workOrderNumber . '\', \'' . $imperfection . '\', \'' . $compQty . '\', \'' . $completeStatus . '\')" class="bs-btn btn-green">Confirm</button>
        ';
    }

    $html .= '
    </div>
    ';

    return $html;

}