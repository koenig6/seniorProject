<form method="post" action="." id="dataEntryForm">
    <a href="/manager/index.php?action=searchEmployees" id="exit"><span>x</span></a>
    <h1>Add New Employee</h1>
            <?php if($message){echo'<li><h2>' . $message . '</h2></li>';}?>
    <div class="grid">
    <div class="unit half">
        <ul>
        <li>
            <label for="firstName">First Name:</label>
            <input type="text" name="firstName" placeholder="Enter First Name" id="firstName" maxlength="30" required>
        </li>
        <li>
            <label for="lastName">Last Name:</label>
            <input type="text" name="lastName" placeholder="Enter Last Name" id="lastName" maxlength="30" required>
        </li>
        <li>
            <label for="addressLine1">Address Line 1:</label>
            <input type="text" name="addressLine1" placeholder="Enter Address Line 1" id="addressLine1" maxlength="50">
        </li>
        <li>
            <label for="addressLine2">Address Line 2:</label>
            <input type="text" name="addressLine2" placeholder="Enter Address Line 2" id="addressLine2" maxlength="50">
        </li>
        <li>
            <label for="city">City:</label>
            <input type="text" name="city" placeholder="Enter City" id="city" maxlength="30">
        </li>
        <li>
            <label for="state">State:</label>
            <input type="text" name="state" placeholder="Enter State: Example CA" value="CA" id="city" maxlength="2">
        </li>
        <li>
            <label for="zip">Zip/+4:</label>
            <input type="text" name="zip" placeholder="Enter Zip Code" id="zip" min="01111" max="99999">
        </li>
        <li>
            <label for="primaryPhone">1st Phone#:</label>
            <input type="number" name="primaryPhone" placeholder="Enter Primary Phone Number" id="primaryPhone" min="1000000000" max="9999999999">
        </li>
        <li>
            <label for="secondaryPhone">2nd Phone#:</label>
            <input type="number" name="secondaryPhone" placeholder="Enter Secondary Phone Number" id="secondaryPhone" min="1000000000" max="9999999999">
        </li>
        </ul>
    </div>
    <div class="unit half">
        <ul>
        <li>
            <label for="hireDate">Hire Date:</label>
            <input type="date" name="hireDate" placeholder="Enter Hire Date" value="<?php echo date("Y-m-d")?>" id="hireDate">
        </li>
        <li>
            <label for="jobTitleID">Title:</label>
            <select name="jobTitleID" required>
                <option value="" disabled selected>Select Title</option>
                <?php
                    for($i = 0; $i < sizeof($listTitleID); $i++){
                        echo '<option value=' . $listTitleID[$i][0];
                                if($listTitleID[$i][0] == 11){
                                    echo ' selected= "selected"';
                                } 
                        echo '>' . $listTitleID[$i][1] . '</option>';
                    }
                ?>
            </select>
        </li>
        <li>
            <label for="employeeStatusID">Status:</label>
            <select name="employeeStatusID" required>
                <option value="" disabled selected>Select Status</option>
                <?php
                    for($i = 0; $i < sizeof($listStatusID); $i++){
                        echo '<option value=' . $listStatusID[$i][0];
                                if($listStatusID[$i][0] == 2){
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
                                if($listShiftID[$i][0] == 1){
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
                
                <option value="1800" selected>30min</option>
                <option value="3600">1hr</option>

            </select>
        </li>
        <li>
            <label for="tempAgencyID">Temp Agency:</label>
            <select name="tempAgencyID" required>
                <option value="" disabled selected>Select Temp Agency</option>
                <?php
                    for($i = 0; $i < sizeof($listTempAgencyID); $i++){
                        echo '<option value=' . $listTempAgencyID[$i][0];
                                if($listTempAgencyID[$i][0] == 1){
                                    echo ' selected= "selected"';
                                } 
                        echo '>' . $listTempAgencyID[$i][1] . '</option>';
                    }
                ?>
            </select>
        </li>
        <div class="checkBox">
        <li>
            
            <label for="active">Active:</label>
            <input type="checkbox" name="active" value="1" id="active" checked>
            
        </li>
        </div>
        <li>
            <label for="employeeNotes">Notes:</label>
            <textarea name="employeeNotes" cols="30" rows="7"></textarea>
        </li>
        
        </ul>
    </div>
    </div>
    
            

    <button type="submit" name="action" id="action" value="submitNewEmployee"><span>e</span></button>
</form>