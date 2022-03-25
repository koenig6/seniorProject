<!DOCTYPE html>
<html>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/module/head.php'; ?>
    <script type="text/javascript" src="/js/ajaxPunchEditing.js"></script>
    <script type="text/javascript" src="/js/jquery-2.1.4.js"></script>
    <link type="text/css" media="print"  rel="stylesheet" href="/css/print.css" />
    <script type="text/javascript" src="/js/print.js"></script>
    <script type="text/javascript" src="/js/ajaxManager.js"></script>
    
    <body class="background">

            <?php include $_SERVER['DOCUMENT_ROOT'] . '/module/sideNav.php'; ?>
        
        <main>
            <div id="busy"></div>
            <div id="viewport" class="viewport">
                
            <div id='opacity'></div>

            <?php if($action == 'binLocations') : ?>

                <?php echo $html; ?>

            <?php elseif($action == 'manager') : ?>

                <form method="post" action="." id="mainMenu">
                    <h1 class="align-center">Manager menu</h1>
                    <?php if($message){echo'<li><h3>' . $message . '</h3></li>';}?>
                      
                    
                </form>
<!-- Users -->
            <?php elseif($action == 'searchUsers') : ?>
                
                <?php
                    if($subAction == 'addUser'){
                        echo '<div class="overlay">';
                        include $_SERVER['DOCUMENT_ROOT'] . '/module/addUser.php';
                        echo '</div>';
                    }
                    elseif($subAction == 'editUser'){
                        echo '<div class="overlay">';
                        include $_SERVER['DOCUMENT_ROOT'] . '/module/editUser.php';
                        echo '</div>';
                    }elseif($subAction == 'printEmployeeCard'){
                        echo '<div class="printOverlay">';
                        include $_SERVER['DOCUMENT_ROOT'] . '/module/editUser.php';
                        echo '</div>';
                    }
                ?>
                
                <a href="/logIn/index.php?action=manager" id="exit"><span>x</span></a>
                    <h1>Search Users</h1>
                    <?php if($message){echo'<li><h3>' . $message . '</h3></li>';}?>
                    <form method="post" action="." id="searchForm">
                                <div class="grid">
                                <div class="unit four-fifths ">
                                <label for="userSearch">Search:</label>
                                <input type="text" name="userSearch" placeholder="Enter search term" id="userSearch" >
                                </div>
                                <div class="unit one-fifth">
                                <button type="submit" name="action" id="action" value="searchUsers" title='Submit Search'><span>s</span></button>
                                <button type="submit" name="action" id="action" value="addUser" title='Add New User'><span>a</span></button>
                                </div>
                                </div>
                    </form>

                    <table class="managerTable">
                        <tr>
                              <td>UserID#</td>
                              <td>Name</td>
                              <td>User Group</td>
                              <td>User Name</td>
                              <td>Login Duration</td>
                              <td>Security Level</td>
                              <td>Options</td>
                          </tr>

                        <?php
                            for($i = 0; $i < count($users); $i++){
                                echo '<tr>';
                                    for($j = 0; $j < 7; $j++){
                                        if($j < 6){
                                            echo '<td>' . $users[$i][$j] . '</td>';
                                        }elseif($j == 6){
                                            echo '<td>' .'<a href="index.php?action=editUser' . $users[$i][0] . '" title="Goto to Edit User"><span>p</span></a>' . '</td>';
                                        } 
                                    }
                                echo '</tr>';
                            }
                        ?>
                    </table>
<!-- Employees -->
            <?php elseif($action == 'searchEmployees') : ?>
                
                <?php
                    if($subAction == 'addEmployee'){
                        echo '<div class="overlay2">';
                        include $_SERVER['DOCUMENT_ROOT'] . '/module/addEmployee.php';
                        echo '</div>';
                    }
                    elseif($subAction == 'editEmployee'){
                        echo '<div class="overlay2">';
                        include $_SERVER['DOCUMENT_ROOT'] . '/module/editEmployee.php';
                        echo '</div>';
                    }
                ?>
                
                <a href= "/logIn/index.php?action=manager" id="exit"><span>x</span></a>
                    <h1>Search Employees</h1>
                    <?php if($message){echo'<li><h3>' . $message . '</h3></li>';}?>
                    <form method="post" action="." id="searchForm">
                                <div class="grid">
                                <div class="unit four-fifths ">
                                <label for="employeeSearch">Search:</label>
                                <input type="text" name="employeeSearch" placeholder="Search User By Name or Status" id="employeeSearch" >
                                </div>
                                <div class="unit one-fifth">
                                <button type="submit" name="action" id="action" value="searchEmployees" title='Submit Search'><span>s</span></button>
                                <button type="submit" name="action" id="action" value="addEmployee" title='Add Employee'><span>a</span></button>
                                </div>
                                </div>
                    </form>
                    
                    <table class="managerTable">
                        <tr>
                              <td>ID#</td>
                              <td>Name</td>
                              <td>Employee Status</td>
                              <td>Employee Phone Number</td>
                              <td>Active</td>
                              <td>Options</td>
                          </tr>
                          
                        <?php
                            for($i = 0; $i < count($employees); $i++){
                                echo '<tr>';
                                    for($j = 0; $j < 6; $j++){
                                        if($j < 4){
                                            echo '<td>' . $employees[$i][$j] . '</td>';
                                        }
                                        elseif($j == 4){
                                            if($employees[$i][4] == 1){
                                                echo '<td>&#10004;</td>';
                                            }else{
                                                echo '<td></td>';
                                            }
                                        }
                                        elseif($j == 5){
                                            echo '<td>';
                                            echo '<a href="index.php?action=viewEmployee' . $employees[$i][0] . '" title="View Employee"><span>F</span></a>';
                                            echo '<a href="index.php?action=editEmployee' . $employees[$i][0] . '" title="Edit Employee"><span>p</span></a>';
                                            echo '</td>';
                                        } 
                                    }
                                echo '</tr>';
                            }
                        ?>
                    </table>
<!-- View Employee -->
            <?php elseif($action == 'viewEmployee') : ?>
                <div class="noPrint">
                    <a href= "/manager/index.php?action=searchEmployees" id="exit"><span>x</span></a>
                </div>
                
                <?php
                    if($subAction == 'printEmployeeCard'){
                        echo '<div class="printOverlay">';
                        include $_SERVER['DOCUMENT_ROOT'] . '/module/printKeyCard.php';
                        echo '</div>';
                    }
                ?>
                <div class="noPrint">
                <h1>Employee Contact Information</h1>
                <div id="displayForm">
                    <div class="grid">
                    <form>
                    <div class="unit half">
                    <ul>
                        <li><label for="employeeID">Employee ID:</label>
                            <input type="text" name="employeeID" id="employeeID" value="<?php echo $employeeData[0][0]?>" disabled></input>
                        </li>
                        <li><label for="name">Name:</label>
                            <input type="text" name="name" id="name" value="<?php echo $employeeData[0][2]?>" disabled></input>
                        </li>
                        <li><label for="name">Address Line 1:</label>
                            <input type="text" name="addressLine1" id="name" value="<?php echo $employeeData[0][5]?>" disabled></input>
                        </li>
                        <li><label for="name">Address Line 2:</label>
                            <input type="text" name="name" id="name" value="<?php echo $employeeData[0][6]?>" disabled></input>
                        </li>
                        <li><label for="name">City, State, Zip:</label>
                            <input type="text" name="name" id="name" value="<?php echo $employeeData[0][7]?>" disabled></input>
                        </li>
                        <li><label for="name">Primary Phone#:</label>
                            <input type="text" name="primaryPhone" id="primaryPhone" value="<?php echo $employeeData[0][3]?>" disabled></input>
                        </li>
                        <li><label for="name">Secondary Phone#:</label>
                            <input type="text" name="secondayPhone" id="secondayPhone" value="<?php echo $employeeData[0][4]?>" disabled></input>
                        </li>
                    
                    </ul>
                    </div>
                    <div class="unit half">
                    <ul>
                        <li>
                            <label for="active">Active:</label>
                            <div class="checkBox">
                            <input type="checkbox" name="active" <?php echo $employeeData[0][11]?> id="active" disabled>
                            </div>
                        </li>
                        <li>
                            <label for="hireDate">Hire Date:</label>
                            <input type="text" name="hireDate" id="hireDate" value="<?php echo $employeeData[0][1]?>" disabled></input>
                        </li>
                        <li>
                            <div class="textField">
                            <label for="employeeNotes">Notes:</label>
                            <textarea name="employeeNotes" cols="30" rows="7" disabled><?php echo $employeeData[0][17]?></textarea>
                            </div>
                        </li>
                        <li>
                            <div class="menuSpace">
                                <a href="index.php?action=printEmployeeCard&employeeID=<?php echo $employeeData[0][0]?>" title="Print Key Card"><span>h</span></a>
                            </div>
                        </li>
                    </ul>
                    </div>
                </form>
                </div>
                   
                <h1>Work Status</h1>
                <form>
                <div class="grid">
                <div class="unit half">
                    
                    <ul>
                        <li><label for="jobTitle">Job Title:</label>
                            <input type="text" name="jobTitle" id="jobTitle" value="<?php echo $employeeData[0][8]?>" disabled></input>
                        </li>
                        <li><label for="status">Status:</label>
                            <input type="text" name="status" id="status" value="<?php echo $employeeData[0][9]?>" disabled></input>
                        </li>
                        <li><label for="shift">Shift:</label>
                            <input type="text" name="shift" id="shift" value="<?php echo $employeeData[0][10]?>" disabled></input>
                        </li>
                        <li><label for="staffingAgency">Staffing Agency:</label>
                            <input type="text" name="staffingAgency" id="staffingAgency" value="<?php echo $employeeData[0][12]?>" disabled></input>
                        </li>
                    </ul>
                </div>
                <div class="unit half">
                    <li><label for="department">Department:</label>
                        <input type="text" name="department" id="department" value="<?php echo $employeeData[0][18]?>" disabled></input>
                    </li>
                    <li><label for="groups">Group:</label>
                        <input type="text" name="groups" id="groups" value="<?php echo $employeeData[0][19]?>" disabled></input>
                    </li>
                    <li><label for="createdBy">Created By:</label>
                        <input type="text" name="createdBy" id="createdBy" value="<?php echo $employeeData[0][13]?>" disabled></input>
                    </li>
                    <li><label for="creationDate">Creation Date:</label>
                        <input type="text" name="creationDate" id="creationDate" value="<?php echo $employeeData[0][14]?>" disabled></input>
                    </li>
                </div>
                </div>
            </form>
        </div>
        </div>
<!-- Time Clock Initial Search -->
            <?php elseif($action == 'timeClock') : ?>
                <a href= "/logIn/index.php?action=manager" id="exit"><span>x</span></a>
                    <h1>Punch Editing</h1>
                    <?php if($message){echo'<li><h3 id="message"' . $message . '</h3></li>';}?>
                    <form method="post" action="." id="searchForm">
                        <div class="grid">
                        <div class="unit four-fifths ">
                        <label for="employeeSearch">Search:</label>
                        <input type="text" name="employeeID" id="employeeID" hidden>
                        <input type="text" name="employeeSearch" placeholder="Search User By Name" 
                               id="employeeSearch" onkeyup="buildDropDown(this.value, event)" onblur="" autocomplete="off">
                        <button type="submit" name="action" id="action" value="timeClockEditor" title='Submit Search' hidden><span>s</span></button>
                        </div>
                            <div class="unit one-fifth">
                                <label id="checkLabel" for="check1">Active</label>
                                <input id="check1" type="checkbox" name="check" value="check1">

                            </div>
                        </div>
                    </form>
                    
                    <div class="dropDown" id="dropDown">
                        
                    </div>
                    
<!-- Time Clock -->
            <?php elseif($action == 'timeClockEditor') : ?>
                
                <?php
                    if($subAction == 'printTimeCard'){
                        echo '<div class="printOverlay">';
                            include $_SERVER['DOCUMENT_ROOT'] . '/module/timeCard.php';
                        echo '</div>';
                    }
                    
                ?>
                <div class="noPrint">
                <a href= "/logIn/index.php?action=manager" id="exit"><span>x</span></a>
                
                    <h1>Punch Editing</h1>
                    <?php if($message){echo'<li><h3 id="message">' . $message . '</h3></li>';}?>
                    <form method="post" action="." id="searchForm">
                        <div class="grid">
                        <div class="unit four-fifths ">
                            <label for="employeeSearch">Select Employee:</label>
                            <input type="text" name="employeeID" id="employeeID" hidden>
                            <input type="text" name="employeeSearch" placeholder="Search User By Name" 
                                   id="employeeSearch" onkeyup="buildDropDown(this.value, event)" onblur="" autocomplete="off">
                            <button type="submit" name="action" id="action" value="timeClockEditor" title='view Editor' hidden><span>s</span></button>
                            <?php
                                if($employeeID == null){
                                    echo '<a href= "/manager/index.php?action=printAllTimeCards&selectedDate=' . $selectedDate . '" title="Print All Time Cards"><span>o</span></a>
                                          <a href= "/manager/index.php?action=printSummaryTimeCards&selectedDate=' . $selectedDate . '" title="Print Summary"><span>R</span></a>';
                                }else{
                                    echo '<a href= "/manager/index.php?action=printTimeCard&employeeID=' . $employeeID . '&selectedDate=' . $selectedDate . '" title="Print Time Card"><span>o</span></a>';
                                } 
                                
                            ?>
                        </div>
                        <div class="unit one-fifth">
                            
<!--                            <label id="checkLabel" for="check1">Active</label>
                            <input id="check1" type="checkbox" name="check" value="check1">-->

                        </div>
                        </div>
                    </form>
                    
                    <div class="dropDown" id="dropDown">
                        
                    </div>
                    
                    <div id="punchEditorHeader">
                        
                        <?php echo $punchHeader ?>
                        
                            
                    </div>
                    
                    <div class="punchHours" id="punchHours">
                        
                            <?php echo $punchEditor ?>
                            
                    </div>
                    
                </div>
            <?php else : ?>
                <?php header('/logIn/Index.php'); ?>       
            <?php endif ?>
           </div>
        </main>
        <footer>
            <?php include $_SERVER['DOCUMENT_ROOT'] . '/module/footer.php'; ?>
        </footer>    
    </body>
</html>