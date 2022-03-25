<?php
session_start();

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$ajax = false;
$email = null;
$message = null;
$users = null;
$action = 'manager';
$employees = null;
$subAction = null;
$employeeID = null;
$employeeName = null;
$fullCardNumber = null;
$curDate = date('Y-m-d');

require 'model.php';

//Get Action
if (isset($_POST['action'])) {
 $action = $_POST['action'];
} elseif (isset($_GET['action'])) {
 $action = $_GET['action'];
}

//Selected Date
if (isset($_POST['selectedDate'])) {
    $selectedDate = filterString($_POST['selectedDate']);
}elseif (isset($_GET['selectedDate'])) {
    $selectedDate = filterString($_GET['selectedDate']);
}else{
    $selectedDate = $curDate;
}


//Get employee ID
if(isset($_POST['employeeID'])){
    $employeeID = filterNumber($_POST['employeeID']);
}elseif(isset($_GET['employeeID'])){
    $employeeID = filterNumber($_GET['employeeID']);
}

if(isset($_POST['employeeID2'])){
    $employeeID = filterNumber($_POST['employeeID2']);
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
    }elseif($action == 'physicalInventory'){
    header('location: /physicalInventory/index.php');
}elseif ($action == 'submitNewUser') {
    //Get data
    $employeeID = filterString($_POST['employeeID']);
    $userGroupID  = filterString($_POST['userGroupID']);
    $userName = filterString($_POST['userName']);
    $password = filterString($_POST['password']);
    $loginDuration  = filterString($_POST['loginDuration']);
    $securityID = filterString($_POST['securityID']);
    $createdBY = $_SESSION['userID'];
    $modifiedBy = $_SESSION['userID'];

    //Verify that user name is not in database. If it is take user to login.
    $exsistingUserName = getUserName($userName);

    $action = 'addUser';
    if($exsistingUserName){
        $message = 'User name exsists. Choose a different login name';
    }else{
        $password = hashPassword($password);
        $insertResult = addUser($employeeID
                                , $userGroupID
                                , $userName
                                , $password
                                , $loginDuration
                                , $securityID
                                , $createdBY
                                , $modifiedBy);

        //If registered successfully take user to content
        if ($insertResult){
            $message = 'New User was successfully Added';
        }else{
            $message = 'Error';
        }

        $action = 'searchUsers';
    }

}
//User Option for searching, adding, and editing users
elseif ($action == 'searchUsers' || $action == 'addUser' || substr($action, 0, 8) == 'editUser'){

    if(!isset($_POST['userSearch'])){
        $searchTerm = null;
    }else{
        $searchTerm = filterString($_POST['userSearch']);
    }
    $users = userSearch($searchTerm);
    if(!$users){
        $message = 'No search results found';
    }

    if($action == 'addUser'){
        $listEmployeeID = getEmployeeIDList();
        $listUserGroupID = getUserGroupIDList();
        $listSecurityID = getSecurityIDList($_SESSION['securityLevel']);

        $action = 'searchUsers';
        $subAction = 'addUser';
            }
    elseif(substr($action, 0, 8) == 'editUser'){
        $listEmployeeID = getEmployeeIDList();
        $listUserGroupID = getUserGroupIDList();
        $listSecurityID = getSecurityIDList($_SESSION['securityLevel']);

        $userID = substr($action,8);
        $userData = getUser($userID);

        if(!isset($userData)){
        $message = 'Error in collecting data.';
        }

        $action = 'searchUsers';
        $subAction = 'editUser';
    }
    else{
        $action = 'searchUsers';
        $subAction = null;
    }
}
elseif($action == 'updateUser'){
    // Process the update
    $userID = filterString($_POST['userID']);
    $employeeID = filterString($_POST['employeeID']);
    $userGroupID  = filterString($_POST['userGroupID']);
    $userName = filterString($_POST['userName']);
    $password = filterString($_POST['password']);
    $loginDuration  = filterString($_POST['loginDuration']);
    $securityID = filterString($_POST['securityID']);
    $modifiedBy = $_SESSION['userID'];;

    // hash the new password, only if a new password has been submitted
    if($password == '**************'){
        $password = null;
    }
    if(!empty($password)){
     $password = hashPassword($password);
    }
    $updateResult = updateUser($userID
                            , $employeeID
                            , $userGroupID
                            , $userName
                            , $password
                            , $loginDuration
                            , $securityID
                            , $modifiedBy);
    // Find out the result, notify client
    if ($updateResult && empty($message)) {
     $message = 'The update was successful.';
    } else {
     $message = 'Sorry, the update failed.';
    }
    $action = 'searchUsers';
    $searchTerm = null;
    $users = userSearch($searchTerm);
}

//Employee Option for searching, adding, and editing employee
elseif ($action == 'searchEmployees' || $action == 'addEmployee' || substr($action, 0, 12) == 'editEmployee'){
    if(!isset($_POST['employeeSearch'])){
        $searchTerm = null;
    }else{
        $searchTerm = filterString($_POST['employeeSearch']);
        $_SESSION['employeeSearchTerm'] = $searchTerm;
    }
    $employees = employeeSearch($searchTerm);
    if(!$employees){
        $message = 'No search results found';
    }

    if($action == 'addEmployee'){
        $listTitleID = getTitleIDList();
        $listStatusID = getStatusIDList();
        $listShiftID = getShiftIDList();
        $listTempAgencyID = getTempAgencyList();

        $action = 'searchEmployees';
        $subAction = 'addEmployee';
    }
    elseif(substr($action, 0, 12) == 'editEmployee'){
        $listTitleID = getTitleIDList();
        $listStatusID = getStatusIDList();
        $listShiftID = getShiftIDList();
        $listTempAgencyID = getTempAgencyList();
        $departmentList = getDepartments();
        $laborGroupList = getLaborGroups();

        $employeeID = substr($action,12);
        $employeeData = getEmployeeData($employeeID);

        if ($employeeData['active'] == 1){
        $employeeData['active'] = 'checked';
        }else{
        $employeeData['active'] = null;
        }

        if(!isset($employeeData)){
        $message = 'Error in collecting data.';
        }
        
        $employees = employeeSearch($_SESSION['employeeSearchTerm']);
        $action = 'searchEmployees';
        $subAction = 'editEmployee';
    }
    else{
        $action = 'searchEmployees';
        $subAction = null;
        if(!isset($_SESSION['employeeSearchTerm'])) {
            $_SESSION['employeeSearchTerm'] = null;
        }
        $employees = employeeSearch($_SESSION['employeeSearchTerm']);
    }
}
elseif ($action == 'submitNewEmployee') {
    //Get data
    $hireDate = filterString($_POST['hireDate']);
    $firstName  = filterString($_POST['firstName']);
    $lastName = filterString($_POST['lastName']);
    $addressLine1 = filterString($_POST['addressLine1']);
    $addressLine2  = filterString($_POST['addressLine2']);
        $city = filterString($_POST['city']);
    $state = filterString($_POST['state']);
    $zip = filterString($_POST['zip']);
    $primaryPhone = filterString($_POST['primaryPhone']);
    $primaryPhone = ($primaryPhone == 0 ? null : $primaryPhone);
    $secondaryPhone = filterString($_POST['secondaryPhone']);
    $secondaryPhone = ($secondaryPhone == 0 ? null : $secondaryPhone);
    $titleID = filterString($_POST['jobTitleID']);
    $statusID = filterString($_POST['employeeStatusID']);
    $shiftID = filterString($_POST['shiftID']);
    $lunchDuration  = filterString($_POST['lunchDuration']);
    $tempAgencyID = filterString($_POST['tempAgencyID']);
    $active = filterString($_POST['active']);
    $employeeNotes = filterString($_POST['employeeNotes']);
    $createdBY = $_SESSION['userID'];
    $modifiedBy = $_SESSION['userID'];
//Verify that user name is not in database. If it is take user to login.
    $exsistingEmployeeName = getEmployeeName($firstName, $lastName);

    if($exsistingEmployeeName){
        $message = 'Modify First Name if employee has same first and last name.';
        $action = 'searchEmployees';
        $subAction = 'addEmployee';
    }else{
        $insertResult = addEmployee($hireDate
                                , $firstName
                                , $lastName
                                , $addressLine1
                                , $addressLine2
                                , $city
                                , $state
                                , $zip
                                , $primaryPhone
                                , $secondaryPhone
                                , $titleID
                                , $statusID
                                , $shiftID
                                , $tempAgencyID
                                , $active
                                , $employeeNotes
                                , $createdBY
                                , $modifiedBy
                                , $lunchDuration);

        //If registered successfully take user to content
        if ($insertResult){
            $message = 'New User was successfully Added';
        }else{
            $message = 'Error';
        }
        $action = 'searchEmployees';
        $searchTerm = null;
        $employees = employeeSearch($searchTerm);

    }

}elseif($action == 'editBinLocItem') {
    $binName = filterString($_GET['binName']);
    $binCatID = filterString($_GET['binCatID']);

    $data = buildBinLocationCatList($binCatID);

    echo $data;
    $ajax = true;
}elseif($action == 'addBinLocItem') {
    $binName = filterString($_GET['binName']);
    $binCatID = filterString($_GET['binCatID']);
    
    $data = insertBinLocation($binName, $binCatID);

    echo $data != null ? 1 : 0;
    $ajax = true;
}elseif($action == 'setBinCategoryID') {
    $binID = filterNumber($_GET['binID']);
    $binCatID = filterNumber($_GET['binCatID']);

    $data = setBinCategoryID($binID, $binCatID);
    
    echo $data != null ? 1 : 0;
    $ajax = true;
}elseif($action == 'updateBinLocItem') {
    $binID = filterString($_GET['binID']);
    $binName = filterString($_GET['binName']);
    $binCatID = filterNumber($_GET['binCatID']);

    $data = updateBinLocation($binID, $binName, $binCatID);

    echo $data != null ? 1 : 0;
    $ajax = true;
}elseif($action == 'searchBinLocation') {
    $searchTerm = filterString($_GET['search']);
    $cat = filterString($_GET['cat']);

    $data = buildBinLocList($searchTerm, $cat);

    echo $data;
    $ajax = true;
}elseif(substr($action, 0, 12) == 'viewEmployee' || $action == 'printEmployeeCard'){
    if($action == 'printEmployeeCard'){
                 //Employee ID
        $employeeData = getEmployee($employeeID);

        //Employee Name
            $employeeName = $employeeData[0][2];
        //Reset Key Card Number
            $newKeyCard = rand(1000, 9999);
            $updateKeyCard = updateKeyCardNumber($employeeID, $newKeyCard);
        //Create new Access Card Number
            //$loginUniversal = getConstant('loadTagKey');
        //Send Data to be printed
            $fullCardNumber = '(E' . $employeeID . $newKeyCard  . ')';
            $subAction = 'printEmployeeCard';

    }else{
        $employeeID = substr($action,12);
        $employeeData = getEmployee($employeeID);
    }
    $listTitleID = getTitleIDList();
    $listStatusID = getStatusIDList();
    $listShiftID = getShiftIDList();
    $listTempAgencyID = getTempAgencyList();

    $employeeData[0][3] = formatPhoneNumber($employeeData[0][3]);
    $employeeData[0][4] = formatPhoneNumber($employeeData[0][4]);

    if ($employeeData[0][11] == 1){
        $employeeData[0][11] = 'checked';
    }else{
        $employeeData[0][11] = null;
    }

    if($employeeID == null && !$updateKeyCard){
        $message = 'Error: Unable to access employee';
        $employees = employeeSearch($searchTerm);
        $action = 'searchEmployees';
    }else{
        $action = 'viewEmployee';
    }
}
elseif($action == 'updateEmployee'){
    // Process the update
    $employeeID = filterString($_POST['employeeID']);
    $firstName = filterString($_POST['firstName']);
    $lastName = filterString($_POST['lastName']);
    $titleID = filterString($_POST['titleID']);
    $statusID = filterString($_POST['statusID']);
    $shiftID = filterString($_POST['shiftID']);
    $lunchDuration = filterString($_POST['lunchDuration']);

    if(isset($_POST['active'])){
        $active  = filterString($_POST['active']);
    }else{
        $active = false;
    }
    $employeeNotes  = filterString($_POST['employeeNotes']);
    $tempAgencyID = filterString($_POST['tempAgencyID']);
    $modifiedBy = $_SESSION['userID'];

    if(isset($_POST['departmentID'])){
        $departmentID = filterNumber($_POST['departmentID']);
    }else{
        $departmentID = null;
    }

    if(isset($_POST['groupID'])){
        $laborGroupID = filterNumber($_POST['groupID']);
    }else{
        $laborGroupID = null;
    }

    if(!isset($_POST['tempEmployeeID']) || $_POST['tempEmployeeID'] == 0){
        $tempEmployeeID = null;
    }else{
        $tempEmployeeID = filterNumber($_POST['tempEmployeeID']);
    }



    //Get current Name to eliminate as duplicate name
    $currentEmployeeData = getEmployeeData($employeeID);
    $currentName = $currentEmployeeData['firstName'] . $currentEmployeeData['lastName'];

    //Verify that user name is not in database. If it is take user to login.
    if($firstName . $lastName != $currentName){
        $exsistingEmployeeName = getEmployeeName($firstName, $lastName);
    }else{
        $exsistingEmployeeName = false;
    }

    //If name exsists then error message sent OR update the employee
    if($exsistingEmployeeName){
        $message = 'Error: Duplicate Name. Add first initial if new person.';
        $action = 'searchEmployees';
        $subAction = 'addEmployee';
    }else{

        $updateResult = updateEmployee($employeeID
                                , $firstName
                                , $lastName
                                , $titleID
                                , $statusID
                                , $shiftID
                                , $active
                                , $employeeNotes
                                , $tempAgencyID
                                , $modifiedBy
                                , $departmentID
                                , $laborGroupID
                                , $tempEmployeeID
                                , $lunchDuration);
        // Find out the result, notify client

        if (1==1) {
         $message = 'The update was successful.';
        } else {
         $message = 'Sorry, the update failed.';
        }
        $action = 'searchEmployees';
        $searchTerm = null;
        $employees = employeeSearch($_SESSION['employeeSearchTerm']);

    }
}elseif($action == 'timeClockEditor' || $action == 'refreshEditor' || $action == 'printTimeCard' || $action == 'printAllTimeCards' || $action == 'printSummaryTimeCards'){

    //Lookup Employee Data
    $employeeData = getEmployeeData($employeeID);

    //Get Date range
    $payPeriod = getPayPeriod($selectedDate);
    $startOfWeek = $payPeriod[0];
    $endOfWeek = $payPeriod[6];

    //Gather Employee/Punch Date
    $employeeTotalHours = getEmployeeHoursInSec($employeeID, $startOfWeek, $endOfWeek);
    $payRollHours = calculatePayrollData($employeeTotalHours, $employeeID);

    //Construct Punch Data
    $punches = getPunches($employeeID, $startOfWeek, $endOfWeek);

    // Get Lunch Duration
    $lunchDuration = !$employeeData ? null : $employeeData['lunchDuration'];

    $dailyHours = getDailyHours($employeeID, $startOfWeek, $endOfWeek, $lunchDuration);

    $missingPunches = getMissingPunch($employeeID, $startOfWeek, $endOfWeek);

    $dailyPayrollHours = calculatePayPeriod($punches, $dailyHours, $payPeriod, $employeeID, $missingPunches);

    $punchHeader = buildHeader($employeeData, $startOfWeek, $endOfWeek, $payRollHours, $selectedDate);

    if(!isset($employeeID) || $employeeID == null){
        $punchEditor = null;
    }else{
        $punchEditor = buildPunchEditor($dailyHours, $dailyPayrollHours, $payPeriod, $employeeID);
    }

    if($action == 'printTimeCard'){
        $action = 'timeClockEditor';
        $subAction = 'printTimeCard';
        $employeesWithHours[0][0]= $employeeID;
        $timeCard = buildTimeCard($employeesWithHours, $selectedDate);
    }

    if($action == 'printAllTimeCards'){
        $action = 'timeClockEditor';
        $subAction = 'printTimeCard';
        $employeesWithHours = getEmployeesWithHours($startOfWeek, $endOfWeek);
        $timeCard = buildTimeCard($employeesWithHours, $selectedDate);
    }

    if($action == 'printSummaryTimeCards'){
        $action = 'timeClockEditor';
        $subAction = 'printTimeCard';
        $employeesWithHours = getEmployeesWithHours($startOfWeek, $endOfWeek);
        $timeCard = buildTimeSummary($employeesWithHours, $selectedDate);
    }

    if($action == 'refreshEditor'){
        $ajax = true;
        $tranArr = null;
        $tranArr[0] = $punchHeader;
        $tranArr[1] = $punchEditor;

        $data = json_encode($tranArr);

        echo $data;
    }


}elseif($action == 'buildDropDown'){
    //Data transmittion Array
    $tranArr = null;

    //Get Search Term & Selection Number
    $searchTerm = filterString($_GET['searchTerm']);
    $selection = filterNumber($_GET['selection']);

    //Get employee List of Names that match (Limit 10)
    $employeeList = employeeNameSearch($searchTerm);

    $dropDown = buildDropDown($employeeList, $selection);

    $tranArr[0] = sizeof($employeeList);
    $tranArr[1] = $dropDown;
    if($selection != -1){
        $tranArr[2] = $employeeList[$selection][0];
    }


    $data = json_encode($tranArr);

    $ajax = true;

    echo $data;

}elseif($action == 'expandTime' || $action == 'insertTimeView'){
    //Get Date range
    $payPeriod = getPayPeriod(date('Y-m-d', strtotime(filterString($_GET['payPeriodDate']))));
    $startOfWeek = $payPeriod[0];
    $endOfWeek = $payPeriod[6];

    //Get pay period
    $payPeriodDay = filterNumber($_GET['payPeriodDay']);
    $payPeriodDate = $payPeriod[$payPeriodDay];

    //Construct Punch Data
    $punches = getPunches($employeeID, $startOfWeek, $endOfWeek);

    //Convert elapsed time to Payroll hours
    for($i=0; $i < sizeof($punches); $i++){
        $punches[$i][4] = calculatePayrollData($punches[$i][4], $employeeID);
    }

    if($action == 'insertTimeView'){
        $expandedTime = buildExpandedTime($punches, $payPeriodDate, $payPeriodDay, $employeeID, true);
    }else{
        $expandedTime = buildExpandedTime($punches, $payPeriodDate, $payPeriodDay, $employeeID);
    }

    $ajax = true;

    echo $expandedTime;

}elseif($action == 'insertPunch'){
    $punchIn = date('Y-m-d H:i:s', strtotime(filterString($_GET['punchIn'])));
    $punchdate = date('Y-m-d', strtotime(filterString($_GET['selectedDate'])));
    $timeStamp = date('Y-m-d H:i:s');
    $userID = $_SESSION['userID'];
    $error = null;

    if($_GET['punchIn'] != 'null'){

        if ($_GET['punchOut'] == 'null'){
            $punchOut = null;

        }else{
            $punchOut = date('Y-m-d H:i:s', strtotime(filterString($_GET['punchOut'])));

           if(dateDifference2($punchIn, $punchOut, 60) < 0){
               $punchOut = date('Y-m-d H:i:s', strtotime($punchOut . ' +1 day'));
           }elseif(dateDifference2($punchIn, $punchOut, 60) < 15){
               $error = 2;
           }
        }

        if ($error != 2){
             $insertPunch = insertPunch($punchIn, $punchOut, $selectedDate, $employeeID, $userID, $timeStamp);
        }

    }else{
        $error = 2;
    }

    if(!isset($insertPunch) || $error == 2){
        echo 0;
    }else if (isset($insertPunch)){
        echo 1;
    }else{
        echo 2;
    }

    $ajax = true;
}elseif($action == 'deletePunch'){
    $punchID = filterNumber($_GET['punchID']);
    $deletePunch = deletePunch($punchID);

//    if(!isset($deletePunch)){
//        echo 0;
//    }else{
//        echo 1;
//    }

    echo $deletePunch;

    $ajax = true;

}elseif($action == 'insertOutPunch'){
    $punchID = filterNumber($_GET['punchID']);

    $userID = $_SESSION['userID'];
    $timeStamp = date('Y-m-d H:i:s');

    if ($_GET['punchOut'] == 'null'){
            $punchOut = null;
    }else{
        $punchOut= $_GET['punchOut'];
        $punchData = getPunch($punchID);
        $punchIn = date('Y-m-d H:i:s', strtotime($punchData[1]));

        if(dateDifference2($punchIn, $punchOut, 60) < 0){
               $punchOut = date('Y-m-d H:i:s', strtotime($punchOut . ' +1 day'));
               //Get Job Clock Data
               $jobClockData = getJobClockDataByTimeClock($punchID);
               //If punched multiple Jobs punch out on last job if not punch out first job
               if(isset($jobClockData[1][0])){
                    $jobClockOutUpdate = updateOutJobPunch($jobClockData[1][0], $punchOut, $userID);
                }else{
                    $jobClockOutUpdate = updateOutJobPunch($jobClockData[0][0], $punchOut, $userID);
                }
           }elseif(dateDifference2($punchIn, $punchOut, 60) < 15){
               $error = 2;
           }
    }

    echo $punchOut;
    $insertOutPunch = insertOutPunch($punchID, $punchOut, $userID, $timeStamp);

    if(isset($insertOutPunch)){
        echo 1;
    }else{
        echo 0;
    }
    $ajax = true;
}elseif($action == 'binLocations') {

    $html = buildBinLocationsMenu();

}elseif($action == 'editPunch'){
    $myArr = null;
    $punchID = filterNumber($_GET['punchID']);
    $punchIn = date('Y-m-d H:i:s', strtotime(filterString($_GET['punchIn'])));
    $error = null;
    $userID = $_SESSION['userID'];
    $timeStamp = date('Y-m-d H:i:s');

    $currentPunchData = getPunch($punchID);
    if($_GET['punchIn'] != 'null'){

        if ($_GET['punchOut'] == 'null'){
            $punchOut = null;

        }else{
            $punchOut = date('Y-m-d H:i:s', strtotime(filterString($_GET['punchOut'])));

           if(dateDifference2($punchIn, $punchOut, 60) < 0){
               $punchOut = date('Y-m-d H:i:s', strtotime($punchOut . ' +1 day'));
           }elseif(dateDifference2($punchIn, $punchOut, 60) < 15){
               $error = 2;
           }
        }

        if ($error != 2){
             $insertPunch = editPunches($punchID, $punchIn, $punchOut, $userID, $timeStamp);

            //Get JobClockPunchID
             $jobClockData = getJobClockDataByTimeClock($punchID);
            //Update Job Clock In if exisits
            if($currentPunchData[1] && isset($jobClockData[0][0])){
                $jobClockInUpdate = updateInJobPunch($jobClockData[0][0], $punchIn, $userID);
            }

            //Update Job Clock Out if exisits
            //If clocked out
            if($currentPunchData[2] && isset($jobClockData[1][0])){
                $jobClockOutUpdate = updateOutJobPunch($jobClockData[1][0], $punchOut, $userID);
            }
        }

    }else{
        $error = 2;
    }

    if(!isset($insertPunch) || $error == 2){
        $myArr[0] = 0;
    }else if (isset($insertPunch)){
        $myArr[0] =  1;
    }else{
        $myArr[0] =  2;
    }

    $data = json_encode($myArr);
    echo $data;
    $ajax = true;

}elseif($action == 'reports'){
    //verify security level before user proceeds
    if($_SESSION['securityLevel'] >= 3){
        header('Location: /reports/index.php');
    }else{
        $message = 'Access Restricted';
        $action = 'mainMenu';
    }
}

if(!$ajax){
    include 'view.php';
}


function buildTimeCard($employeesWithHours, $selectedDate){
    $missingPunchCount = 0;
    
    $timeCard = null;
    
    for($i = 0; $i < sizeof($employeesWithHours); $i++){
        $timeCard .= '<div class="timeCardPage">';
        $missedPunch = false;
        
        $employeeID = $employeesWithHours[$i][0];

        //Lookup Employee Data
        $employeeData = getEmployeeData($employeeID);

        //Get Date range
        $payPeriod = getPayPeriod($selectedDate);
        $startOfWeek = $payPeriod[0];
        $endOfWeek = $payPeriod[6];

        //Gather Employee/Punch Date
        $employeeTotalHours = getEmployeeHoursInSec($employeeID, $startOfWeek, $endOfWeek);
        $payRollHours = calculatePayrollData($employeeTotalHours, $employeeID);

        //Construct Punch Data
        $punches = getPunches($employeeID, $startOfWeek, $endOfWeek);
        for($l=0; $l < sizeof($punches); $l++){
            $punches[$l][4] = calculatePayrollData($punches[$l][4], $employeeID);
        }
        
        //Count missing punches
        $missingPunchCount = countMissingPunch($employeeID, $startOfWeek, $endOfWeek);
        $missingPunchCount = !$missingPunchCount ? false : $missingPunchCount[0];
        
        //Get missing punches if exsist
        $missingPunches = getMissingPunch($employeeID, $startOfWeek, $endOfWeek);

        // Get Lunch Durration
        $lunchDuration = !$employeeData ? null : $employeeData['lunchDuration'];
        
        $dailyHours = getDailyHours($employeeID, $startOfWeek, $endOfWeek, $lunchDuration);

        $dailyPayrollHours = calculatePayPeriod($punches, $dailyHours, $payPeriod, $employeeID, $missingPunches);
        if($_SESSION['clientID'] == 2){
            $timeCard .= '<img src="/image/iconLogo.jpg">';
        }
        if($missingPunchCount){
            $timeCard .= '<div id="watermarkp"><h2 id="watermarkc">Missed Punch</h2></div>';
        }
        $timeCard .= '
                    
                    <h2>Time Card</h2>
                    <p>' . date('F j, Y, g:i a') . '<p>

                    <table class="headerTable">
                        <tr>
                            <td>Heritage ID:</td>
                            <td>' . $employeeID . '</td>
                        </tr>
                        <tr>
                            <td>Employee:</td>
                            <td>' . $employeeData['firstName'] . ' ' . $employeeData['lastName'] . '</td>
                        </tr>
                        <tr>
                            <td>Pay Period:</td>
                            <td>' . date('m-d-Y', strtotime($startOfWeek)) . ' - ' . date('m-d-Y', strtotime($endOfWeek)) . '</td>
                        </tr>
                    </table>

                    <table class="bodyTable">
                        <tr>
                            <th></th>
                            <th>In</th>
                            <th>Out</th>
                            <th>Hours</th>
                            <th>Reg Hrs</th>
                            <th>OT Hrs</th>
                            <th>DT Hrs</th>
                        </tr>';

                for($j = 0; $j < 7; $j++ ){
                    
                    $punched = false;
                    for($h = 0; $h < sizeof($missingPunches); $h++){
                        
                        if(in_array($payPeriod[$j], $missingPunches[$h])){
                            $punched = true;
                            if($missingPunches[$h][1] == 1){
                                $missedPunch = true;
                            }
                            break;
                        }
                    }
                    if($punched){
                        $timeCard .= '<tr>
                                        <th>' . date('l, F j, Y', strtotime($payPeriod[$j])) . '</th>
                                        <th colspan="3"></th>
                                        <th>' . $dailyPayrollHours[$j][1] . '</th>
                                        <th>' . $dailyPayrollHours[$j][2] . '</th>
                                        <th>' . $dailyPayrollHours[$j][3] . '</th>
                                    </tr>';
                        
                        for($k = 0; $k < sizeof($punches); $k++){
                            if(isset($punches[$k][2]) && ($payPeriod[$j] == $punches[$k][3])){
                                $timeCard .= '<tr class="timeCardPunch">
                                                <td></td>
                                                <td>' . ($punches[$k][2] == NULL ? "-" : date('h:i:s a',strtotime($punches[$k][2]))) . '</td>
                                                <td>' . ($punches[$k][1] == NULL ? "-" : date('h:i:s a',strtotime($punches[$k][1]))) . '</td>
                                                <td>' . $punches[$k][4][0] . '</td>
                                                <td colspan="3"></td>
                                            </tr>';
                            }
                        }
                    }
                    
                }


            $timeCard .= '<tr>
                            <td colspan="4"></td>
                            <th>Reg Hrs</th>
                            <th>OT Hrs</th>
                            <th>DT Hrs</th>
                        </tr>
                        <tr>
                            <td colspan="3"></td>
                            <td>Total Hours:</td>
                            <td>' . $payRollHours[1] . '</td>
                            <td>' . $payRollHours[2] . '</td>
                            <td>' . $payRollHours[3] . '</td>
                        </tr>


                    </table>

                
                    <table class="footerTable">
                        <tr>
                            <td>Total Hours Worked:</td>
                            <td>' . $payRollHours[0] . '</td>
                        </tr>
                        <tr>
                            <td>Regular Hours:</td>
                            <td>' . $payRollHours[1] . '</td>
                        </tr>
                        <tr>
                            <td>Over Time Hours:</td>
                            <td>' . $payRollHours[2] . '</td>
                        </tr>
                        <tr>
                            <td>Double Time Hours:</td>
                            <td>' . $payRollHours[3] . '</td>
                        </tr>

                    </table>


                    <table>
                        <tr>
                            <td>Supervisor:</td>
                            <td>_________________________________________</td>
                            <td></td>
                            <td>Employee:</td>
                            <td>_________________________________________</td>
                        </tr>
                    </table>';

        $timeCard .= '</div><div class="pageBreak"></div>';
    }
    
        
        return $timeCard;
}

function buildDropDown($employeeList, $selection){
    
    $dropDownMenu = '<div class="overlay3"><ul>';
    $empCount = sizeof($employeeList);
        for($i = 0; $i < $empCount; $i++){
                if($selection == $i){
                    $dropDownMenu .= '<li><a href="/manager/index.php?action=timeClockEditor&employeeID=' . $employeeList[$i][0] . 
                            '"><div id="highlight">' .$employeeList[$i][1] . '</div></a></li>';
                }elseif($selection > $empCount && ($i == $empCount)){
                    $dropDownMenu .= '<li><a href="/manager/index.php?action=timeClockEditor&employeeID=' . $employeeList[$i][0] .
                             '"><div id="highlight">' .$employeeList[$i][1] . '</div></a></li>';
                }else{
                    $dropDownMenu .= '<li><a href="/manager/index.php?action=timeClockEditor&employeeID=' . $employeeList[$i][0] . '">'. $employeeList[$i][1] . '</a></li>';
                }
        }
        $dropDownMenu .= '</ul></div>';
    
    return $dropDownMenu;
}

function buildHeader($employeeData, $startOfWeek, $endOfWeek, $payRollHours, $selectedDate){
    // error_reporting(0);

    if($employeeData == false) {
        $employeeData['firstName'] = null;
        $employeeData['lastName'] = null;
        $employeeData['employeeID'] = null;
    }
    

    $punchHeader = '<hr>
                        <h1>'
                        . $employeeData['firstName'] . ' ' . $employeeData['lastName'];
        if($employeeData['firstName'] != ''){
            $punchHeader .= '<a href= "/manager/index.php?action=viewEmployee' . $employeeData['employeeID'] . '" id="exit">
                         <span>I</span>
                         </a>';
        }                
                        
        $punchHeader .= '</h1>
                        <form method="post" action="." id="searchForm">
                                <div id="hours">
                                
                                <div class="grid">
                                    <div class="unit one-fifth">
                                        <h2>Select Pay Period</h2>
                                    </div>
                                    <div class="unit one-fifth">
                                        <h2>Total Hours</h2>
                                    </div>
                                    <div class="unit one-fifth">
                                        <h2>Regular</h2>
                                    </div>
                                    <div class="unit one-fifth">
                                        <h2>OT</h2>
                                    </div>
                                    <div class="unit one-fifth">
                                        <h2>DT</h2>
                                    </div>
                                </div>


                                <div class="grid">
                                    <div class="unit one-fifth">
                                        <input type="date" name="selectedDate" id="selectedDate" value="' . $selectedDate . '" onchange="this.form.submit()">
                                        <input type="text" name="action" id="action" value="timeClockEditor" hidden>
                                        <input type="text" name="employeeID2" id="employeeID2" value="' . $employeeData['employeeID'] . '" hidden>
                                    </div>
                                    <div class="unit one-fifth">
                                        <div><span>' . $payRollHours[0] . '</span></div>
                                    </div>
                                    <div class="unit one-fifth">
                                        <div><span>' . $payRollHours[1] . '</span></div>
                                    </div>
                                    <div class="unit one-fifth">
                                        <div><span>' . $payRollHours[2] . '</span></div>
                                    </div>
                                    <div class="unit one-fifth">
                                        <div><span>' . $payRollHours[3] . '</span></div>
                                    </div>
                                </div>

                            </div>
                            <hr>
                                <div class="grid">
                                <div class="unit whole align-center">
                                    <h2>Pay Period</h2>
                                </div>
                                </div>
                                <div class="grid">
                                <div class="unit one-third align-right">
                                    <h3>' . date('m-d-Y', strtotime($startOfWeek)). '</h3>
                                </div>
                                <div class="unit one-third align-center">
                                    <h3>to</h3>
                                </div>
                                <div class="unit one-third align-left">
                                    <h3>' .date('m-d-Y', strtotime($endOfWeek)) . '</h3>
                                </div>
                                </div>
                            
                        </form>';
    
    return $punchHeader;
}

function buildPunchEditor($dailyHours, $dailyPayrollHours, $payPeriod, $employeeID){
    
    $punchEditor = '<div class="editor" id="editor">';
    $payPeriodDate = strtotime($payPeriod[0])*1000;
    
    for($i = 0; $i < 7; $i++){
    $punchEditor .= '<ul>
                        <li>'; 
        $punchEditor .= 
                            '<div class="grid">
                                <div class="unit three-fifths align-left">
                                    <div class="expandIcon" id="expandIcon' . $i . '">
                                        <a href="javascript:;" onclick="expandHours(' . $employeeID . ', ' .$i . ', ' . $payPeriodDate . ', 0)"><span>1</span></a>
                                    </div>';

                            if ($dailyPayrollHours[$i][4] == 1){
                                $punchEditor .= '<span>' . date('l, F j, Y', strtotime($payPeriod[$i])) . '*</span></div>';   
                            }else{
                                $punchEditor .= '<span>' . date('l, F j, Y', strtotime($payPeriod[$i])) . '</span></div>';                       
                            }
                                    
        $punchEditor .= '<div class="unit two-fifths align-right">
                                <div class="payrollHours">
                                    <div id="totalHrs"><span>' . $dailyPayrollHours[$i][0] . '</span></div>
                                    <div id="regHrs"><span>' . $dailyPayrollHours[$i][1] . '</span></div>
                                    <div id="otHrs"><span>' . $dailyPayrollHours[$i][2] . '</span></div>
                                    <div id="dtHrs"><span>' . $dailyPayrollHours[$i][3] . '</span></div>
                                </div>
                                    <a href="javascript:;" onclick="expandHours(' . $employeeID . ', ' .$i . ', ' . strtotime($payPeriod[$i])*1000 . ', 1);"><span>L</span></a>
                                </div>
                            </div>
                            <div id=expandTime' . $i . '></div>';
                            
        $punchEditor .= '</li>
                    </ul>';
            
    }
    
    return $punchEditor;
}

function buildExpandedTime($punches, $payPeriodDate, $payPeriodDay, $employeeID, $insertTimeView = false){
    $convDate = strtotime($payPeriodDate)*1000;
    $convDate2 = date('Y-m-d', strtotime($payPeriodDate));
    $punchEditor = '<ul>
                        <li>
                            <div class="grid">
                                    <div class="unit one-quarter align-center">
                                        <span>In</span>
                                    </div>
                                    <div class="unit one-quarter align-center">
                                        <span>Out</span>
                                    </div>
                                    <div class="unit half align-center">
                                        <span>Hours</span>
                                    </div>
                            </div>
                        </li>';
                for($j=0;$j<sizeof($punches); $j++){
                    if(isset($punches[$j][2]) && ($payPeriodDate == $punches[$j][3])){
                        //Creats Unique loction for missing out punch addition icon
                        $addPunchIn = 'addPunchIn' . $punches[$j][5];
                        $addPunchOut = 'addPunchOut' . $punches[$j][5];
                        $addIcon = 'addIcon' . $punches[$j][5];
                        
                        if(!isset($punches[$j][1])){
                            $punchOut = '<a href="javascript:;" onclick="viewInsertOutPunch(\'' .$addPunchOut . '\', ' . $punches[$j][5] . ', ' . $employeeID . ', ' . $convDate . ', ' . $payPeriodDay . ');"><span>L</span></a>';
                        }else{
                            $punchOut = date('h:i:s a',strtotime($punches[$j][1]));
                        }
                        
    $punchEditor .= 
                        '<li>
                            <div class="grid">
                                <div class="unit one-quarter align-center">
                                    <div id="' . $addPunchIn . '"><span>' . date('h:i:s a',strtotime($punches[$j][2])) . '</span></div>
                                </div>
                                <div class="unit one-quarter align-center">
                                    <div id="' . $addPunchOut . '"><span>' . $punchOut . '</span></div>
                                </div>
                                <div class="unit one-quarter align-center">
                                    <div><span>' . $punches[$j][4][0] . '</span></div>
                                </div>
                                <div class="unit one-quarter align-right">
                                    <a href="javascript:;" onclick="editPunchView(\'' . $addPunchOut 
                                                                            . '\', \'' . $addPunchIn 
                                                                            . '\', ' . $punches[$j][5] 
                                                                            . ', ' . $employeeID 
                                                                            . ', ' . $convDate
                                                                            . ', ' . $payPeriodDay;
                                                                        if(!isset($punches[$j][2])){
                                                                            $punchEditor .= ', null';
                                                                        }Else{
                                                                            $punchEditor .= ', \'' . date('H:i', strtotime($punches[$j][2])) . '\'';
                                                                        }

                                                                        if(!isset($punches[$j][1])){
                                                                            $punchEditor .= ', null';
                                                                        }Else{
                                                                            $punchEditor .= ', \'' . date('H:i', strtotime($punches[$j][1])) . '\'';
                                                                        }
    $punchEditor .= 
                                                                            ', \'' . $convDate2 
                                                                            . '\');"><span>p</span></a>
                                    <a href="javascript:;" onclick="deletePunch(' . $punches[$j][5] . ', ' . $employeeID . ', ' . $convDate . ', ' . $payPeriodDay . ');"><span>x</span></a>
                                </div>';
                    }
    $punchEditor .=         '</div>
                        </li>';
                }
                if($insertTimeView){
                    $punchEditor .= '<li>
                                        <div class="grid">
                                            <div class="unit one-quarter align-center">
                                                <input type="time" name="inPunch" id="inPunch" placeholder="IN" required>
                                            </div>
                                            <div class="unit one-quarter align-center">
                                                <input type="time" name="outPunch" id="outPunch" placeholder="OUT">
                                            </div>
                                            <div class="unit half align-left">
                                            <a href="javascript:;" id="checkMark" onclick="insertPunch(' . $employeeID . ', \'' . $convDate2 . '\', ' . $payPeriodDay . ', ' . $convDate . ');"><span>v</span></a>
                                            <a href="javascript:;" id="cancel" onclick="expandHours(' . $employeeID . ', ' .$payPeriodDay . ', ' . $convDate . ', 0);"><span>O</span></a>
                                            <span id=errorMessage></span>
                                            </div>
                                        </div>
                                    </li>';
                }
    
    $punchEditor .= '</ul>';
    
    return $punchEditor;
}


function buildBinLocationsMenu() {

    $html = '
    <h1>Bin Locations</h1>

    <div class="binLocSearch">
        <input onkeydown="Javascript: if (event.keyCode==13) searchBinLocation();" id="binLocSearchInput" type="text" placeholder="Search...">
        <a onclick="searchBinLocation()"><i class="bi bi-search"></i></a>
    </div>';

    $html .= buildBinLocationCatList(null, true);

    $html .= '
    <div id="binLocList">
    ';

    $html .= buildBinLocList();

    $html .= '
    </div>

    <div class="binLocAdd">
        <input id="binNameAddInput" class="pill" type="text" placeholder="Enter Bin Location...">';
        
        $html .= buildBinLocationCatList(null, null, true);

        $html .= '
        <a onclick="addBinLocItem()"><i class="bi bi-plus-circle greenIcon"></i></a>
    </div>
    ';

    return $html;

}

function buildBinLocList($searchTerm = null, $cat = null) {

    $data = getBinLocationData($searchTerm, (!$cat ? false : $cat));

    $html = '';

    for ($i=0; $i < sizeof($data); $i++) {    
        $binLocCat = getBinLocationCats($data[$i][2]);
        $binLocCat = $binLocCat != null ? $binLocCat[0][1] : null;
        $html .= '
        <div class="binLocItem" id="binLocItem_' . $data[$i][0] . '">
            <p>' . $data[$i][0] . '</p>
            <p>' . $data[$i][1] . '</p>
            <p class="status binStatusBlue">' . $binLocCat . '</p>
            <p>' . ( isset($data[$i][3]) ? $data[$i][3] : '-' ) . '</p>
            <div class="edit">
                <a title="Edit" onclick="editBinLocItem(`' . $data[$i][0] . '`, `' . $data[$i][1] . '`, `' . $data[$i][2] . '`)"><i class="bi bi-pencil"></i></a>
                ' . ($data[$i][2] != 0 ? '<a title="Deactivate" onclick="setBinCategoryID(`' . $data[$i][0] . '`, 0)"><i class="bi bi-dash-circle redIcon"></i></a>' : '') . '
            </div>
        </div>
        ';
    }

    // return $searchTerm . ', ' . $cat;
    return $html;

}

function buildBinLocationCatList($binCatID = null, $search = false, $add = false) {
    
    $blc = getBinLocationCats();

    $html = '
    <select id="' . ( $search ? 'binCatSearchInput' : ( $add ? 'binCatAddInput' : 'binCatEditInput' ) ) . '" ' . ( $search ? ' class="searchBinLocCats"' : '' ) . '>
    <option value="" selected>' . ( $search ? 'Search By' : 'Select' ) . ' Category...</option>
    ';
    
    for ($i=0; $i <sizeof($blc) ; $i++) { 
        $html .= '<option value="' . $blc[$i][0] . '" ' . ( $binCatID == $blc[$i][0] ? 'selected' : '' ) . '>' . $blc[$i][1] . '</option>';
    }

    $html .= '
    </select>
    ';

    return $html;

}