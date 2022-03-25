<?php

require $_SERVER['DOCUMENT_ROOT'] . '/library/library.php';
require $_SERVER['DOCUMENT_ROOT'] . '/library/library3.php';
require $_SERVER['DOCUMENT_ROOT'] . '/library/model.php';
// Return UserName


function getPickPackOrders($customerID, $status, $source = 1, $search = false, $start = false, $end = false){
    $connection = connWhitneyUser();
    try {
    $sql = 'SELECT * FROM pickpackheader WHERE pickPackCompanyID = :customerID AND active = :status AND source = :source';

    if($search != false) {
        $sql .= ' AND customerSONumber LIKE \'%' . $search . '%\'
            OR customerName LIKE \'%' . $search . '%\'
            OR customerNumber LIKE \'%' . $search . '%\'
            OR customerPO LIKE \'%' . $search . '%\'
            OR shipToAddressLine1 LIKE \'%' . $search . '%\'
            OR shipToAddressLine2 LIKE \'%' . $search . '%\'
            OR shipVia LIKE \'%' . $search . '%\'';
    }

    if($start != false && $end != false) {
        $sql .= ' AND orderDate BETWEEN CAST(:start AS DATE) AND CAST(:end AS DATE)';
    }

    $sql .= ' ORDER BY active DESC, pickpackheaderID DESC';


    if($status > 2){
    $sql .= ' LIMIT 20';
    }
     
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':customerID', $customerID, PDO::PARAM_INT);
        $stmt->bindParam(':status', $status, PDO::PARAM_INT);
        $stmt->bindParam(':source', $source, PDO::PARAM_STR);

        if($start != false && $end != false) {
            $stmt->bindParam(':start', $start, PDO::PARAM_STR);
            $stmt->bindParam(':end', $end, PDO::PARAM_STR);
        }
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

function getPickPackHeader($orderNumber){
    $connection = connWhitneyUser();
    try {
     $sql = 'SELECT * FROM pickpackheader WHERE customerSONumber = :orderNumber';
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':orderNumber', $orderNumber, PDO::PARAM_STR);
        $stmt->execute();
        $data = $stmt->fetch();
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

function getPickPackHeaderPO($orderNumber){
    $connection = connWhitneyUser();
    try {
     $sql = 'SELECT * FROM pickpackheader WHERE customerPO = :orderNumber';
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':orderNumber', $orderNumber, PDO::PARAM_STR);
        $stmt->execute();
        $data = $stmt->fetch();
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

// function getPickPackItems($orderNumber, $customerID){
//     $connection = connWhitneyUser();
//     try {

//         $sql = 'SELECT 
//             ppi.pickPackItemsID
//             , ppi.customerSONumber
//             , ppi.pickPackCompanyID
//             , ppi.item
//             , IFNULL(ppi.description, itf.DESC1)
//             , ppi.options
//             , ppi.rate
//             , ppi.amount
//             , ppi.price
//             , ppi.ordered
//             , ppi.uom
//             , ppi.case
//             , ppi.backOrdered
//             , ppi.shipped
//             , ppi.UPC
//             , ppi.itemKey
//             , IFNULL(x.assigned, 0)
//             , TRIM(IFNULL(ppi.case_cnt, ppi.ordered/ppi.case))+0
//             , itf.skuNumber
//                 FROM pickpackitems ppi
//                 LEFT JOIN (SELECT ppi.item, IFNULL(sum(ppla.assignedQty),0) AS assigned
//                             FROM pickpackheader pph
//                             LEFT JOIN pickpacklotassign ppla
//                             ON ppla.customerSONumber = pph.customerSONumber
//                             LEFT JOIN pickpackitems ppi
//                             ON ppla.pickpackItemsID = ppi.pickpackitemsID
//                             WHERE pph.customerSONumber = :orderNumber AND pph.pickPackCompanyID = :customerID
//                             GROUP BY ppi.item) x
//                 ON ppi.item = x.item
//                 LEFT JOIN itemsf itf
//                 ON ppi.skuNumber = itf.skuNumber
//                 WHERE customerSONumber = :orderNumber AND pickPackCompanyID = :customerID';
//         $stmt = $connection->prepare($sql);
//         $stmt->bindParam(':orderNumber', $orderNumber, PDO::PARAM_INT);
//         $stmt->bindParam(':customerID', $customerID, PDO::PARAM_INT);
//         $stmt->execute();
//         $data = $stmt->fetchall();
//         $stmt->closeCursor();
//     } catch (PDOException $ex) {
//         return FALSE;
//     }
//     return $data;
// }

function getPickPackItem($pickPackID, $customerID){
    $connection = connWhitneyUser();
    try {

        $sql = 'SELECT pph.customerPO, ppi.*
                FROM pickpackitems ppi
                LEFT JOIN pickpackheader pph
                ON pph.customerSONumber = ppi.customerSONumber
                WHERE ppi.pickpackItemsID = :pickPackItemsID AND ppi.pickPackCompanyID = :customerID';
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':pickPackItemsID', $pickPackID, PDO::PARAM_INT);
        $stmt->bindParam(':customerID', $customerID, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetch();
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}


// function getSkuNumber($itemNumber){
//     $connection = connWhitneyUser();
//     try {
//      $sql = 'SELECT * FROM itemsf WHERE partNumber = :itemNumber';
//         $stmt = $connection->prepare($sql);
//         $stmt->bindParam(':itemNumber', $itemNumber, PDO::PARAM_INT);
//         $stmt->execute();
//         $data = $stmt->fetch();
//         $stmt->closeCursor();
//     } catch (PDOException $ex) {
//         return FALSE;
//     }
//     return $data;
// }

function getSkuNo($partNumber){
    $connection = connWhitneyUser();
    try {
     $sql = 'SELECT skuNumber FROM itemsf WHERE partNumber = :partNumber';
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':partNumber', $partNumber, PDO::PARAM_STR);
        $stmt->execute();
        $data = $stmt->fetch();
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}


function getItemBatch($sku) {
    $connection = connWhitneyUser();
    try {
     $sql = 'SELECT i.batchNumber
            , COUNT(q.inventoryQuantity) AS palletCount
            , SUM(q.inventoryQuantity) AS totalQty
            , (SUM(q.inventoryQuantity) - IFNULL(x.assignedBatch, 0)) AS avl
            , i.receiptDate
            , i.expirationDate
            FROM `inventory` i
            LEFT JOIN (SELECT inv.inventoryID, inv.binLocationID
                        FROM inventorylocation inv
                     INNER JOIN (SELECT inventoryID, max(inventorylocationID) inventorylocationID
                                    FROM (SELECT invs.inventoryID, inventorylocationID
                                          FROM inventorylocation invs
                                          INNER JOIN inventory i
                                          ON invs.inventoryID = i.inventoryID
                                          WHERE skuNumber = :sku) invs
                                    GROUP BY inventoryID) ss
                        ON inv.inventoryID = ss.inventoryID AND inv.inventorylocationID = ss.inventorylocationID) l
            ON i.inventoryID = l.inventoryID
            LEFT JOIN `binlocation` b
            ON l.binlocationID = b.binlocationID
            LEFT JOIN (SELECT inv.inventoryID, inv.inventoryStateID
                        FROM inventorystatus inv
                        INNER JOIN(SELECT inventoryID, max(inventoryStatusID) inventoryStatusID
                                    FROM (SELECT invs.inventoryID, inventoryStatusID
                                          FROM inventoryStatus invs
                                          INNER JOIN inventory i
                                          ON invs.inventoryID = i.inventoryID
                                          WHERE skuNumber = :sku) invs
                                    GROUP BY inventoryID) ss
                        ON inv.inventoryID = ss.inventoryID AND inv.inventoryStatusID = ss.inventoryStatusID
                        INNER JOIN inventory i
                        ON i.inventoryID = inv.inventoryID) s
            ON i.inventoryID = s.inventoryID
            LEFT JOIN (SELECT inv.inventoryID, inv.inventoryQuantity
                        FROM inventoryunitquantity inv
                        INNER JOIN(SELECT inventoryID, max(inventoryQuantityID) inventoryQuantityID
                                    FROM (SELECT invs.inventoryID, invs.inventoryQuantityID
                                          FROM inventoryUnitQuantity invs
                                          INNER JOIN inventory i
                                          ON invs.inventoryID = i.inventoryID
                                          WHERE skuNumber = :sku) invs
                                    GROUP BY inventoryID) ss
                        ON inv.inventoryID = ss.inventoryID AND inv.inventoryQuantityID = ss.inventoryQuantityID
                        INNER JOIN inventory i
                        ON i.inventoryID = inv.inventoryID) q
            ON i.inventoryID = q.inventoryID
            LEFT JOIN (SELECT ppla.batchNumber, sum(ppla.assignedQty) AS assignedBatch
                    FROM pickpacklotassign ppla
                    LEFT JOIN pickpackheader pph
                    ON ppla.customerSONumber = pph.customerSONumber
                    LEFT JOIN pickpackitems ppi
                    ON ppi.pickpackitemsID = ppla.pickpackitemsID
                    LEFT JOIN itemsf itf
                    ON itf.skuNumber = ppi.item OR itf.partNumber = ppi.item
                    WHERE (pph.active = 1 OR pph.active = 2) AND itf.skuNumber = :sku
                    GROUP BY ppla.batchNumber) x
            ON i.batchNumber = x.batchNumber
            WHERE s.inventoryStateID <> 2 AND s.inventoryStateID <> 3 AND s.inventoryStateID <> 6 AND i.skuNumber = :sku
            AND i.active = 1 AND (NOT palletNumber = "pending shipment" OR palletNumber IS NULL)
            GROUP BY i.batchNumber
            ORDER BY i.receiptDate';
        $stmt = $connection->prepare($sql);
        $stmt->bindValue(':sku', $sku, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}


function insertBatch($batch, $qty, $orderNumber, $pickPackID, $userID){
    $connection = connWhitneyUser();
    try {
        $sql = 'INSERT INTO pickpacklotassign(
                                        customerSONumber
                                        , batchNumber
                                        , pickPackItemsID
                                        , assignedQty
                                        , createdBy
                                        , creationDate
                                        , modifiedBy
                                        , modifiedDate)
                                        
                    VALUES (            :customerSONumber
                                        , :batchNumber
                                        , :pickPackItemsID
                                        , :qty
                                        , :userID
                                        , Now()
                                        , :userID
                                        , Now())';
    $stmt = $connection->prepare($sql);
    $stmt->bindParam(':customerSONumber', $orderNumber, PDO::PARAM_STR);
    $stmt->bindParam(':batchNumber', $batch, PDO::PARAM_STR);
    $stmt->bindParam(':pickPackItemsID', $pickPackID, PDO::PARAM_STR);
    $stmt->bindParam(':qty', $qty, PDO::PARAM_STR);
    $stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
    $stmt->execute();
        $insertRow = $stmt->rowCount();
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $insertRow;
    
}


function getAssignedLots($orderNumber){
    $connection = connWhitneyUser();
    try {
     $sql = 'SELECT ppa.pickpacklotAssignID, ppa.customerSONumber, ppa.batchNumber, ppi.item, ppa.assignedQty  FROM pickPackLotAssign ppa
             LEFT JOIN pickpackitems ppi
             ON ppa.pickpackitemsID = ppi.pickpackItemsID
             WHERE ppa.customerSONumber = :orderNumber';
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':orderNumber', $orderNumber, PDO::PARAM_STR);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

function getPickPackLotAssign($assignedLotID){
    $connection = connWhitneyUser();
    try {
     $sql = 'SELECT ppa.pickpacklotAssignID, ppa.customerSONumber, ppa.batchNumber, ppi.item, ppa.assignedQty  FROM pickPackLotAssign ppa
             LEFT JOIN pickpackitems ppi
             ON ppa.pickpackitemsID = ppi.pickpackItemsID
             WHERE ppa.pickpacklotassignID = :assignedLotID';
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':assignedLotID', $assignedLotID, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetch();
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

function deleteBatch($lotAssignID){
    $connection = connWhitneyAdmin();
    try {
     $sql = 'DELETE FROM pickPackLotAssign WHERE pickpacklotAssignID = :lotAssignID';
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':lotAssignID', $lotAssignID, PDO::PARAM_STR);
        $stmt->execute();
        $deleteRow = $stmt->rowCount();
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $deleteRow;
}

function updateAssignmentQty($pickPackLotAssignID, $newQty){

    $connection = connWhitneyUser();
 try {
    $sql = 'UPDATE pickpacklotassign SET 
                 assignedQty = :newQty
            WHERE pickPackLotAssignID = :pickPackLotAssignID';
    $stmt = $connection->prepare($sql);
    $stmt->bindParam(':pickPackLotAssignID', $pickPackLotAssignID, PDO::PARAM_INT);
    $stmt->bindParam(':newQty', $newQty, PDO::PARAM_INT);
    $stmt->execute();
    $updateRow = $stmt->rowCount();
    $stmt->closeCursor();
 } catch (PDOException $ex) {
        return FALSE;
    }
    return $updateRow;
}

function updateLineItem($pickPackItemID, $backOrder, $orderQty, $newCaseCount){

    $connection = connWhitneyUser();
 try {
    $sql = 'UPDATE pickpackitems SET 
                 backOrdered = :backOrder
                 , ordered = :orderQty
                 , `case` = :newCaseCount
            WHERE pickpackitemsID = :pickPackItemID';
    $stmt = $connection->prepare($sql);
    $stmt->bindParam(':pickPackItemID', $pickPackItemID, PDO::PARAM_INT);
    $stmt->bindParam(':backOrder', $backOrder, PDO::PARAM_INT);
    $stmt->bindParam(':orderQty', $orderQty, PDO::PARAM_INT);
    $stmt->bindParam(':newCaseCount', $newCaseCount, PDO::PARAM_INT);
    $stmt->execute();
    $updateRow = $stmt->rowCount();
    $stmt->closeCursor();
 } catch (PDOException $ex) {
        return FALSE;
    }
    return $updateRow;
}

function updateShippedQty($pickPackItemID, $shippedQty){

    $connection = connWhitneyUser();
 try {
    $sql = 'UPDATE pickpackitems SET 
                 shipped = :shippedQty
            WHERE pickpackitemsID = :pickPackItemID';
    $stmt = $connection->prepare($sql);
    $stmt->bindParam(':pickPackItemID', $pickPackItemID, PDO::PARAM_INT);
    $stmt->bindParam(':shippedQty', $shippedQty, PDO::PARAM_INT);
    $stmt->execute();
    $updateRow = $stmt->rowCount();
    $stmt->closeCursor();
 } catch (PDOException $ex) {
        return FALSE;
    }
    return $updateRow;
}

function deleteItemsShipping($orderNumber){

    $connection = connWhitneyAdmin();
    try {
     $sql = 'DELETE FROM shipping WHERE customerSONumber = :orderNumber';
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':orderNumber', $orderNumber, PDO::PARAM_STR);
        $stmt->execute();
        $deleteRow = $stmt->rowCount();
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $deleteRow;
}

function deleteOrderItems($orderNumber){

    $connection = connWhitneyAdmin();
    try {
     $sql = 'DELETE FROM pickPackItems WHERE customerSONumber = :orderNumber';
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':orderNumber', $orderNumber, PDO::PARAM_STR);
        $stmt->execute();
        $deleteRow = $stmt->rowCount();
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $deleteRow;
}

function deleteOrderHeader($orderNumber){
    $connection = connWhitneyAdmin();
    try {
     $sql = 'DELETE FROM pickPackHeader WHERE customerSONumber = :orderNumber';
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':orderNumber', $orderNumber, PDO::PARAM_STR);
        $stmt->execute();
        $deleteRow = $stmt->rowCount();
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $deleteRow;
}

function addOrder($customerSONumber
            , $pickPackCompanyID
            , $orderDate
            , $shipDate
            , $customerPO
            , $shipVia
            , $customerComment
            , $customerName
            , $shipToPhone
            , $shipToAddressLine1
            , $shipToAddressLine2
            , $shipToAddressLine3
            , $shipToCity
            , $shipToState
            , $country
            , $userID
            , $shipmentGroupID
            , $shipToCustomerID
            , $shipToID
            , $customerNumber = null){
    $connection = connWhitneyUser();
    try {
        $sql = 'INSERT INTO pickpackheader(
                                        customerSONumber
                                        , pickPackCompanyID
                                        , orderDate
                                        , shipDate
                                        , customerPO
                                        , shipVia
                                        , customerComment
                                        , customerName
                                        , shipToPhone
                                        , shipToAddressLine1
                                        , shipToAddressLine2
                                        , shipToAddressLine3
                                        , shipToCity
                                        , shipToState
                                        , country
                                        , orderEnteredBy
                                        , source
                                        , shipToCustomerID
                                        , shipToID
                                        , customerNumber)                                       
                                VALUES (:customerSONumber
                                        , :pickPackCompanyID
                                        , :orderDate
                                        , :shipDate
                                        , :customerPO
                                        , :shipVia
                                        , :customerComment
                                        , :customerName
                                        , :shipToPhone
                                        , :shipToAddressLine1
                                        , :shipToAddressLine2
                                        , :shipToAddressLine3
                                        , :shipToCity
                                        , :shipToState
                                        , :country
                                        , :userID
                                        , :shipmentGroupID
                                        , :shipToCustomerID
                                        , :shipToID
                                        , :customerNumber)';
    $stmt = $connection->prepare($sql);
    $stmt->bindParam(':customerSONumber', $customerSONumber, PDO::PARAM_STR);
    $stmt->bindParam(':pickPackCompanyID', $pickPackCompanyID, PDO::PARAM_INT);
    $stmt->bindParam(':orderDate', $orderDate, PDO::PARAM_STR);
    $stmt->bindParam(':shipDate', $shipDate, PDO::PARAM_STR);
    $stmt->bindParam(':customerPO', $customerPO, PDO::PARAM_STR);
    $stmt->bindParam(':shipVia', $shipVia, PDO::PARAM_STR);
    $stmt->bindParam(':customerComment', $customerComment, PDO::PARAM_STR);
    $stmt->bindParam(':customerName', $customerName, PDO::PARAM_STR);
    $stmt->bindParam(':shipToPhone', $shipToPhone, PDO::PARAM_STR);
    $stmt->bindParam(':shipToAddressLine1', $shipToAddressLine1, PDO::PARAM_STR);
    $stmt->bindParam(':shipToAddressLine2', $shipToAddressLine2, PDO::PARAM_STR);
    $stmt->bindParam(':shipToAddressLine3', $shipToAddressLine3, PDO::PARAM_STR);
    $stmt->bindParam(':shipToCity', $shipToCity, PDO::PARAM_STR);
    $stmt->bindParam(':shipToState', $shipToState, PDO::PARAM_STR);
    $stmt->bindParam(':country', $country, PDO::PARAM_STR);
    $stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
    $stmt->bindParam(':shipmentGroupID', $shipmentGroupID, PDO::PARAM_INT);
    $stmt->bindParam(':shipToCustomerID', $shipToCustomerID, PDO::PARAM_INT);
    $stmt->bindParam(':shipToID', $shipToID, PDO::PARAM_INT);
    $stmt->bindParam(':customerNumber', $customerNumber, PDO::PARAM_INT);
    $stmt->execute();
        $insertRow = $stmt->rowCount();
        $rowID = $connection->lastInsertId();
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $rowID;
    // return $sql;
    
}

function editOrder($orderDate, $shipDate, $poNumber, $customerNumber, $shipVia, $customerSONumber) {

    $connection = connWhitneyUser();
    try {
       $sql = ' 
       UPDATE pickPackHeader 
       SET orderDate = :orderDate
       , shipDate = :shipDate
       , customerPO = :poNumber
       , customerNumber = :customerNumber
       , shipVia = :shipVia
       WHERE customerSONumber = :customerSONumber;
       ';
       $stmt = $connection->prepare($sql);
       $stmt->bindParam(':orderDate', $orderDate, PDO::PARAM_STR);
       $stmt->bindParam(':shipDate', $shipDate, PDO::PARAM_STR);
       $stmt->bindParam(':poNumber', $poNumber, PDO::PARAM_STR);
       $stmt->bindParam(':customerNumber', $customerNumber, PDO::PARAM_STR);
       $stmt->bindParam(':shipVia', $shipVia, PDO::PARAM_STR);
       $stmt->bindParam(':customerSONumber', $customerSONumber, PDO::PARAM_STR);
       $stmt->execute();
       $updateRow = $stmt->rowCount();
       $stmt->closeCursor();
    } catch (PDOException $ex) {
           return FALSE;
       }
       return $updateRow;
}

function  addOrderSlim(
            $customerSONumber
            ,$pickPackCompanyID
            , $orderDate
            , $shipDate
            , $customerPO
            , $shipVia
            , $customerComment
            , $shipmentGroupID
            , $shipToCustomerID
            , $shipToID){
    $connection = connWhitneyUser();
    try {
        $sql = 'INSERT INTO pickpackheader(
                                        customerSONumber
                                        , pickPackCompanyID
                                        , orderDate
                                        , shipDate
                                        , customerPO
                                        , shipVia
                                        , customerComment
                                        , source
                                        , shipToCustomerID
                                        , shipToID)                                                  
                                VALUES (
                                        :customerSONumber
                                        , :pickPackCompanyID
                                        , :orderDate
                                        , :shipDate
                                        , :customerPO
                                        , :shipVia
                                        , :customerComment
                                        , :shipmentGroupID
                                        , :shipToCustomerID
                                        , :shipToID);';
    $stmt = $connection->prepare($sql);
    $stmt->bindParam(':customerSONumber', $customerSONumber, PDO::PARAM_STR);
    $stmt->bindParam(':pickPackCompanyID', $pickPackCompanyID, PDO::PARAM_STR);
    $stmt->bindParam(':orderDate', $orderDate, PDO::PARAM_STR);
    $stmt->bindParam(':shipDate', $shipDate, PDO::PARAM_STR);
    $stmt->bindParam(':customerPO', $customerPO, PDO::PARAM_STR);
    $stmt->bindParam(':shipVia', $shipVia, PDO::PARAM_STR);
    $stmt->bindParam(':customerComment', $customerComment, PDO::PARAM_STR);
    $stmt->bindParam(':shipmentGroupID', $shipmentGroupID, PDO::PARAM_STR);
    $stmt->bindParam(':shipToCustomerID', $shipToCustomerID, PDO::PARAM_STR);
    $stmt->bindParam(':shipToID', $shipToID, PDO::PARAM_STR);
    $stmt->execute();
        $insertRow = $stmt->rowCount();
        $rowID = $connection->lastInsertId();
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $rowID;
    
}

function  insertNewItem($SONo, $qty, $skuNumber, $customerID, $notes){
    $connection = connWhitneyUser();
    try {
        $sql = 'INSERT INTO pickpackitems(
                                        customerSONumber
                                        , pickPackCompanyID
                                        , item
                                        , description
                                        , ordered
                                        , itemKey
                                        , notes)                                       
                                VALUES (:customerSONumber
                                        , :customerID
                                        , :item
                                        , (SELECT DESC1 FROM itemsf WHERE skuNumber = :item)
                                        , :ordered
                                        , CONCAT(:customerSONumber, :item)
                                        , :notes)';
    $stmt = $connection->prepare($sql);
    $stmt->bindParam(':customerSONumber', $SONo, PDO::PARAM_STR);
    $stmt->bindParam(':ordered', $qty, PDO::PARAM_INT);
    $stmt->bindParam(':item', $skuNumber, PDO::PARAM_STR);
    $stmt->bindParam(':customerID', $customerID, PDO::PARAM_INT);
    $stmt->bindParam(':notes', $notes, PDO::PARAM_STR);
    $stmt->execute();
        $insertRow = $stmt->rowCount();
        $rowID = $connection->lastInsertId();
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $sql;
}



function getOrderSONumber($newID){
    $connection = connWhitneyUser();
    try {
        $sql = 'SELECT customerSONumber FROM pickPackHeader WHERE pickPackHeaderID = :newID';
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':newID', $newID, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetch();
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

function deleteOrderItem($pickPackItemID){
    
    $connection = connWhitneyAdmin();
    try {
        $sql = 'DELETE FROM pickPackItems WHERE pickPackItemsID = :pickPackItemID';
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':pickPackItemID', $pickPackItemID, PDO::PARAM_STR);
        $stmt->execute();
        $deleteRow = $stmt->rowCount();
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $deleteRow;
}

function getSearchedItems() {
    $connection = connWhitneyUser();
    try {
        $sql = '
        SELECT 
        DISTINCT item
        , description
        FROM pickpackitems
        WHERE item LIKE %:search%
        OR description LIKE %:search%
        ';
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':orderNumber', $orderNumber, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetch();
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

function updateOrderItem($pickpackItemsID, $notes, $ordered) {
    $connection = connWhitneyUser();
    try {
        $sql = 'UPDATE pickpackitems
                SET notes = :notes
                , ordered = :ordered
                WHERE pickPackItemsID = :pickpackItemsID';
    $stmt = $connection->prepare($sql);
    $stmt->bindParam(':pickpackItemsID', $pickpackItemsID, PDO::PARAM_INT);
    $stmt->bindParam(':ordered', $ordered, PDO::PARAM_INT);
    $stmt->bindParam(':notes', $notes, PDO::PARAM_STR);
    $stmt->execute();
    $updateRow = $stmt->rowCount();
    $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $updateRow;
}

function updatePOCustomer($pickPackHeaderID, $shipmentGroupID, $pickPackCompanyID) {
    $connection = connWhitneyUser();
    try {
        $sql = 'UPDATE pickPackHeader SET source = :shipmentGroupID, pickPackCompanyID = :pickPackCompanyID WHERE pickPackHeaderID = :pickPackHeaderID';
    $stmt = $connection->prepare($sql);
    $stmt->bindParam(':pickPackHeaderID', $pickPackHeaderID, PDO::PARAM_INT);
    $stmt->bindParam(':shipmentGroupID', $shipmentGroupID, PDO::PARAM_INT);
    $stmt->bindParam(':pickPackCompanyID', $pickPackCompanyID, PDO::PARAM_INT);
    $stmt->execute();
        $insertRow = $stmt->rowCount();
        $rowID = $connection->lastInsertId();
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $rowID;
    // return $sql;
    
}
