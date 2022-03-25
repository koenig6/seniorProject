<form method="post" action="." id="dataEntryForm">

    <a href="/reports/index.php" id="exit"><span>x</span></a>
    <h1>Forklift Activity Options</h1>
    <div class="grid">
    <div class="unit whole">
        
        <ul>
            <?php if($message){echo'<li><h2>' . $message . '</h2></li>';}?>

            <li>
                <label for="date1">Select Date Range:</label>
                <input type="date" name="date1" id="date1" value="<?php echo date('Y-m-d', strtotime('-31 days', strtoTime(date('Y-m-d'))))?>" required autofocus>
                TO
                <input type="date" name="date2" id="date2" value="<?php echo date("Y-m-d")?>" required>
            </li>
            <li>
                <label for="reportOptions">Select Report Opt:</label>
                <select name="reportOptions" Required>
                    <option value="" disabled selected>Select Report Options</option>
                    <option value=1>Totals Only</option>
                    <option value=2>Transactions By Day</option>
                </select>
            </li>
            <li>
                <label for="userID">Select User:</label>
                <select name="userID">
                    <option value="0" selected>Select User (Optional)</option>
                    <?php
                        for($i = 0; $i < sizeof($activeUsers); $i++){
                            echo '<option value=' . $activeUsers[$i][0];
                            echo '>' . $activeUsers[$i][1] . ', ' . $activeUsers[$i][2] . '</option>';
                        }
                    ?>
                </select>
            </li>


        </ul>
        </div>
    </div>
    <button type="submit" name="action" id="action" value="<?php echo $reportName ?>"><span>i</span></button>
    
</form>