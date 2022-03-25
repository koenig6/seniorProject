<?php
$html = '
<div class="completeWOExModal">
<h1>Complete Work Order</h1>
    <div class="completeWOExItem">
        <label for="workOrderNumber">workOrderNumber: </label>
        <input class="pill" type="text" name="workOrderNumber" placeholder="Enter load tag SKU#" id="workOrderNumber" value="' . $workOrderNumber . '" readonly ' . ($workOrderData[10] == 1 ? 'disabled' : '') . '>
    </div>
    <div class="completeWOExItem">
        <label for="crewLeader">Crew Leader: </label>
        <select class="pill" id="crewLeader" name="crewLeader" required>
                <option value="" disabled selected>Select Crew Leader</option>';
    
                for($j = 0; $j < sizeof($employeeList); $j++){
                    $html .= '<option value=' . $employeeList[$j][0];
                        if($employeeList[$j][0] == $workOrderData[7]){
                            $html .=  ' selected= "selected"';
                        } 
                    $html .= '>' . $employeeList[$j][1] . '</option>';
                }
        $html .= '       
        </select>
    </div>
    <div class="completeWOExItem">
        <label for="completionDate">Completion Date: </label>
        <input class="pill" ' . ($workOrderData[10] == 1 ? 'disabled' : '') . ' type="date" name="completionDate" id="completionDate" value="' . ($workOrderData[5] == null ? date('Y-m-d') : $workOrderData[5]) . '" required>
    </div>
    <div class="completeWOExItem">
        <label for="numInCrew"># in Crew: </label>
        <input class="pill" ' . ($workOrderData[10] == 1 ? 'disabled' : '') . ' type="number" min="0" name="crewCount" id="crewCount" value="' . $workOrderData[12] . '" required>
    </div>
    <div class="completeWOExItem">
        <label for="quantityCompleted">Quantity Completed: </label>
        <input class="pill" ' . ($workOrderData[10] == 1 ? 'disabled' : '') . ' type="number" name="quantity" placeholder="Enter Quantity" id="quantity" value="' . (int)$workOrderData[6] . '" required>
    </div>
    <div class="completeWOExItem completeWOExItemTimeStudy">
        <label for="timeCompleted">Time Completed (H:M:S): </label>
        <input class="pill" type="number" id="elapsedHrs" style="text-align:right" placeholder="Hour" value="' . ( $timeStudyH == null ? 0 : $timeStudyH ) .'" required ' . ($workOrderData[10] == 1 ? 'disabled' : '') . '>
        <span>:</span>
        <input class="pill" type="number" id="elapsedMins" step="15" max="45" min="0" placeholder="Min" value="' . (float)$timeStudyM .'" ' . ($workOrderData[10] == 1 ? 'disabled' : '') . '>
        <span>:</span>
        <input class="pill" type="number" id="elapsedSecs" step="15" max="45" min="0" placeholder="Sec" value="' . (float)$timeStudyS .'" ' . ($workOrderData[10] == 1 ? 'disabled' : '') . '>
    </div>
</div>
<a ' . ($workOrderData[10] == 1 ? 'style="display:none;"' : '') . ' class="bs-btn btn-blue" onclick="updateWO()"><i class="bi bi-arrow-bar-up"></i></a>';
echo $html;
?>