 <!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
        <meta name="Description" content="Whitney Database Fulfillment/Assembly">

        <title>Whitney</title>
        <link type="text/css" media="screen"  rel="stylesheet" href="/css/screen.css" />
        <link type="text/css" media="screen"  rel="stylesheet" href="/css/screenBC.css" />
        <?php include $_SERVER['DOCUMENT_ROOT'] . '/module/head.php'; ?>
        <script type="text/javascript" src="/js/ajaxScan.js"></script> 
        <script type="text/javascript" src="/js/ajaxScan2.js"></script> 
        <script type="text/javascript" src="/js/jquery-3.2.1.min.js"></script>
        <script type="text/javascript" src="/js/scannerInputDetection.js"></script>
        <script type="text/javascript" src="/js/globalScan_forkLift.js"></script>
        <script type="text/javascript" src="/js/print.js"></script>
        
    </head>
    
    <body class="forklift">
       <div id="busy"></div>
       <div id="viewport" class="viewport">
        <div id="assignWorkOrderPanel"></div>
        <?php include $_SERVER['DOCUMENT_ROOT'] . '/module/sideNav.php'; ?>
        <header>
            
            
            <?php if($action == 'scan'){
                    echo '
                        <nav id="forkliftOptionHeader">
                            <ul>
                                <li><input type="text" name="scan" id="scan" autocomplete="off" autofocus onkeydown="Javascript: if (event.keyCode==13) validateBin('
                                        . $serialData['inventoryID'] . ');">
                                <li id="shipButton"><button onclick="ship(' . $serialData['inventoryID'] . ')"><span>g</span></button>';
                                if($status[3] == 1){
                                    echo '<li id="pickButton"><button onclick="returnPick(' . $serialData['inventoryID'] . ')"><span>n</span></button>';
                                }else{
                                    if($eUsage){
                                        echo '<li id="pickButton"><button onclick="pickAssignValidate(' . $serialData['inventoryID'] . ')"><span>W</span></button>';
                                    }else{
                                        echo '<li id="pickButton"><button onclick="pick(' . $serialData['inventoryID'] . ')"><span>W</span></button>';
                                    }
                                }
                        echo    '<li id="shipButton"><button onclick="reDirect()"><span>O</span></button>
                            </ul>
                        </nav>';
                }else if($action == 'sku'){

                    
                    
                }else if($action == 'shippingMenu'){
                    echo '<div id="overlayPanel"></div>
                          <div class="skuData">
                            <div class="grid">
                              <div class="unit one-fifth">
                                <section id="ordersList">';
                                    echo $ordersList;
                                 echo '</section>
                              </div>
                              <div class="unit two-fifths">
                                <section id="orderInfo">
                                    <div id="orderCommandCenter" class="unit whole">

                                    </div>
                                    <div id="orderDetailsBox" class="unit whole">
                                        <h2>Order Header</h2>
                                    </div>
                                    <div id="orderItemsBox" class="unit whole">
                                        <h2>Order Items</h2>
                                    </div>
                                 </section>
                              </div>
                              
                              <div class="unit two-fifths">
                                 <section id="shippedInfo">
                                 <div id="shippedInfoBox">
                                    <h2 style="margin-left: 0.8em; margin-top: 0.5em;">Avalible Pallets</h2>
                                 </div>
                                 ';
                                echo '</section>
                              </div>
                              <div class="unit two-fifths">
                                <section id="shippedPallets">
                                    <h2 style="margin-left: 0.8em; margin-top: 0.5em;" class="align-center">Picked Pallets</h2>
                                </section>
                              </div>
                            </div>
                          </div>';
                }else{
                    echo '<nav>
                            <form class="align-center" method="post" action="." id="mainMenu">
                                <ul>
                                    <li><button type="submit" name="action" id="shipping" value="shippingMenu"><div><h2>Orders</h2>' . $orderCount[0] . '</div></button>
                                    <li><button type="submit" name="action" id="newTag" value="newTag"><div><h2>New</h2>' . $newCount[1] . '</div></button>
                                    <li><button type="submit" name="action" id="pickedTag" value="pickedTag"><div><h2>Picked</h2>' . $pickedCount[1] . '</div></button>';
                            if($action == 'typeValues'){
                                echo '<li><button type="submit" name="action" id="action" value="forklift"><h2>Return</h2><span>B</span></button>';
                            }
                           echo '</ul>
                            </form>
                        </nav>';
                }
            ?>
            
        </header>
            <div id='opacity'></div>
<!-- Default Action -->
        <?php if($action == 'forklift') : ?>
            
        
            <div class="barcode">
                <div id="scanBox">
                    <div id="errorMessage"></div>
                    <input type="text" name="scan" id="scan" autocomplete="off" autofocus onkeydown="Javascript: if (event.keyCode==13) scan();">
                </div>
                
                <div id="ha_holder">
                    <div id="ha_1"></div>
                    <div id="ha_2"></div>
                    <div id="ha_3"></div>
                    <div id="ha_4"></div>
                    <div id="ha_5"></div>
                    <div id="ha_6"></div>
                    <div id="ha_7"></div>
                    <div id="ha_8"></div>
                    <div id="ha_9"></div>
                    <div id="ha_10"></div>
                    <div id="ha_11"></div>
                    <div id="ha_12"></div>
                    <div id="ha_13"></div>
                    <div id="ha_14"></div>
                    <div id="ha_15"></div>
                    <div id="ha_16"></div>
                    <div id="ha_17"></div>
                    <div id="ha_18"></div>
                    <div id="ha_19"></div>
                    <div id="ha_20"></div>
                    <div id="ha_21"></div>
                    <div id="ha_22"></div>
                    <div id="ha_23"></div>
                    <div id="ha_24"></div>
                    <div id="ha_25"></div>
                    <div id="ha_26"></div>
                    <div id="ha_27"></div>
                    <div id="ha_28"></div>
                    <div id="ha_29"></div>
                    <div id="ha_30"></div>
                </div>
            </div>


            
<!-- Serial Scan View -->
            <?php elseif($action == 'scan') : ?>
                <?php echo $generalScanView ?>


<!-- SKU scan view -->
            <?php elseif($action == 'sku') : ?>
<div id="viewport" class="viewport">
                <div class="skuData">
                    <?php echo $skuDataHeader ?>
                    <div class="grid">
                        <div class="unit half">
                            <section id="table1">
                                <?php echo $table1 ?>
                            </section>
                        </div>
                        <div class="unit half">
                            <section id="table2">
                                <?php echo $table2 ?>
                            </section>
                        </div>
                    </div>
                </div>
</div>

            <?php elseif($action == 'typeValues') : ?>
                <main>
                <?php echo $itemTable; ?>
            <?php else : ?>
                <!--Cannot Modify Header Error-->
<!--                <?php header('/logIn/Index.php'); ?>       -->
            <?php endif ?>
          </div>
        </main>
        <footer>
            <?php include $_SERVER['DOCUMENT_ROOT'] . '/module/footer.php'; ?>
        </footer>    
    </body>
</html>