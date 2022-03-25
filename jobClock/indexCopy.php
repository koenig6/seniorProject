<?php
session_start();
date_default_timezone_set('America/Los_Angeles');

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$action = null;
$date = date("Y-m-d");
$ajax = false;
$employeeScanHTML = null;
$productionScheduleHTML = null;
$workOrderHeaderHTML = null;
$htmlData = null;

if (isset($_POST['action'])) {
 $action = $_POST['action'];
} elseif (isset($_GET['action'])) {
 $action = $_GET['action'];
}
require 'model.php';
require $_SERVER['DOCUMENT_ROOT'] . '/library/library3.php';

if($_SESSION['securityLevel'] < 3){
    header('location: /logIn/index.php');
    $_SESSION['message'] = 'Access Denied';
}elseif(timedOut()){
    header('location: /logIn/index.php');
}elseif($action == 'logOut'){
    logOut();
    header('location: /logIn/index.php');
}elseif($_SESSION['loggedIn'] != true){
    header('location: /logIn/index.php');
}elseif($action == 'workOrder'){
    $htmlData = buildWorkOrderView();
    
}elseif($action == 'refresh'){
    
    $htmlData = buildWorkOrderView();

    $data = json_encode($htmlData);
    echo $data;
     
    $ajax = true;
     
}elseif($action == 'jobClockSummaryCopy'){
    $htmlData = buildJobClockSummaryView();

    
}elseif($action == 'clockInEmployee'){
    //Get Data
    $keyCard = filterString($_GET['keyCard']);
    $workOrderNumber = filterString($_GET['workOrderNumber']);
    $error = null;
    $userID = $_SESSION['userID'];
    
    if(!$userID){
        $userID = 1;
    }
    
    //set current punch
    $punch = date('Y-m-d H:i:s');
    
    //get EmployeeID from keycard#
    $keyCardDecoded = keyCardDecoder($keyCard);
        
    //Assign keycode values
    $indvKeyCard = $keyCardDecoded[0];
    $employeeID = $keyCardDecoded[1];
    
    //Get employee data useing employeeID
    $employeeData = getEmployeeData($employeeID);
    
    //Clock in employee return error if fails
    $responseArr = punchJobClock($employeeID, $workOrderNumber, $punch, $userID);
    $error = $responseArr[0];
    $punch = $responseArr[1];
    $punchSuccess = $responseArr[2];
    
    $buildOverlayResponseHTML = buildOverlayResponse($employeeData, $workOrderNumber, $punch, $error);
    $htmlData = buildWorkOrderView($buildOverlayResponseHTML);
    
    $data = json_encode($responseArr);
    
    echo $data;
    $ajax = true;
    
}elseif($action == 'openWOLaborAssigner'){
    $workOrderNumber = filterString($_GET['workOrderNumber']);
    
    //Build labor work order
    $productionScheduleHTML = buildProductionSchedule($workOrderNumber);
    $employeeScanHTML = buildEmployeeScan($workOrderNumber);

}elseif($action == 'employeeAssignJob'){
    $workOrderNumber = filterString($_GET['workOrderNumber']);
    $employeeID = filterString($_GET['employeeID']);
    $departmentID = filterString($_GET['departmentID']);
    
    //Check to see if it is first punch of the day and if so then get punch in time
    //Check to see if there is any current punch in on another job and if so punch out that job and punch in on the new job
    
    //Get current punch list of job
    
    echo $employeeID;
    
    $ajax = true;
    
}elseif($action == 'newLoadTag'){
    $tranArr = null;
    $error = null;
    $workOrderNumber = filterString($_GET['workOrderNumber']);
    $inventoryID = filterString($_GET['inventoryID']);
    $userID = $_SESSION['userID'];
    
    //Get inventoryData and workOrderData
    $workOrderData = getWorkOrder($workOrderNumber);
    $inventoryData = getInventory($inventoryID);
    
    
    if($workOrderData[0][1] == $inventoryData[1]){
        //Check to see if work order has already been added.
        $duplicated = checkForDuplicate($workOrderNumber, $inventoryID);
        if(!$duplicated){
            assignLoadTag($inventoryID, $workOrderNumber, $userID);
        }else{
            $error = "Tag has already been added";
        }
    }else{
        $error = "Load Tag is not part of this Work Order";
    }
    
    //Check to see if inventoryID SKU = workorder SKU
    $buildOverlayResponseHTML = buildOverlayConfimation($workOrderNumber, $inventoryID, $error);
    $htmlData = buildWorkOrderView($buildOverlayResponseHTML);
    
    $data = json_encode($htmlData);
    
    echo $data;
    $ajax = true;
    
    
}elseif($action == 'clockInGroup'){
    $laborGroupID = filterNumber($_GET['groupID']);
    $workOrderNumber = filterString($_GET['workOrderNumber']);
    $userID = $_SESSION['userID'];
    
    $laborGroup = getLaborGroup($laborGroupID);
    $cnt = sizeof($laborGroup);
    
    //set current punch
    $punch = date('Y-m-d h:i:s');
    
    //Set Error Structure
    $error = null;
    $errorArr = null;
    $errorCnt = 0;
    $successArr = null;
    $successCnt = 0;
    
    
    if($cnt > 0){
        for($i = 0; $i < $cnt; $i++){
            $employeeID = $laborGroup[$i][0];
            $name = $laborGroup[$i][1];
            
            $responseArr = punchJobClock($employeeID, $workOrderNumber, $punch, 1);
            $error = $responseArr[0];
            $punch = $responseArr[1];
            
            
            if ($error){
                $errorArr[$errorCnt] = "$name - $error";
                $errorCnt++;
            }else{
                $successArr[$successCnt] = "$name - IN @ $punch";
                $successCnt++;
            }
        }
    }
    
    $buildOverlayResponseHTML = buildGroupOverlayResponse($successArr, $workOrderNumber, $punch, $errorArr);
    $htmlData = buildWorkOrderView($buildOverlayResponseHTML);
    
    $data = json_encode($htmlData);
    
    echo $data;
    $ajax = true;
    
}elseif($action == 'workOrderSwap'){
    $workOrderNumber = filterString($_GET['workOrderNumber']);
    $htmlData = buildWorkOrderSection($workOrderNumber);
    
    $myArr[0] = $htmlData;
    $data = json_encode($myArr);
    
    echo $data;
    $ajax = true;
    
}elseif($action == 'summaryAddEmployee'){
    $workOrderNumber = filterString($_GET['workOrderNumber']);
    $originWONumber = filterString($_GET['originWO']);
    $employeeID = filterString($_GET['employeeID']);
    $userID = $_SESSION['userID'];
    $departmentID = filterString($_GET['departmentID']);
    
    //set current punch
    $punch = date('Y-m-d H:i:s');
    
    if($departmentID == "false" || !$departmentID){
        $departmentID = null;
    }
    
    if($workOrderNumber == "productionSchedule"){
        $workOrderNumber = null;
    }
    
    //Clock in employee return error if fails
    $responseArr = punchJobClock($employeeID, $workOrderNumber, $punch, $userID, $departmentID);
    $error = $responseArr[0];
    $punch = $responseArr[1];
    $punchSuccess = $responseArr[2];

    //Rebuild workorderpanel
    $htmlData = buildWorkOrderSection($workOrderNumber);
    $htmlData2 = buildWorkOrderSection($originWONumber);
    $htmlData3 = buildUnassignedSection();
    
    $myArr[0] = $htmlData;
    $myArr[1] = $htmlData2;
    $myArr[2] = $htmlData3;
    $myArr[3] = $error;

    $data = json_encode($myArr);
    
    echo $data;
    $ajax = true;
    
}elseif($action == 'editEmployeeTime'){
    $employeeID = filterString($_GET['employeeID']);
    $selectedDate = filterString($_GET['selectedDate']);
    
    $htmlData = buildEmployeeOverlay($employeeID, $selectedDate);
    
    $myArr[0] = $htmlData;

    $data = json_encode($myArr);

    echo $data;
    $ajax = true;
    
}elseif($action == 'employeeDeptAdd'){
    $employeeID = filterString($_GET['employeeID']);
    
    $myArr[0] = "employeeDeptAdd";

    $data = json_encode($myArr);
    
    echo $data;
    $ajax = true;
    
}elseif($action == 'viewDept'){
    $employeeID = filterString($_GET['transferId']);
    $woTarget = filterString($_GET['targetId']);
    $woOrigin = filterString($_GET['originId']);
    $workOrderAdd = filterString($_GET['workOrderAdd']);
    
    $htmlData = buildDeptList($employeeID, $woTarget, $woOrigin, $workOrderAdd);
    
    $myArr[0] = $htmlData;
    
    $data = json_encode($myArr);
  
    echo $data;
    $ajax = true;
    
}elseif($action == 'addToDept'){
    $transferId = filterString($_GET['transferId']);
    $workOrderNumber = filterString($_GET['workOrderNumber']);
    $departmentID = filterString($_GET['departmentID']);
    $userID = $_SESSION['userID'];
   
    $data = "$employeeID , $departmentID, $originWONumber, $userID";
    echo $data;
    $ajax = true;
    
}elseif($action == 'refreshSummary'){
    
    $activeJobs = getActiveJobs();
    $myArr[0] = $activeJobs;
    
    for($i = 0; $i < sizeof($activeJobs); $i++){
        $myArr[$i + 1] = buildWorkOrderSection($activeJobs[$i][0]);
    }
    
    $myArr[sizeof($activeJobs) + 1] = buildUnassignedSection();
    $data = json_encode($myArr);
    echo $data;
     
    $ajax = true;
     
}elseif($action == 'clockOut'){
    $myArr = null;
    $punchID = $_GET['punchID'];
    $employeeID = $_GET['employeeID'];
    $timeClockID = $_GET['timeClockID'];
    $userID = $_SESSION['userID'];
    
    //Update last punch to out now()
    $punch = date('Y-m-d H:i:s');
    $curDate = date('Y-m-d');
    
    
    //punch out of time clock
    $timeClockOut = punchOut($employeeID, $punch, $curDate);
    
    //punch out of job clock
    $jobClockOut = updateOutJobPunch($punchID, $punch, $userID, $timeClockID);
    
    if(!$timeClockOut){
        $myArr[0] = 0;
        $myArr[1] = "Error: Failed to insert into Time Clock";
    }elseif(!$jobClockOut){
        $myArr[0] = -1;
        $myArr[1] = "Error: Failed to insert into Job Clock";
    }else{
        $myArr[0] = 1;
    }
    
    
    $data = json_encode($myArr);
    echo $data;
     
    $ajax = true;
     
}elseif($action == 'editJobClockPunch'){
    $myArr = null;
    $error = null;
    $tcpPunchID = filterNumber($_GET['tcpPunchId']);
    $ajcPunchID = filterNumber($_GET['ajcPunchId']);
    $ajcLastPunchID = filterNumber($_GET['ajcLastPunchId']);
    $punch = date('Y-m-d H:i:s', strtotime(filterString($_GET['punchValue'])));
    $type = filterString($_GET['type']);
    $userID = $_SESSION['userID'];

    $nextJob = getJobClockData($ajcPunchID);
    $lastJob = getJobClockData($ajcLastPunchID);
    $currentTime = date('Y-m-d H:i:s');
    
    if($nextJob[0][5]){
        $nextPunch = date('Y-m-d H:i:s', strtotime($nextJob[0][5]));
        $currentPunch = date('Y-m-d H:i:s', strtotime($nextJob[0][4]));
    }else{
        $nextPunch = null;
    }
    
    if(isset($lastJob[0][4])){
        $lastPunch = date('Y-m-d H:i:s', strtotime($lastJob[0][4]));
    }
    
    if($type == 0){
        if($nextPunch && $punch >= $nextPunch){
            $error = "IN Punch must be set before next out punch. $punch vs $nextPunch";
        }elseif($punch > $currentTime){
            $error = "Punch cannot be made in the future.";
        }else{
            //Update job clock IN time
            $jobClockOut = updateInJobPunch($ajcPunchID, $punch, $userID);
            //Update time clock time IN time
            $inPunchEdit = editTimeClockINPunch($tcpPunchID, $punch, $userID);

            if(!$jobClockOut){
                $error = "Error: Job Clock update failed.";
            }elseif(!$inPunchEdit){
                $error = "Error: Time Clock update failed.";
            }
        }
    }elseif($type == 1){
        if($nextPunch && $punch >= $nextPunch){
            $error = "Punch must be set before next IN punch. $nextPunch vs $punch";
        }elseif($punch <= $lastPunch){
            $error = "Punch must be set after pervious IN punch.";
        }elseif($punch > $currentTime){
            $error = "Punch cannot be made in the future.";
        }else{
            //Update Last out punch to match set IN punch
            $jobClockOut1 = updateInJobPunch($ajcPunchID, $punch, $userID);
            //Update current IN punch
            $jobClockOut2 = updateOutJobPunch($ajcLastPunchID, $punch, $userID);
            
            if(!$jobClockOut1 || !$jobClockOut2){
                $error = "Error: Job Clock update failed.";
            }
            
        }
    }elseif($type == 2){
        if($punch <= $currentPunch){
            $error = "Punch must be set after pervious IN punch.";
        }elseif($punch > $currentTime){
            $error = "Punch cannot be made in the future.";
        }else{
            //Update current IN punch
            $jobClockOut = updateOutJobPunch($ajcPunchID, $punch, $userID);
            //Update time Clock out Punch
            $outPunchEdit = editTimeClockOUTPunch($tcpPunchID, $punch, $userID);
            
            if(!$outPunchEdit || !$jobClockOut){
                $error = "Error: Job Clock update failed.";
            }
            
        }
    }
    
    
    if($error){
        $myArr[0] = 0;
        $myArr[1] = $error;
    }else{
        $myArr[0] = 1;
    }
    
    $data = json_encode($myArr);
    echo $data;
     
    $ajax = true;
     
}elseif($action == 'deleteJobClockPunch'){
    $myArr = null;
    $error = null;
    $delete = null;
    $update = null;
    $tcpPunchID = filterNumber($_GET['tcpPunchId']);
    $ajcPunchID = filterNumber($_GET['ajcPunchId']);
    $ajcLinkedPunchId = filterNumber($_GET['linkedPunchId']);
    $type = filterString($_GET['type']);
    $userID = $_SESSION['userID'];
    
    $deletedJob = getJobClockData($ajcPunchID);
    
    $deletedIN = date('Y-m-d H:i:s', strtotime($deletedJob[0][4]));
    
    if($deletedJob[0][5]){
        $deletedOUT = date('Y-m-d H:i:s', strtotime($deletedJob[0][5]));
    }else{
        $deletedOUT = null;
    }
    
    
    //If type = 0, update linkedr IN to deleted IN and update linked timeClockID
    if($type == 0){
        $delete = deleteAdvancedJobPunch($ajcPunchID);
        if($delete && $ajcLinkedPunchId && $ajcLinkedPunchId != $ajcPunchID){
            $update = updateInJobPunch($ajcLinkedPunchId, $deletedIN, $userID, $tcpPunchID);
        }elseif($delete){
            $update = 1;
        }
        
    }elseif($type == 1){
        $delete = deleteAdvancedJobPunch($ajcPunchID);
        if($delete){
            $update = updateInJobPunch($ajcLinkedPunchId, $deletedIN, $userID, $tcpPunchID);
        }
    }elseif($type == 2){
        //if last punch update linked OUT TO deleted OUT, update linked timeClockID
        $delete = deleteAdvancedJobPunch($ajcPunchID);
        if($delete){
            $update = updateOutJobPunch($ajcLinkedPunchId, $deletedOUT, $userID, $tcpPunchID);
        }
    }
    
    if(!$delete && !$update){
        $error = 'Deletion Failed.';
    }elseif(!$update){
        $error = 'Failed to update.';
    }elseif(!$delete){
        $error = 'Failed to delete';
    }
    
    if($error){
        $myArr[0] = 0;
        $myArr[1] = $error;
    }else{
        $myArr[0] = 1;
    }
    
    
    $data = json_encode($myArr);
    echo $data;
     
    $ajax = true;
     
}elseif($action == 'insertJobPunch'){
    $myArr = null;
    $error = null;
    $employeeID = filterNumber($_GET['employeeID']);
    $workOrderNumber = filterNumber($_GET['workOrderNumber']);
    $punch = date('Y-m-d H:i:s', strtotime(filterString($_GET['insertTime'])));
    $lastPunchID = filterNumber($_GET['lastPunchID']);
    $userID = $_SESSION['userID'];
    
    $lastJob = getJobClockData($lastPunchID);
    $currentTime = date('Y-m-d H:i:s');
    
    if(isset($lastJob[0][4])){
        $lastPunch = date('Y-m-d H:i:s', strtotime($lastJob[0][4]));
    }
    
    //Validate Workorder
    $workOrderData = getWorkOrder($workOrderNumber);
    
    if(!$workOrderData || $workOrderData[0][10] == TRUE){
        $error = "Work Order is not valid";
    }
    
    if(!$error){
        if($punch <= $lastPunch){
            $error = "Punch must be set after pervious IN punch.";
        }elseif($punch > $currentTime){
            $error = "Punch cannot be made in the future.";
        }else{
            //Clock in employee return error if fails
            $responseArr = punchJobClock($employeeID, $workOrderNumber, $punch, $userID);
            
        }
    }
    
    if($error){
        $myArr[0] = 0;
        $myArr[1] = $error;
    }else{
        $myArr[0] = 1;
    }
    
    $data = json_encode($myArr);
    echo $data;
     
    $ajax = true;
}elseif($action == 'editDepartmentList'){
    $myArr = null;
    $error = null;
    $departmentID = filterString($_GET['departmentID']);
    
    $departmentList = getDepartments();
    
    $departmentListHTML = buildDepartmentList($departmentList);
    
    $myArr[0] = $departmentListHTML;
    
    $data = json_encode($myArr);
    echo $data;
     
    $ajax = true;
     
}elseif($action == 'updateJobDepartment'){
    $myArr = null;
    $error = null;
    $departmentID = filterString($_GET['departmentID']);
    $ajcPunchId = filterString($_GET['ajcPunchId']);
    $userID = $_SESSION['userID'];
    
    $update = updateJobDepartment($departmentID, $ajcPunchId, $userID);
    
    if(!$update){
        $error = "Update Failed";
    }
    
    $myArr[0] = $error;
    
    $data = json_encode($myArr);
    echo $data;
     
    $ajax = true;
     
}elseif($action == ''){
    $myArr = null;
    $error = null;
    $punchID = filterString($_GET['punchID']);
    $punchValue = date('H:i:s', strtotime(filterString($_GET['punchValue'])));
    $type = $_GET['type'];

    
    
    if($error){
        $myArr[0] = true;
    }else{
        
    }
    
    $myArr[0] = "test";
    
    $data = json_encode($myArr);
    echo $data;
     
    $ajax = true;
     
}else{
}


if (!$ajax){
    include 'viewCopy.php';
}

function buildDepartmentList($departmentList){
    $html = null;
    $departmentID = filterString($_GET['departmentID']);
    
    $html .= '<select name="departmentList" id="departmentList" required>
                <option value="" disabled>Select Department</option>';

                    for($i = 0; $i < sizeof($departmentList); $i++){
                        if($departmentList[$i][0] == $departmentID){
                            $html .= '<option value=' . $departmentList[$i][0] . ' selected= "selected">' . $departmentList[$i][1] . '</option>';
                        }else{
                            $html .= '<option value=' . $departmentList[$i][0] . '>' . $departmentList[$i][1] . '</option>';
                        }
                    }
                    
    $html .= '</select>';
    
    return $html;
}


//this screen is when the work order # is clicked on from the Job Summary screen.  
function buildWorkOrderView($buildOverlayResponseHTML = null){
    $tranArr = null;
    
    //Retrieve workOrderNumber
    $workOrderNumber = filterString($_GET['workOrderNumber']);
    
    //Get work order data and assigned active labor and labor history
    $workOrderData = getWorkOrder($workOrderNumber);
    $currentLabor = getWorkOrderLabor($workOrderNumber, true);
    $laborHistory = getWorkOrderLabor($workOrderNumber, false);
    $assignedFG = getAssignedFinishGoods($workOrderNumber, $workOrderData[0][1]);
    
    //Calculate Totals
    $liveWorkOrderData = getLiveWorkOrderData($workOrderNumber);
         
    //Build workorder live data
    $workOrderHeaderHTML = buildWorkOrderHeader($workOrderData, $liveWorkOrderData);
    $activeLaborHTML = buildActiveLabor($currentLabor);
    $laborHistoryHTML = buildLaborHistory($laborHistory);
    $productionQtyHTML = buildProductionQty($assignedFG);
    
    //Refresh Headers, this only shows when first loading 
    $productionQtyHeader = '<h2>Production Quantity line 648  (' . $liveWorkOrderData[1] . ')</h2>';
    
    $tranArr[0] = $buildOverlayResponseHTML;
    $tranArr[1] = $workOrderHeaderHTML;
    $tranArr[2] = $activeLaborHTML;
    $tranArr[3] = $laborHistoryHTML;
    $tranArr[4] = $productionQtyHTML;
    $tranArr[5] = $productionQtyHeader;
    
    return $tranArr;
}

function buildJobClockSummaryView($buildOverlayResponseHTML = null){
    $tranArr = null;
    
    //Build workorder live data
    $jobClockSummaryHTML = buildWorkOrderSummary();
    
    $tranArr[0] = $jobClockSummaryHTML;
    
    return $tranArr;
}

function punchJobClock($employeeID, $workOrderNumber, $punch, $userID, $departmentID = null){
    $error = null;
    $responseArr = null;
    $punchSuccess = null;
    
    $test = $departmentID;
    //Check if Employee is IN
    $employeeIN = checkEmployeeIn2($employeeID, $punch);
    
    If($employeeIN){ //If Employee is clocked

        //Check if Employee is clocked into a job
        $jobClockPunch = getLastJobClockPunch($employeeID);
        
        //Get employees Time Clock punch In
        $timeClockPunch = getLastPunchData($employeeID, $punch);
        
        If($jobClockPunch && $jobClockPunch['start'] >= $timeClockPunch[2]){ //If punch is a transfer
            //Check to see employee is already on Job
            if($workOrderNumber != $jobClockPunch['workOrderNumber']){
                If($jobClockPunch && $jobClockPunch['start'] >= $timeClockPunch[2]){ //If punch is a transfer
                    //Clock out of last job
                    updateOutJobPunch($jobClockPunch['advancedJobClockID'], $punch, $userID);

                    //Clock In new Job with current date stamp
                    $punchSuccess = insertInJobPunch($employeeID, $departmentID, $workOrderNumber, $punch, $userID, null);
                    if(!$punchSuccess){
                        $error = 'Punch Failed';
                    }
                }else{
                    $error = "Error";
                }
                
            }else{
                $error = "Already assigned to job.";
            }
        }else{ //If First Job of the day
                //Clock in from start punch
                $punchSuccess = insertInJobPunch($employeeID, $departmentID, $workOrderNumber, $timeClockPunch[2], $userID, $timeClockPunch[0]);
                //Set punch to actual punch inserted
                $punch = $timeClockPunch[2];
                
                if(!$punchSuccess){
                    $error = 'Punch Failed';
                }
        }
    }else{
        //If employee is not clocked In Error
        $error = "Employee has not been Clocked In within 12 hours";
    }
    
    $responseArr[0] = $error;
    $responseArr[1] = $punch;
    $responseArr[2] = $punchSuccess;
    
    return $responseArr;
}

function buildEmployeeScan($workOrderNumber){
    
    $departments = getDepartments();
    
     $html = '<ul>
                <li><h1>' . $workOrderNumber . '</h1></li>
                <li><label for="departmentID">Select Department: This is line 750</label>
                    <select name="departmentID" id="departmentID" required>
                        <option value="" disabled selected>Select Customer This is line 752</option>';
                            for($i = 0; $i < sizeof($departments); $i++){
                                $html .=  '<option value=' . $departments[$i][0] . '>' . $departments[$i][1] . '</option>';
                            }
      $html .=  '</select></li>
                <li><label for="scanEmployee">Scan Employee #: This is line 757</label>
                <input type="text" name="scanEmployee" id="scanEmployee" autocomplete="off" autofocus onkeydown="Javascript: if (event.keyCode==13){ employeeAssignJob(\'' . $workOrderNumber . '\'); return false;}"></li>
                 </ul>';
      
      return $html;
}

function buildProductionSchedule($workOrderNumber){
    //Get Schedule
    $workOrder = null;
    //$productionSchedule = getProdSchedule(date('Y-m-d'));
    $productionSchedule = getProdSchedule('Y-m-d');
    //Get Work Order Details
    $workOrderDetails = getWorkOrderDetails($workOrderNumber);
    //Get currently assigned labor
    $currentAssignedLabor = getAssignedLabor($workOrderNumber, 1);
    $historyAssignedLabor = getAssignedLabor($workOrderNumber, 0);
    
    $html = '<h2>Scheduled WO# This is line 763</h2><ul>';
    
    for($i=0; $i < sizeof($productionSchedule); $i++){
        $html .= '<li>' . $productionSchedule[$i][0] . ':' . $productionSchedule[$i][14] . '</li>';
    }
    
    $html .= ' line 769</ul>';
    
    return $html;
}

function buildWorkOrderHeader($workOrderData, $dataArr){
    $html = null;
    
    $html = '<div class="grid">
                <div class="unit one-third">
                    <section>
                        <h2> ' . $workOrderData[0][1] . ' - ' . $workOrderData[0][14] . '</h2>
                        <h1>WorkOrder# line 781: ' . $workOrderData[0][0] . '</h1> 
                    </section>
                    <input type="text" id="workOrderNumber" value="' . $workOrderData[0][0] . '">
                </div>
                <div class="unit one-third">
                    <section>
                        <div class="grid">
                            <div class="unit one-quarter align-center">
                                <h2>Time Study line 789</h2>
                                <h3>' . ($workOrderData[0][18] = null ? "-" : $workOrderData[0][18]) . '</h3>
                            </div>
                            <div class="unit one-quarter align-center">
                                <h2>Act Time Study 793</h2>
                                <h3>' . $dataArr[2] . '</h3>
                            </div>
                            <div class="unit one-quarter align-center">
                                <h2>Total Quantity</h2>
                                <h3>' . $dataArr[1] . '</h3>
                            </div>
                            <div class="unit one-quarter align-center">
                                <h2>Total Hours</h2>
                                <h3>' . $dataArr[0] . '</h3>
                            </div>
                        </div>
                    </section>
                </div>
                <div class="unit one-third">
                    <section>
                        <ul>
                            <li>time study 810: ' . $dataArr[4] . '</li>
                        </ul>
                    </section>
                </div>
            </div>';
    
    return $html;
}

function buildProductionQty($assignedFG){
    $html = '<ul>';
       
    for($i = 0; $i < sizeof($assignedFG); $i++){
        $html .= '<li>' . $assignedFG[$i][0] . '. ' . $assignedFG[$i][1] . ' => ' . $assignedFG[$i][2] .
                '</li>';
    }
    $html .= '</ul>';
    
    return $html;
}

function buildActiveLabor($currentLabor){
    $html = '<ul>';
    
    for($i = 0; $i < sizeof($currentLabor); $i++){
        $html .= '<li>' . $currentLabor[$i][7] . ' ' . $currentLabor[$i][8] .
                '</li>';
    }
    $html .= '</ul>';
    
    return $html;
}

function buildLaborHistory($laborHistory){
    $html = '<ul>';
    
    for($i = 0; $i < sizeof($laborHistory); $i++){
        $html .= '<li>' . $laborHistory[$i][7] . ' ' . $laborHistory[$i][8] . '-' . $laborHistory[$i][9] .
                '</li>';
    }
    $html .= '</ul>';
    
    return $html;
}


function buildOverlayResponse($employeeData, $workOrderNumber, $punch, $error){
    $html = '<div class="overlay">';
    
    if(!$error){
        $html .= '<h1>' . $employeeData['firstName'] . ' ' . $employeeData['lastName'] . '</h1>
             <h2> IN @' . date('m-d-y h:i', strtotime($punch)) . '</h2>';
    }else{
        $html .= '<h1>' . $employeeData['firstName'] . ' ' . $employeeData['lastName'] . '</h1>
             <h2>' . $error . '</h2>';
    }
    
    $html .= '</div>';
    
    return $html;
}

function buildGroupOverlayResponse($successArr, $workOrderNumber, $punch, $errorArr){
    $html = '<div class="overlay">';
    
    if($successArr){
        $html .= '<h2>Success</h2>
                <ul>';
        for($i = 0; $i < sizeof($successArr); $i++){
            $html .= '<li>' . $successArr[$i] . '</li>';
        }
        $html .= '</ul>';
    }
    
    if($errorArr){
        $html .= '<h2>Error</h2>
                <ul>';
        for($i = 0; $i < sizeof($errorArr); $i++){
            $html .= '<li>' . $errorArr[$i] . '</li>';
        }
        $html .= '</ul>';
    }
    
    $html .= '</ul></div>';
    
    return $html;
}

function buildOverlayConfimation($workOrderNumber, $inventoryID, $error){
    $html = '<div class="overlay">';
    
    if(!$error){
        $html .= '<h1>TAG#:' . $inventoryID . ' => WO#:' . $workOrderNumber . '</h1>
             <h2>Tag added to Workorder</h2>';
    }else{
        $html .= '<h1>Error: ' . $inventoryID . '</h1>
             <h2>' . $error . '</h2>';
    }
    
    $html .= '</div>';
    
    return $html;
}

function buildWorkOrderSummary()
{
    //Get Data for Summary
    $activeSummaryData = getActiveSummaryData();
    $activeJobCount = totalActiveJobs();
    $currentProdSchedule= getProdSchedule(date('Y-m-d'));
    $unassignedEmployees = getUnAssignedEmployees();
    $clockedOut = getClockedOutEmployees();
    $currentWO = null;
    
    $html = '<div class="grid">
    <div class="unit half">
    <div class="grid">';
    $html .= '<div class="unit one-quarter">';

        //this allows for moving the workOrders to different columns
        if(isset($activeSummaryData[0]['workOrderNumber'])){
            $html .= '<section class="workOrderSection" id="w' . $activeSummaryData[0]['workOrderNumber'] .
                    '" ondrop="drop(event)" ondragover="allowDrop(event)" draggable="true" ondragstart="drag(event)">
                        <div id="deptAssign' . $activeSummaryData[0]['workOrderNumber'] . '" class="deptAssign">';
            $html .= '</div>
            
            <div class="activeWO">';

        

           $html .= buildWorkOrderSummarySubHeader($activeSummaryData, 0);

            $currentWO = $activeSummaryData[0]['workOrderNumber'];
        }else{
            $html .= '<section class="workOrderSection" ondrop="drop(event)" ondragover="allowDrop(event)">';
        }
        
        $history = false;
        $sectionCount = 1;

        
        for($j=0; $j < sizeof($activeSummaryData); $j++){

            if($activeSummaryData[$j]['workOrderNumber'] != $currentWO){

                if($history == true){
                    $html .= '</div>';
                }/*else{
                    $html .= '<div class="historyWO">
                                <div class="woHeader"  onclick="flip(\'w' . $activeSummaryData[$j]['workOrderNumber'] . '\')">
                                    No History
                                </div>
                            </div>';
                }*/

                $html .= '</section>
                    </div>';

                if($sectionCount == 4){
                    $html .= '</div></div><div class="unit half">
                                <div class="grid">
                                <div class="unit one-quarter">';
                }elseif($sectionCount > 7){
                    $html .= '<div class="unit one-quarter" hidden>';
                }else{
                    $html .= '<div class="unit one-quarter">';
                }

                $html .= '<section class="workOrderSection" id="w' . $activeSummaryData[$j]['workOrderNumber'] .
                     '" ondrop="drop(event)" ondragover="allowDrop(event)" draggable="true" ondragstart="drag(event)">';
                    $html .= '<div id="deptAssign' . $activeSummaryData[$j]['workOrderNumber'] . '" class="deptAssign">';
                $html .= '</div><div class="activeWO">';

               $html .= buildWorkOrderSummarySubHeader($activeSummaryData, $j);

                $currentWO = $activeSummaryData[$j]['workOrderNumber'];
                $sectionCount++;
                $history = false;
            }

           /* if($activeSummaryData[$j]['stop'] && $history == false){
                $html .= '</div><div class="historyWO">'
                            . '<div class="woHeader"  onclick="flip(\'w' . $activeSummaryData[$j]['workOrderNumber'] . '\')">'
                            . 'History'
                            . '</div>';
                $history = true;
            }*/

            if($activeSummaryData[$j]['stop'] && $history == true){
                $html .= '<div class="woSummaryEmp" id="e' . $activeSummaryData[$j]['employeeID'] . '" draggable="true" ondragstart="drag(event)" '
                        . 'onclick="editEmployee(' . $activeSummaryData[$j]['employeeID'] . ')">'
                        . $activeSummaryData[$j]['name'] . '-' . date('h:i' , strtotime($activeSummaryData[$j]['start'])) . '-' . date('h:i' , strtotime($activeSummaryData[$j]['stop']))
                        . ' - ' . $activeSummaryData[$j]['departmentName'] . '</div>';
            }else{
                $html .= '<div class="woSummaryEmp" id="e' . $activeSummaryData[$j]['employeeID'] . '" draggable="true" ondragstart="drag(event)" '
                        . 'onclick="editEmployee(' . $activeSummaryData[$j]['employeeID'] . ')">'
                        . $activeSummaryData[$j]['name'] . '-' . date('h:iA' , strtotime($activeSummaryData[$j]['start']))
                        . ' - ' . $activeSummaryData[$j]['departmentName'] . '</div>';
            }

        }
        
        
        if($history == false){
            $html .= '<div class="historyWO">
                            <div class="woHeader"  onclick="flip(\'w' . $currentWO . '\')">
                                //No History
                            </div>
                        </div>';
        }
        $html .= '</section>
                    </div>';

        
        // this is creating the columns on the Job Clock Summary page
        $cnt = 1;
        for ($i = $sectionCount; $i < 7; $i++){
            
            if($sectionCount == 4){
                $html .= '</div>
                        </div>
                        <div class="unit half">
                        <div class="unit one-quarter">';
            }else{
                $html .= '<div class="unit one-quarter">';
            }
            
                $html .= '<section id="openSection'. $cnt . '" class="workOrderSection" ondrop="drop(event)" ondragover="allowDrop(event)">';
                $cnt++;
            
                
            $html .= '</section>
                    </div>';
            $sectionCount++;

        }

        //I removed all the loops.  The page loads once and then loads again with the info.  Doesn't need to loops each time.

        $html .= '<div class="unit one-quarter">
            <section class="workOrderSection" id="productionSchedule" ondrop="drop(event)" ondragover="allowDrop(event)">
            <div id="deptAssign" class="deptAssign">';

       /* This was the 1st loading of the Production schedule and was unneccesarry.  It loads in the function buildUnassignedSection
       
       $html .= '</div><div class="activeWO">';

        $html .= '<h2 onclick="flip(\'productionSchedule\')">Production Schedule 1052</h2>';

        $html .= '<h2 onclick="flip(\'productionSchedule\')">Unassigned 1099</h2>';

        $html .= '<h2 onclick="flip(\'productionSchedule\')">Clocked Out 1098</h2>';

        $html .= '</div><div class="historyWO"><h2 onclick="flip(\'productionSchedule\')">Production Schedule 1100</h2>';

        $html .= '          </div>
                    </div>
                </div>  
            </div>';*/

            $html .= '</div><div class="activeWO">';

            $html .= buildUnassignedSection();


        $html .= '</div>
            </div>';
return $html;
}


function buildWorkOrderSection($workOrderNumber){
    
    $activeSummaryData = getActiveSummaryData($workOrderNumber);
    
    $html = '<div id="deptAssign' . $workOrderNumber . '" class="deptAssign">';
                    
    if($activeSummaryData){
        //the below div is for columns with employees assigned
        $html .= '</div><div class="activeWO">'; //1080
        
        $html .= buildWorkOrderSummarySubHeader($activeSummaryData, 0);

        $history = false;

        for($j=0; $j < sizeof($activeSummaryData); $j++){
               if($activeSummaryData[$j]['stop'] && $history == false){
                     /*  $html .= '</div><div class="historyWO">' . '<div class="woHeader"  onclick="flip(\'w' . $workOrderNumber . '\')">
                                    No History
                                </div>';*/

                       $history = true;
                }

               if($activeSummaryData[$j]['stop'] && $history == true){
                    $html .= '<div class="woSummaryEmp" id="e' . $activeSummaryData[$j]['employeeID'] . '" draggable="true" ondragstart="drag(event)" '
                            . 'onclick="editEmployee(' . $activeSummaryData[$j]['employeeID'] . ')">'
                            . $activeSummaryData[$j]['name'] . '-' . date('h:i' , strtotime($activeSummaryData[$j]['start'])) . '-' . date('h:i' , strtotime($activeSummaryData[$j]['stop']))
                            . ' - ' . $activeSummaryData[$j]['departmentName'] . '</div>';
                }else{
                    $html .= '<div class="woSummaryEmp" id="e' . $activeSummaryData[$j]['employeeID'] . '" draggable="true" ondragstart="drag(event)" '
                            . 'onclick="editEmployee(' . $activeSummaryData[$j]['employeeID'] . ')">'
                            . $activeSummaryData[$j]['name'] . '-' . date('h:iA' , strtotime($activeSummaryData[$j]['start']))
                            . ' - ' . $activeSummaryData[$j]['departmentName'] . '</div>';
                } 

        }
           //This is the flip of the card from job summary
          /* if($history == false){
            $html .= '</div><div class="historyWO">
                                <div class="woHeader"  onclick="flip(\'w' . $workOrderNumber . '\')">
                                    No History 1111
                                </div>
                            </div>';
            }*/

           $html .= '</div>';
    }elseif($workOrderNumber){
        //this builds the wo in the column w/o the employee
        $html .= '</div>1120<div class="activeWO">';
        $html .= buildWorkOrderSummarySubHeader($activeSummaryData, 0, $workOrderNumber);
       /* $html .= '</div><div class="historyWO">1122
                    <div class="woHeader"  onclick="flip(\'w' . $workOrderNumber . '\')">
                        No History
                    </div>
                </div>';*/
                
                
    }else{
        $html .= '</div><div class="activeWO">';
       /* $html .= '</div><div class="historyWO">
                    <div class="woHeader"  onclick="flip(\'w' . $workOrderNumber . '\')">
                        No History
                    </div>
                </div>';*/
    }
    
    
    
    return $html;

}

function buildWorkOrderSummarySubHeader($activeSummaryData, $j, $workOrderNumber = Null){
    
    if($activeSummaryData){
    
     /* Line below is with flip that happens when the wo# is clicked on in the column*/
    /* $html = '<div class="woHeader" onclick="flip(\'w' . $activeSummaryData[$j]['workOrderNumber'] . '\')">*/
    /* Line below is no flip that happens when the wo# is clicked on in the column*/
    $html = '<div class="woHeader" ' . $activeSummaryData[$j]['workOrderNumber'] . '>
    <div class="grid">
            <div class="unit one-third">
                <a href="/jobClock/indexCopy.php?action=workOrder&workOrderNumber='
            . $activeSummaryData[$j]['workOrderNumber']  . '"id=buttonJobSum>WO#: ' . $activeSummaryData[$j]['workOrderNumber'] . '</a>
            </div>

            <div class="unit one-third align-center">QTY: '
                . $activeSummaryData[$j]['total_qty'] .
            '</div>

            <div class="unit one-third align-right">TIME: '
                . $activeSummaryData[$j]['Grand_Total'] .
            '</div>
    </div>
    <div class="grid">
        <div class="unit whole">'
            . $activeSummaryData[$j]['DESC1'] .
        '</div>
    </div>
</div>';
    }else{
        //before an employee is added to the project and the project has been assigned a column
        $workOrderDetails = getWorkOrder($workOrderNumber);

        /* Line below is with flip that happens when the wo# is clicked on in the column*/
         /* $html = '<div class="woHeader" onclick="flip(\'w' . $workOrderNumber . '\')">*/
        /* Line below is no flip that happens when the wo# is clicked on in the column*/
        $html = '<div class="woHeader" ' . $workOrderNumber . '>
                    <div class="grid">
                            <div class="unit one-third">
                                <a href="/jobClock/indexCopy.php?action=workOrder&workOrderNumber=' 
                            . $workOrderNumber  . '" id=buttonJobSum>WO##: ' . $workOrderNumber . '</a>
                            </div>
                            <div class="unit one-third align-center">QTY: 0
                            </div>
                            <div class="unit one-third align-right">TIME: 0
                            </div>
                    </div>
                    <div class="grid">
                        <div class="unit whole">'
                            . (isset($workOrderDetails[0][14]) ? $workOrderDetails[0][14] : "") .
                        '</div>
                    </div>
                </div>';
    }
    return $html;
}

// Last column on the right, dragging unassigned employees to column for project.  This is where the 2nd loading comes from
function buildUnassignedSection(){
    $html = null;
    $currentProdSchedule= getProdSchedule(date('Y-m-d'));
    $unassignedEmployees = getUnAssignedEmployees();
    $clockedOut = getClockedOutEmployees();
    
    //List of projects moved to the font page without flip
    $html .= '<div id="deptAssign" class="deptAssign"></div>
                  <div class="activeWO">
                    <h2>Production Schedule 1094</h2>';

    for($i = 0; $i < sizeof($currentProdSchedule); $i++){
        $html .= '<div class="scheduledWO" id="s' . $currentProdSchedule[$i][0] . '" draggable="true" ondragstart="drag(event)">'
                    . $currentProdSchedule[$i][0] . '-' . $currentProdSchedule[$i][24]
                    . '</div>';

    }
    
    //list of clocked in employees that are unassigned to a project
    $html .= '<h2>1248 Unassigned</h2>';
    for($i = 0; $i < sizeof($unassignedEmployees); $i++){
        $html .= '<div class="woSummaryEmp" id="u' . $unassignedEmployees[$i]['employeeID'] . '" draggable="true" ondragstart="drag(event)" '
                . 'onclick="editEmployee(' . (isset($unassignedEmployees[$i]['employeeID']) ? $unassignedEmployees[$i]['employeeID'] : null) . ')">'
                . $unassignedEmployees[$i]['name'] . ($unassignedEmployees[$i]['departmentName'] == null ? "" : ' - ' . $unassignedEmployees[$i]['departmentName'])
                . '</div>';
    }

    //list of clocked out employees
    $html .= '<h2>1259 Clocked Out</h2>';
    
    for($i = 0; $i < sizeof($clockedOut); $i++){
        $html .= '<div class="woSummaryEmp" id="u' . $clockedOut[$i]['employeeID'] . '" draggable="true" ondragstart="drag(event)" '
                . 'onclick="editEmployee(' . (isset($clockedOut[$i]['employeeID']) ? $clockedOut[$i]['employeeID'] : null) . ')">'
                . $clockedOut[$i]['name']
                . '</div>';
    }
    
    // this is production on the back side of the flip
   /* $html .= '</div><div class="historyWO"><h2 onclick="flip(\'productionSchedule\')">Production Schedule</h2>';
    
     for($i = 0; $i < sizeof($currentProdSchedule); $i++){
        $html .= '<div class="scheduledWO" id="s' . $currentProdSchedule[$i][0] . '" draggable="true" ondragstart="drag(event)">'
                    . $currentProdSchedule[$i][0] . '-' . $currentProdSchedule[$i][14]
                    . '</div>';
    }
    
    $html .= '</div>';*/
    
    return $html;
}



function buildDeptList($employeeID, $woTarget, $woOrigin, $workOrderAdd){
    $departments = getDepartments();
    $employeeData = getEmployeeData($employeeID);
    
    if(!is_numeric($woOrigin)){
        $woOrigin = 0;
    }
    
    $html = '<h2>Department<a href="" onclick="closeDeptOverlay(); return false;" id="exit"><span>x</span></a></h2>'
            . '<div id="employeeName' . $woTarget . '" class="employeeName">' . $employeeData['firstName'] . " " . $employeeData['lastName'] . '</div>';
    
    if($workOrderAdd){
        for($i = 0; $i < sizeof($departments); $i++){
            $html .= '<button class="deptOption" onclick="employeeAdd(' . $employeeID .  ', '; 
            $html .=  '\'' . $woTarget . '\', ' . $woOrigin . ', ' . $departments[$i][0] . ')">'
                    . $departments[$i][1] . '</button><br>';
        }
    }else{
        for($i = 0; $i < sizeof($departments); $i++){
            $html .= '<button class="deptOption" onclick="addToDept(' . $departments[$i][0] . ', ' . $employeeID .  ', ' . $woOrigin . ')">' 
                    . $departments[$i][1] . '</button><br>';
        }
    }
    
    return $html;
}