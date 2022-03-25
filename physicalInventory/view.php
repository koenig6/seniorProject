<!DOCTYPE html>
<html>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/module/head.php'; ?>
    <script type="text/javascript" src="/js/ajaxPhysicalInventory.js"></script>
    <body class="background">
        <?php include $_SERVER['DOCUMENT_ROOT'] . '/module/sideNav.php'; ?>
        <div class="viewport" id="viewport">
        <div id="overlayPanel"></div>
        <main>    
            
              <div id='physicalInventoryDetails'></div>

<!-- Search Inventory -->
        
                <?php
                    if($subAction == 'editIteS'){
                        echo '<div class="overlay">';
                        echo '<a href="/manager/index.php><span>x</span></a>';
                        include $_SERVER['DOCUMENT_ROOT'] . '/module/editItem.php';
                        echo '</div>';
                    }
                ?>
                

                <div id="busy"></div>
        <div id="viewport" class="viewport physicalInv">
                
                <div class="grid">
                    <div class="unit three-fifths">
                        <h1 id="inventoryHeader">Physical Inventory</h1>
                    </div>
                    <div class="unit one-fifth align-right">
                        <?php
                        if($invDate != 0){
                            if($subAction != 'esculate' && $subAction != 'recount'){
                                echo '<button onclick="complete()" title="Go To Complete Summary">Complete Inventory</button>';
                                // echo $_SESSION['cats'];
                                // echo $invDate[0][0];
                            }
                        }else{
                            echo '<button onclick="startInventory()" title="Go To Complete Summary">Start New Inventory</button>';
                            // echo $invDate[0][0];
                        }
                        
                        ?>
                    </div>
                    <div class="unit one-fifth align-left">
                            <?php
                                if($subAction != 'esculate' && $subAction != 'recount'){
                                    echo '
                                    <button href="#" id="physicalInvExportButton" onclick="exportInventory();">Export Inventory</button>
                                    <button href="#" id="reset Inventory" onclick="resetInventory();">Cancel Inventory</button>
                                    ';
                                }
                            ?>
                    </div>
                </div>
                <h3 class="shortError" id="message"></h3>
                    <div id="reCountScan"></div>
                    <div id="physicalInventoryHeader">
                        <?php
                            echo $phyInvHeaderBuild;
                           
                        ?>
                    </div>
                    
                    <div id="physicalInventory">
                        <?php
                            echo $phyInvBuild;
                        ?>
                    </div>
                  </div>  
            </div>
        </main>
        <footer>
            <?php include $_SERVER['DOCUMENT_ROOT'] . '/module/footer.php';?>
        </footer>    
    </body>
</html>