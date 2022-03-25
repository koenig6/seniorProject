<form method="post" action="." id="dataEntryForm">
    <a href="/manager/index.php?action=searchUsers" id="exit"><span>x</span></a>
    <h1>Edit User</h1>
    <ul>
            <?php if($message){echo'<li><h2>' . $message . '</h2></li>';}?>
        <li>
            <label for="userID">User ID:</label>
            <input type="text" name="userID"  
                   id="userID" value="<?php echo $userData['userID'] ?>" readonly="readonly">
        </li>
        <li>
            <label for="employeeID">Employee:</label>
            <select name="employeeID" required>
                <option value="" disabled selected>Select employee</option>
                <?php
                    for($i = 0; $i < sizeof($listEmployeeID); $i++){
                        echo '<option value=' . $listEmployeeID[$i][0];
                                if($userData['employeeID'] == $listEmployeeID[$i][0]){
                                    echo ' selected= "selected"';
                                } 
                        echo '>' . $listEmployeeID[$i][1] . '</option>';
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
                        echo '<option value=' . $listUserGroupID[$i][0];
                                if($userData['userGroupID'] == $listUserGroupID[$i][0]){
                                    echo ' selected= "selected"';
                                } 
                        echo '>' . $listUserGroupID[$i][1] . '</option>';
                    }
                ?>
            </select>
        </li>
        <li>
            <label for="userName">User Name:</label>
            <input type="text" name="userName" placeholder="Enter User Name" 
                   id="userName" value="<?php echo $userData['userName'] ?>" required>
        </li>
        <li>
            <label for="password">Password:</label>
            <input type="password" name="password" placeholder="Enter Password" 
                   id="password" value="**************" required>
        </li>
        <li>
            <label for="loginDuration">Login Duration:</label>
            <input type="text" name="loginDuration" placeholder="Future Select Login Duration" 
                   id="loginDuration" value="<?php echo $userData['loginDuration'] ?>" required>
        </li>
        <li>
            <label for="securityID">Security Level:</label>
            <select name="securityID" required>
                <option value="" disabled selected>Select Security Level</option>
                <?php
                    for($i = 0; $i < sizeof($listSecurityID); $i++){
                        echo '<option value=' . $listSecurityID[$i][0];
                                if($userData['securityID'] == $listSecurityID[$i][0]){
                                    echo ' selected= "selected"';
                                } 
                        echo '>' . $listSecurityID[$i][1] . '</option>';
                    }
                ?>
            </select>
        </li>
    </ul>
    <button type="submit" name="action" id="action" value="updateUser"><span>e</span></button>

</form>
