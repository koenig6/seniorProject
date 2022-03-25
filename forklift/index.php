<?php
session_start();

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$ajax = false;
$action = 'forklift';
$employeeID = null;
$curDate = date('Y-m-d');
$heading = null;
$table1 = null;
$table2 = null;
$timeStamp = date('Y-m-d H:i:s');


require 'model.php';

//Get Action
if (isset($_POST['action'])) {
    $action = $_POST['action'];
} elseif (isset($_GET['action'])) {
    $action = $_GET['action'];
}

//Selected Date
if (isset($_POST['selectedDate'])) {
    $selectedDate = filterString($_POST['selectedDate']);
} elseif (isset($_GET['selectedDate'])) {
    $selectedDate = filterString($_GET['selectedDate']);
} else {
    $selectedDate = $curDate;
}

//Get employee ID
if (isset($_POST['employeeID'])) {
    $employeeID = filterNumber($_POST['employeeID']);
} elseif (isset($_GET['employeeID'])) {
    $employeeID = filterNumber($_GET['employeeID']);
}
if (isset($_POST['employeeID2'])) {
    $employeeID = filterNumber($_POST['employeeID2']);
}

//Get serial ID
if (isset($_POST['serialID'])) {
    $serialID = filterNumber($_POST['serialID']);
} elseif (isset($_GET['serialID'])) {
    $serialID = filterNumber($_GET['serialID']);
}

//Get shipmentGroup
if (isset($_GET['shipmentGroupID'])) {
    $shipmentGroupID = $_GET['shipmentGroupID'];
    $currentShipmentGroup = getShipmentGroup($shipmentGroupID);
} else {
    $currentShipmentGroup = getShipmentGroup(1);
}

if ($_SESSION['securityLevel'] < 2) {
    header('location: /logIn/index.php');
    $_SESSION['message'] = 'Access Denied';
} elseif (timedOut()) {
    header('location: /logIn/index.php');
} elseif ($_SESSION['loggedIn'] != true) {
    header('location: /logIn/index.php');
} elseif ($action == 'logOut') {
    logOut();
    header('location: /logIn/index.php');
} elseif ($action == 'scanValidation') {
    $scan = filterString($_GET['scan']);
    if (substr($scan, 0, 1) == '*' || substr($scan, 0, 1) == 'S') {
        if (substr($scan, 0, 1) == '*') {
            $skuValid = skuValidation(ltrim($scan, "*"));
        } elseif (substr($scan, 0, 1) == 'S') {
            $skuValid = skuValidation(ltrim($scan, "S"));
        }

        //$skuValid = skuValidation(ltrim($scan, "*"));
        if ($skuValid[0]) {
            echo 1;
        } else {
            echo 0;
        }
    } else {
        $serialValid = serialValidation($scan);
        if ($serialValid[0]) {
            echo 1;
        } else {
            echo 0;
        }
    }

    $ajax = true;
} elseif ($action == 'validateBin') {
    $scan = filterString($_GET['binLocation']);
    $startBin = $_SESSION['firstBin'];
    $endBin = $_SESSION['lastBin'];

    if (substr($scan, 0, 5) == 'LSSLP') {
        $scan = 'LSSLP+' . substr($scan, 6, 100);
    }

    if (is_numeric($scan)) {
        // Number
    } else {
        // String
        $scan = translateAltLocID($scan);
    }

    // echo $scan;

    //Check to see if serial # is valid
    $validateSerial = getInventory($scan);

    if (!$validateSerial || $validateSerial == null) {
        $validateAltLocID = translateAltLocID($scan);  // Double Check to see its not altLocationID
        if ($validateAltLocID != false || $validateAltLocID != null) {
            $scan = $validateAltLocID;
        }
    }

    if (isset($startBin) && isset($endBin)) {
        if (($scan >= $startBin && $scan <= $endBin) or ($scan >= 14989 && $scan <= 15183)) {
            $binName = getBinName($scan);
            if (isset($binName)) {
                echo 1;
            } else {
                echo 0;
            }
        } else {
            echo 0;
        }
    } else {
        echo 0;
    }

    $ajax = true;
} elseif ($action == 'forklift') {
    //Count of New & Picked
    $newCount = countNew();
    $pickedCount = countPicked();

    $orderCount = countTransferOrders();
} elseif ($action == 'reloadOrders') {

    if (isset($_GET['shipmentGroupID'])) {
        $shipmentGroupID = $_GET['shipmentGroupID'];
        $currentShipmentGroup = getShipmentGroup($shipmentGroupID);
    } else {
        $currentShipmentGroup = getShipmentGroup(1);
    }

    //    echo $currentShipmentGroup[0][1];

    $customerID = $currentShipmentGroup[0][1];

    $_SESSION['customCustomerID'] = $currentShipmentGroup[0][1];
    $_SESSION['currentShipmentGroup'] = $currentShipmentGroup;
    $displayPO = true;


    //Build The Div Again?

    $pickPackOrders = getPickPackOrders($currentShipmentGroup[0][1], 2, $currentShipmentGroup[0][0]);
    $ordersList = buildOrdersList($pickPackOrders);

    echo $ordersList;
    $ajax = true;
} elseif ($action == 'scan') {
    //Get Status & Bin
    $status = getStatus($serialID);
    $binLocation = getBinLocation($serialID);
    $eUsage = $_SESSION['eUsage'];

    //Get general Serial Data
    $serialData = getSerialData($serialID);

    //Inventory Summary Data for the summary view
    $currentLocationSummary = currentInventory($serialData['skuNumber']);

    //Get inventory Totals
    $currentTotals = currentInventoryTotals($serialData['skuNumber']);
    //Build Summary View
    $currentLocationsView = buildCurrentLocations($currentLocationSummary, $currentTotals);

    //Build Scan Screen View with included History
    $generalScanView = buildScanView($serialID, $serialData, $currentLocationsView, $status, $binLocation);
} elseif ($action == 'filterCurrent') {

    $skuNumber = filterString($_GET['skuNumber']);
    $component = filterString($_GET['component']);
    $filterBy = filterString($_GET['filterBy']);
    $filter = filterString($_GET['filter']);

    if ($filterBy == 0) {
        $inventoryData = currentInventoryData($skuNumber, $component, $filter, 'i.batchNumber');
        // $html = buildCurrentData($inventoryData);
        $html = buildCurrentTable($inventoryData, $skuNumber, $component);
    }

    echo $html;

    $ajax = true;
} elseif ($action == 'completeOrder') {

    $pickpackitems = getPickPackItems($_SESSION['orderNumber'], $_SESSION['customerID']);

    for ($i = 0; $i < sizeof($pickpackitems); $i++) {  // Loop through all pickpackitems and do the following

        if ($pickpackitems[$i][18] == null) {
            $skuNumber = getPONo($pickpackitems[$i][3]);
        } else {
            $skuNumber = $pickpackitems[$i][18];
        }

        $shipping = getShippingData($pickpackitems[$i][0], $pickpackitems[$i][1]);

        $shipped = countShippedQty($shipping[0], $skuNumber);


        updatePickPackItem($pickpackitems[$i][0], $shipped[0]);  // update each pickpackitem (backorder to 0, shipped to shipped + qtyshipped) Where pickpackitemsID = ...
        updateShipping($pickpackitems[$i][1], $pickpackitems[$i][0], $shipping[0], $_SESSION['userID'], $shipped[0]);  // update each shipping where customerSO and pickitemsID  = pickitemsID and shippingID = shippingID qtyshipped = qtyshipped, complete = true
        updateInventoryShipped($shipping[0], 0);  // UPDATE inventory set active = 0 WHERE shippingID = :shippingID

        $pickedpallets = availableInventory($skuNumber, $pickpackitems[$i][0], true, $shipping[0]);  // GEt inventory units SELECT inventoryID from inventory where shippingID = :shippingID

        for ($j = 0; $j < sizeof($pickedpallets); $j++) {  // Loop through inventory units 
            insertNewStatus($pickedpallets[$j][0], 3, $_SESSION['userID']);  //CALL insert to inventoryStatus to 3
        }

        // Complete entire order
        setStatus($_SESSION['orderNumber'], 3);  // Call setStatus  set = 3

    }
} elseif ($action == 'backOrder') {

    $customerSONumber = filterString($_GET['customerSONumber']);
} elseif ($action == 'completedOrders') {

    if (isset($_GET['customerID'])) {
        $shipmentGroupID = filterString($_GET['customerID']);
        $currentShipmentGroup = getShipmentGroup($shipmentGroupID);
    } else {
        $currentShipmentGroup = getShipmentGroup(1);
    }

    $pickPackOrders = getPickPackOrders($currentShipmentGroup[0][1], 3, $shipmentGroupID);

    if ($pickPackOrders == null || $pickPackOrders == '' || $pickPackOrders == false) {
        $orders = '<p>No Completed Orders.</p>';
    } else {
        $orders = buildOrders($pickPackOrders);
    }

    echo $orders;
    $ajax = true;
} elseif ($action == 'expandSummary') {
    $binLocationID = filterNumber($_GET['binLocationID']);

    $serialData = getSerialData($serialID);
    $currentPallets = buildExpandedView($serialData, $binLocationID);

    echo $currentPallets;

    $ajax = true;
} elseif ($action == 'pickAssignValidate') {
    $serialID = filterNumber($_GET['serialID']);

    //Check to see if picked is assigned
    $usageAssignment = getInCompPickAssignment($serialID);

    //If not assigned build assign menu
    if ($usageAssignment) {
        $menu = buildErrorMenu($usageAssignment[2]);
    } else {
        $menu = buildAssignWorkOrder($serialID);
    }

    echo $menu;

    $ajax = true;
} elseif ($action == 'printShipping') {

    $skuNumber = filterString($_GET['skuNumber']);
    $comp = filterString($_GET['comp']);

    $html = buildShipping($skuNumber, $comp);

    echo $html;

    $ajax = true;
} elseif ($action == 'pickAssign') {

    $workOrderNumber = filterString($_GET['workOrderNumber']);
    $serialID = filterNumber($_GET['serialID']);
    $userID = $_SESSION['userID'];
    $insertAssignment = null;

    //Validate work order number
    $workOrderData = getWorkOrder($workOrderNumber);

    if ($workOrderData) {
        if ($workOrderData[0][10] == 0) {
            $insertAssignment = addEUsage($workOrderNumber, $serialID, $userID);
        }
    }

    if ($insertAssignment) {
        echo 1;
    } else {
        echo 0;
    }

    $ajax = true;
} elseif ($action == 'pickSerial' || $action == 'returnSerial' || $action == 'shipSerial' || $action == 'scanBin') {
    $userID = $_SESSION['userID'];
    // $check = filterNumber($_GET['status']);
    $binStatus = isset($_GET['status']) ? filterNumber($_GET['status']) : null;


    if ($binStatus == 8 || $binStatus == 9 || $binStatus == 10) {
    } else {
        if ($action == 'scanBin') {
            $binLocation = filterString($_GET['binLocation']);

            if (substr($binLocation, 0, 5) == 'LSSLP') {
                $binLocation = 'LSSLP+' . substr($binLocation, 6, 100);
                // echo $binLocation;
            }

            if (is_numeric($binLocation)) {
                // Number
            } else {
                // String
                $binLocation = translateAltLocID($binLocation);
            }

            $binName = getBinName($binLocation);
            $newBin = insertNewBin($serialID, $binLocation, $timeStamp, $userID);
            $status = 4;
        } else {
            $status = filterNumber($_GET['status']);
        }

        //Add new Status
        $statusChange = insertNewStatus($serialID, $status, $userID);
        if ($action == 'shipSerial') {
            $deactivate = deactivate($serialID, $userID);
        }
        if ($action == 'pickSerial' || $action == 'returnSerial' || $action == 'scanBin') {
            $activate = activate($serialID, $userID);
        }
        //Add new Binlocation
        if ($action == 'scanBin' && isset($statusChange)) {
            echo $binName[0];
        } else if (isset($statusChange)) {
            echo 1;
        } else {
            echo 0;
        }
    }

    $ajax = true;
} elseif ($action == 'sku') {
    $sku = $serialID;
    //Get SKU data
    $skuData = getSkuData($sku);

    $skuDataHeader = buildSkuData($skuData);

    $component = $skuData['component'] == true ? 1 : 0;

    //Get SKU type
    if ($skuData['component'] == true) {
        $currentInventoryData = currentInventoryData($sku, 1);
        $inventoryData = pickedInventoryData($sku);
        $table2 = buildPickedTable($inventoryData);
    } else {
        $currentInventoryData = currentInventoryData($sku, 0);
        $inventoryData = shippedInventoryData($sku);
        $table2 = buildShippedTable($inventoryData);
    }

    $table1 = buildCurrentTable($currentInventoryData, $sku, $component);

    $_SESSION['shippingCurrentTable'] = $table1;
} elseif ($action == 'pickedPallets') {
    $sku = $serialID;
    $inventoryData = pickedInventoryData($sku);

    $table = buildPickedTable($inventoryData);


    echo $table;
    $ajax = true;
} elseif ($action == 'shippedPallets') {

    $sku = $serialID;

    $inventoryData = shippedInventoryData($sku);
    $table = buildShippedTable($inventoryData);

    echo $table;
    $ajax = true;
} elseif ($action == 'getPickedPalletSerial') {

    $skuNumber = filterString($_GET['skuNumber']);
    $shippingID = filterNumber($_GET['shippingID']);

    $tranArr = getPickedPallets($skuNumber, $shippingID);

    echo $tranArr;

    $ajax = true;
} elseif ($action == 'openMasterTag') {

    $customerSONumber = filterString($_GET['customerSONumber']);

    $masterTagMenu = buildMasterTagCC($customerSONumber);

    echo $masterTagMenu;

    $ajax = true;
} elseif ($action == 'goToOrderPart') {

    $orderNumber = filterString($_GET['customerSONumber']);
    $skuNumber = filterString($_GET['skuNumber']);

    $tranArr = buildForkLiftView($orderNumber, $_SESSION['currentShipmentGroup'][0][1], $skuNumber, $serialNumber);

    $ajax = true;
    $data = json_encode($tranArr);

    echo $tranArr[8];
} elseif ($action == 'goToMasterTag') {
} elseif ($action == 'masterTag') {

    $masterTagID = filterNumber($_GET['masterTagID']);
    $skuNumber = filterString($_GET['skuNumber']);
    $shippingID = filterNumber($_GET['shippingID']);
    $customerSONumber = filterString($_GET['customerSONumber']);
    $masterList = filterNumber($_GET['masterList']);
    $overlay = filterString($_GET['overlay']);

    if ($masterList) {

        $masterPallets = getMasterPalletList($customerSONumber);
        // if($overlay) {
        // }else {
        //     $masterPallets = getSkuMasterPalletList($customerSONumber, $skuNumber);
        // }

        $masterTagList = buildMasterTagList($masterPallets, null, null, $customerSONumber, $skuNumber, $shippingID);

        echo $masterTagList;
    } else {

        $openMasterTag = buildMasterTagPallets($masterTagID, $skuNumber, $customerSONumber, $overlay, $shippingID);

        echo $openMasterTag;
    }

    $ajax = true;
} elseif ($action == 'newMasterTag') {

    // $newMasterTag = buildNewMasterTag($newMasterTagID);

    // echo $newMasterTag;

} elseif ($action == 'manualPalletEntry') {

    $orderNumber = $_SESSION['orderNumber'];
    $userID = $_SESSION['userID'];
    $partNum = filterString($_GET['partNum']);
    $serialNumber = filterString($_GET['scanInput']);
    $skuNumber = filterString($_GET['skuNumber']);
    $pickPackItemsID = filterString($_GET['pickPackItemsID']);
    $customerSONumber = filterString($_GET['customerSONumber']);
    $shippingID = filterString($_GET['shippingID']);
    $newQty = filterString($_GET['qty']);
    $customerID = filterString($_GET['customerID']);
    $invQty = getInventoryUnitQty($serialNumber)[0];
    $remainingQty = $invQty - $newQty;
    $masterTagID = filterString($_GET["masterTagID"]);
    $pickPackData = getPickPackItemPallets($orderNumber, $_SESSION['currentShipmentGroup'][0][1], $pickPackItemsID);
    $oldInventoryID = $serialNumber;
    $newInventoryID = advanceSku()[0];
    $success = duplicateInvUnit($newInventoryID, $userID, $oldInventoryID);  // Get the old pallet's info to add to the new one

    if ($success != null || !$success) {

        insertInventoryQty($oldInventoryID, $remainingQty, $_SESSION['userID']);  // Sets the old pallet to the remaining qty

        updateSku($newInventoryID);

        $shipping = getShippingData($pickPackItemsID, $customerSONumber);  // Query ShippentID with CustomerSONUmber and pickpackitemsID

        if (!$shipping) {
            $check = addShippingID($customerSONumber, $pickPackData[0][0], $userID);  //***If No shipmentID then create new shipment utilizing CustomerSONumber and pickpackitemsID ...
            $shipping = getShippingData($pickPackItemsID, $customerSONumber);
        }

        if ($shipping != null || !$shipping) {

            $shipping = getShippingData($pickPackData[0][0], $customerSONumber);

            $check = updateShippingID($shipping[0], $newInventoryID);  // UPDATE inventory to add shippingID

            if ($masterTagID != false) {
                $j = insertMasterTag($masterTagID, $newInventoryID);
            } // Add Master Tag ID

            $r = insertNewStatus($newInventoryID, 1, $userID);  // Insert status of picked

            if ($r) {
                $r2 = insertNewQty($newInventoryID, $newQty, null, $userID);  // Sets the old pallet to the remaining qty
            }

            $tranArr[5] = $newInventoryID;

            $error = false;
        }
    } else {
        $error = true;
    }

    // Rebuilds all the list sections

    $availablePallets = buildShippingInfo($partNum, $pickPackItemsID, $customerSONumber, $shippingID, false, $skuNumber, $customerID);  //Rebuild Available Pallets. The picked should be removed. The total shipped refreshed.
    $pickedPallets = buildShippingInfo($partNum, $pickPackItemsID, $customerSONumber, $shippingID, true, $skuNumber, $customerID);  //Rebuild Shipping Pallets menu. This will display picked items.

    $pickPackItem = getPickPackItems($orderNumber, $_SESSION['currentShipmentGroup'][0][1]);
    $assignedLots = getAssignedLots($orderNumber);
    $orderItems = buildForkLiftItems($pickPackItem, $orderNumber, $assignedLots);  // Rebuild Order items to show total shipped refreshed.

    $tranArr[0] = $error;
    $tranArr[1] = $availablePallets;
    $tranArr[2] = $pickedPallets;
    $tranArr[3] = $orderItems;
    $tranArr[4] = $serialNumber;
    $tranArr[5] = $check;

    $data = json_encode($tranArr);
    echo $data;

    $ajax = true;
} elseif ($action == 'pickWholePallet') {

    $orderNumber = $_SESSION['orderNumber'];
    $partNum = filterString($_GET['partNum']);
    $serialNumber = filterString($_GET['scanInput']);
    $pickPackItemsID = filterString($_GET['pickPackItemsID']);
    $customerSONumber = filterString($_GET['customerSONumber']);
    $shippingID = filterString($_GET['shippingID']);
    $skuNumber = filterString($_GET['skuNumber']);
    $userID = $_SESSION['userID'];
    $masterTagID = filterString($_GET["masterTagID"]);

    // Pick the scanned item

    updateShippingID($shippingID, $serialNumber);  // UPDATE inventory to add shippingID 

    insertNewStatus($serialNumber, 1, $curDate, $userID);  // Insert status of picked

    if ($masterTagID != false) {
        $j = insertMasterTag($masterTagID, $serialNumber);
    } // Add Master Tag ID

    // Rebuilds all the list sections

    $availablePallets = buildShippingInfo($partNum, $pickPackItemsID, $customerSONumber, $shippingID, false, $skuNumber);  //Rebuild Available Pallets. The picked should be removed. The total shipped refreshed.
    $pickedPallets = buildShippingInfo($partNum, $pickPackItemsID, $customerSONumber, $shippingID, true, $skuNumber);  //Rebuild Shipping Pallets menu. This will display picked items.

    $pickPackItem = getPickPackItems($orderNumber, $_SESSION['currentShipmentGroup'][0][1]);
    $assignedLots = getAssignedLots($orderNumber);
    $orderItems = buildForkLiftItems($pickPackItem, $orderNumber, $assignedLots);  // Rebuild Order items to show total shipped refreshed.

    $tranArr[1] = $availablePallets;
    $tranArr[2] = $pickedPallets;
    $tranArr[3] = $orderItems;
    $tranArr[4] = $serialNumber;

    $data = json_encode($tranArr);
    echo $data;

    $ajax = true;
} elseif ($action == 'getSerialInventoryQty') {
} elseif ($action == 'scanPallet') {

    // From Javascript 
    $serialNumber = filterNumber($_GET['serialNumber']);
    $shippingID = filterNumber($_GET['shippingID']);
    $masterTagID = filterString($_GET["masterTagID"]);
    $customerSONumber = filterString($_GET["customerSONumber"]);
    $manualQty = filterNumber($_GET["manualQty"]);
    $pickWhole = filterNumber($_GET['pickWhole']);
    $headerStatus = filterNumber($_GET['headerStatus']);
    $goTo = filterNumber($_GET['goTo']);

    // From SESSION
    $userID = $_SESSION['userID'];
    $curDate = date('d-m-yy');

    // Defined Here
    $tranArr = null;

    // Validate Serial
    $validated = validateSerial($serialNumber);

    if (!$validated) {
        echo 'noserial'; // Error : No Serial Found
    } else {

        // From Serial
        $serialData = getInventoryData($serialNumber, $customerSONumber);
        $active = $serialData['active'];
        $skuNumber = $serialData['skuNumber'];
        $partNumber = $serialData['partNumber'];
        $serialShippingID = $serialData['serialShippingID'];
        // $shippingID = $serialData['shippingID'];
        $splitFromID = $serialData['splitfromID'];
        $orderedQty = (int)$serialData['ordered'];
        $pickPackItemsID = $serialData['pickPackItemsID'];
        $inventoryStatus = $serialData['phyInventoryUnitStatusID'];

        $tranArr[18] = $shippingID;

        // die(json_encode($serialData));

        if ($headerStatus != 2) {
            echo 'orderComplete'; // Order already completed
        } else if (!$active) {
            echo 'inactive'; // Order is not active or available
        } else {

            if ($inventoryStatus == 2) {
                $code = 'deactivated'; // Pallet Deactivated
            } else if ($inventoryStatus == 9) {
                $code = 'notreceived'; // Pallet Not Received
            } else if ($goTo) {
                // Do Nothing
                $code = 'donothing';
            } else {

                // Define Quantities
                $picked = (int)countShippedQty($shippingID, $skuNumber)[0]; // Sum of shipped pallet qty
                $invQty = (int)checkInv($serialNumber); // Amount in Inventory
                $pickedQty = $picked + $invQty; // Total Picked Inventory For Order

                $code = $serialShippingID ? true : false;


                // Serial Already Picked
                if ($serialShippingID) {

                    if ($serialShippingID != $shippingID) die('inactive');

                    // Serial Was Split
                    if ($splitFromID) {

                        // Merge Serial
                        $mergeQty = (int)getQuantity($splitFromID)[0]; // Get Split From Quantity
                        $newQty = $invQty + $mergeQty; // Add quantities together

                        insertInventoryQty($splitFromID, $newQty, $_SESSION['userID']);  // Sets the old pallet to the remaining qty
                        deactivate($serialNumber, $userID);  // Deactivate
                        insertNewStatus($serialNumber, 2, $userID);  // Status = deactivated
                        mergeInvUnit($serialNumber, $splitFromID);  // CombineToID = splitfromID
                        updateShippingID(null, $serialNumber);  // UPDATE inventory to remove shippingID
                        removeMasterTag($serialNumber);  // Remove Master Tag 

                        $code = 'merge'; // Serial Merged

                    } else {

                        updateShippingID(null, $serialNumber);  // UPDATE inventory to remove shippingID
                        insertNewStatus($serialNumber, 4, $userID);  // Set Status to not picked
                        removeMasterTag($serialNumber);  // Remove Master Tag 

                        $code = 'remove'; // Serial Un-picked

                    }
                } else if ($invQty == 0) {
                    die('noinv');
                } else if ($invQty < $manualQty) {
                    die('notEnoughInv');
                } else if ($orderedQty < $picked && !$pickWhole && !$manualQty) {
                    $tranArr[0] = $partNumber;
                    $tranArr[1] = 'reqQtyMet'; // Picked Quantity Is Greater than order required Quantity
                    $data = json_encode($tranArr);
                    die($data);

                    // Serial Quantity Is Greater Than the Order Requires ( Split Pallet )
                } else if ($pickedQty > $orderedQty && !$pickWhole) {

                    // Update picked pallet's qty to have the difference between the ordered qty and the picked qty
                    $newQty = $manualQty == 0 ? ($orderedQty - $picked) : $manualQty;
                    $remainingQty = (int)$invQty - (int)$newQty;

                    $tranArr[15] = $manualQty;
                    $tranArr[16] = $orderedQty;
                    $tranArr[17] = $picked;

                    // End if quantity = 0
                    if ($newQty == 0) {
                        $code = 'qtyMet'; // Ordered Qty is already met
                    } else {
                        // Create a new pallet sku by advancing
                        $oldInventoryID = $serialNumber;

                        // Split the Serial ( Pallet )
                        $newInventoryID = duplicateInvUnit($userID, $oldInventoryID, $shippingID); // Get the old pallet's info to add to the new one
                        !$newInventoryID && die('newInvFailed');

                        updateShippingID($shippingID, $newInventoryID); // UPDATE inventory to add shippingID
                        insertNewStatus($newInventoryID, 1, $userID); // Insert status of picked 
                        insertNewQty($newInventoryID, $newQty, null, $userID); // Sets the old pallet to the remaining qty
                        insertInventoryQty($oldInventoryID, $remainingQty, $_SESSION['userID']); // Sets the old pallet to the remaining qty

                        ($masterTagID != false) && insertMasterTag($masterTagID, $newInventoryID); // Add Master Tag ID

                        $code = 'surplus'; // Serial is Surplus
                    }

                    // Simple Pallet Pick
                } else {
                    // Serial status is not picked
                    if ($inventoryStatus != 1) {


                        $addedShippingID = updateShippingID($shippingID, $serialNumber); // UPDATE inventory to add shippingID 
                        $addedNewStatus = insertNewStatus($serialNumber, 1, $curDate, $userID); // Insert status of picked
                        $tranArr[7] = $shippingID;
                        $tranArr[8] = $addedShippingID;
                        $tranArr[9] = $addedNewStatus;

                        ($masterTagID != false) && insertMasterTag($masterTagID, $serialNumber); // Add Master Tag ID

                        $code = 'picked'; // Serial Picked
                    } else {
                        die('pickingIssue');
                    }
                }
            }

            $complete = getOrderStatus($customerSONumber) == 3 ? 1 : 0;
            // Rebuilds all the list sections
            // [$availablePallets, $pickedPallets] = buildPalletView($partNumber, $customerSONumber, $shippingID, $skuNumber);
            $html = buildPalletView($partNumber, $customerSONumber, $shippingID, $skuNumber, $complete);
            $availablePallets = $html[0];
            $pickedPallets = $html[1];

            $pickPackItem = getPickPackItems($customerSONumber, $_SESSION['currentShipmentGroup'][0][1]);
            $assignedLots = getAssignedLots($customerSONumber);  // Get Assigned Lots
            $orderItems = buildForkLiftItems($pickPackItem, $customerSONumber, $assignedLots);  // Rebuild Order items to show total shipped refreshed.

            // Build Transfer Array
            $tranArr[0] = (!isset($code) ? 'No Code......??' : $code);
            $tranArr[1] = $availablePallets;
            $tranArr[2] = $pickedPallets;
            $tranArr[3] = $orderItems;

            $data = json_encode($tranArr);
            echo $data;
        }
    }

    $ajax = true;
} elseif ($action == 'splitPallet') {

    $serialNumber = filterString($_GET["serialNumber"]);
    $orderedQty = filterString($_GET["orderedQty"]);
    $invQty = filterString($_GET["invQty"]);
    $picked = filterString($_GET["picked"]);
    $customerSONumber = filterString($_GET["customerSONumber"]);
    $shippingID = filterString($_GET["shippingID"]);
    $skuNumber = filterString($_GET["skuNumber"]);
    $partNumber = filterString($_GET["partNumber"]);
    $pickPackItemsID = filterString($_GET["pickPackItemsID"]);
    $masterTagID = filterString($_GET["masterTagID"]);

    $picked = ($picked == null) ? 0 : $picked;

    $orderNumber = $_SESSION['orderNumber'];
    $userID = $_SESSION['userID'];

    // $pickPackData = getPickPackItemPallets($orderNumber, $_SESSION['currentShipmentGroup'][0][1], $pickPackItemsID);

    // Update picked pallet's qty to have the difference between the ordered qty and the picked qty
    $newQty = $orderedQty - $picked;
    $remainingQty = $invQty - $newQty;

    // Create a new pallet sku by advancing
    $oldInventoryID = $serialNumber;
    $newInventoryID = advanceSku()[0];

    // Get the old pallet's info to add to the new one
    $a = duplicateInvUnit($newInventoryID, $userID, $oldInventoryID);

    // Update constants with new sku
    $b = updateSku($newInventoryID);

    // Create Shipping ID
    $c = addShippingID($customerSONumber, $pickPackItemsID, $userID);

    // UPDATE inventory to add shippingID
    $d = updateShippingID($shippingID, $newInventoryID);

    // Insert status of picked
    $e = insertNewStatus($newInventoryID, 1, $userID);

    // Sets the old pallet to the remaining qty
    $f = insertNewQty($newInventoryID, $newQty, null, $userID);

    // Sets the old pallet to the remaining qty
    $g = insertInventoryQty($serialNumber, $remainingQty);

    // Add Master Tag ID
    if ($masterTagID != false) {
        $h = insertMasterTag($masterTagID, $newInventoryID);
    } else {
        $h = true;
    }

    // Rebuild Menus
    $availablePallets = buildShippingInfo($partNumber, $pickPackItemsID, $customerSONumber, $shippingID, false, $skuNumber);  //Rebuild Available Pallets. The picked should be removed. The total shipped refreshed.
    $pickedPallets = buildShippingInfo($partNumber, $pickPackItemsID, $customerSONumber, $shippingID, true, $skuNumber);  //Rebuild Shipping Pallets menu. This will display picked items.
    $pickPackItem = getPickPackItems($orderNumber, $_SESSION['currentShipmentGroup'][0][1]);
    $assignedLots = getAssignedLots($orderNumber);  // Get Assigned Lots
    $orderItems = buildForkLiftItems($pickPackItem, $orderNumber, $assignedLots);  // Rebuild Order items to show total shipped refreshed.

    // Run Checks
    $checks = [$a, $b, $c, $d, $e, $f, $g, $h];
    $trigger = 0;
    for ($i = 0; $i < 6; $i++) {
        $trigger = $checks[$i] ? $i : $trigger;
    }

    $tranArr = null;
    $tranArr[0] = $trigger;
    $tranArr[1] = $availablePallets;
    $tranArr[2] = $pickedPallets;
    $tranArr[3] = $orderItems;
    $tranArr[4] = $newQty;
    $tranArr[5] = $oldInventoryID;

    // 0 : Error Trigger
    // 1 : Available Pallets
    // 2 : Picked Pallets
    // 3 : Order Items
    // 4 : New Qty
    // 5 : Old Inventory ID

    $data = json_encode($tranArr);
    echo $data;

    $ajax = true;
} elseif ($action == 'bulkPick' || $action == 'bulkShip') {
    $userID = $_SESSION['userID'];
    $statusChange = null;
    $table2 = null;
    $pick = null;

    //get sku# from serial data then get skuData
    $serialData = getSerialData($serialID);
    $skuData = getSkuData($serialData['skuNumber']);

    //Change status accordingly
    if ($action == 'bulkPick') {
        $statusChange = insertNewStatus($serialID, 1, $userID);
        //Get inventory Data for 
        $activate = activate($serialID, $userID);
        $inventoryData = pickedInventoryData($serialData['skuNumber']);
        $table2 = buildPickedTable($inventoryData);
        $pick = 1;
    } elseif ($action == 'bulkShip') {
        $statusChange = insertNewStatus($serialID, 3, $userID);
        $deactivate =  deactivate($serialID, $userID);
        //Get inventory Data for 
        $inventoryData = shippedInventoryData($serialData['skuNumber']);
        $table2 = buildShippedTable($inventoryData);
        $pick = 2;
    }

    //Update table 1
    $inventoryData = currentInventoryData($serialData['skuNumber'], $pick);
    $table1 = buildCurrentTable($inventoryData, $serialData['skuNumber']);

    if ($statusChange) {
        $tranArr = null;
        $tranArr[0] = $table1;
        $tranArr[1] = $table2;

        $data = json_encode($tranArr);
        echo $data;
    } else {
        echo 0;
    }

    $ajax = true;
} elseif ($action == 'newTag') {
    //Count of New
    $newCount = countNew();
    $pickedCount = countPicked();

    $orderCount = countOrders();

    $newItems = getItems(5);
    $itemTable = buildNewTable($newItems, 0);
    $action = 'typeValues';
} elseif ($action  == 'insertMaster') {

    $serialNumber = filterNumber($_GET['serialNumber']);

    $success = insertMasterTag(1, $serialNumber);

    echo var_dump($success);
} elseif ($action  == 'removeMaster') {

    $serialNumber = filterNumber($_GET['serialNumber']);

    $success = removeMasterTag($serialNumber);

    echo var_dump($success);
} elseif ($action == 'pickedTag') {
    //Count of Picked
    $newCount = countNew();
    $pickedCount = countPicked();

    $orderCount = countOrders();

    $pickedItems = getItems(1);
    $itemTable = buildNewTable($pickedItems, 1);
    $action = 'typeValues';
} elseif ($action == 'summaryShip') {

    $summaryID = $_GET['summaryID'];
    $salesOrderNumber = substr($summaryID, 1, strLen($summaryID));
    $userID = $_SESSION['userID'];
    $error = 0;

    $inventoryIDList = getSummaryInventoryID($salesOrderNumber);



    if (!$inventoryIDList) {
        $error = 1;
        echo -1;
    } elseif (!$inventoryIDList[0][1]) {
        $error = 2;
        echo -2;
    } else {
        for ($i = 0; $i < sizeof($inventoryIDList); $i++) {
            $statusChange = insertNewStatus($inventoryIDList[$i][0], 3, null, $userID);
            $deactivate =  deactivate($inventoryIDList[$i][0], $userID);
        }
        echo 1;
    }
    $ajax = true;
} elseif ($action == 'shippingMenu') {

    //Sets Default Shipment Group on Load
    $currentShipmentGroup = getShipmentGroup(6);

    $_SESSION['currentShipmentGroup'] = $currentShipmentGroup;

    $pickPackOrders = getPickPackOrders($currentShipmentGroup[0][1], 2, $currentShipmentGroup[0][0]);
    $ordersList = buildOrdersList($pickPackOrders);
    //    $orderInfo = buildOrderInfo();
} elseif ($action == 'shippedInfo') {

    $tranArr = null;

    // GET Data
    $partNumber = filterString($_GET['partNo']);
    $skuNumber = filterString($_GET['skuNumber']);
    $customerSONumber = filterString($_GET['customerSONumber']);
    $shippingID = filterString($_GET['shippingID']);
    $complete = getOrderStatus($customerSONumber) == 3 ? 1 : 0;
    // $pickPackItemsID = filterString($_GET['pickPackItemsID']);

    // buildShippingInfo();

    $data = buildPalletView($partNumber, $customerSONumber, $shippingID, $skuNumber, $complete);

    // $tranArr[0] = buildShippingInfo($partNo, $pickPackItemsID, $customerSONumber, $shippingID); // Display ready to be picked items.
    // $tranArr[1] = buildShippingInfo($partNo, $pickPackItemsID, $customerSONumber, $shippingID, true);  // Builds Shipping Pallets menu. This will display picked items.

    $data = json_encode($data);

    echo $data;

    $ajax = true;
} elseif ($action == 'uploadHeader') {
    $tranArr = null;

    //Setup & build pick pack header
    $orderNumber = filterString($_GET['orderNumber']);

    //    echo $orderNumber;

    $tranArr = buildForkLiftView($orderNumber, $_SESSION['currentShipmentGroup'][0][1]);


    $data = json_encode($tranArr);

    echo $data;
    $ajax = true;
}
if (!$ajax) {
    include 'view.php';
}

function buildScanView($serialID, $serialData, $currentLocationsView, $status, $binLocation)
{

    $skuData = getSkuData($serialData['skuNumber']);
    $quantity = getQuantity($serialID);
    $age = dateDifference(date('Y-m-d', strtotime($serialData['receiptDate'])), date('Y-m-d'));
    $palletType = null;

    //Determine Pallet Type
    if ($skuData['paperColorID'] == 3) {
        $palletType = 'Customer Owned';
    } elseif ($skuData['kit'] == 1 && $skuData['component'] == 1) {
        $palletType = 'Component/Kit';
    } elseif ($skuData['kit'] == 1) {
        $palletType = 'Kit';
    } elseif ($skuData['component'] == 1) {
        $palletType = 'Component';
    }

    //Determine Pallet Age Color
    if ($age <= 30) {
        $ageID = 'levelOne';
    } elseif ($age > 30 && $age <= 90) {
        $ageID = 'levelTwo';
    } else {
        $ageID = 'levelThree';
    }

    $scanSerialView = '<div class="grid">
                    <div class="unit one-third">
                    
                        <section>
                            <h1>History</h1>
                            <input id="binStatus" class="hide" value="' . $status[3] . '">
                            <div id="gradient">
                                <h2>Last Located</h2>
                                <div class="grid">
                                    <div class="unit half align-left">'
        . $binLocation[1] .
        '</div>
                                    <div class="unit half align-left">'
        . date('m-d-y H:i:s', strtotime($binLocation[2])) .
        '</div>
                                </div>
                            </div>
                            <div id="gradient">
                                <h2>Last Status</h2>
                                <div class="grid">
                                    <div class="unit half align-left">'
        . $status[1] .
        '</div>
                                    <div class="unit half align-left">'
        . date('m-d-y H:i:s', strtotime($status[2])) .
        '</div>
                                </div>
                            </div>
                            <div id="gradient">
                                <h2>Last Quantity</h2>
                                <div class="grid">
                                    <div class="unit half align-left">'
        . $quantity[1] .
        '</div>
                                    <div class="unit half align-left">'
        . date('m-d-y H:i:s', strtotime($quantity[2])) .
        '</div>
                                </div>
                            </div>
                        </section>
                        
                        <section>
                            <h1>Current Inventory</h1>
                            
                            <div class="grid">
                                <div class="unit two-fifths align-center">
                                    <h2>Bin Location</h2>
                                </div>
                                <div class="unit three-fifths align-center">
                                    <h2>Quantity</h2>
                                    <h2>Count</h2>
                                </div>
                            </div>
                                
                                <div class="punchHours" id="punchHours">'
        . $currentLocationsView .
        '</div>
                            
                        </section>
                        
                    </div>
                    <div class="unit two-thirds">
                        <section id="serialData">
                    <!-- Upper Serial Display -->
                            <div class="grid">
                                <div class="unit one-quarter align-left">
                                    <h2>Serial #</h2>
                                </div>
                                <div class="unit one-quarter align-center">
                                    <h2>Status</h2>
                                </div>
                                <div class="unit one-quarter align-center">
                                    <h2>Type</h2>
                                </div>
                                <div class="unit one-quarter align-center">
                                    <h2>Age (d)</h2>
                                </div>
                            </div>
                            
                            <div class="grid">
                                <div class="unit one-quarter align-center">
                                    <div id="serialID">' . $serialData['inventoryID'] . '</div>
                                </div>
                                <div class="unit one-quarter align-center" id="focus">
                                    <div id="focusOff">' . $status[0] . '</div>
                                </div>
                                <div class="unit one-quarter align-center">
                                    <div id="customerOwned">'
        . $palletType .
        '</div>
                                </div>
                                <div class="unit one-quarter align-center">
                                    <div id="' . $ageID . '">' . $age . '</div>
                                </div>
                            </div>
                            
                <!-- Bin Location Serial Display -->
                            <div class="grid">
                                <div class="unit two-thirds align-center" id="bin">
                                    <div id="binLocation">'
        . ($binLocation[0] == null ? '*' : $binLocation[0]) .
        '</div>
                                </div>
                                <div class="unit one-third align-center">
                                    <div id="quantity">
                                        <h2>Quantity</h2>
                                        <div>' . $quantity[0] . '</div>
                                    </div>
                                </div>
                            </div>
                <!-- Lower Serial Display -->
                            <div class="grid">
                                <div class="unit one-quarter align-center">
                                    <h2>PO #</h2>
                                </div>
                                <div class="unit one-quarter align-center">
                                    <h2>Batch #</h2>
                                </div>
                                <div class="unit one-quarter align-center">
                                    <h2>Exp Date</h2>
                                </div>
                                <div class="unit one-quarter align-center">
                                    <h2>Cust Pallet#</h2>
                                </div>
                            </div>
                            
                            <div class="grid">
                                <div class="unit one-quarter align-center">
                                    <div>' . ($serialData['poNumber'] == null ? 'NONE' : $serialData['poNumber']) . '</div>
                                </div>
                                <div class="unit one-quarter align-center">
                                    <div>' . ($serialData['batchNumber'] == null ? 'NONE' : $serialData['batchNumber']) . '</div>
                                </div>
                                <div class="unit one-quarter align-center">
                                    <div>' . ($serialData['expirationDate'] == null ? 'NONE' : $serialData['expirationDate']) . '</div>
                                </div>
                                <div class="unit one-quarter align-center">
                                    <div>' . ($serialData['palletNumber'] == null ? 'NONE' : $serialData['palletNumber']) . '</div>
                                </div>
                            </div>
                            
                        </section>
                        
                        <section>
                            <div class="grid">
                                <div class="unit one-fifth align-center">
                                    <div>
                                        <h2>SKU#</h2>
                                        <div>' . $skuData['skuNumber'] . '</div>
                                    </div>
                                </div>
                                <div class="unit four-fifths align-left">
                                    <div>' . $skuData['DESC1'] . '</div>
                                    <div>' . $skuData['DESC2'] . '</div>
                                </div>  
                            </div>';
    $filePath = $_SERVER['DOCUMENT_ROOT'] . '/image/' . $skuData['skuNumber'] . '.JPG';
    if (file_exists($filePath)) {
        $scanSerialView .= '<img src="/image/' . $skuData['skuNumber'] . '.JPG">';
    } else {
        $scanSerialView .= '<img src="/image/noImage.png">';
    }
    '</section>
                    </div>
                </div>';

    return $scanSerialView;
}


function buildCurrentLocations($currentLocationSummary, $currentTotals)
{

    $currentLocations = null;
    for ($i = 0; $i < sizeof($currentLocationSummary); $i++) {
        $currentLocations .= '<ul>
                            <li>';

        if ($currentLocationSummary[$i][1] == Null) {
            $currentLocationSummary[$i][1] = 0;
        }

        $currentLocations .=
            '<div class="grid">
                                <div class="unit half align-left">
                                    <div class="expandIcon" id="expandIcon' . $currentLocationSummary[$i][1] . '">
                                        <a href="#" onclick="expandHours(' . $currentLocationSummary[$i][0] . ', ' . $currentLocationSummary[$i][1] . ');"><span>1</span></a>
                                    </div>
                                    <span>' . $currentLocationSummary[$i][2] . '</span>
                                </div>';
        $currentLocations .=   '<div class="unit half align-right">
                                    <div class="payrollHours">
                                        <div id="totalHrs"><span>' . $currentLocationSummary[$i][3] . '</span></div>
                                        <div id="regHrs"><span>' . $currentLocationSummary[$i][4] . '</span></div>
                                    </div>
                                </div>
                            </div>';
        $currentLocations .= '<div id=expand' . $currentLocationSummary[$i][1] . '></div>';

        $currentLocations .= '</li>
                    </ul>';
    }
    $currentLocations .= '<div id="totals">
                            <ul><li>
                            <div class="grid">
                                <div class="unit half">
                                    <h2>Totals</h2>
                                </div>
                                <div class="unit half">
                                    <div class="payrollHours">
                                        <div id="totalValue"><span>'
        . $currentTotals[0] .
        '</span></div>
                                        <div id="totalValue"><span>'
        . $currentTotals[1] .
        '</span></div>
                                    </div>
                                </div>
                            </div>
                        </li></ul>
                        </div>';
    return $currentLocations;
}


function buildExpandedView($serialData, $binLocationID)
{

    $currentSerialByLocation = currentInventoryUnits($serialData['skuNumber'], $binLocationID);
    $punchEditor = '<ul>
                        <li>
                            <div class="grid">
                                    <div class="unit half align-center">
                                        <span>Serial#</span>
                                    </div>
                                    <div class="unit half align-center">
                                        <span>Quantity</span>
                                    </div>
                            </div>
                        </li>';
    for ($j = 0; $j < sizeof($currentSerialByLocation); $j++) {

        $punchEditor .=
            '<li>
                            <div class="grid">
                                <div class="unit half align-center">
                                    <div><span>' . $currentSerialByLocation[$j][0] . '</span></div>
                                </div>
                                <div class="unit half align-center">
                                    <div><span>' . $currentSerialByLocation[$j][3] . '</span></div>
                                </div>';

        $punchEditor .=         '</div>
                        </li>';
    }
    $punchEditor .= '</ul>';

    return $punchEditor;
}

function buildSkuData($skuData)
{

    $comp = $skuData['component'];
    $skuNumber = $skuData['skuNumber'];

    $skuData = '
                <div class="grid">
                    <div class="unit whole">
                        <section>
                            <div class="grid">
                                <div class="unit one-quarter">
                                    <h2>SKU</h2>
                                    <div id="skuNumber">' . $skuData['skuNumber'] . '</div>
                                </div>
                                <div class="unit one-quarter">
                                    <div id="skuDataDesc">
                                        <h2>Description</h2>
                                        <div id="desc1">' . $skuData['DESC1'] . '</div>
                                        <div id="desc2">' . $skuData['DESC2'] . '</div>
                                    </div>
                                </div>
                                <div class="unit half">';

    if ($comp == true) {
        $skuData .= '
                                        <div class="grid">
                                            <div class="unit half">
                                                <div id="scanBox">
                                                    <input type="text" name="scan" id="scan" autocomplete="off" autofocus onkeydown="Javascript: if (event.keyCode==13) scan(1);">
                                                </div>
                                            </div>
                                            <div class="unit half">
                                                <div class="switch switch-yellow">
                                                    <input type="radio" class="switch-input" name="view3" value="week3" id="week3" checked onclick="toggle(' . $skuNumber . ')">
                                                    <label for="week3" class="switch-label switch-label-off">Pick</label>
                                                    <input type="radio" class="switch-input" name="view3" value="month3" id="month3" onclick="toggle(' . $skuNumber . ')">
                                                    <label for="month3" class="switch-label switch-label-on">Ship</label>
                                                    <span class="switch-selection"></span>
                                                    <button onclick="reDirect()"><span>O</span></button>
                                                    <button onclick="printShipping(' . $skuNumber . ', ' . $comp . ')"><span>i</span></button>
                                                </div>
                                            </div>
                                        </div>';
    } else {
        $skuData .=  '
                                    <div class="grid">
                                        <div class="unit half">
                                            <div id="scanBox">
                                                <input type="text" name="scan" id="scan" autocomplete="off" autofocus onkeydown="Javascript: if (event.keyCode==13) scan(1);">
                                            </div>
                                        </div>
                                        <div class="unit half">
                                            <div class="switch switch-yellow">
                                                <input type="radio" class="switch-input" name="view3" value="week3" id="week3" onclick="toggle(' . $skuNumber . ')">
                                                <label for="week3" class="switch-label switch-label-off">Pick</label>
                                                <input type="radio" class="switch-input" name="view3" value="month3" id="month3" checked onclick="toggle(' . $skuNumber . ')">
                                                <label for="month3" class="switch-label switch-label-on">Ship</label>
                                                <span class="switch-selection"></span>
                                                <button onclick="reDirect()"><span>O</span></button>
                                                <button onclick="printShipping(' . $skuNumber . ', ' . $comp . ')"><span>i</span></button>
                                            </div>
                                        </div>
                                    </div>';
    }

    $skuData .= '</div>
                            </div>
                            <div id="errorMessage"></div>
                        </section>
                    </div>
                </div>';

    return $skuData;
}

function buildCurrentTable($inventoryData, $skuNumber = null, $component = 0)
{
    $table = '
    <div id="currentTableData">
        <h1 style="margin-left: 0">Currently In-Stock</h1>';

    // Get Distinct Lots
    $lots = [];
    for ($i = 0; $i < sizeof($inventoryData); $i++) {
        $lots[] = $inventoryData[$i][5];
    }
    $lots = array_unique($lots);

    $table .= '
        <div class="text-center">
            <input type="text" id="filterInput" placeholder="Search..." onkeyup="filterTable()">
            <div id="filterCnt">Cnt: ' . sizeof($inventoryData) . '</div>
        </div>
        <table id="filterTable" style="margin-left: 0">
            <tr>
                <th>Q</th>
                <th>S#</th>
                <th>Bin</th>
                <th>PO#</th>
                <th>LOT#';

    // <div class="dropdown">
    //     <a href="#" onclick="toggleDropdown()" class="dropdown-btn">LOT#<i class="bi bi-arrow-down"></i></a>
    //     <div id="dropContent" class="dropdown-content hide">';

    //     $filterBy = 0; // Filter by lot#
    //     for ($i=0; $i < sizeof($lots) ; $i++) { 
    //         if(isset($lots[$i])) {
    //             // $table .= '<a href="#" onclick="filterCurrent(\'' . $skuNumber . '\', \'' . $lots[$i] . '\', \'' . $component . '\', ' . $filterBy . ')">' . $lots[$i] . '</a>';
    //             $table .= '<a href="#" onclick="filterTable(\'' . $lots[$i] . '\')">' . $lots[$i] . '</a>';
    //         }
    //     }

    //     </div>
    //     </div>


    $table .= '
                </th>
                <th>Exp</th>
                <th>Plt#</th>
                <th>WO</th>
                <th>Qty</th>
                <th>Age</th>
            </tr>';

    $table .= buildCurrentData($inventoryData);

    $table .= '
            
        </table>
    </div>';
    return $table;
}

function buildCurrentData($inventoryData = false)
{

    $table = '';
    for ($i = 0; $i < sizeof($inventoryData); $i++) {
        if ($inventoryData[$i][6] != null && $inventoryData[$i][6] != '0000-00-00') {
            $expDate = date('m-d-Y', strtotime($inventoryData[$i][6]));
        } else {
            $expDate = null;
        }
        $age = dateDifference(date('Y-m-d'), date('Y-m-d', strtotime($inventoryData[$i][3])));

        $table .= '<tr>
                    <td>' . ($inventoryData[$i][19] == 1 ? 'Q' : '') . '</td>
                    <td>' . ($inventoryData[$i][0] == null ? '*' :  $inventoryData[$i][0]) . '</td>
                    <td>' . ($inventoryData[$i][16] == null ? '*' :  $inventoryData[$i][16]) . '</td>
                    <td>' . ($inventoryData[$i][4] == null ? '*' :  $inventoryData[$i][4]) . '</td>
                    <td>' . ($inventoryData[$i][5] == null ? '*' :  $inventoryData[$i][5]) . '</td>
                    <td>' . ($expDate == null ? '*' :  $expDate) . '</td>
                    <td>' . ($inventoryData[$i][7] == null ? '*' :  $inventoryData[$i][7]) . '</td>
                    <td>' . ($inventoryData[$i][18] == null ? '*' :  $inventoryData[$i][18]) . '</td>
                    <td>' . ($inventoryData[$i][17] == null ? '*' :  $inventoryData[$i][17]) . '</td>
                    <td>' . $age . '</td>
                </tr>';
    }
    return $table;
}

function buildCurrentTableF($inventoryData)
{
    $table = '<h1>Currently In-Stock</h1>';

    $table .= '<table>
                    <tr>
                        <th>Q</th>
                        <th>S#</th>
                        <th>Bin</th>
                        <th>LOT#</th>
                        <th>Qty</th>
                        <th>Age</th>
                    </tr>';

    for ($i = 0; $i < sizeof($inventoryData); $i++) {
        if ($inventoryData[$i][6] != null && $inventoryData[$i][6] != '0000-00-00') {
            $expDate = date('m-d-Y', strtotime($inventoryData[$i][6]));
        } else {
            $expDate = null;
        }
        $age = dateDifference(date('Y-m-d'), date('Y-m-d', strtotime($inventoryData[$i][3])));

        $table .= '<tr>
                                <td>' . ($inventoryData[$i][19] == 1 ? 'Q' : '') . '</td>
                                <td>' . ($inventoryData[$i][0] == null ? '*' :  $inventoryData[$i][0]) . '</td>
                                <td>' . ($inventoryData[$i][16] == null ? '*' :  $inventoryData[$i][16]) . '</td>
                                <td>' . ($inventoryData[$i][4] == null ? '*' :  $inventoryData[$i][4]) . '</td>
                                <td>' . ($inventoryData[$i][5] == null ? '*' :  $inventoryData[$i][5]) . '</td>
                                <td>' . ($expDate == null ? '*' :  $expDate) . '</td>
                                <td>' . ($inventoryData[$i][7] == null ? '*' :  $inventoryData[$i][7]) . '</td>
                                <td>' . ($inventoryData[$i][18] == null ? '*' :  $inventoryData[$i][18]) . '</td>
                                <td>' . ($inventoryData[$i][17] == null ? '*' :  $inventoryData[$i][17]) . '</td>
                                <td>' . $age . '</td>
                            </tr>';
    }
    $table .= '</table>';
    return $table;
}

function buildPickedTable($inventoryData)
{
    $table = '<h1 class="forkliftFix">Currently Picked</h1>';

    $table .= '<table class="forkliftFix">
                    <tr>
                        <th>S#</th>
                        <th>Bin</th>
                        <th>PO#</th>
                        <th>LOT</th>
                        <th>Exp</th>
                        <th>Plt#</th>
                        <th>WO</th>
                        <th>Qty</th>
                        <th>Age</th>
                    </tr>';
    for ($i = 0; $i < sizeof($inventoryData); $i++) {
        if ($inventoryData[$i][6] != null && $inventoryData[$i][6] != '0000-00-00') {
            $expDate = date('m-d-Y', strtotime($inventoryData[$i][6]));
        } else {
            $expDate = null;
        }
        $age = dateDifference(date('Y-m-d'), date('Y-m-d', strtotime($inventoryData[$i][3])));

        $table .= '<tr>
                                <td>' . ($inventoryData[$i][0] == null ? '*' :  $inventoryData[$i][0]) . '</td>
                                <td>' . ($inventoryData[$i][16] == null ? '*' :  $inventoryData[$i][16]) . '</td>
                                <td>' . ($inventoryData[$i][4] == null ? '*' :  $inventoryData[$i][4]) . '</td>
                                <td>' . ($inventoryData[$i][5] == null ? '*' :  $inventoryData[$i][5]) . '</td>
                                <td>' . ($expDate == null ? '*' :  $expDate) . '</td>
                                <td>' . ($inventoryData[$i][7] == null ? '*' :  $inventoryData[$i][7]) . '</td>
                                <td>' . ($inventoryData[$i][19] == null ? '*' :  $inventoryData[$i][19]) . '</td>
                                <td>' . ($inventoryData[$i][17] == null ? '*' :  $inventoryData[$i][17]) . '</td>
                                <td>' . $age . '</td>
                            </tr>';
    }
    $table .= '</table>';
    return $table;
}

function buildShippedTable($inventoryData)
{
    $table = '<h1>Currently Shipped - Count: (' . sizeof($inventoryData) . ')</h1>';

    $table .= '<table>
                    <tr>
                        <th>S#</th>
                        <th>Bin Loc</th>
                        <th>PO#</th>
                        <th>Batch#</th>
                        <th>Exp Date</th>
                        <th>Pallet#</th>
                        <th>Qty</th>
                        <th>Age</th>
                    </tr>';
    for ($i = 0; $i < sizeof($inventoryData); $i++) {
        if ($inventoryData[$i][6] != null && $inventoryData[$i][6] != '0000-00-00') {
            $expDate = date('m-d-Y', strtotime($inventoryData[$i][6]));
        } else {
            $expDate = '*';
        }
        if (!$inventoryData[$i][0] == null) {
            $age = dateDifference(date('Y-m-d'), date('Y-m-d', strtotime($inventoryData[$i][3])));
        } else {
            $age = null;
        }

        $table .= '<tr>
                                <td>' . ($inventoryData[$i][0] == null ? '*' :  $inventoryData[$i][0]) . '</td>
                                <td>' . ($inventoryData[$i][15] == null ? '*' :  $inventoryData[$i][15]) . '</td>
                                <td>' . ($inventoryData[$i][4] == null ? '*' :  $inventoryData[$i][4]) . '</td>
                                <td>' . ($inventoryData[$i][5] == null ? '*' :  $inventoryData[$i][5]) . '</td>
                                <td>' . ($expDate == null ? '*' :  $expDate) . '</td>
                                <td>' . ($inventoryData[$i][7] == null ? '*' :  $inventoryData[$i][7]) . '</td>
                                <td>' . ($inventoryData[$i][16] == null ? '*' :  $inventoryData[$i][16]) . '</td>
                                <td>' . $age . '</td>
                            </tr>';
    }
    $table .= '</table>';
    return $table;
}


function buildAssignWorkOrder($serialID)
{
    $assignWorkOrder = '<div class="overlay" id="overlay">
                            <form method="post" action="." id="dataEntryForm">
                                <a href="" onclick="phyInvDetailsClose(); return false;" title="quit" id="exit"><span>x</span></a>
                                <h1>Assign Material</h1>
                                <div id="errorMessage"></div>
                                <h2>Selected Serial ID: ' . $serialID . '</h2>
                                <div class="grid">
                                <div class="unit two-thirds">
                                    <ul>
                                        <li>
                                            <label for="workOrderNumber">Enter WO#:</label>
                                            <input type="text" name="scan" id="workOrder" autocomplete="off" autofocus onkeydown="Javascript: if (event.keyCode==13){ pickAssign(' . $serialID . '); return false;}">
                                        </li>
                                    </ul>
                                </div>
                                </div>
                            </form>
                            <form>
                            </form>
                        </div>';

    return $assignWorkOrder;
}

function buildErrorMenu($workOrderNumber)
{
    $errorMenu = '<div class="overlay" id="overlay">
                        <form>
                            <a href="" onclick="phyInvDetailsClose(); return false;" title="quit" id="exit"><span>x</span></a>
                            <h1>Pallet has been assigned to WO#: ' . $workOrderNumber . '</h1>
                        </form>
                    </div>';

    return $errorMenu;
}

function buildNewTable($items, $type)
{
    $newItemHTML = '
                    <table style="margin-left: 0">
                        <tr>
                        <th>Serial#</th>
                        <th>sku Number</th>
                        <th>Desc</th>
                        <th>Creation Date</th>
                        <th>Option</th>
                        </tr>';

    for ($i = 0; $i < sizeof($items); $i++) {
        $newItemHTML .= '<tr>
                                    <td>' . $items[$i][0] . '</td>
                                    <td>' . $items[$i][1] . '</td>
                                    <td>' . $items[$i][2] . '</td>
                                    <td>' . $items[$i][3] . '</td>
                                    <td><a href="/forklift/index.php?action=scan&serialID=' . $items[$i][0] . '"><span>i</span></a></td>
                                    </tr>';
    }
    $newItemHTML .= '</table>';

    return $newItemHTML;
}


function buildOrdersList($orders)
{

    $shipmentGroupOptions = getShipmentGroup();



    $buildOrderListHTML = '<div id="errorMessage"></div>
                                        <section id="shippingLAside">
                                            <div class="align-center">
                                                <select name="customerID" id="customerID" onchange="customerChangeForklift(value);" required>
                                                    <option value="" disabled selected>Select customer</option>';

    for ($i = 0; $i < sizeof($shipmentGroupOptions); $i++) {
        if ($_SESSION['currentShipmentGroup'][0][0] == $shipmentGroupOptions[$i][0]) {
            $buildOrderListHTML .= '<option value=' . $shipmentGroupOptions[$i][0] . ' selected="selected">' . $shipmentGroupOptions[$i][2] . ' - ' . $shipmentGroupOptions[$i][3] . '</option>';
        } else {
            $buildOrderListHTML .= '<option value=' . $shipmentGroupOptions[$i][0] . '>' . $shipmentGroupOptions[$i][2] . ' - ' . $shipmentGroupOptions[$i][3] . '</option>';
        }
    }
    $buildOrderListHTML .= '</select>';

    $buildOrderListHTML .= buildOrders($orders);

    $buildOrderListHTML .= '<hr>
                        
                    </hr>
                    
                    <div id="completedOrdersBox">
                        <a href="#">
                            <span class="openCO" onclick="openCO()">!</span>
                        </a>
                        <p>Completed</p>
                    </div>

                    </section>';

    return $buildOrderListHTML;
}


function buildForkLiftView($orderNumber, $customerID, $skuNumber = false, $serialNumber = false)
{
    //Setup & build pick pack header

    $headerOrder = getPickPackHeader($orderNumber);

    $forkLiftHeaderView = buildForkliftHeader($headerOrder);
    $forkliftCommandCenter = buildForkliftCommandCenter($headerOrder[23], $orderNumber);
    //  $pickPackHeaderView = buildPickPackHeader($headerOrder);

    //Setup & build pick pack items
    $pickPackItem = getPickPackItems($orderNumber, $customerID, $skuNumber);
    $assignedLots = getAssignedLots($orderNumber);
    $forkLiftItemsView = buildForkLiftItems($pickPackItem, $orderNumber, $assignedLots);

    //    $pickPackOrders = getPickPackOrders($currentShipmentGroup[0][1], 1, $currentShipmentGroup[0][0]);
    //    $ordersList = buildOrdersList($pickPackOrders);

    if (sizeof($pickPackItem) == 1) {

        $shipping = getShippingData($pickPackItem[0][0], $pickPackItem[0][1]);

        $pickPackItem = $pickPackItem ? $pickPackItem[0] : null;
        $pickPackItemsID = $pickPackItem[0];
        $customerSONumber = $pickPackItem[1];
        $partNumber = $pickPackItem[19];
        $shippingID = $shipping[0];
        $complete = getOrderStatus($customerSONumber) == 3 ? 1 : 0;

        $html = buildPalletView($partNumber, $customerSONumber, $shippingID, $skuNumber, $complete);
        $tranArr[3] = $html[0];
        $tranArr[4] = $html[1];

        // $tranArr[3] = buildShippingInfo($partNum, $pickPackItemsID, $customerSONumber, $shippingID, false, $skuNumber, $customerID);
        // $tranArr[4] = buildShippingInfo($partNum, $pickPackItemsID, $customerSONumber, $shippingID, true, $skuNumber, $customerID);

        // $tranArr[3] = buildShippingInfo($pickPackItem[0][3], $pickPackItem[0][0], $customerID, $shipping[0], null, null, null, $orderNumber); // Display ready to be picked items.
        // $tranArr[4] = buildShippingInfo($pickPackItem[0][3], $pickPackItem[0][0], $customerID, $shipping[0], true, null, null, $orderNumber);  // Builds Shipping Pallets menu. This will display picked items.

    }


    $_SESSION['orderNumber'] = $orderNumber;

    $tranArr[0] = $forkLiftHeaderView;
    $tranArr[1] = $forkLiftItemsView;
    $tranArr[2] = $forkliftCommandCenter;
    if ($skuNumber != false) {
        $tranArr[8] = var_dump($pickPackItem);
    }
    return $tranArr;
}


// <a id="buttonB" class="forkliftCompleteBtn" onclick="completeOrder()">Complete</a>
// <a style="margin-left: 1.5em;" id="buttonB" onclick="backOrder()">BackOrder</a>


function buildForkliftHeader($orderData)
{
    $smallWorldMenu = null;
    if ($orderData[23] == 2) {  // ********************  Used to be 1 instead of 2?
        $smallWorldMenu = '<h2>Order Header</h2>';
    } elseif ($orderData[23] == 2) {
        $smallWorldMenu = '<h2>Order Header
                            <a href="javascript:;" onclick="setOrder(' . $orderData[1] . ', 3)"><span> $</span></a>
                            <a href="javascript:;" onclick="setOrder(' . $orderData[1] . ', 1)"><span> 2</span></a></h2>';
    } elseif ($orderData[23] == 3) {
        $smallWorldMenu = '<h2>Order Header</h2>';
    } elseif ($orderData[23] == 3) {
        $smallWorldMenu = '<h2>Order Header<a href="javascript:;" onclick="setOrder(' . $orderData[1] . ', 3)"><span> $</span></a></h2>';
    } else {
        $smallWorldMenu = '<h2>Order Not Found</h2>';
    }

    // elseif ($orderData[23] == 3){
    //     $smallWorldMenu = '<h2>Order Header
    //                         <a href="javascript:;" onclick="setOrder(' . $orderData[1] . ', 4)"><span> $</span></a>
    //                         <a href="javascript:;" onclick="setOrder(' . $orderData[1] . ', 2)"><span> 2</span></a></h2>';
    // }

    $smallWorldMenu .= '<div id="orderDetailsBox" class="unit whole">
                                        <div id="orderDetails" class="unit whole">
                                          <div class="unit half">
                                            <li>SO Number: ' . $orderData[1] . '</li>
                                            <li>Order Date: ' . $orderData[3] . '</li>
                                            <li>Ship Date: ' . $orderData[4] . '</li>
                                            <li>PO Number: ' . $orderData[5] . '</li>
                                          </div>
                                          <div class="unit half">
                                            <li>Cust Num: ' . $orderData[6] . '</li>
                                            <li>Ship Via: ' . $orderData[7] . '</li>
                                            <li>Cust Name: ' . $orderData[16] . '</li>
                                          </div>
                                          <div class="unit whole" style="margin-top: 0.2em;">
                                            <li>Address 1: ' . $orderData[17] . '</li>
                                            <li>Address 2: ' . $orderData[18] . '</li>
                                          </div>
                                          <input class="hide" id="customerSONumber" value="' . $orderData[1] . '">
                                          <input class="hide" id="headerStatus" value="' . $orderData[23] . '">
                                        </div>
                                    </div>';
    return $smallWorldMenu;
}


function buildForkLiftItems($items, $orderNumber, $assignedLots)
{

    $picked = 0;
    $orderedQty = 0;

    $buildItems = '
                        <div class="grid">';

    $buildItems = '
                        <h2>Order Items</h2>
                        <table id="forkLiftItemsTb">
                        
                        <tr>
                            <th>Part#</th>
                            <th>Qty [picked/ordered]</th>
                            <th>Desc</th>
                            <th></th>
                        </tr>';

    for ($i = 0; $i < sizeof($items); $i++) {

        $pickPackItemsID = $items[$i][0];
        $customerSONumber = $items[$i][1];
        $_SESSION['customerID'] = $items[$i][2];
        $userID = $_SESSION['userID'];

        $shipping = getShippingData($pickPackItemsID, $customerSONumber);  // Query ShippentID with CustomerSONUmber and pickpackitemsID

        if (!$shipping) {
            $check = addShippingID($items[$i][1], $items[$i][0], $userID);  //***If No shipmentID then create new shipment utilizing CustomerSONumber and pickpackitemsID ...
            $shipping = getShippingData($pickPackItemsID, $customerSONumber);
        } else if ($shipping[6] == 0) {  // Checks if it is complete

            //IF shipmentID exsists and is complete create new shipment with backOrderNumber

            //****IF shipmentID exsists and is not complete then use shipmentID

        }

        if ($items[$i][18] == null) {
            $skuNumber = getPONo($items[$i][3]);
        } else {
            $skuNumber = $items[$i][18];
        }

        $pickedQty = countShippedQty($shipping[0], $skuNumber);  // Sum of shipped pallet qty

        // if($pickedQty == false || !isset($pickedQty)) {
        //     $pickedQty[0] = null;
        // }

        // Get PickPack Data
        $pickPackData = getPickPackItems($orderNumber, $_SESSION['customerID']);
        $totalQty = $pickPackData[$i][9];

        $picked += (int)$pickedQty[0];
        $orderedQty += (int)$totalQty;

        $buildItems .= '
                            <tr id="liNum_' . $items[$i][3] . '">
                                <td style="text-align: center; padding-left: 7px; border-left: 0; width: 5vw">' . $items[$i][3] . '</td>
                                <td class="align-center" style="min-width: 7vw; ';

        if ($pickedQty[0] == $totalQty) {
            $buildItems .= 'color: green;';
        } else if ($pickedQty[0] > $totalQty) {
            $buildItems .= 'color: orange;';
        }

        $buildItems .= '">' . ($pickedQty[0] == null ? '0' :  $pickedQty[0]) . ' / ' . ($totalQty == null ? '0' :  $totalQty) . '</td>
                                <td>' . $items[$i][4] . '</td>
                                <td class="align-center"><a href="javascript:;" onclick="showShippingInfo(' . $items[$i][3] . ', ' . $items[$i][0] . ', \'' . $items[$i][1] . '\', ' . $shipping[0] . ', \'' . $skuNumber . '\')"><span> ></span></a></td>
                            </tr>';

        if ($assignedLots) {
            $buildItems .= '<tr><td colspan="4">';

            for ($j = 0; $j < sizeof($assignedLots); $j++) {
                if ($assignedLots[$j][3] == $items[$i][3]) {
                    $buildItems .= '<li>Batch#: ' . $assignedLots[$j][2] . ', qty: ' . $assignedLots[$j][4];
                }
            }
            $buildItems .= '</td><tr>';
        }
    }
    $buildItems .= '</table>
                            <input id="orderCompleted" value="' . ($picked == $orderedQty ? 1 : 0) . '" class="hide">
                            ';

    return $buildItems;
}


function buildPalletView($partNumber, $customerSONumber, $shippingID, $carriedSkuNumber = false, $complete)
{

    // Convert partNumber to skuNumber
    $skuNumber = !$carriedSkuNumber ? getPONo($partNumber) : $carriedSkuNumber;
    $skuNumber = !$skuNumber ? $partNumber : $skuNumber;

    // Get Data
    $data = getAvailableInventory($skuNumber, $customerSONumber);

    $orderData = getOrderData($skuNumber, $customerSONumber);
    $currentMasterTag = getMasterTag($customerSONumber);
    $masterPalletCnt = (int)countShippedQty($shippingID, $skuNumber, true);
    $pickedQty = (int)countShippedQty($shippingID, $skuNumber);

    // Calculate
    $totalQty = $orderData['ordered'];
    $qtyLeftToPick = $totalQty - $pickedQty;
    $overPicked = $pickedQty > $totalQty ? 1 : 0;
    $orderFinished = $qtyLeftToPick == 0 ? 1 : 0;

    // Build Page
    $html = '
        <table class="palletTable">
            <tr>
                <td>Part#: ' . $partNumber . '</td>
                <td>Sku#: ' . $skuNumber . '</td>
                <td>';

    if ($overPicked || $orderFinished) {
        $html .= ($overPicked ? 'Over Picked: ' : 'All Picked: ') .
            '<font color="' . ($overPicked ? 'orange' : 'green') . '">
            ' . !$pickedQty ? 0 : $pickedQty . ' / ' . $totalQty . '
            </font>';
    } else {
        $html .= '<font color="orange">' . $qtyLeftToPick . '</font> left to pick.';
    }

    $html .=
        '</td>
            </>
            <tr>
                <td colspan="3">Desc: ' . $orderData['description'] . '</td>
            </tr>
            <input id="masterTag" title="masterTagID" type="number" class="hide" value="' . ($currentMasterTag == null ? 1 : $currentMasterTag) . '">
            <input id="masterTagMode" type="checkbox" class="hide">
            <input id="masterCnt" title="masterCnt" class="hide" value="' . $masterPalletCnt . '">
            <input id="skuNumber" class="hide" value="' . $skuNumber . '">
            <input id="shippingID" class="hide" value="' . $shippingID . '">
        </table>
    ';

    $tranArr = null;

    // Build Tables
    $available = '<h2>Available Pallets' . (!$complete ? '<a title="Manual Entry" onclick="scanPalletS()"> <i class="bi bi-upc-scan"></i></a>' : '') . '</h2>' . $html . buildPalletTable($data, $masterPalletCnt, $shippingID, false);
    $shipped = '<h2>Picked Pallets</h2>' . buildPalletTable($data, $masterPalletCnt, $shippingID, true);

    $available = '<div class="sectionBack">' . $available . '</div>';
    $shipped = '<div class="sectionBack">' . $shipped . '</div>';
    // id="pickedPalletSlider"
    $tranArr[0] = $available;
    $tranArr[1] = $shipped;

    return $tranArr;
}
function buildPalletTable($data, $masterPalletCnt, $shippingID, $shipped)
{

    // Build Table
    $html = '
    <div ' . ($shipped ? 'id="pickedPalletSlider"' : '') . ' class="shippingInfoScroll">
        <table class="palletTable">
            <tr>
                <th>S#</th>
                <th>Bin</th>
                <th>Lot</th>
                <th>Qty</th>
                <th>Age</th>
                ' . ($shipped ? '<th></th>' : '') . '
            </tr>';

    for ($i = 0; $i < sizeof($data); $i++) {

        // Skipping Conditions
        if ($shipped) {
            if ($data[$i]['shippingID'] != $shippingID) continue;
            if ($data[$i]['invMasterTagID']) continue;
        }
        if (!$shipped) {
            if ($data[$i]['shippingID']) continue;
        }
        if (!$data[$i]['active']) continue;

        $html .= '
                <tr>
                    <td>' . $data[$i]['inventoryID'] . '</td>
                    <td>' . $data[$i]['binName'] . '</td>
                    <td>' . $data[$i]['batchNumber'] . '</td>
                    <td>' . $data[$i]['inventoryQuantity'] . '</td>
                    <td>' . dateDifference(date('Y-m-d'), date('Y-m-d', strtotime($data[$i]['receiptDate']))) . '</td>';
        if ($shipped) {
            $html .= '<td><a href="#" onclick="scanPallet(' . $data[$i]['inventoryID'] . ', null, null, true)" title="Un-Pick"><span>&gt;</span></a></td>';
        }
        $html .= '
                </tr>
                ';
    }

    $html .= '
        </table>
    </div>
        ';
    if ($shipped) {
        $html .= '
            <div id="masterTagSlideUp">
                <div id="masterTagSliderInner">
                    <a href="#" onclick="masterTag(null, true)" title="Master Pallets">
                        <span class="viewportGreenBtn">!</span>
                    </a>

                    <p>Master Pallets (' . $masterPalletCnt . ')</p> 
                </div>
            </div>
            ';
    }

    return $html;
}

function buildShippingInfo($partNum, $pickPackItemsID, $customerSONumber, $shippingID, $shipped = false, $carriedSku = null, $customerID = false, $orderNumber = false)
{

    $masterTagID = getMasterTag($customerSONumber)[0];

    $companyID = getCompanyID($pickPackItemsID);

    if ($customerID == false) {
        $customerID = $_SESSION['orderNumber'];
    }

    if ($orderNumber == false) {
        $orderNumber = $_SESSION['orderNumber'];
    }

    // Get PickPack Data
    // $pickPackData = getPickPackItems($_SESSION['orderNumber'], $_SESSION['customerID']);
    $pickPackData = getPickPackItemPallets($orderNumber, $_SESSION['customerID'], $pickPackItemsID);


    // Bypass the getPONo Function Issue
    if ($carriedSku != null) {
        $skuNo = $carriedSku;
    } else {
        // Get PO# associated to Part#
        if ($pickPackData[0][18] == null) {
            $skuNo = getPONo($partNum);
        } else {
            $skuNo = $pickPackData[0][18];
        }
    }

    //Get SKU data
    $skuData = getSkuData($skuNo);

    //Get SKU type
    if ($skuData != false) {

        $shippingInfo = availableInventory($skuNo, $pickPackItemsID, $shipped, $shippingID);
        $masterPalletCnt = countShippedQty($shippingID, $skuNo, true)[0];
        $pickPackHeaderData = getPickPackHeader($_SESSION['orderNumber']);

        // return var_dump($masterPalletCnt);
        // die(0);

        if ($shipped) {
            $html = '<div id="pickedPalletSlider" class="sectionBack">';
        } else {
            $html = '<div class="sectionBack">';
        }

        if ($shipped == false) {

            $pickedQty = countShippedQty($shippingID, $skuNo)[0];  // Sum of shipped pallet qty

            $totalQty = $pickPackData[0][9];

            $qtyLeftToPick = $totalQty - $pickedQty;

            $html .= '
            <div class="grid">
                <div class="unit whole"><h2 style="margin-left: 0.8em; margin-top: 0.5em;">Available Pallets</h2></div>
                <div class="unit whole"><p style="font-size: 0.7em">Quarantined in <font color="red";>Red</font>.</p></div>
                <div id="partInfoMini" class="unit whole align-center">
                    <table>
                        <tr>
                            <td>
                                Part#:  ' . $partNum . ' 
                            </td>
                            <td>
                                Sku#:  ' . $skuNo . ' 
                            </td>
                            <td>';

            if ($pickedQty[0] > $totalQty) {
                $html .= 'Over Picked: <font color="orange">' . $pickedQty . ' / ' . $totalQty . '</font>';
            } else if ($qtyLeftToPick == 0) {
                $html .= 'All Picked: <font color="green">' . $pickedQty . ' / ' . $totalQty . '</font>';
            } else {
                $html .= '<font color="orange">' . $qtyLeftToPick . '</font> left to pick.';
            }

            $html .= '</td>
                        </tr>
                        <tr>
                            <td colspan="3">
                                Desc:  ' . $skuData['DESC1'] . ' 
                            </td>
                        </tr>
                    </table>
                    <input id="masterTag" title="masterTagID" type="number" class="hide" value="' . $masterTagID . '">
                    <input id="masterTagMode" type="checkbox" class="hide">
                    <input id="masterCnt" title="masterCnt" class="hide" value="' . $masterPalletCnt . '">
                </div>
            </div>';
        } else {
            $html .= '<h2 class="align-center">Picked Pallets</h2>';
        }

        // <input id="shippingID" class="hide" value="' . $shippingID . '">
        // <input id="skuNumber" class="hide" value="' . $skuNo . '">
        // <input id="pickPackItemsID" class="hide" value="' . $pickPackItemsID . '">

        $html .= '
        <div id="shippingInfoScroll">
        <table class="shippingInfoTb">
            <tr>
                <th>S#</th>
                <th>Bin</th>
                <th>Lot</th>
                <th>Qty</th>
                <th>Age</th>
                ' . ($shipped == true ? '<th></th>' : '') . '
            </tr>';

        if ($shipped) {
            $html .= '<input id="pickedSkuList" class="hide" value="';
            for ($i = 0; $i < sizeof($shippingInfo); $i++) {
                $html .= ($shippingInfo[$i][0] == null ? '*' :  $shippingInfo[$i][0]) . ' ';
            }
            $html .= '">';
        }

        for ($i = 0; $i < sizeof($shippingInfo); $i++) {
            if ($shippingInfo[$i][20] == null) {

                $age = dateDifference(date('Y-m-d'), date('Y-m-d', strtotime($shippingInfo[$i][3])));

                $html .=
                    '<tr id="liNum_' . ($shippingInfo[$i][0] == null ? '*' :  $shippingInfo[$i][0]) . '" class="';
                if ($shippingInfo[$i][19] == 1) {
                    $html .= 'palletQuarantined';
                }

                $html .= '">
                        <td>' . ($shippingInfo[$i][0] == null ? '*' :  $shippingInfo[$i][0]) . '</td>
                        <td>' . ($shippingInfo[$i][16] == null ? '*' :  $shippingInfo[$i][16]) . '</td>
                        <td>' . ($shippingInfo[$i][5] == null ? '*' :  $shippingInfo[$i][5]) . '</td>
                        <td>' . ($shippingInfo[$i][17] == null ? '*' :  $shippingInfo[$i][17]) . '</td>
                        <td>' . $age . '</td>';
                if ($shipped == true) {
                    $html .= '
                            <td><a href="#" onclick="scanPallet(\'' . $shippingInfo[$i][0] . '\', null, null, true)" title="Un-Pick"><span>&gt;</span></a></td>
                            ';
                }
                $html .= '
                        </tr>';
            }
        }

        $html .= '</table>
        </div>
        </div>';

        // <a href="#" onclick="masterTag(null, true)" title="Master Pallets">

        if ($shipped == true) {
            $html .= '
            <div id="masterTagSlideUp">
                <div id="masterTagSliderInner">
                    <a href="#" onclick="masterTag(null, true)" title="Master Pallets">
                        <span class="viewportGreenBtn">!</span>
                    </a>

                    <p>Master Pallets (' . $masterPalletCnt . ')</p> 
                </div>
            </div>';
        }
        $html .= '
        </div>
        ';
    } else {
        $html = 0;
    }

    return $html;
}

// function buildOrders($orders) {

//     $html = '<div id="forkliftOrderListBox">';

//     for($i=0; $i < sizeof($orders); $i++){
//         if($_SESSION['currentShipmentGroup'][0][4]){
//             $html .= '<li class="text-align: center"><a id="buttonB" class="forkliftListBtn" onclick="uploadHeader(' . $orders[$i][1] . ')">' . $orders[$i][5] . '</a></li>';
//         }else{
//             $html .= '<li class="text-align: center"><a id="buttonB" class="forkliftListBtn" onclick="uploadHeader(' . $orders[$i][1] . ')">' . $orders[$i][1] . '</a></li>';
//         }
//     }

//     $html .= '</div>';

//     return $html;
// }

function buildOrders($orders)
{

    $html = '<div id="forkliftOrderListBox">';

    for ($i = 0; $i < sizeof($orders); $i++) {
        $html .= '<hr>';

        if ($_SESSION['currentShipmentGroup'][0][4]) {
            $html .= '<p class="orderListItem" onclick="uploadHeader(\'' . $orders[$i][1] . '\')">' . $orders[$i][5] . '</p>';
        } else {
            $html .= '<p class="orderListItem" onclick="uploadHeader(\'' . $orders[$i][1] . '\')">' . $orders[$i][1] . '</p>';
        }
    }

    $html .= '</div>';

    return $html;
}

function buildForkliftCommandCenter($status, $orderNumber)
{

    if ($status == 2) {
        $html = '
        <div class="grid">
            <div id="confirmComplete" class="unit one-quarter">
                <a href="#" title="Complete Order" onclick="completeOrder(\'' . $orderNumber . '\')"><span>v</span></a>
            </div>
            <div class="unit one-quarter">
                <a href="#" title="Back Order" class="disable" onclick="backOrder()"><span>3</span></a>
            </div>
            <div id="masterTagMenuBtnSpot" class="unit one-quarter">
                <a href="#" id="masterTagMenuBtn" title="Master Tag" onclick="openMasterTag(\'' . $orderNumber . '\')"><span>t</span></a>
            </div>
            <div id="manualModeBtnSection" class="unit one-quarter">     
                <a href="#" id="masterTagMenuBtn" title="Manual Mode" onclick="toggleManualMode()"><span>&</span></a>
                <input id="manualMode" class="hide" type="checkbox">
            </div>
        </div>
        ';
    } else {
        $html = '<p>Order Completed</p>';
    }
    return $html;
}

function buildMasterTagCC($customerSONumber)
{

    $masterTagID = getMasterTag($customerSONumber);

    $html = '
    <div class="overlay" style="height: 10vh; width: 16em;">
        <div class="grid">
            <div class="unit whole masterTagCC">
                <a href="#" onclick="masterTag(' . $masterTagID . ', false, false)" title="New Master Pallet"><span class="viewportGreenBtn">+</span></a>
                <a href="#" onclick="closeOverlay()" title="exit"><span class="viewportRedBtn">x</span></a>
            </div>';

    $masterPallets = getMasterPalletList($customerSONumber);

    $success = buildMasterTagList($masterPallets, true, true, $customerSONumber);

    if ($success) {
        $html .= $success;
    } else {
        $html .= '
                <div class="unit whole align-center">
                    <h3 style="margin-top: 5vh">No master pallets...</h3>
                </div>';
    }

    $html .= '

        </div>
    </div>
    ';

    return $html;
}

function buildmasterTag()
{
    $html = '<p>No pallets yet.</p>';

    return $html;
}

function buildMasterTagPallets($masterTagID, $skuNumber, $customerSONumber, $overlay = false, $shippingID)
{

    $shippingInfo = getMasterPallets($masterTagID, $customerSONumber);

    $html = '<div id="shippingInfoScroll">
        <table style="padding: 2vw" class="shippingInfoTb">
            <tr>
                <th>S#</th>
                <th>Sku#</th>
                <th>Bin</th>
                <th>Lot</th>
                <th>Qty</th>
                <th>Age</th>
                <th></th>';

    $html .= '</tr>';

    for ($i = 0; $i < sizeof($shippingInfo); $i++) {

        $age = dateDifference(date('Y-m-d'), date('Y-m-d', strtotime($shippingInfo[$i]['receiptDate'])));

        $html .=
            '<tr id="liNum_' . $shippingInfo[$i]['inventoryID'] . '" class="';
        if ($shippingInfo[$i]['quarantine']) {
            $html .= 'palletQuarantined';
        }

        // i.inventoryID
        // , b.binName
        // , i.batchNumber
        // , q.inventoryQuantity
        // , i.receiptDate
        // , i.masterTagID
        // , i.quarantine
        // , i.shippingID


        // ! Override
        $overlay = false;



        $html .= '" style="color: ' . ($shippingID == $shippingInfo[$i]['shippingID'] ? 'limegreen' : 'gray') . '">
                    <td>' . $shippingInfo[$i]['inventoryID'] . '</td>
                    <td>' . $shippingInfo[$i]['skuNumber'] . '</td>
                    <td>' . $shippingInfo[$i]['binName'] . '</td>
                    <td>' . $shippingInfo[$i]['batchNumber'] . '</td>
                    <td>' . $shippingInfo[$i]['inventoryQuantity'] . '</td>
                    <td>' . $age . '</td>';
        if ($overlay) {
            $html .= '<td><a href="#" onclick="goToOrderPart(\'' . $shippingInfo[$i]['inventoryID'] . '\')"><span class="viewportGreenBtn">></span></a></td>';
        } else {
            $html .= '
                        <td class="masterTagActions">

                        <a href="#" onclick="scanPallet(\'' . $shippingInfo[$i]['inventoryID'] . '\', null, null, true)" title="Un-Pick"><span>&gt;</span></a>

                        </td>';
        }



        $html .= '</tr>';
    }

    $html .= '</table>
        </div>
        </div>';

    return $html;
}

// 287352

function buildMasterTagList($masterPallets, $masterList = false, $overlay = false, $customerSONumber, $skuNumber = null, $shippingID = null)
{

    $lastMasterTagID = getMasterTag($customerSONumber);

    if ($masterPallets == false || $masterPallets == null) {
        return false;
    } else {

        $html = '';

        if ($masterList) {
            $html = '

            <div class="unit whole align-center">
                <h1>Master Pallets</h1><a href="#" onclick="masterTag(' . $lastMasterTagID . ', false, ' . $overlay . ')"><span class="viewportGreenBtn" >+</span></a>
            </div>
            <hr style="margin-bottom: 3vh">';
        }

        $html .= '<ul class="align-center">';

        // $masterPallets[$i][1] is the corresponding first skuNumber included in the master pallet (no duplicates, only first)

        for ($i = 0; $i < sizeof($masterPallets); $i++) {

            if (!$overlay) {
                $count = countShippedPerMasterTag($masterPallets[$i][0], $shippingID);
            }

            $html .= '<li style="transform: translateX(-5%); margin: 10px;">
            Master Pallet #' . $masterPallets[$i][0];

            if (!$overlay) {
                $html .= ' <font style="color: ';

                $html .= 1 == 1 ? 'limegreen' : 'gray';

                $html .= ';">(' . $count . ')</font>';
            }

            $html .= '<a href="#" style="';

            if ($masterList) {
                $html .= 'font-size: 8px;';
            } else {
                $html .= 'font-size: 20px;';
            }

            $html .= ' margin-left: 0.5vw;" onclick="masterTag(' . $masterPallets[$i][0] . ', false, ' . ($overlay ? 1 : 0) . ')" title="Open Master Pallet"><span class="viewportGreenBtn">></span></a>';
        }

        $html .= '</ul>';

        return $html;
    }

    // return var_dump($masterPallets);

}

function buildShipping($skuNumber, $comp)
{

    //Get SKU data
    $skuDesc = getSkuDesc($skuNumber);

    //Get SKU type
    if ($comp == true) {
        // $currentInventoryData = currentInventoryData($skuNumber, 1);
        $inventoryData = pickedInventoryData($skuNumber);
        $table2 = buildPickedTable($inventoryData);
    } else {
        // $currentInventoryData = currentInventoryData($skuNumber, 0);
        $inventoryData = shippedInventoryData($skuNumber);
        $table2 = buildShippedTable($inventoryData);
    }

    // $table1 = buildCurrentTable($currentInventoryData, $skuNumber);

    $table1 = $_SESSION['shippingCurrentTable'];

    $html = '
    <div id="printThis">
        <div class="grid">
            <table>
                <tr>
                    <th>SKU</th>
                    <th>Description</th>
                </tr>
                <tr>
                    <td>' . $skuNumber . '</td>
                    <td>' . $skuDesc["DESC1"] . '<br>' . $skuDesc["DESC2"] . '</td>
                </tr>
            </table>
        </div>
        <div class="grid">
            <div class="unit half">' . $table1 . '</div>
            <div class="unit half">' . $table2 . '</div>
        </div>
    </div>';

    return $html;
}
