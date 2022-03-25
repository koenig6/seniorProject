<!DOCTYPE html>
<html>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/module/head.php'; ?>
    <link type="text/css" media="print"  rel="stylesheet" href="/css/print_1.css" />
    <script type="text/javascript" src="/js/print.js"></script>
    <script type="text/javascript" src="/js/macro.js"></script>
    <script type="text/javascript" src="/js/reports.js"></script>
    <body class="background">
    <div id="busy"></div>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/module/sideNav.php'; ?>
        <main>
             <div id="viewport" class="viewport">
            <div id='opacity'></div>
            <?php if($action == 'reports') : ?>

                <?php
                    if($subAction == 'reportSingleDate'){
                        echo '<div class="reportSelectorOverlay">';
                        include $_SERVER['DOCUMENT_ROOT'] . '/module/reportSingleDate.php';
                        echo '</div>';
                    }elseif($subAction == 'reportSingleDate2'){
                        echo '<div class="reportSelectorOverlay">';
                        include $_SERVER['DOCUMENT_ROOT'] . '/module/reportSingleDate2.php';
                        echo '</div>';
                    }elseif($subAction == 'reportCompanySelect'){
                        echo '<div class="reportSelectorOverlay">';
                        include $_SERVER['DOCUMENT_ROOT'] . '/module/reportCompanySelect.php';
                        echo '</div>';
                    }elseif($subAction == 'reportDateRange'){
                        echo '<div class="reportSelectorOverlay">';
                        include $_SERVER['DOCUMENT_ROOT'] . '/module/reportDateRange.php';
                        echo '</div>';
                    }elseif($subAction == 'selectSkuNumber'){
                        echo '<div class="reportSelectorOverlay">';
                        include $_SERVER['DOCUMENT_ROOT'] . '/module/reportKitSkuNumber.php';
                        echo '</div>';
                    }elseif($subAction == 'chooseCategory'){
                        echo '<div class="reportSelectorOverlay">';
                        include $_SERVER['DOCUMENT_ROOT'] . '/module/chooseCategory.php';
                        echo '</div>';
                    }elseif($subAction == 'selectJobNumber'){
                        echo '<div class="reportSelectorOverlay">';
                        include $_SERVER['DOCUMENT_ROOT'] . '/module/selectJobNumber.php';
                        echo '</div>';
                    }elseif($subAction == 'historyOptions'){
                        echo '<div class="reportSelectorOverlay">';
                        include $_SERVER['DOCUMENT_ROOT'] . '/module/historyOptions.php';
                        echo '</div>';
                    }elseif($subAction == 'forkliftActivityOptions'){
                        echo '<div class="reportSelectorOverlay">';
                        include $_SERVER['DOCUMENT_ROOT'] . '/module/forkliftActivityOptions.php';
                        echo '</div>';
                    }elseif($subAction == 'pickPackOptions'){
                        echo '<div class="reportSelectorOverlay">';
                        include $_SERVER['DOCUMENT_ROOT'] . '/module/pickPackOptions.php';
                        echo '</div>';
                    }elseif($subAction == 'dailyShipping'){
                        echo '<div class="printOverlay">';
                        include $_SERVER['DOCUMENT_ROOT'] . '/module/report1.php';
                        echo '</div>';
                    }elseif($subAction == 'billablePallet'){
                        echo '<div class="printOverlay">';
                        include $_SERVER['DOCUMENT_ROOT'] . '/module/report3.php';
                        echo '</div>';
                    }elseif($subAction == 'billablePalletBatch'){
                        echo '<div class="printOverlay">';
                        include $_SERVER['DOCUMENT_ROOT'] . '/module/report4.php';
                        echo '</div>';
                    }elseif($subAction == 'newSkuNumberReport'){
                        echo '<div class="printOverlay">';
                        include $_SERVER['DOCUMENT_ROOT'] . '/module/report5.php';
                        echo '</div>';
                    }elseif($subAction == 'timeStudyReport'){
                        echo '<div class="printOverlay">';
                        include $_SERVER['DOCUMENT_ROOT'] . '/module/report6.php';
                        echo '</div>';
                    }elseif($subAction == 'inventoryDiscrepancy'){
                        echo '<div class="printOverlay">';
                        include $_SERVER['DOCUMENT_ROOT'] . '/module/report2.php';
                        echo '</div>';
                    }elseif($subAction == 'inventoryDiscrepancyCustomerOwned'){
                        echo '<div class="printOverlay">';
                        include $_SERVER['DOCUMENT_ROOT'] . '/module/report2c.php';
                        echo '</div>';
                    }elseif($subAction == 'openWO'){
                        echo '<div class="printOverlay">';
                        include $_SERVER['DOCUMENT_ROOT'] . '/module/report7.php';
                        echo '</div>';
                    }elseif($subAction == 'whosIn'){
                        echo '<div class="printOverlay">';
                        include $_SERVER['DOCUMENT_ROOT'] . '/module/report8.php';
                        echo '</div>';
                    }elseif($subAction == 'simpleJobClockReport'){
                        echo '<div class="printOverlay">';
                        include $_SERVER['DOCUMENT_ROOT'] . '/module/report9.php';
                        echo '</div>';
                    }elseif($subAction == 'inventoryHistoryBySku'){
                        echo '<div class="printOverlay">';
                        include $_SERVER['DOCUMENT_ROOT'] . '/module/report10a.php';
                        echo '</div>';
                    }elseif($subAction == 'forkliftActivitySummary'){
                        echo '<div class="printOverlay">';
                        include $_SERVER['DOCUMENT_ROOT'] . '/module/report11a.php';
                        echo '</div>';
                    }elseif($subAction == 'forkliftActivityByDay'){
                        echo '<div class="printOverlay">';
                        include $_SERVER['DOCUMENT_ROOT'] . '/module/report11b.php';
                        echo '</div>';
                    }elseif($subAction == 'simpleJobClockSummary'){
                        echo '<div class="printOverlay">';
                        include $_SERVER['DOCUMENT_ROOT'] . '/module/report12.php';
                        echo '</div>';
                    }elseif($subAction == 'wipReport'){
                        echo '<div class="printOverlay">';
                        include $_SERVER['DOCUMENT_ROOT'] . '/module/report12b.php';
                        echo '</div>';
                    }elseif($subAction == 'pickPackHistoryByLot'){
                        echo '<div class="printOverlay">';
                        include $_SERVER['DOCUMENT_ROOT'] . '/module/report13a.php';
                        echo '</div>';
                    }elseif($subAction == 'pickPackHistoryByOrder'){
                        echo '<div class="printOverlay">';
                        include $_SERVER['DOCUMENT_ROOT'] . '/module/report13b.php';
                        echo '</div>';
                    }elseif($subAction == 'pickPackHistoryByDate'){
                        echo '<div class="printOverlay">';
                        include $_SERVER['DOCUMENT_ROOT'] . '/module/report13c.php';
                        echo '</div>';
                    }elseif($subAction == 'pickPackHistoryByDate'){
                        echo '<div class="printOverlay">';
                        include $_SERVER['DOCUMENT_ROOT'] . '/module/report13c.php';
                        echo '</div>';
                    }elseif($subAction == 'tempAgencyAudit'){
                        echo '<div class="printOverlay">';
                        echo $tempSummary;
                        echo '</div>';
                    }
                    
                ?>
            <div class="noPrint">
                <!-- <a href= "/logIn/index.php?action=mainMenu" id="exit"><span>x</span></a> -->
                
                
                <div id="reports">
                    <h1>Reports Menu</h1>
                    <form method="post" action="." id="mainMenu">
                        <?php if($message){echo'<li><h3>' . $message . '</h3></li>';}?>
                            <div class="grid">
                                <div class="unit half">
                                    <ul>
                                        <li><button type="submit" name="action" id="action" value="report1" title='Report 1'><span>g</span><?php echo $report1 ?></button>
                                        <!-- <li><button type="submit" name="action" id="action" value="report2" title='Report 2'><span>B</span><?php echo $report2 ?></button> -->
                                        <li><button type='button' onclick="invReportType()" title='Inventory Report'><span>B</span><?php echo 'Inventory Report' ?></button>
                                        <li><button type="submit" name="action" id="action" value="report3" title='Report 3'><span>B</span><?php echo $report3 ?></button>
                                        <li><button type="submit" name="action" id="action" value="report4" title='Report 4'><span>B</span><?php echo $report4 ?></button>
                                        <li><button type="submit" name="action" id="action" value="report5" title='Report 5'><span>d</span><?php echo $report5 ?></button>
                                        <li><button type="submit" name="action" id="action" value="report6" title='Report 6'><span>d</span><?php echo $report6 ?></button>
                                        <li><button type="submit" name="action" id="action" value="report7" title='Report 7'><span>d</span><?php echo $report7 ?></button>
                                    </ul>
                                </div>
                                <div class="unit half">
                                    <ul>
                                        <li><button type="submit" name="action" id="action" value="report8" title='Report 8'><span>k</span><?php echo $report8 ?></button>
                                        <li><button type="submit" name="action" id="action" value="report9" title='Report 9'><span>d</span><?php echo $report9 ?></button>
                                        <li><button type="submit" name="action" id="action" value="report10" title='Report 10'><span>B</span><?php echo $report10 ?></button>
                                        <li><button type="submit" name="action" id="action" value="report11" title='Report 11'><span>B</span><?php echo $report11 ?></button>
                                        <li><button type="submit" name="action" id="action" value="report12" title='Report 12'><span>,</span><?php echo $report12 ?></button>
                                        <li><button type="submit" name="action" id="action" value="report13" title='Report 13'><span>u</span><?php echo $report13 ?></button>
                                        <li><button type="button" id="action" onClick="autoReport()" title='Report 14'><span>d</span><?php echo 'Report Exports' ?></button>
                                    </ul>
                                </div>
                            </div>
                    </form>
                    </div>


                
            <?php else : ?>
                <!-- <?php header('/reports/Index.php'); ?>        -->
            <?php endif ?>
            
        </main>
        <footer>
            <?php include $_SERVER['DOCUMENT_ROOT'] . '/module/footer.php'; ?>
        </footer>    
    </body>
</html>

