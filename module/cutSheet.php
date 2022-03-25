<div class='print'>
    <div class='noPrint'>
<a href="#" onclick="printReport();"><span>h</span></a> <a href="/production/index.php?action=productionSchedule" id="exit"><span>x</span></a>
    </div>
<header>
    <?php
        if($cutSheetData[0][2] == 1){
            echo '<h1>Lumber Processing Sheet</h1>';
        }else{
            echo '<h1>Plywood Processing Sheet</h1>';
        }
    ?>
</header>
<body>

    <table class="minTable" id="report5">
        <thead></thead>
        <tbody>
            <?php
                echo  '<tr>
                        <th colspan="11">Cut Sheet</th>
                        <th colspan="2">Material Usage Log</th>
                        </tr>
                        <tr>
                            <th>Job Name:</th>
                            <td colspan="5">' . $cutSheetData[0][13] . '</td>
                            <th>Date Completed:</th>
                            <td colspan="3"></td>
                            <th>QtyCompleted:</th>
                            <th>Serial.....</th>
                            <th>Qty.......</th>
                        </tr>
                        <tr>
                            <th>Job Number:</th>
                            <td colspan="5">' . $cutSheetData[0][10] . '</td>
                            <th>Start Time:</th>
                            <td colspan="3"></td>
                            <td rowspan="3"></td>
                            <td></td>
                            <td></td>
                        </tr>
                        <tr>
                            <th>Sku Number:</th>
                            <td colspan="5">' . $cutSheetData[0][11] . '</td>
                            <th>End Time:</th>
                            <td colspan="3"></td>
                            <td></td>
                            <td></td>
                        </tr>
                        <tr>
                            <th>Job Quantity:</th>
                            <td colspan="5">' . (float)$cutSheetData[0][12] . '</td>
                            <th>Total Time:</th>
                            <td colspan="3"></td>
                            <td></td>
                            <td></td>
                        </tr>
                        <tr>
                            <th>Part Name:</th>
                            <th>Qty Per:</th>
                            <th>Thick:</th>
                            <th>X</th>
                            <th>Width:</th>
                            <th>X</th>
                            <th>Length:</th>
                            <th>Total Parts:</th>
                            <th>Stock Sku:</th>
                            <th>Description:</th>
                            <th>Total Boards Needed:</th>
                            <td></td>
                            <td></td>
                        </tr>';
                
                $pbcnt = 0;
                for($i = 0; $i < count($cutSheetData); $i++){
                    if($pbcnt > 15){
                        echo '</tbody></table>';
                        echo '<div class="pageBreak"></div>';
                        echo '<table class="minTable" id="report5"><tbody>';
                        echo '<header>
                                    <h1>Cont</h1>
                            </header>';
                        echo '<tr>
                            <th colspan="11">Cut Sheet</th>
                            <th colspan="2">Material Usage Log</th>
                            </tr>
                            <tr>
                            <th>Part Name:</th>
                            <th>Qty Per:</th>
                            <th>Thick:</th>
                            <th>X</th>
                            <th>Width:</th>
                            <th>X</th>
                            <th>Length:</th>
                            <th>Total Parts:</th>
                            <th>Stock Sku:</th>
                            <th>Description:</th>
                            <th>Total Boards Needed:</th>
                            <td></td>
                            <td></td>
                            </tr>';
                        $pbcnt = 0;
                    }
                    echo '<tr>
                            <td>' . $cutSheetData[$i][3] . '</td>
                            <td>' . (float)$cutSheetData[$i][4] . '</td>
                            <td>' . decimal2fraction($cutSheetData[$i][5]) . '</td>
                            <td>X</td>
                            <td>' . decimal2fraction($cutSheetData[$i][6]) . '</td>
                            <td>X</td>
                            <td>' . decimal2fraction($cutSheetData[$i][7]) . '</td>
                            <td>' . (float)$cutSheetData[$i][15] . '</td>
                            <td>' . $cutSheetData[$i][8] . '</td>
                            <td>' . $cutSheetData[$i][14] . '</td>
                            <td>' . ceil($cutSheetData[$i][9]) . '</td>
                            <td></td>
                            <td></td>
                        </tr>';
                    $pbcnt++;
                }
                if($pbcnt < 18){
                    $z = (18 - $pbcnt);
                    for($j = 0; $j < $z; $j++){
                        echo '<tr>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td>X</td>
                                <td></td>
                                <td>X</td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>';
                        
                        $pbcnt++;
                    }
                }
            ?>
        </tbody>
    </table>
</div>
</body>