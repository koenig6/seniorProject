<form method="post" action="." id="dataEntryForm">
    <?php if($subAction == 'editEmployee2'){
        echo '<a href="index.php?action=viewEmployee' . $employeeID . '" id="exit"><span>x</span></a>';
    }else{
        echo '<a href="/manager/index.php?action=searchEmployees" id="exit"><span>x</span></a>';
    }
    ?>
    
    <h1>Edit Employee</h1>
            <?php if($message){echo'<li><h2>' . $message . '</h2></li>';}?>
    <div class="grid">
    <div class="unit half">
        <ul>
            <li>
                <label for="employeeID">Employee ID:</label>
                <input type="text" name="employeeID" value="<?php echo $employeeData['employeeID'] ?>" id="employeeID" readonly="readonly">
            </li>   
            <li>
                <label for="firstName">First Name:</label>
                <input type="text" name="firstName" placeholder="Enter First Name" 
                       id="firstName" maxlength="30" value="<?php echo $employeeData['firstName'] ?>" required>
            </li>
            <li>
                <label for="lastName">Last Name:</label>
                <input type="text" name="lastName" placeholder="Enter Last Name" 
                       id="lastName" maxlength="30" value="<?php echo $employeeData['lastName'] ?>" required>
            </li>
            <li>
                <label for="tempEmployeeID">Temp ID:</label>
                <input type="Number" name="tempEmployeeID" placeholder="Temp ID Number" 
                       id="tempEmployeeID" value="<?php echo $employeeData['tempEmployeeID'] ?>">
            </li>
            <li>
                <label for="hireDate">Hire Date:</label>
                <input type="date" name="hireDate" placeholder="Enter Hire Date" value="<?php echo $employeeData['hireDate'] ?>" id="hireDate">
            </li>
            <li>
                <label for="employeeNotes">Notes:</label>
                <textarea name="employeeNotes" cols="30" rows="7"><?php echo $employeeData['employeeNotes']?></textarea>
            </li>
            
        </ul>
    </div>
    <div class="unit half">
        <ul>
        
        
        <li>
            <label for="titleID">Title:</label>
            <select name="titleID" required>
                <option value="" disabled selected>Select Title</option>
                <?php
                    for($i = 0; $i < sizeof($listTitleID); $i++){
                        echo '<option value=' . $listTitleID[$i][0];
                                if($listTitleID[$i][0] == $employeeData['titleID']){
                                    echo ' selected= "selected"';
                                } 
                        echo '>' . $listTitleID[$i][1] . '</option>';
                    }
                ?>
            </select>
        </li>
        <li>
            <label for="statusID">Status:</label>
            <select name="statusID" required>
                <option value="" disabled selected>Select Status</option>
                <?php
                    for($i = 0; $i < sizeof($listStatusID); $i++){
                        echo '<option value=' . $listStatusID[$i][0];
                                if($listStatusID[$i][0] == $employeeData['statusID']){
                                    echo ' selected= "selected"';
                                } 
                        echo '>' . $listStatusID[$i][1] . '</option>';
                    }
                ?>
            </select>
        </li>
        <li>
            <label for="shiftID">Shift:</label>
            <select name="shiftID" required>
                <option value="" disabled selected>Select Shift</option>
                <?php
                    for($i = 0; $i < sizeof($listShiftID); $i++){
                        echo '<option value=' . $listShiftID[$i][0];
                                if($listShiftID[$i][0] == $employeeData['shiftID']){
                                    echo ' selected= "selected"';
                                } 
                        echo '>' . $listShiftID[$i][1] . '</option>';
                    }
                ?>
            </select>
        </li>
        <li>
            <label for="lunchDuration">Lunch Duration:</label>
            <select name="lunchDuration" required>
                <!-- <option value="" disabled selected>Select Lunch</option> -->
                <?php

                echo '<option value="1800"';
                if($employeeData['lunchDuration'] == '1800') { echo 'selected'; }
                echo '>30min</option>';

                echo '<option value="3600"';
                if($employeeData['lunchDuration'] == '3600') { echo 'selected'; }
                echo '>1hr</option>';

                ?>
                
            </select>
        </li>
        <li>
            <label for="tempAgencyID">Temp Agency:</label>
            <select name="tempAgencyID" required>
                <option value="" disabled selected>Select Temp Agency</option>
                <?php
                    for($i = 0; $i < sizeof($listTempAgencyID); $i++){
                        echo '<option value=' . $listTempAgencyID[$i][0];
                                if($listTempAgencyID[$i][0] == $employeeData['tempAgencyID']){
                                    echo ' selected= "selected"';
                                } 
                        echo '>' . $listTempAgencyID[$i][1] . '</option>';
                    }
                ?>
            </select>
        </li>
        <li>
            <label for="departmentID">Department:</label>
            <select name="departmentID">
                <option value="" disabled selected>Select Department</option>
                <?php
                    for($i = 0; $i < sizeof($departmentList); $i++){
                        echo '<option value=' . $departmentList[$i][0];
                                if($departmentList[$i][0] == $employeeData['departmentID']){
                                    echo ' selected= "selected"';
                                } 
                        echo '>' . $departmentList[$i][1] . '</option>';
                    }
                ?>
            </select>
        </li>
        <li>
            <label for="groupID">Group:</label>
            <select name="groupID">
                <option value="" disabled selected>Select Department</option>
                <?php
                    for($i = 0; $i < sizeof($laborGroupList); $i++){
                        echo '<option value=' . $laborGroupList[$i][0];
                                if($laborGroupList[$i][0] == $employeeData['laborGroupID']){
                                    echo ' selected= "selected"';
                                } 
                        echo '>' . $laborGroupList[$i][1] . '</option>';
                    }
                ?>
            </select>
        </li>
        <div class="checkBox">
        <li>
            
            <label for="active">Active:</label>
            <input type="checkbox" name="active" value="1" id="active" <?php echo $employeeData['active']?>>
            
        </li>
        </div>
        
        </ul>
    </div>
    </div>

    <button type="submit" name="action" id="action" value="updateEmployee"><span>e</span></button>
</form>