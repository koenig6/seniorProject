<!DOCTYPE html>
<html>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/module/head.php'; ?>
    <script type="text/javascript" src="/js/jquery-3.2.1.min.js"></script>
    <script type="text/javascript" src="/js/scannerInputDetection.js"></script>
    <script type="text/javascript" src="/js/globalScan.js"></script>
    <script type="text/javascript" src="/js/ajaxInventory.js"></script>
    <script type="text/javascript" src="/js/print.js"></script>
    <!-- <link type="text/css" media="print"  rel="stylesheet" href="/css/print_1.css" /> -->

    <link rel="stylesheet" href="../css/inventoryDetails.css">
    <link rel="stylesheet" href="../css/receiving.css">

    <body class="background">
        <header>
        
        </header>
        <main>
            <?php include $_SERVER['DOCUMENT_ROOT'] . '/module/sideNav.php'; ?>
            <div id="busy"></div>
            <div id="overlay"></div>
<!-- Search Inventory -->
            <div id="viewport" class="viewport">

            <?php if($action == 'searchItemsx') : ?>
            <div id="overlay"></div>
                <?php
                    // if($subAction == 'editIteS'){
                    //     echo '<div class="overlay">';
                    //     echo '<a href="/inventory/index.php?action=searchItems" id="exit"><span>x</span></a>';
                    //     include $_SERVER['DOCUMENT_ROOT'] . '/module/editItem.php';
                    //     echo '</div>';
                    // }
                ?>
                    <h1>Search Inventory</h1>
                    <?php if($message){echo'<li><h3>' . $message . '</h3></li>';}?>
                    <form method="post" action="." id="searchForm">
                        <div class="grid">
                        <div class="unit four-fifths ">
                        <label for="itemSearch">Search:</label>
                        <input type="text" name="itemSearch" placeholder="Enter search term" id="itemSearch" >
                        <button type="submit" name="action" id="action" value="searchItems" title='Submit Search'><span>s</span></button>
                        <a href="#" onclick="addItem()" title="Add Item"><span>+</span></a>
                        </div>
                        <div class="unit one-fifth">
                        </div>
                        </div>
                    </form>

                    
                    <?php
                    echo $searchMenuHTML;
                    ?>


                    <!-- <table id="searchTable">
                        <tr>
                              <td>SKU#</td>
                              <td>Company</td>
                              <td>Part#</td>
                              <td>Description 1</td>
                              <td>Description 2</td>
                              <td>Qty (QAV/BIN)</td>
                              <td>Options</td>
                          </tr>

                        
                            for($i = 0; $i < count($items); $i++){
                                
                            }

                            echo '<tr>';
                                    for($j = 0; $j < 7; $j++){
                                        if($j < 5){
                                            echo '<td>' . ($items[$i][$j] == 'NULL' ? '' : $items[$i][$j]) . '</td>';
                                        }elseif($j == 5){
                                            echo '<td>' . (float)($items[$i][5] - $items[$i][6]) . '/' . $items[$i][7] . '</td>';
                                        } elseif($j == 6){
                                            echo '<td>
                                             <a href="index.php?action=viewItem&skuNumber=' . $items[$i][0] . '" title="View Item"><span>N</span></a>';
                                            If($_SESSION['securityLevel'] >=3){
                                                echo '<a href="#" onclick="openEditMenu(' . $items[$i][0] . ')" title="Edit Item"><span>p</span></a></td>';
                                            }else{
                                                echo '</td>';
                                            }
                                             
                                        } 
                                    }
                                echo '</tr>';
                        
                    </table> -->

                    
<!-- View Item -->
            <?php elseif($action == 'viewItem') : ?>
                <div id="searchMenu">
                    <?php echo $viewItemHTML; ?>
                </div>
            <?php elseif($action == 'viewItem_old') : ?>
                <?php
                    if(isset($_SESSION['return'])){
                        if($_SESSION['return'] == 'returnToProduction'){
                            echo '<a href= "/production/index.php" id="exit"><span>x</span></a>';
                        }
                    }else{
                      //  echo '<a href= "/inventory/index.php?action=searchItems" id="exit"><span>x</span></a>';
                    }
                    
                    echo '<div id="overlay"></div>';
                    
                    if($subAction == 'editIteV'){
                        echo '<div class="overlay">';
                        echo '<a href="/inventory/index.php?action=viewItem&skuNumber=' . $itemData[0] . '" id="exit"><span>x</span></a>';
                        include $_SERVER['DOCUMENT_ROOT'] . '/module/editItem.php';
                        echo '</div>';
                    }elseif($subAction == 'newCustomer'){
                        echo '<div class="overlay">';
                        echo '<a href="/inventory/index.php?action=viewItem&skuNumber=' . $itemData[0] . '" id="exit"><span>x</span></a>';
                        include $_SERVER['DOCUMENT_ROOT'] . '/module/addCustomer.php';
                        echo '</div>';
                    }elseif($subAction == 'newVendor'){
                        echo '<div class="overlay">';
                        echo '<a href="/inventory/index.php?action=viewItem&skuNumber=' . $itemData[0] . '" id="exit"><span>x</span></a>';
                        include $_SERVER['DOCUMENT_ROOT'] . '/module/addVendor.php';
                        echo '</div>';
                    }elseif($subAction){
                        echo '<div class="overlay">';
                        echo '<a href="/inventory/index.php?action=viewItem&skuNumber=' . $itemData[0] . '" id="exit"><span>x</span></a>';
                        include $_SERVER['DOCUMENT_ROOT'] . '/module/viewImage.php';
                        echo '</div>';
                    }
                ?>




                <div class="gridPanel">
                <div class="whole"> 
                <div class="unit half">
                <h1><a href="/inventory/index.php" class="" title="Back"><span id="skuInfoBtnBlue"> < </span></a>Sku Information 
                    <?php 
                    if($_SESSION['securityLevel'] > 2){
                         echo '<a href="#" onclick="openEditMenu(' . $itemData[0] . ')" title="Edit Item"><span>p</span></a>';
                         } 
                    ?>
                </h1>
                 
                <?php if($message){echo'<li><h3>' . $message . '</h3></li>';}?>
                    
                 <form method="post" action="." id="displayForm">
                        <ul>
                         <div class="grid">
                             <div class="unit whole">
                                 <li class="skuInfoTop">
                                     <label for="skuNumber">SKU#:</label>
                                     <input type="text" name="skuNumber" id="skuNumber" value="<?php echo $itemData[0]?>" disabled></input>
                                 </li>
                                 <li class="skuInfoTop">
                                     <label for="partNumber">Part#:</label>
                                     <input type="text" name="partNumber" id="partNumber" value="<?php echo $itemData[1]?>" disabled></input>
                                 </li>
                                 <li class="skuInfoTop">
                                     <label for="description1">Description 1:</label>
                                     <input class="skuInfoDescInput" type="text" name="description1" id="description1" value="<?php echo $itemData[2]?>" disabled></input>
                                 </li>
                                 <li class="skuInfoTop">
                                     <label for="description2">Description 2:</label>
                                     <input class="skuInfoDescInput" type="text" name="description2" id="description2" value="<?php echo $itemData[3]?>" disabled></input>
                                 </li>
                                 <li class="skuInfoTop">
                                     <label for="description3">Description 3:</label>
                                     <input class="skuInfoDescInput" type="text" name="description3" id="description3" value="<?php echo $itemData[4]?>" disabled></input>
                                 </li>
                                 <li class="skuInfoTop">
                                     <label for="description4">Description 4:</label>
                                     <input class="skuInfoDescInput" type="text" name="description4" id="description4" value="<?php echo $itemData[5]?>" disabled></input>
                                 </li>
                             </div>
                         </div>

                         <div class="grid">

                             <div class="unit half">
                                 <li>
                                     <label class="skuInfoLabel" for="PUT">Pricing Unit:</label>
                                     <input class="skuInfoHalfInput" type="text" name="PUT" id="PUT" value="<?php echo $itemData[7]?>" disabled></input>
                                 </li>
                                 <li>
                                     <label class="skuInfoLabel" for="PFCTR">Pricing Factor:</label>
                                     <input class="skuInfoHalfInput" type="text" name="PFCTR" id="PFCTR" value="<?php echo (float)$itemData[9]?>" disabled></input>
                                 </li>
                                 <li>
                                     <label class="skuInfoLabel" for="timeStudy">Time Study:</label>
                                     <input class="skuInfoHalfInput" type="text" name="timeStudy" id="timeStudy" value="<?php echo $itemData[12]?>" disabled></input>
                                 </li>
                                 <li>
                                     <label class="skuInfoLabel" for="paperColor">Paper Color:</label>
                                     <input class="skuInfoHalfInput" type="text" name="paperColor" id="paperColor" value="<?php echo $itemData[13]?>" disabled></input>
                                 </li> 
                             </div>

                             <div class="unit half">
                                 <li><label class="skuInfoLabel" for="palletQty">Qty Per Pallet:</label>
                                     <input class="skuInfoHalfInput" type="text" name="palletQty" id="palletQty" value="<?php echo $itemData[14]?>" disabled></input>
                                 </li>
                                 <li>
                                     <label class="skuInfoLabel" for="customerRefNumber">CustomerRefNo:</label>
                                     <input class="skuInfoHalfInput" type="text" name="customerRefNumber" id="customerRefNumber" value="<?php echo $itemData[28]?>" disabled>
                                 </li>
                                 <li>
                                     <label class="skuInfoLabel" for="stockingUnit">Stocking Unit:</label>
                                     <input class="skuInfoHalfInput" type="text" name="stockingUnit" id="stockingUnit" maxlength="2" value="<?php echo $itemData[29]?>" disabled>
                                 </li>
                                 <li>
                                     <label class="skuInfoLabel" for="stockingRatio">Stocking Ratio:</label>
                                     <input class="skuInfoHalfInput" type="number" name="stockingRatio" id="stockingRatio" value="<?php echo $itemData[30]?>" disabled>
                                 </li>
                             </div>
                         </div>

                         <div class="grid">
                                 <div class="unit half">
                                     <li>
                                         <label class="" for="organic">Organic:</label>
                                         <input type="checkbox" name="organic" id="organic" <?php echo ($itemData[31] == 1 ? 'checked' : null)?> disabled>
                                     </li>
                                     <li>
                                         <label class="" for="kosher">Kosher:</label>
                                         <input type="checkbox" name="kosher" id="kosher" <?php echo ($itemData[32] == 1 ? 'checked' : null)?> disabled>
                                     </li>
                                     <li>
                                         <label class="" for="alergen">Alergen:</label>
                                         <input type="checkbox" name="alergen" id="alergen"  <?php echo ($itemData[33] == 1 ? 'checked' : null)?> disabled>
                                     </li>
                                     <li>
                                         <label for="billablePallet">Billable:</label>
                                         <div class="checkBox">
                                         <input type="checkbox" name="billablePallet" <?php echo ($itemData[19] == 1 ? 'checked' : null)?> id="billablePallet" disabled>
                                         </div>
                                     </li>
                                     <li>
                                         <label for="rackCharge">Charge:</label>
                                         <input class="skuInfoHalfInput" type="text" name="rackCharge" id="rackCharge" value="<?php echo $itemData[20]?>" disabled></input>
                                     </li>
                                 </div>
                                 <div class="unit half">
                                     <li>
                                         <label for="kit">Kit:</label>
                                         <div class="checkBox">
                                         <input type="checkbox" name="kit" <?php echo ($itemData[15] == 1 ? 'checked' : null)?> id="kit" disabled>
                                         </div>
                                     </li>
                                     <li>
                                         <label for="component">Component:</label>
                                         <div class="checkBox">
                                         <input type="checkbox" name="component" <?php echo ($itemData[16] == 1 ? 'checked' : null)?> id="component" disabled>
                                         </div>
                                     </li>
                                     <li>
                                         <label for="poRequired">PO Req:</label>
                                         <div class="checkBox">
                                             <input type="checkbox" name="poRequired" <?php echo ($itemData[17] == 1 ?
                                             'checked' : null)?> id="poRequired" disabled>
                                         </div>
                                     </li>
                                     <li>
                                         <label for="batchRequired">Batch Req:</label>
                                         <div class="checkBox">
                                             <input type="checkbox" name="batchRequired" <?php echo ($itemData[18] == 1 ?
                                             'checked' : null)?> id="batchRequired" disabled>
                                         </div>
                                     </li>
                                     <li>
                                         <label for="batchRequired">Plt# Req.:</label>
                                         <div class="checkBox">
                                             <input type="checkbox" name="batchRequired" <?php echo ($itemData[27] == 1 ?
                                             'checked' : null)?> id="palletNumberRequired" disabled>
                                         </div>
                                     </li>
                                 </div>
                             </div>
                     </ul>                
                </form>
                       <div class="unit half">
<div class="division"  id="associatedCustomer">

        <?php if($_SESSION['securityLevel'] > 1){ 
            if($componentTagType == 2 /*&& $itemData[15] == 0*/){
                echo '<h1>Associated Vendor
                        <a href="#" onclick="newVendor(' . $itemData[0] . ')"><span>+</span></a></h1>';
            }else{
                echo '<h1>Associated Customer
                        <a href="#" onclick="newCustomer(' . $itemData[0] . ')"><span id="skuInfoBtnBlue">+</span></a>';
            }
        }
        ?>
                      
        <div class="grid" id ="skuInfoCustomerListBox">
        <div class="unit three-fifths scrollListHeader">Customer</div>
        <div class="unit one-fifth scrollListHeader">City</div>
        <br>
        <hr>

            <?php if($componentTagType == 2 && $itemData[15] == 0){
                for($i = 0; $i < count($associatedVendor); $i++){
                          echo  '<div class="unit three-fifths">' . $associatedVendor[$i][2] . '</div>
                             <div class="unit one-fifth">' . $associatedVendor[$i][3] . '</div>';
                    if($_SESSION['securityLevel'] > 2){ 
                        echo '<div class="unit one-fifth">
                            <a href= "/inventory/index.php?action=deleteVendor&associatedVendorID=' 
                        . $associatedVendor[$i][0] 
                                . '&skuNumber=' . $itemData[0]
                                . '"><span>x</span></a>
                            </div>';

                    }else{
                        echo '<div class="unit one-fifth"></div>';
                    }
                }
            }else{
                for($i = 0; $i < count($associatedCustomer); $i++){
                           echo '<div class="unit three-fifths">' . $associatedCustomer[$i][2] . '</div>
                             <div class="unit one-fifth">' . $associatedCustomer[$i][3] . '</div>';
                    if($_SESSION['securityLevel'] > 2){ 
                           echo '<div class="unit one-fifth">
                             <a href= "/inventory/index.php?action=deleteCustomer&associatedCustomerID=' 
                        . $associatedCustomer[$i][0] 
                                . '&skuNumber=' . $itemData[0]
                                . '"><span id="skuInfoBtnRed">x</span></a>
                            </div>';

                    }else{
                        echo '<div class="unit one-fifth"></div>';
                    }
                }
            }?>

</div>
                    
<div class="unit half align-center">
    <div class="gridPanel">
        <div class="division"  id="billOfMaterials">
                <h1>Bill of Material
                    <a href="#" onclick="buildNewBomMenu(<?php echo $skuNumber?>)"><span id="skuInfoBtnBlue">+</span></a>
                </h1>
                    <div class="grid" id ="overflowScroll">

                        <div class="unit one-fifth">Sku#</div>
                        <div class="unit two-fifths">Description</div>
                        <div class="unit one-fifth">Qty Per</div>
                        <br>

                        <hr>
                        
                        <?php for($i = 0; $i < count($bomData); $i++){
                            echo '<div class="grid">
                                <div class="unit one-fifth">' . $bomData[$i][1] . '</div>
                                <div class="unit two-fifths">' . $bomData[$i][2] . '</div>
                                <div class="unit one-fifth">' . (Float)$bomData[$i][4] . '</div>
                                <div class="unit one-fifth">
                                <a href="#" onclick="openBomDetails()"><span id="skuInfoBtnBlue">N</span></a>
                                <a href="#" onclick="deleteBomItem('. $bomData[$i][0] . ', ' . $bomData[$i][1] . ')"><span id="skuInfoBtnRed">x</span></a>
                                </div></div>'; }?>
                        
                        <?php echo '<hr>
                                    <div class="selectionPanel" id="newBOM"></div>
                                    <div id="errorMessage"></div>
                                    </div>
                                    </div>
                                    <div class="division"  id="binLocations">
                                    <h1>Bin dagdahdaaha
                                    <a href="#" onclick="openInventoryDetails()"><span id="skuInfoBtnBlue">I</span></a>
                                    </h1>
                                    <div class="grid" id ="overflowScroll">
                                    <div class="unit one-fifth">S#</div>
                                    <div class="unit one-fifth">Bin</div>
                                    <div class="unit one-fifth">Qty</div>
                                    <div class="unit one-fifth">Received</div>
                                    <div class="unit one-fifth">Status</div>

                        <hr>'; ?>
                        <?php for($i = 0; $i < count($binData); $i++){
                        echo  '<div><div class="unit one-fifth">' . (Float)$binData[$i][0] . '</div>
                                <div class="unit one-fifth">' . (!isset($binData[$i][1])?"-":$binData[$i][1]) . '</div>
                                <div class="unit one-fifth">' . (!isset($binData[$i][2])?"-":$binData[$i][2]) . '</div>
                                <div class="unit one-fifth">' . date('m-d-Y', strtotime($binData[$i][3])) . '</div>
                                <div class="unit one-fifth">' . $binData[$i][4] . '</div></div>';
                    }
                    echo  
                            '<hr><div class="unit one-quarter">QAV: ' . $quantityAvailable . '</div>
                            <div class="unit half">Bin Qty: ' . $binTotals[1] . '</div>
                            <div class="unit one-quarter">Cnt: ' . $binTotals[0] . '</div>'; ?>
                </div>
            </div>
        </div>
    </div>
</div>
 
        
        <?php elseif($action == 'purchaseOrderMenu') : ?>
            
            <div class="POViewport" id="POViewPort">
                
                <h1>Receiving</h1>
                <!-- <a href="#" onclick="latestPOToast('20024');"><span>a</span></a> -->
                <div id="snackbar"></div>
                
                <div class="grid">

                    <div class="unit whole poReset">

                        <div class="unit one-fifth">
                            <div id="POOrderList">
                                <?php echo $tranArr[0] ?>
                            </div>
                        </div>
                        
                        <div class="unit one-fifth">
                            <div class="poPanel" id="poInfo">
                                <?php echo $tranArr[1] ?>
                            </div>
        </div>
                        
                            <div class="poPanel" id="poDetails">
                                <?php echo $tranArr[2] ?>
                            </div>
                        </div>

                        <div class="unit three-fifths">
                            <div class="poPanel" id="poItems">
                                <?php echo $tranArr[3] ?>
                            </div>
                        </div>
                        
                    </div>

                </div>
                
            </div>

            <?php else : ?> 
                <div id="searchMenu">
                    
                <?php 
                
                // if($_SESSION['invSearchTerm']) {
                //     searchInventory();
                // }else {
                    echo $searchMenuHTML; 
                // }
                
                ?>
                
            <?php endif ?>
            </div>
        </main>
        <footer>
            <?php include $_SERVER['DOCUMENT_ROOT'] . '/module/footer.php'; ?>
        </footer>    
    </body>
</html>



</div>
</div>
</div>
