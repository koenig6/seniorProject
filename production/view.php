<!DOCTYPE html>
<html>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/module/head.php'; ?>
    <script type="text/javascript" src="/js/ajaxProduction.js"></script>
    <link type="text/css" media="print"  rel="stylesheet" href="/css/print_1.css" />
    <script type="text/javascript" src="/js/print.js"></script>
  
    
    <body class="background">
            <?php include $_SERVER['DOCUMENT_ROOT'] . '/module/sideNav.php'; ?>
        <main>
             <div id="overlay"></div>
             <div id="busy"></div>
             <div id="viewport" class="viewport">
        <?php        
            if($subAction == 'printCutSheet'){
                echo '<div class="printOverlay">';
                include $_SERVER['DOCUMENT_ROOT'] . '/module/cutSheet.php';
                echo '</div>';
            }
        ?>    
            
            <div class="noPrint, productionTable">
            
    <?php if($action == 'production') : ?>
            <?php
                    if($subAction == 'selectWorkOrder'){
                        echo '<div class="overlay">';
                        include $_SERVER['DOCUMENT_ROOT'] . '/module/workOrder.php';
                        echo '</div>';
                    }else if($subAction == 'completeWO'){
                        echo '<div class="overlay">';
                        include $_SERVER['DOCUMENT_ROOT'] . '/module/completeWO.php';
                        echo '</div>';
                    }else if($subAction == 'completeWOEx'){
                        echo '<div class="overlay">';
                        include $_SERVER['DOCUMENT_ROOT'] . '/module/completeWOEx.php';
                        echo '</div>';
                    }
                ?>

                
                <form method="post" action="." id="mainMenu">
                    <h1 class="align-center">Production Menu</h1>
                        
                </form>
                
<!-- Production Schedule --> 
        <?php elseif($action == 'productionSchedule' || $action =='workOrder' || $action == 'woConfirmationMenu' || $action == 'woRejectsMenu') : ?>
            <?php
                if($action != 'woConfirmationMenu' && $action != 'woRejectsMenu') {
                    if (isset($_SESSION['returnStr'])){
                        echo $_SESSION['returnStr'];
                    }else{
                        echo '<a href="/logIn/index.php?action=production" id="exit"><span>x</span></a>';
                    }
                }
            ?>    
               
                <!-- <h1>Production Schedule</h1> -->
                <?php echo $html ?>

<!-- Past Due Work Orders --> 
<?php elseif($action == 'pastDue') : ?>

                    <!-- Include Production Schedule View -->
                    <?php echo $html ?>

<!-- Schedule Work Order -->        
        <?php elseif($action == 'scheduleWO') : ?>
                
                <!-- <a href="/production/index.php?action=exit" id="exit"><span>x</span></a> -->
                
                    <!-- Include Production Schedule View -->
                    
                    <?php echo $html ?>

                    
<!-- Schedule Employees -->
        <?php elseif($action == 'employeeScheduler') : ?>
            <a href="/production/index.php?action=exit" id="exit"><span>x</span></a>
            <h1>Schedule Employees</h1>
            <form method="post" action="." id="searchForm">
            <div class="grid">
                <div class="unit one-fifth">
                    <a href="/production/index.php?action=autoEmployeeSchedule&date=<?php echo date('Y-m-d', strtotime($selectedDate))?>" title="Auto Schedule"><span>b</span></a>
                </div>
                <div class="unit two-fifths">
                        <nav>
                            <a href="/production/index.php?action=employeeScheduler&date=<?php echo date('Y-m-d', strtotime("$selectedDate -1 day"))?>" 
                                title="Yesterday"><span><</span></a>
                            <input type="date" name="selectedDate" id="selectedDate" value="<?php echo $selectedDate ?>" onchange="this.form.submit()">
                            <a href="/production/index.php?action=employeeScheduler&date=<?php echo date('Y-m-d', strtotime("$selectedDate +1 day"))?>" 
                                title="Tomorrow"><span>></span></a>
                        </nav>
                </div>
                
                <div class="unit two-fifths">
                    <label for="statusID">Shift:</label>
                    <select id="selectedShift" name="statusID" required>
                        <?php
                            echo '<optgroup label="Common Shifts">';
                            for($i = 0; $i < sizeof($listShiftsID); $i++){
                                if($listShiftsID[$i][2] == 1){
                                    echo '<option value=' . $listShiftsID[$i][0];
                                            if($listShiftsID[$i][0] == 3){
                                                echo ' selected= "selected"';
                                            } 
                                    echo '>' . $listShiftsID[$i][1] . '</option>';
                                }
                            }
                            echo '</optgroup>';
                            
                            echo '<optgroup label="All Shifts">';
                            for($i = 0; $i < sizeof($listShiftsID); $i++){
                                echo '<option value=' . $listShiftsID[$i][0];
                                echo '>' . $listShiftsID[$i][1] . '</option>';
                            }
                            echo '</optgroup>';
                        
                        ?>
                    </select>
                    <a href="/production/index.php?action=scheduleWO" title="Add New Shift"><span>L</span></a>
                </div>
            </div>
                
            </form>
            
            <div id="scheduleTables">

                <?php echo $scheduleTable; ?>
        
            </div>    



            
        <?php elseif($action == 'sJobClock') : ?>
            <div id="overlay"></div>
            
            <div id="simpleJobClock">
            <a href="/production/index.php?action=exit" id="exit"><span>x</span></a>
            <h1>Simple Job Clock</h1>
            <section id="sjcOverlay"></section>
            <div class="grid">
                <div class="unit half">
                    <section id="sjcHeader">
                        <?php echo $sjcHeaderView[0]; ?><?php echo $sjcHeaderView[1] . $sjcHeaderView[2]; ?>
                    </section>
                </div>
                <div class="unit half align-center">
                    
                </div>
            </div>
            <div class="grid">
                <div class="unit half">
                    <h2>Current Jobs</h2>
                    <section id="sjcCurrentJobs">
                        
                        <?php echo $workOrderListView; ?>
                    </section>
                </div>
                <div class="unit half">
                    <section id="sjcPunches">
                        <h2>Job Punches</h2>
                    </section>
                </div>
            </div>
            </div>
            
        <?php else : ?>
                <?php //header('/logIn/Index.php'); ?>       
        <?php endif ?>
            </div> 
                 </div>
        </main>
        <footer>
            <?php include $_SERVER['DOCUMENT_ROOT'] . '/module/footer.php'; ?>
        </footer>    
    </body>
</html>

