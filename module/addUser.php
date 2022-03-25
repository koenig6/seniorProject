<form method="post" action="." id="dataEntryForm">
    <a href="/manager/index.php?action=searchUsers" id="exit"><span>x</span></a>
    <h1>Add New User</h1>
    <div class="grid">
    <div class="unit two-thirds">
    <ul>
            <?php if($message){echo'<li><h2>' . $message . '</h2></li>';}?>
        <li>
            <label for="employeeID">Employee:</label>
            <select name="employeeID" required>
                <option value="" disabled selected>Select employee</option>
                <?php
                    for($i = 0; $i < sizeof($listEmployeeID); $i++){
                        echo '<option value=' . $listEmployeeID[$i][0] . '>' . $listEmployeeID[$i][1] . '</option>';
                    }
                ?>
            </select>
        </li>
        <li>
            <label for="userGroupID">User Group:</label>
            <select name="userGroupID" required>
                <option value="" disabled selected>Select User Group</option>
                <?php
                    for($i = 0; $i < sizeof($listUserGroupID); $i++){
                        echo '<option value=' . $listUserGroupID[$i][0] . '>' . $listUserGroupID[$i][1] . '</option>';
                    }
                ?>
            </select>
        </li>
        <li>
            <label for="userName">User Name:</label>
            <input type="text" name="userName" placeholder="Enter User Name" id="userName" required>
        </li>
        <li>
            <label for="password">Password:</label>
            <input type="password" name="password" placeholder="Enter Password" id="password" required>
        </li>
        <li>
            <label for="loginDuration">Login Duration:</label>
            <input type="text" name="loginDuration" placeholder="Future Select Login Duration" id="loginDuration" required>
        </li>
        <li>
            <label for="securityID">User Group:</label>
            <select name="securityID" required>
                <option value="" disabled selected>Select Security Level</option>
                <?php
                    for($i = 0; $i < sizeof($listSecurityID); $i++){
                        echo '<option value=' . $listSecurityID[$i][0] . '>' . $listSecurityID[$i][1] . '</option>';
                    }
                ?>
            </select>
        </li>
    </ul>
    </div>
    </div>
    <button type="submit" name="action" id="action" value="submitNewUser"><span>e</span></button>

</form>