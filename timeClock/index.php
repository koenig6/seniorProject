<?php
session_start();
/* 
 * Time Clock/ Schedule Display
 */

//Bring in Model Functions
require 'model.php';

date_default_timezone_set('America/Los_Angeles');

//Variables
$ajax = false;
$action = null;
$employeeID = null;
$punch = null;
$curDate = null;
$punchOverlay = null;
$punchData = null;
$punchStatus = null;
$scheduleTable = null;
$keyCard = null;

if (isset($_POST['action'])) {
 $action = filterString($_POST['action']);
} elseif (isset($_GET['action'])) {
 $action = filterString($_GET['action']);
}

if (isset($_GET['employeeID'])) {
 $employeeID = $_GET['employeeID'];
}

if (isset($_GET['keyCard'])) {
 $keyCard = $_GET['keyCard'];
}elseif (isset($_POST['keyCard'])) {
 $keyCard = filterString($_POST['keyCard']);
}

if (isset($_GET['disclaimerResponse'])) {
 $disclaimerResponse = filterString($_GET['disclaimerResponse']);
}elseif (isset($_POST['disclaimerResponse'])) {
 $disclaimerResponse = filterString($_POST['disclaimerResponse']);
}



if (isset($_GET['punch'])) {
    $punch = $_GET['punch'];
    $curDate = date('Y-m-d', strtotime($punch));
    $yesterday = date('Y-m-d', strtotime('-1 day', strtotime($punch)));
    $curTimeStamp = date('Y-m-d H:i:s', strtoTime($punch));
    $dayOfWeek = date('w', strtotime($curDate));
    $payPeriod = getPayPeriod($curDate);
    $startOfWeek = $payPeriod[0];
    $endOfWeek = $payPeriod[6];
}


if ($action == 'punch'){
//Break apart key card
        
        $keyCardDecoded = keyCardDecoder($keyCard);
        
        //Assign keycode values
        $indvKeyCard = $keyCardDecoded[0];
        $employeeID = $keyCardDecoded[1];
        
        $tranArr = null;
        $tranArr[0] = true;
        $punchDisclaimer = getConstant("punchDisclaimer")['constantValue'];
        
    //Check if Job Clock Department Punch is On
        $jobClockDeptPunch = getConstant("jobClockDeptPunch")["constantValue"];
        $userID = $_SESSION['userID'];
        $employeeData = getEmployeeData($employeeID);
        $employeeDept = $employeeData["departmentID"];

    //Verify Card Number Length
    if(strLen($keyCard) > 4){
        //Validate Key Card numbers
        if(getIndvKeyCard($employeeID)[0] != $indvKeyCard){
            $invalid = true;
        }else{
            //verify employeeID to exsist/scheduled
            $invalid = false;
            $exsists = verifyValidEmployeeID($employeeID);

            
            $schedulePunchReq = getConstant("schedulePunchReq")['constantValue'];
            if($schedulePunchReq) {
                $scheduled = verifyEmployeeScheduled($employeeID, $curDate);
            }else {
                $scheduled = true;
            }
            
            //See if employee is in from yesterday
            $employeeIn = checkEmployeeIn($employeeID, $yesterday);
            $timeWorked = workedHours($employeeID, $curTimeStamp, $yesterday);
            $sincePunch = sinceLastPunch($employeeID, $yesterday, $curTimeStamp);
        }
    }else{
        $invalid = true;
    }
    
    if((!$invalid) && (($exsists && $scheduled) || ($employeeIn && $timeWorked < 43200) 
            || ($sincePunch < 900 && $sincePunch != null)) && ($disclaimerResponse != 2)){
        //Check to see if punched in today 
        if (checkEmployeeIn($employeeID, $curDate)){
            
            if(workedHours($employeeID, $curTimeStamp, $curDate) < 900){
                //Set last punch for punch info
                $curTimeStamp = getLastPunch($employeeID, $yesterday);
                
                //Set status
                $punchStatus = 'IN';
            }else{
                //Punch employee out
                $punchOut = punchOut($employeeID, $curTimeStamp, $curDate);
                
                //Punch Employee out of Advanced Job Clock
                $jobClockPunch = getLastJobClockPunch($employeeID);
                If($jobClockPunch){
                    updateOutJobPunch($jobClockPunch['advancedJobClockID'], $curTimeStamp, 1);
                }
                
                //Set status
                $punchStatus = 'OUT';
            }
            
        }elseif($sincePunch < 900 && $sincePunch != null){
            
                //Set last punch for punch info
                $curTimeStamp = getLastPunch($employeeID, $yesterday);
                //Set status1
                $punchStatus = 'OUT';
                
        }elseif($employeeIn && $timeWorked < 43200){

                //Punch Out For Yesterday's start punch
                $punchOut = punchOut($employeeID, $curTimeStamp, $yesterday);
                
                //Punch Employee out of Advanced Job Clock
                $jobClockPunch = getLastJobClockPunch($employeeID);
                If($jobClockPunch){
                    updateOutJobPunch($jobClockPunch['advancedJobClockID'], $curTimeStamp, 1);
                }
                
                //Set status
                $punchStatus = 'OUT';
        }else{
            if(($punchDisclaimer && $disclaimerResponse == 1) || !$punchDisclaimer){
                //Punch Time Clock In
                $punchIn = punchIn($employeeID, $curTimeStamp, $curDate);
            
                //Clock employee into Advanced Job Clock as well
                if($employeeDept){
                    $x = insertInJobPunch($employeeID, $employeeData["departmentID"], null, $curTimeStamp, 1, $punchIn);
                }

                //Set Status
                $punchStatus = 'IN';
            }
        }
            if($punchDisclaimer && $disclaimerResponse == 0 && $punchStatus == null){
                $tranArr[0] = 2;
                $tranArr[2] = $keyCard;
                $punchOverlay = buildPunchDisclaimer($employeeID);
            }else{
                
                //Gather Employee/Punch Date
                    $employeeTotalHours = getEmployeeHoursInSec($employeeID, $startOfWeek, $endOfWeek);

                    $totalPayRollHours = calculatePayrollData($employeeTotalHours, $employeeID);


                //Gather All Data--------------------------------------
                    $punchData[0] = $employeeID;
                    $punchData[1] = $startOfWeek;
                    $punchData[2] = $endOfWeek;
                    $punchData[3] = ($totalPayRollHours[0] <= 0 ? 0 : $totalPayRollHours[0]);
                    $punchData[4] = ($totalPayRollHours[1] <= 0 ? 0 : $totalPayRollHours[1]);
                    $punchData[5] = ($totalPayRollHours[2] <= 0 ? 0 : $totalPayRollHours[2]);
                    $punchData[6] = ($totalPayRollHours[3] <= 0 ? 0 : $totalPayRollHours[3]);
                    $punchData[7] = $punchStatus;
                    $punchData[8] = $curTimeStamp;


                //Build Successful Punch In Output
                    $punchOverlay = buildPunchOverlay($punchData);
            }
        
    }else{
        if ($disclaimerResponse == 2){
            //Not Scheduled Error Message
            $errorMessage = 'See Manager';
            $punchOverlay = buildPunchOverlay(null, $errorMessage);

        }elseif($invalid){
            //Invalid Card
            $errorMessage = 'Key Card Invalid';
            $punchOverlay = buildPunchOverlay(null, $errorMessage);

        }elseif(!$exsists){
            //No such employee Error Message
            $errorMessage = 'Employee Not Found';
            $punchOverlay = buildPunchOverlay(null, $errorMessage);

        }elseif (!$scheduled){
            //Nor Scheduled Error Message
            $errorMessage = 'Not Scheduled';
            $punchOverlay = buildPunchOverlay(null, $errorMessage);

        }else{
            //echo for ajax
            $tranArr[0] = false;
        }
    }
    
    //skip normal page load
    $tranArr[1] = $punchOverlay;
    $data = json_encode($tranArr);
    echo $data;
    $ajax = true;
    
}elseif($action == 'refresh'){
    //Build Schedule
     $buildSchedule = buildSevenDaySchedule($curDate);
    for($i = 0; $i < 7; $i++){
        $scheduleTable .= $buildSchedule[$i];
    }
    
    $ajax = true;
    echo $scheduleTable;
}else{
    //Build Schedule
     $buildSchedule = buildSevenDaySchedule($curDate);
    for($i = 0; $i < 7; $i++){
        $scheduleTable .= $buildSchedule[$i];
    }
}

if(!$ajax){
    include 'view.php';
}

function buildPunchOverlay($punchData, $errorMessage = null){
    if(isset($errorMessage)){
        $punchOverlay = '<div class="overlay" id="overlayError"><div id="error"><span>' . $errorMessage . '</span></div></div>';
    }else{
        $employeeData = getEmployeeData($punchData[0]);
        $employeeName = $employeeData['firstName'] . ' ' . $employeeData['lastName'];
        
        
        $punchOverlay = '<div class="overlay">'
                        . '<h1>' . $employeeName . '</h1></br>'
                
                        . '<h2>Pay Period</h2>'
                
                           . '<div class="grid">'
                           . '<div class="unit two-fifths">'
                           . '<span>' . date('m-d-Y', strtotime($punchData[1])) . '</span></div>'
                           . '<div class="unit one-fifth">'
                           . '<span>to</span></div>'
                           . '<div class="unit two-fifths">'
                           . '<span>' . date('m-d-Y', strtotime($punchData[2])) . '</span></div>'
                           . '</div>'
                           
                        . '<div id="hours">'
                
                           . '<div class="grid">'
                           . '<div class="unit one-quarter">'
                           . '<h2>Total Hours</h2></div>'
                           . '<div class="unit one-quarter">'
                           . '<h2>Regular</h2></div>'
                           . '<div class="unit one-quarter">'
                           . '<h2>OT</h2></div>'
                           . '<div class="unit one-quarter">'
                           . '<h2>DT</h2></div>'
                           . '</div>'
                           
                           
                           . '<div class="grid">'
                           . '<div class="unit one-quarter">'
                           . '<span>' . $punchData[3] . '</span></div>'
                           . '<div class="unit one-quarter">'
                           . '<span>' . $punchData[4] . '</span></div>'
                           . '<div class="unit one-quarter">'
                           . '<span>' . $punchData[5] . '</span></div>'
                           . '<div class="unit one-quarter">'
                           . '<span>' . $punchData[6] . '</span></div>'
                           . '</div>'
                
                        . '</div>'
                        
                            
                        . '<h2>Status</span></h2>';
                        if($punchData[7] == 'IN'){
                            $punchOverlay .= '<div id="statusIn">'
                                            . '<span>' . $punchData[7] . '</span></br>'
                                            . '</div>'
                                            . '<span>@' . date('m-d-Y h:i:s', strtotime($punchData[8])) . '</span>' 
                                            . '</div>';
                        }elseif($punchData[7] == 'OUT'){
                            $punchOverlay .= '<div id="statusOut">'
                                            . '<span>' . $punchData[7] . '</span></br>'
                                            . '</div>'
                                            . '<span>@' . date('m-d-Y h:i:s', strtotime($punchData[8])) . '</span>' 
                                            . '</div>';
                        }
                        
    }
    return $punchOverlay;
    
}

function buildPunchDisclaimer($employeeID){
        $employeeData = getEmployeeData($employeeID);
        $employeeName = $employeeData['firstName'] . ' ' . $employeeData['lastName'];
        $punchOverlay = '<div class="overlay" id="disclaimer">
                            <h1>' . $employeeName . '</h1>
                            <h2>Read and click yes if you understand.</h2>
                            <h3>Each employee must sign off on this piece of equipment prior to operation. Your
                            supervisor will instruct you on all saftey requirements to operate this piece of equipment.
                            DO NOT click "yes" until you fully understand all safety requirements to operate
                            this piece of equipment.</h3>
                            <ol>
                                <li>Location of power switch and scource of power location.</li>
                                <li>All guarding must remain in place at all times.</li>
                                <li>If a faulty gaurd is detected, turn off power, and notify supervisor of condidtion. DO NOT continue to operate if a faulty gaurd is detected.</li>
                                <li>Lockout/Tagout: If this equipment is Locked or Tagged out, DO NOT OPERATE or turn on for any reason.</li>
                                <li>Keep machine free of debris at all times and clean during oepration.</li>
                                <li>If you notice an unsafe condition, report it immediately to your supervisor and DO NOT OPERATE the equipment.</li>
                                <li>You are required to wear adequate P.P.E. at all times when operating this piece of equipment.</li>
                                <li>No loose clothing allowed.</li>
                            </ol>
                            <button onclick="punch(1)">Yes</button>
                            <button onclick="punch(2)">No</button>
                            <button onclick="punch(2)">Not Sure</button>
                        </div>';

    return $punchOverlay;
    
}

function buildSevenDaySchedule($curDate){
    //Get schedule for next seven days
    for($i = 0; $i < 7; $i++){
        $schedule[$i] = getScheduledEmployees(date('Y-m-d', strtotime($curDate . '+'. $i . ' day')));
    }

    //Get Day Headers for all seven days
    for($i = 0; $i < 7; $i++){
        $dateHeader[$i] = date("F j, Y", strtotime($curDate . '+'. $i . ' day'));
        $dayOfWeek[$i] = date("l", strtotime($curDate . '+'. $i . ' day'));
    }

    //Build html table for next seven days
    for($i = 0; $i < 7; $i++){
        $scheduleTable[$i] = buildSchedule($schedule[$i], $dateHeader[$i], $dayOfWeek[$i]);
    }
    
    return $scheduleTable;
}

function buildSchedule($schedule, $dateHeader, $dayOfWeek){

    //Rebuild Schedule Tables
    $scheduleTable = '<section>
                            <h1>' . $dayOfWeek . '</h1>
                            <h2>' . $dateHeader . '</h2>
                            <table>';
    $scheduleTable .= '<tr>
                              <th>Employee</th>
                              <th>Schedule</th>
                              <th>I/O</th>
                       </tr>';
    for($i = 0; $i < sizeof($schedule); $i++){
        $scheduleTable .= '  <tr>
                                <td>' . $schedule[$i][1] . '</td>
                                <td>' . $schedule[$i][2] . '</td>';
                                if($schedule[$i][4] != null){
                                    $scheduleTable .= '<td>out</td>';
                                }elseif($schedule[$i][3] != null){
                                    $scheduleTable .= '<td>in</td>'; 
                                }else{
                                    $scheduleTable .= '<td></td>';
                                }
                                $scheduleTable .= '</tr>';
    }
    
    $scheduleTable .= '     </table>
                    </section>';
    
    return $scheduleTable;
}