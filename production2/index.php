<?php
session_start();

require 'model.php';
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
    $workOrderNumber = filterString($_POST['workOrderNumber']);
    $workOrderData = getWorkOrder($workOrderNumber);
    
    if($workOrderData){
        $action = 'production';
        $subAction = 'completeWOEx';

        //Get WO data
        $employeeList = getCrewLeaders();
        $elapsedTime = $workOrderData[0][8];
        $elapsedHour = explode(':', $elapsedTime)[0];
        $elapsedMin = substr($elapsedTime, strpos($elapsedTime, ':')+ 1, 2);
    }else{
        $action = 'production';
        $subAction = 'completeWO';
        $message = 'work order #: "' . $workOrderNumber . '" not found.';
    }
    
}elseif($action == 'updateWO'){
    $workOrderNumber = filterString($_POST['workOrderNumber']);
    $crewLeader = filterNumber($_POST['crewLeader']);
    $completionDate = date('Y-m-d', strtotime(filterString($_POST['completionDate'])));
    $crewCount = filterNumber($_POST['crewCount']);
    $quantity = filterNumber($_POST['quantity']);
    $curTimeStamp = date('Y-m-d H:i:s');
    $userID = $_SESSION['userID'];
    $hours = (filterNumber($_POST['elapsedHrs']) == null ? 0 : filterNumber($_POST['elapsedHrs']));
    $mins = (filterNumber($_POST['elapsedMins']) == null ? 0 : filterNumber($_POST['elapsedMins']));
    $completionTime = $hours . ':' . $mins . ':' . 00;
    
    $updateWorkOrder = updateWorkOrder($workOrderNumber
                                        , $crewLeader
                                        , $completionDate
                                        , $crewCount
                                        , $quantity
                                        , $completionTime
                                        , $curTimeStamp
                                        , $userID);
    
    if($updateWorkOrder){
        $action = 'production';
        $subAction = null;
    }else{
        //redirect and add message
        $action = 'production';
        $subAction = 'completeWOEx';
        $message = 'failed to complete work order.';
        
        //Get WO data
        $employeeList = getCrewLeaders();
        $workOrderNumber = filterString($_POST['workOrderNumber']);
        $workOrderData = getWorkOrder($workOrderNumber);
        $elapsedTime = $workOrderData[0][8];
        $elapsedHour = explode(':', $elapsedTime)[0];
        $elapsedMin = substr($elapsedTime, strpos($elapsedTime, ':')+ 1, 2);
    }
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
    
}elseif($action == 'productionSchedule'){
    
    if(isset($_GET['returnDate'])){
        $selectedDate = date('Y-m-d', strtotime(filterString($_GET['returnDate'])));
    }
    $prodSchedule = getProdSchedule($selectedDate);
    $employeeList = getCrewLeaders();
    
    if (!$prodSchedule){
        $message = 'No scheduled Work Orders';  
    }
}elseif($action == 'pastDue'){
    $prodSchedule = getPastDue();
    $employeeList = getCrewLeaders();
    
    if (!$prodSchedule){
            $message = 'No past due Work Orders';  
    }
}elseif($action == 'workOrder'){
        $verifyWO = validateWorkOrderNumber($workOrderNumber);

    
    if($verifyWO){
        $prodSchedule = getWorkOrder($workOrderNumber);
        $employeeList = getCrewLeaders();
        $selectedDate = null;

        if (!$prodSchedule){
                $message = 'Work Order Unavailable';  
        }
        
        $_SESSION['returnStr'] = '<a href="/logIn/index.php?action=production" id="exit"><span>x</span></a>';
    }else{
        if($workOrderNumber != null){
            $message = 'Work Order ' . $_POST['workOrderNumber'] . ' Not Found';
        }
        $action = 'production';
        $subAction = 'selectWorkOrder';
    }
}elseif($action == 'updateScheduleDate'){

    //Get new date and work order
    $newDate = filterString($_POST['updateScheduleDate']);
    $workOrderNumber = filterString($_POST['workOrderNumber']);
    
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
        if($message){
            $selectedDate = filterString($_POST['selectedDate']);
        }else{
            $selectedDate = filterString($_POST['updateScheduleDate']);
        }
        $selectedDate = filterString($_POST['updateScheduleDate']);
        $prodSchedule = getProdSchedule($selectedDate);
        $employeeList = getCrewLeaders();
        $action = 'productionSchedule';
        if (!$prodSchedule){
            $message = 'No scheduled Work Orders';  
        }
    }
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
 
}elseif($action == 'scheduleWO'){
    
    if(isset($_POST['searchTerm'])){
            $searchTerm = filterString($_POST['searchTerm']);
            $_SESSION['scheduleSearchTerm'] = $searchTerm;
    }elseif(isset($_SESSION['scheduleSearchTerm'])){
        $searchTerm = $_SESSION['scheduleSearchTerm'];
    }else{
        $searchTerm = null;
    }
    
    $prodSchedule = getUnscheduledWO($searchTerm);
    $employeeList = getCrewLeaders();
    
    if (!$prodSchedule){
            $message = 'No scheduled Work Orders';  
    }    
}elseif($action == 'viewItem'){
    $_SESSION['return'] = 'returnToProduction'; 
    if(isset($searchTerm)){
        $_SERVER['scheduleSearchTerm'] = $searchTerm;
    }
    if(isset($selectedDate)){
        $_SESSION['productionSelectedDate'] = $selectedDate;
    }
    header('Location: /inventory/index.php?action=viewItem' . filterNumber($_GET['skuNumber']));
}elseif($action == 'exit'){
    $_SESSION['scheduleSearchTerm'] = null;
    $_SESSION['productionScheduleCurrentView'] = null;
    header('Location: /logIn/index.php?action=production');
}elseif($action == 'return'){
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
        $employeeList = getCrewLeaders();
    
        $prodSchedule = getWorkOrder($workOrderNumber);
        $selectedDate = null;
        $action = 'workOrder';
    }
}elseif($action == 'hot'){
    $hot = filterNumber($_GET['hot']);
    $updateHot = null;
    $workOrderNumber = filterString($_GET['workOrderNumber']);
    if($hot == 1){
        $updateHot = updateHot(0, $workOrderNumber);
    }else{
        $updateHot = updateHot(1, $workOrderNumber);
    }
    
    if (!$updateHot){
            $message = 'Hot Update Failed';  
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
        if (!$prodSchedule){
            $message = 'No scheduled Work Orders';  
        }
    }
}elseif($action == 'printWorkOrder'){
    $workOrderNumber = filterString($_GET['workOrderNumber']);
    $cutSheetData = getSubComponent($workOrderNumber);
    
    if(!$cutSheetData){
        $message = 'Cut Sheet Does not exsist';
    }else{
        $subAction = 'printCutSheet';
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
        if (!$prodSchedule){
            $message = 'No scheduled Work Orders';  
        }
    }
}elseif($action == 'complete'){
    //Verify if workorder is complete. If complete
    $complete = filterNumber($_GET['complete']);
    $updateComplete = null;
    $workOrderNumber = filterString($_GET['workOrderNumber']);
    if($complete == 1){
        $updateComplete = completeWO(0, $workOrderNumber);
    }else{
        $updateComplete = completeWO(1, $workOrderNumber);
    }
    
    if (!$updateComplete){
            $message = 'Failed to Complete WO';  
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
        if (!$prodSchedule){
            $message = 'No scheduled Work Orders';  
        }
    }
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
            $message .= 'Completion Date is set - ';
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
    $sjcHeaderView[0] = '<h2>Current Total Crew Count: Clocked In ' . $currentCrewCount[0] . ' / Assigned ' . $currentAssignedCrew[0] . '</h2>';
    
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