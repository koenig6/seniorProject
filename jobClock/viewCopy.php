<!DOCTYPE html>
<html>


<?php include $_SERVER['DOCUMENT_ROOT'] . '/module/head.php'; ?>
<?php include $_SERVER['DOCUMENT_ROOT'] . '/module/sideNav.php'; ?>

<script src="/js/scannerInputDetection.js"></script>
<script src="/js/checkTimeOut.js"></script>
<script type="text/javascript" src="/js/globalScan_workOrder.js"></script>
<script type="text/javascript" src="/js/ajaxJobClock.js"></script>
<script type="text/javascript" src="/js/jobClockSummaryCopy.js"></script>
<script type="text/javascript" src="/js/jobClockEditCopy.js"></script>
<script type="text/javascript" src="/js/jobClockEditCopy.js"></script>


 



<body class="advJobClock">
    <main>
        <div id="viewport" class="viewport">
            <div id="overlay"></div>

            <?php if ($action == 'workOrder') : ?>
                <div class="grid">
                    <div class="unit one-third align-left">
                        <h1 id="workOrderHeading">Job Clock: Work Orders Copy</h1>
                    </div>
                    <div class="unit one-third align-left">

                    </div>
                    <div class="unit one-third align-Right">
                        <a href="/jobClock/indexCopy.php?action=jobClockSummaryCopy" id="exit"><span>x</span></a>
                    </div>
                </div>

                <div class="grid">
                    <div class="workOrderDisplay" id="workOrderHeader">
                        <?php echo $htmlData[1] ?>
                    </div>

                    <div class="unit one-fifth align-left">
                        <div class="workOrderDisplay" id="productionQty">
                            <div id="productionQtyHeader"><?php echo $htmlData[5] ?></div>
                            <div id="productionQtyList">
                            <?php echo $htmlData[4] ?>
                            </div>

                        </div>

                    </div>
                    <div class="unit two-fifths align-left">
                        <div class="workOrderDisplay" id="activeLabor">
                            <div id="activeLaborHeader">
                                <h2>Active Labor</h2>
                                </div>
                            <div id="activeLaborList">
                                <?php echo $htmlData[2] ?>
                            </div>

                        </div>

                    </div>
                    <div class="unit two-fifths align-left">
                        <div class="workOrderDisplay" id="laborHistory">
                            <div id="laborHistoryHeader">
                                <h2>Labor History</h2>
                            </div>
                            <div id="laborHistoryList">
                            <?php echo $htmlData[3] ?>
                            </div>
                        </div>

                    </div>
                </div>
                <script>
                    window.onload = refreshWorkOrder();
                </script>
            <?php elseif ($action == 'jobClockSummaryCopy') : ?>
                <div class="grid">
                    <div class="division" id="employeeScan">
                        <div class="grid">
                            <div class="unit one-third">
                                <h1>Job Clock Summary Test</h1>
                                <input id="deptPunch" value="<?php echo $_SESSION['deptPunch']; ?> " hidden>
                            </div>
                            <div class="unit one-third">

                            </div>
                            <div class="unit one-third">
                                <a href="/production/index.php" id="exit"><span>x</span></a>
                            </div>
                        </div>
                    </div>

                    <div class="workOrderPanel" id="workOrderPanel">
                        <?php echo $htmlData[0] ?>
                    </div>
                </div>
                <script>
                    window.onload = refreshSummary();
                </script>
            <?php endif ?>
        </div>
    </main>

</body>

</html>