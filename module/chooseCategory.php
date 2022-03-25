<form method="post" action="." id="selectionForm">

    <a href="/reports/index.php" id="exit"><span>x</span></a>
    <h1>Discrepancy Report Filters</h1>
    <div class="reportSelection">
        <div class="grid">
            <div class="unit half">
                <h2 style="margin-left: 12%;">Select Category <a href="#" id="invertChecks" onclick="uncheckAll();">Uncheck-All/Check-All</a></h2>
            </div>
            <div class="unit half">
                <label for="includeZeros">Include Zeros</label>
                <input type="checkbox" name="includeZeros" id="includeZeros" value="includeZeros"></input>
            </div>
        </div>
        <?php if($message){echo'<li><h2>' . $message . '</h2></li>';}?>
        <div class="grid">
        <?php
            $col = 0;
            for($i = 0; $i < 3; $i++){
                echo '<div class="unit one-third">';
                for($j = 0; $j < ((sizeof($categoryList) / 3)); $j++){
                    $x = $j + ($i * (floor(sizeof($categoryList) / 3) + 1));
                    //Echo every cycle a new option box
                    if(isset($categoryList[$x][0])){
                    echo '<label for="' . $categoryList[$x][0] . '">' . $categoryList[$x][0] . '-' . $categoryList[$x][1] . '</label>
                            <input type="checkbox" name="' . $categoryList[$x][0] . '" value="' . $categoryList[$x][0] . '" id="' . $categoryList[$x][0] . '" checked><br>';
                    }
                }
                echo '</div>';
            }
        ?>
            
        </div>
    </div>
    <button type="submit" name="action" id="action" value="inventoryDiscrepancy"><span>i</span></button>
    
    
</form>