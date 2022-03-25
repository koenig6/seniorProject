<?php

require $_SERVER['DOCUMENT_ROOT'] . '/library/library.php';
require $_SERVER['DOCUMENT_ROOT'] . '/library/model.php';

// Get serial data
function getSerialData($serialID)
{
    $connection = connWhitneyUser();
    try {
        $sql = 'SELECT *
             FROM inventory i   
             LEFT JOIN customer c
             ON i.companyID = c.customerID
             WHERE inventoryID = :serialID';
        $stmt = $connection->prepare($sql);
        $stmt->bindValue(':serialID', $serialID, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

// Get serial Location
function getBinLocation($serialID)
{
    $connection = connWhitneyUser();
    try {
        $sql = 'SELECT b.binName, userName, i.creationDate, b.binLocationID
             FROM inventoryLocation i
             LEFT JOIN binLocation b
             ON i.binLocationID = b.binLocationID
             LEFT JOIN users u
             ON i.createdBy = u.userID
             WHERE inventoryID = :serialID
             ORDER BY i.creationDate DESC
             LIMIT 1';
        $stmt = $connection->prepare($sql);
        $stmt->bindValue(':serialID', $serialID, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

// Get serial status
function getStatus($serialID)
{
    $connection = connWhitneyUser();
    try {
        $sql = 'SELECT st.stateName, userName, su.creationDate, su.InventoryStateID
             FROM inventoryStatus su
             LEFT JOIN inventoryState st
             ON su.inventoryStateID = st.inventoryStateID
             LEFT JOIN users u
             ON su.createdBy = u.userID
             WHERE inventoryID = :serialID
             ORDER BY su.creationDate DESC
             LIMIT 1';
        $stmt = $connection->prepare($sql);
        $stmt->bindValue(':serialID', $serialID, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}


// Get serial status
function getQuantity($serialID)
{
    $connection = connWhitneyUser();
    try {
        $sql = 'SELECT i.inventoryQuantity, userName, i.creationDate
             FROM inventoryUnitQuantity i
             LEFT JOIN users u
             ON i.createdBy = u.userID
             WHERE inventoryID = :serialID
             ORDER BY i.creationDate DESC
             LIMIT 1';
        $stmt = $connection->prepare($sql);
        $stmt->bindValue(':serialID', $serialID, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

// Get serial status
function currentInventory($sku)
{
    $connection = connWhitneyUser();
    try {
        $sql = 'SELECT i.inventoryID, b.binlocationID, b.binName, COUNT(q.inventoryQuantity) AS palletCount, SUM(q.inventoryQuantity) AS totalQty
FROM `inventory` i
LEFT JOIN (SELECT inv.inventoryID, inv.binLocationID
               FROM inventorylocation inv
                           INNER JOIN (SELECT inventoryID, max(inventoryLocationID) inventoryLocationID
                           FROM inventorylocation
                           GROUP BY inventoryID) ss
               ON inv.inventoryID = ss.inventoryID AND inv.inventoryLocationID = ss.inventoryLocationID) l
ON i.inventoryID = l.inventoryID
LEFT JOIN `binlocation` b
ON l.binlocationID = b.binlocationID
LEFT JOIN (SELECT inv.inventoryID, inv.inventoryStateID
               FROM inventorystatus inv
               INNER JOIN(SELECT inventoryID, max(inventoryStatusID) inventoryStatusID
                           FROM inventorystatus
                           GROUP BY inventoryID) ss
               ON inv.inventoryID = ss.inventoryID AND inv.inventoryStatusID = ss.inventoryStatusID) s
ON i.inventoryID = s.inventoryID
LEFT JOIN (SELECT inv.inventoryID, inv.inventoryQuantity, inv.creationDate
               FROM inventoryunitquantity inv
               INNER JOIN(SELECT inventoryID, max(inventoryQuantityID) inventoryQuantityID
                           FROM inventoryunitquantity
                           GROUP BY inventoryID) ss
               ON inv.inventoryID = ss.inventoryID AND inv.inventoryQuantityID = ss.inventoryQuantityID) q
ON i.inventoryID = q.inventoryID
WHERE s.inventoryStateID <> 2 AND s.inventoryStateID <> 3 AND s.inventoryStateID <> 6 AND i.skuNumber = :sku AND i.active = 1
GROUP BY b.binName';
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



// Get current pallets
function currentInventoryUnits($sku, $locationID)
{
    $connection = connWhitneyUser();
    try {
        $sql = 'SELECT i.inventoryID, b.binlocationID, b.binName, q.inventoryQuantity AS totalQty
FROM `inventory` i
LEFT JOIN (SELECT inv.inventoryID, inv.binLocationID
               FROM inventorylocation inv
                           INNER JOIN (SELECT inventoryID, max(inventoryLocationID) inventoryLocationID
                           FROM inventorylocation
                           GROUP BY inventoryID) ss
               ON inv.inventoryID = ss.inventoryID AND inv.inventoryLocationID = ss.inventoryLocationID) l
ON i.inventoryID = l.inventoryID
LEFT JOIN `binlocation` b
ON l.binlocationID = b.binlocationID
LEFT JOIN (SELECT inv.inventoryID, inv.inventoryStateID
               FROM inventorystatus inv
               INNER JOIN(SELECT inventoryID, max(inventoryStatusID) inventoryStatusID
                           FROM inventorystatus
                           GROUP BY inventoryID) ss
               ON inv.inventoryID = ss.inventoryID AND inv.inventoryStatusID = ss.inventoryStatusID) s
ON i.inventoryID = s.inventoryID
LEFT JOIN (SELECT inv.inventoryID, inv.inventoryQuantity, inv.creationDate
               FROM inventoryunitquantity inv
               INNER JOIN(SELECT inventoryID, max(inventoryQuantityID) inventoryQuantityID
                           FROM inventoryunitquantity
                           GROUP BY inventoryID) ss
               ON inv.inventoryID = ss.inventoryID AND inv.inventoryQuantityID = ss.inventoryQuantityID) q
ON i.inventoryID = q.inventoryID
WHERE (s.inventoryStateID = 1 OR s.inventoryStateID = 4 OR s.inventoryStateID = 5) AND i.skuNumber = :sku AND i.active = 1';

        if ($locationID == 0) {
            $sql .= ' AND b.binLocationID IS NULL';
        } else {
            $sql .= ' AND b.binLocationID = :locationID';
        }

        $stmt = $connection->prepare($sql);
        $stmt->bindValue(':sku', $sku, PDO::PARAM_INT);
        if ($locationID != 0) {
            $stmt->bindValue(':locationID', $locationID, PDO::PARAM_INT);
        }
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}


function skuValidation($sku)
{
    $connection = connWhitneyUser();
    try {
        $sql = 'SELECT COUNT(skuNumber)
             FROM itemsf
             WHERE skuNumber = :sku';
        $stmt = $connection->prepare($sql);
        $stmt->bindValue(':sku', $sku, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

function serialValidation($serialID)
{
    $connection = connWhitneyUser();
    try {
        $sql = 'SELECT COUNT(inventoryID)
             FROM inventory
             WHERE inventoryID = :serialID';
        $stmt = $connection->prepare($sql);
        $stmt->bindValue(':serialID', $serialID, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}



// Get serial status
function pickedInventoryData($sku)
{
    $connection = connWhitneyUser();
    try {
        $sql = 'SELECT
            i.inventoryID
            , i.skuNumber
            , i.companyID
            , i.receiptDate
            , i.poNumber
            , i.batchNumber
            , i.expirationDate
            , i.palletNumber
            , i.salesOrder
            , i.active
            , i.createdBy
            , i.creationDate
            , i.modifiedBy
            , i.modifiedDate
            , i.phyInventoryUnitStatusID
            , i.vendorID
            , b.binlocationID
            , b.binName
            , q.inventoryQuantity
            , i.workOrderNumber
   FROM `inventory` i
   LEFT JOIN (SELECT inv.inventoryID, inv.binLocationID
               FROM inventorylocation inv
                           INNER JOIN (SELECT inventoryID, max(inventoryLocationID) inventoryLocationID
                           FROM inventorylocation
                           GROUP BY inventoryID) ss
               ON inv.inventoryID = ss.inventoryID AND inv.inventoryLocationID = ss.inventoryLocationID) l
   ON i.inventoryID = l.inventoryID
   LEFT JOIN `binlocation` b
   ON l.binlocationID = b.binlocationID
   LEFT JOIN (SELECT inv.inventoryID, inv.inventoryStateID, inv.creationDate
               FROM inventorystatus inv
               INNER JOIN(SELECT inventoryID, max(inventoryStatusID) inventoryStatusID
                           FROM inventorystatus
                           GROUP BY inventoryID) ss
               ON inv.inventoryID = ss.inventoryID AND inv.inventoryStatusID = ss.inventoryStatusID) s
   ON i.inventoryID = s.inventoryID
   LEFT JOIN (SELECT inv.inventoryID, inv.inventoryQuantity, inv.creationDate
               FROM inventoryunitquantity inv
               INNER JOIN(SELECT inventoryID, max(inventoryQuantityID) inventoryQuantityID
                           FROM inventoryunitquantity
                           GROUP BY inventoryID) ss
               ON inv.inventoryID = ss.inventoryID AND inv.inventoryQuantityID = ss.inventoryQuantityID) q
   ON i.inventoryID = q.inventoryID
   WHERE s.inventoryStateID = 1 AND i.skuNumber = :sku AND DATE(s.creationDate) = CURDATE() AND i.active = TRUE
    ORDER BY i.receiptDate ASC';
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

// Get serial status
function shippedInventoryData($sku)
{
    $connection = connWhitneyUser();
    try {
        $sql = 'SELECT i.*, b.binlocationID, b.binName, q.inventoryQuantity
   FROM `inventory` i
   LEFT JOIN (SELECT inv.inventoryID, inv.binLocationID
               FROM inventorylocation inv
                           INNER JOIN (SELECT inventoryID, max(inventoryLocationID) inventoryLocationID
                           FROM inventorylocation
                           GROUP BY inventoryID) ss
               ON inv.inventoryID = ss.inventoryID AND inv.inventoryLocationID = ss.inventoryLocationID) l
   ON i.inventoryID = l.inventoryID
   LEFT JOIN `binlocation` b
   ON l.binlocationID = b.binlocationID
   LEFT JOIN (SELECT inv.inventoryID, inv.inventoryStateID, inv.creationDate
               FROM inventorystatus inv
               INNER JOIN(SELECT inventoryID, max(inventoryStatusID) inventoryStatusID
                           FROM inventorystatus
                           GROUP BY inventoryID) ss
               ON inv.inventoryID = ss.inventoryID AND inv.inventoryStatusID = ss.inventoryStatusID) s
   ON i.inventoryID = s.inventoryID
   LEFT JOIN (SELECT inv.inventoryID, inv.inventoryQuantity, inv.creationDate
               FROM inventoryunitquantity inv
               INNER JOIN(SELECT inventoryID, max(inventoryQuantityID) inventoryQuantityID
                           FROM inventoryunitquantity
                           GROUP BY inventoryID) ss
               ON inv.inventoryID = ss.inventoryID AND inv.inventoryQuantityID = ss.inventoryQuantityID) q
   ON i.inventoryID = q.inventoryID
   WHERE s.inventoryStateID = 3 AND i.skuNumber = :sku AND DATE(s.creationDate) = CURDATE() AND i.active = FALSE
    ORDER BY i.receiptDate ASC';
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

// Get serial status
function countPicked()
{
    $connection = connWhitneyUser();
    try {
        $sql = 'SELECT s.inventoryStateID, COUNT(s.inventoryID) AS count
   FROM `inventory` i
   LEFT JOIN (SELECT inv.inventoryID, inv.inventoryStateID
               FROM inventorystatus inv
               INNER JOIN(SELECT inventoryID, max(inventoryStatusID) inventoryStatusID
                           FROM inventorystatus
                           GROUP BY inventoryID) ss
               ON inv.inventoryID = ss.inventoryID AND inv.inventoryStatusID = ss.inventoryStatusID) s
   ON i.inventoryID = s.inventoryID
   WHERE s.inventoryStateID = 1 AND i.active = 1 AND i.modifiedDate > "2016-12-31"
   GROUP BY s.inventoryStateID';
        $stmt = $connection->prepare($sql);
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

// Get serial status
function countNew()
{
    $connection = connWhitneyUser();
    try {
        $sql = 'SELECT s.inventoryStateID, COUNT(s.inventoryID) AS count
   FROM `inventory` i
   LEFT JOIN (SELECT inv.inventoryID, inv.inventoryStateID
               FROM inventorystatus inv
               INNER JOIN(SELECT inventoryID, max(inventoryStatusID) inventoryStatusID
                           FROM inventorystatus
                           GROUP BY inventoryID) ss
               ON inv.inventoryID = ss.inventoryID AND inv.inventoryStatusID = ss.inventoryStatusID) s
   ON i.inventoryID = s.inventoryID
   WHERE s.inventoryStateID = 5 AND i.active = 1 AND i.modifiedDate > "2016-12-31" AND (NOT palletNumber = "Pending Shipment" OR palletNumber IS NULL)
   GROUP BY s.inventoryStateID';
        $stmt = $connection->prepare($sql);
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

function getInCompPickAssignment($serialID)
{
    $connection = connWhitneyUser();
    try {
        $sql = 'SELECT eUsageID, inventoryID, workOrderNumber, quantity
             FROM eusage eu
             WHERE inventoryID = :serialID AND quantity IS NULL';
        $stmt = $connection->prepare($sql);
        $stmt->bindValue(':serialID', $serialID, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

function addEUsage($workOrderNumber, $serialID, $userID)
{
    $connection = connWhitneyUser();
    try {
        $sql = 'INSERT INTO eUsage(
                                        inventoryID
                                        , workOrderNumber
                                        , createdBy
                                        , creationDate
                                        , modifiedBy
                                        , modifiedDate)
                                        
                    VALUES (            :serialID
                                        , :workOrderNumber
                                        , :userID
                                        , Now()
                                        , :userID
                                        , Now())';
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':workOrderNumber', $workOrderNumber, PDO::PARAM_STR);
        $stmt->bindParam(':serialID', $serialID, PDO::PARAM_INT);
        $stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
        $stmt->execute();
        $insertRow = $stmt->rowCount();
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $insertRow;
}


function getItems($state)
{
    $connection = connWhitneyUser();
    try {
        $sql = 'SELECT i.inventoryID, i.skuNumber, itf.DESC1, s.creationDate, s.inventoryStateID FROM inventory i
            LEFT JOIN (SELECT inv.inventoryID, inv.inventoryStateID, inv.creationDate
                           FROM inventorystatus inv
                           INNER JOIN(SELECT inventoryID, max(inventoryStatusID) inventoryStatusID
                                       FROM inventorystatus
                                       GROUP BY inventoryID) ss
                           ON inv.inventoryID = ss.inventoryID AND inv.inventoryStatusID = ss.inventoryStatusID) s
            ON i.inventoryID = s.inventoryID
            LEFT JOIN itemsf itf
            ON itf.skuNumber = i.skuNumber
            WHERE s.inventoryStateID = :state AND i.active = 1 AND i.modifiedDate > "2016-12-31" AND (NOT i.palletNumber = "Pending Shipment" OR i.palletNumber IS NULL)
            ORDER BY s.creationDate DESC';

        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':state', $state, PDO::PARAM_STR);
        $stmt->execute();
        $data = $stmt->fetchall(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}


function getSummaryInventoryID($salesOrderNumber)
{
    $connection = connWhitneyUser();
    try {
        $sql = 'SELECT i.inventoryID, active
            FROM `inventory` i
            WHERE i.salesOrder = :saleOrderNumber AND i.salesOrder IS NOT NULL AND NOT i.salesOrder = ""';

        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':saleOrderNumber', $salesOrderNumber, PDO::PARAM_STR);
        $stmt->execute();
        $data = $stmt->fetchALL(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}


function getPickPackOrders($customerID, $status, $source = 1)
{
    $connection = connWhitneyUser();
    try {
        $sql = 'SELECT * FROM pickpackheader WHERE pickPackCompanyID = :customerID AND active = :status AND source = :source ORDER BY pickpackheaderID DESC';

        if ($status > 2) {
            $sql .= ' LIMIT 20';
        }

        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':customerID', $customerID, PDO::PARAM_INT);
        $stmt->bindParam(':status', $status, PDO::PARAM_INT);
        $stmt->bindParam(':source', $source, PDO::PARAM_STR);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

function getPickPackHeader($orderNumber)
{
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
//                 ON ppi.item = itf.skuNumber
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

function getPallets($orderNumber)
{
}

function getPickPackItemPallets($orderNumber, $customerID, $pickPackItemsID)
{
    $connection = connWhitneyUser();
    try {

        $sql = 'SELECT 
            ppi.pickPackItemsID
            , ppi.customerSONumber
            , ppi.pickPackCompanyID
            , ppi.item
            , IFNULL(ppi.description, itf.DESC1)
            , ppi.options
            , ppi.rate
            , ppi.amount
            , ppi.price
            , ppi.ordered
            , ppi.uom
            , ppi.case
            , ppi.backOrdered
            , ppi.shipped
            , ppi.UPC
            , ppi.itemKey
            , IFNULL(x.assigned, 0)
            , TRIM(IFNULL(ppi.case_cnt, ppi.ordered/ppi.case))+0
            , itf.skuNumber
            , ppi.notes
                FROM pickpackitems ppi
                LEFT JOIN (SELECT ppi.item, IFNULL(sum(ppla.assignedQty),0) AS assigned
                            FROM pickpackheader pph
                            LEFT JOIN pickpacklotassign ppla
                            ON ppla.customerSONumber = pph.customerSONumber
                            LEFT JOIN pickpackitems ppi
                            ON ppla.pickpackItemsID = ppi.pickpackitemsID
                            WHERE pph.customerSONumber = :orderNumber AND pph.pickPackCompanyID = :customerID
                            GROUP BY ppi.item) x
                ON ppi.item = x.item
                LEFT JOIN itemsf itf
                ON ppi.item = itf.skuNumber
                WHERE customerSONumber = :orderNumber AND pickPackCompanyID = :customerID AND ppi.pickPackItemsID = :pickPackItemsID';
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':orderNumber', $orderNumber, PDO::PARAM_STR);
        $stmt->bindParam(':customerID', $customerID, PDO::PARAM_INT);
        $stmt->bindParam(':pickPackItemsID', $pickPackItemsID, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetchAll();
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

function getAssignedLots($orderNumber)
{
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

function updateShippingID($shippingID, $inventoryID)
{
    $connection = connWhitneyUser();
    try {
        $sql = 'UPDATE inventory
        SET shippingID = :shippingID
        WHERE inventoryID = :inventoryID';
        $stmt = $connection->prepare($sql);
        $stmt->bindValue(':shippingID', $shippingID, PDO::PARAM_INT);
        $stmt->bindValue(':inventoryID', $inventoryID, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $shippingID;
}

function shippingInv($shippingID)
{
    $connection = connWhitneyUser();
    try {
        $sql = '';  // Incomplete --------------|
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':shippingID', $shippingID, PDO::PARAM_STR);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

function getInvQty($serialNum)
{
    $connection = connWhitneyUser();
    try {
        $sql = 'select inventoryQuantity from inventoryunitquantity where inventoryID = :SerialNum';
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':SerialNum', $serialNum, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

function getOrderData($skuNumber, $customerSONumber)
{
    $connection = connWhitneyUser();
    try {
        $sql = '
        SELECT 
        itf.partNumber
        , IFNULL(ppi.description, itf.DESC1) AS description
        , ppi.ordered as ordered
        FROM itemsf itf
        LEFT JOIN pickPackItems ppi
            ON ppi.item = itf.skuNumber
            AND ppi.customerSONumber = :customerSONumber
        WHERE skuNumber = :skuNumber
        ';

        $stmt = $connection->prepare($sql);
        $stmt->bindValue(':skuNumber', $skuNumber, PDO::PARAM_STR);
        $stmt->bindValue(':customerSONumber', $customerSONumber, PDO::PARAM_STR);
        $stmt->execute();
        $data = $stmt->fetch();
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

function getAvailableInventory($skuNumber, $customerSONumber)
{
    $connection = connWhitneyUser();
    try {
        $sql = '
        SELECT 
        i.inventoryID
        , b.binName
        , i.batchNumber
        , q.inventoryQuantity
        , i.receiptDate
        , i.masterTagID AS invMasterTagID
        , i.quarantine
        , i.shippingID
        , i.active
        FROM inventory i
        LEFT JOIN (SELECT inv.inventoryID, inv.binLocationID
            FROM inventorylocation inv
                        INNER JOIN (SELECT inventoryID, max(inventoryLocationID) inventoryLocationID
                        FROM inventorylocation
                        GROUP BY inventoryID) ss
            ON inv.inventoryID = ss.inventoryID AND inv.inventoryLocationID = ss.inventoryLocationID) l
            ON i.inventoryID = l.inventoryID
        LEFT JOIN binlocation b
            ON l.binlocationID = b.binlocationID
        LEFT JOIN (SELECT inv.inventoryID, inv.inventoryQuantity, inv.creationDate FROM inventoryunitquantity inv
            INNER JOIN(SELECT inventoryID, max(inventoryQuantityID) inventoryQuantityID FROM inventoryunitquantity GROUP BY inventoryID) ss
                ON inv.inventoryID = ss.inventoryID AND inv.inventoryQuantityID = ss.inventoryQuantityID) q
            ON i.inventoryID = q.inventoryID 
        WHERE i.skuNumber = :skuNumber;
        ';
        $stmt = $connection->prepare($sql);
        $stmt->bindValue(':skuNumber', $skuNumber, PDO::PARAM_STR);
        $stmt->execute();
        $data = $stmt->fetchAll();
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

function addShippingID($customerSONumber, $pickpackItemsID, $userID)
{
    $connection = connWhitneyUser();
    try {
        $sql = 'INSERT INTO shipping(
                                        customerSONumber
                                        , pickpackItemsID
                                        , shipmentDate
                                        , createdBy
                                        , modifiedBy
                                        , modifiedDate)
                                        
                    VALUES (            :customerSONumber
                                        , :pickpackItemsID
                                        , CURDATE()
                                        , :userID
                                        , :userID
                                        , Now())';
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':customerSONumber', $customerSONumber, PDO::PARAM_STR);
        $stmt->bindParam(':pickpackItemsID', $pickpackItemsID, PDO::PARAM_INT);
        $stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
        $stmt->execute();
        $insertRow = $stmt->rowCount();
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $insertRow;
}

// function getShippingData($pickPackItemsID, $customerSONumber) {
//     $connection = connWhitneyUser();
//     try {
//      $sql = 'SELECT *
//              FROM shipping   
//              WHERE pickPackItemsID = ' . $pickPackItemsID . ' AND customerSONumber = ' . $customerSONumber;
//         $stmt = $connection->prepare($sql);
//         $stmt->bindParam(':pickPackItemsID', $pickPackItemsID, PDO::PARAM_INT);
//         $stmt->bindParam(':customerSONumber', $customerSONumber, PDO::PARAM_INT);
//         $stmt->execute();
//         $data = $stmt->fetch(PDO::FETCH_ASSOC);
//         $stmt->closeCursor();
//     } catch (PDOException $ex) {
//         return FALSE;
//     }
//     return $sql;
// }

function getShippingID($customerSONumber)
{
    $connection = connWhitneyUser();
    try {
        $sql = 'SELECT shippingID FROM shipping WHERE customerSONumber = :customerSONumber';
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':customerSONumber', $customerSONumber, PDO::PARAM_STR);
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}


function countOrders()
{
    $connection = connWhitneyUser();
    try {
        $sql = 'SELECT COUNT(pickPackHeaderID) FROM pickpackheader 
        WHERE active = 1';
        $stmt = $connection->prepare($sql);
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

function countTransferOrders()
{
    $connection = connWhitneyUser();
    try {
        $sql = 'SELECT COUNT(pickPackHeaderID) FROM pickpackheader 
        WHERE active = 2 AND source = 6';
        $stmt = $connection->prepare($sql);
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

// $sql = 'SELECT s.inventoryStateID, COUNT(s.inventoryID) AS count
// FROM `inventory` i
// LEFT JOIN (SELECT inv.inventoryID, inv.inventoryStateID
//             FROM inventorystatus inv
//             INNER JOIN(SELECT inventoryID, max(inventoryStatusID) inventoryStatusID
//                         FROM inventorystatus
//                         GROUP BY inventoryID) ss
//             ON inv.inventoryID = ss.inventoryID AND inv.inventoryStatusID = ss.inventoryStatusID) s
// ON i.inventoryID = s.inventoryID
// WHERE s.inventoryStateID = 5 AND i.active = 1 AND i.modifiedDate > "2016-12-31" AND (NOT palletNumber = "Pending Shipment" OR palletNumber IS NULL)
// GROUP BY s.inventoryStateID';

function countShippedPerMasterTag($masterTagID, $shippingID)
{
    $connection = connWhitneyUser();
    try {
        $sql = '
        SELECT IFNULL(SUM(q.inventoryQuantity), 0)
        FROM inventory i
        LEFT JOIN (SELECT inv.inventoryID, inv.inventoryQuantity, inv.creationDate
                    FROM inventoryunitquantity inv
                    INNER JOIN(SELECT inventoryID, max(inventoryQuantityID) inventoryQuantityID
                                FROM inventoryunitquantity
                                GROUP BY inventoryID) ss
                ON inv.inventoryID = ss.inventoryID AND inv.inventoryQuantityID = ss.inventoryQuantityID) q
        ON i.inventoryID = q.inventoryID
        AND i.shippingID = :shippingID
        WHERE i.masterTagID = :masterTagID;
        ';
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':masterTagID', $masterTagID, PDO::PARAM_INT);
        $stmt->bindParam(':shippingID', $shippingID, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetch();
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data[0];
}



function updateShipping($customerSONumber, $pickpackItemsID, $shippingID, $userID, $totalShipped)
{
    $connection = connWhitneyUser();
    try {
        $sql = 'UPDATE shipping
            SET complete = 1
            , modifiedDate = CURDATE()
            , modifiedBy = :userID
            , totalShipped = :totalShipped
            WHERE customerSONumber = :customerSONumber AND pickpackItemsID = :pickpackItemsID AND shippingID = :shippingID;
     ';
        $stmt = $connection->prepare($sql);
        $stmt->bindValue(':shippingID', $shippingID, PDO::PARAM_INT);
        $stmt->bindValue(':customerSONumber', $customerSONumber, PDO::PARAM_STR);
        $stmt->bindValue(':userID', $userID, PDO::PARAM_INT);
        $stmt->bindValue(':totalShipped', $totalShipped, PDO::PARAM_INT);
        $stmt->bindValue(':pickpackItemsID', $pickpackItemsID, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

function updatePickPackItem($pickPackItemsID, $shipped)
{
    $connection = connWhitneyUser();
    try {
        $sql = 'UPDATE pickpackitems
            SET backOrdered = 0
            , shipped = :shipped
            WHERE pickPackItemsID = :pickPackItemsID; 
     ';
        $stmt = $connection->prepare($sql);
        $stmt->bindValue(':pickPackItemsID', $pickPackItemsID, PDO::PARAM_INT);
        $stmt->bindValue(':shipped', $shipped, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

function insertInventoryQty($serialNumber, $newQty, $userID)
{
    $connection = connWhitneyUser();
    try {
        $sql = 'INSERT INTO inventoryunitquantity
            VALUES (DEFAULT, :serialNumber, :newQty, :userID, NOW())
            ';
        $stmt = $connection->prepare($sql);
        $stmt->bindValue(':serialNumber', $serialNumber, PDO::PARAM_INT);
        $stmt->bindValue(':newQty', $newQty, PDO::PARAM_INT);
        $stmt->bindValue(':userID', $userID, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

function duplicateInvUnit($userID, $oldInventoryID, $shippingID)
{
    $connection = connWhitneyUser();
    try {
        $sql = 'INSERT into inventory (
            skuNumber
            , companyID
            , receiptDate
            , poNumber
            , batchNumber
            , expirationDate
            , palletNumber
            , salesOrder
            , active
            , createdBy
            , creationDate
            , modifiedBy
            , modifiedDate
            , phyInventoryUnitStatusID
            , vendorID
            , workOrderNumber
            , receiptID
            , quarantine
            , combinetoID
            , splitfromID
            , shippingID)
        SELECT 
            skuNumber
            , companyID
            , receiptDate
            , poNumber
            , batchNumber
            , expirationDate
            , palletNumber
            , salesOrder
            , active
            , :userID
            , NOW()
            , :userID
            , NOW()
            , phyInventoryUnitStatusID
            , vendorID
            , workOrderNumber
            , receiptID
            , quarantine
            , null
            , :oldInventoryID
            , :shippingID
        FROM inventory WHERE inventoryID = :oldInventoryID;';
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
        $stmt->bindParam(':oldInventoryID', $oldInventoryID, PDO::PARAM_INT);
        $stmt->bindParam(':shippingID', $shippingID, PDO::PARAM_INT);
        $stmt->execute();
        $insertRow = $connection->lastInsertId();
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $insertRow;
}

function mergeInvUnit($inventoryID, $oldInventoryID)
{
    $connection = connWhitneyUser();
    try {
        $sql = 'UPDATE inventory SET
            combinetoID = :oldInventoryID
            WHERE inventoryID = :inventoryID';
        $stmt = $connection->prepare($sql);
        $stmt->bindValue(':oldInventoryID', $oldInventoryID, PDO::PARAM_INT);
        $stmt->bindValue(':inventoryID', $inventoryID, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

function getCompanyID($pickPackItemsID)
{
    $connection = connWhitneyUser();
    try {
        $sql = 'SELECT pickPackCompanyID
            FROM pickpackitems
            WHERE pickPackItemsID = :pickPackItemsID';
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':pickPackItemsID', $pickPackItemsID, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

function getPickPackItemsID($customerSONumber, $pickPackCompanyID, $skuNumber)
{
    $connection = connWhitneyUser();
    try {
        $sql = 'SELECT pickPackItemsID
            FROM pickpackitems
            WHERE customerSONumber = :customerSONumber AND
            pickPackCompanyID = :pickPackCompanyID AND
            item = :skuNumber
            ';
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':customerSONumber', $customerSONumber, PDO::PARAM_STR);
        $stmt->bindParam(':pickPackCompanyID', $pickPackCompanyID, PDO::PARAM_INT);
        $stmt->bindParam(':skuNumber', $skuNumber, PDO::PARAM_STR);
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

function getPickPackItemQty($skuNumber, $customerSONumber)
{
    $connection = connWhitneyUser();
    try {
        $sql = 'SELECT pickPackItemsID
            FROM pickpackitems
            WHERE customerSONumber = :customerSONumber AND
            pickPackCompanyID = :pickPackCompanyID AND
            item = :skuNumber
            ';
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':customerSONumber', $customerSONumber, PDO::PARAM_STR);
        $stmt->bindParam(':pickPackCompanyID', $pickPackCompanyID, PDO::PARAM_INT);
        $stmt->bindParam(':skuNumber', $skuNumber, PDO::PARAM_STR);
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

// function insertMasterTagID($masterTagID, $inventoryID){
//     $connection = connWhitneyUser();
//     try {
//         $sql = 'UPDATE inventory
//         SET masterTagID = :masterTagID
//         WHERE inventoryID = :inventoryID';
//     $stmt = $connection->prepare($sql);
//     $stmt->bindValue(':masterTagID', $masterTagID, PDO::PARAM_INT);
//     $stmt->bindValue(':inventoryID', $inventoryID, PDO::PARAM_INT);
//     $stmt->execute();
//         $insertRow = $stmt->rowCount();
//         $stmt->closeCursor();
//     } catch (PDOException $ex) {
//         return FALSE;
//     }
//     return $sql;
// }

function insertMasterTag($masterTagID, $inventoryID)
{
    $connection = connWhitneyUser();
    try {
        $sql = 'UPDATE inventory
        SET masterTagID = :masterTagID
        WHERE inventoryID = :inventoryID';
        $stmt = $connection->prepare($sql);
        $stmt->bindValue(':masterTagID', $masterTagID, PDO::PARAM_INT);
        $stmt->bindValue(':inventoryID', $inventoryID, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

function removeMasterTag($inventoryID)
{
    $connection = connWhitneyUser();
    try {
        $sql = 'UPDATE inventory
        SET masterTagID = null
        WHERE inventoryID = :inventoryID';
        $stmt = $connection->prepare($sql);
        $stmt->bindValue(':inventoryID', $inventoryID, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

// function removeMasterTag($inventoryID) {
//     $connection = connWhitneyUser();
//     try {
//         $sql = 'UPDATE inventory
//         SET masterTagID = null
//         WHERE inventoryID = :inventoryID';
//     $stmt = $connection->prepare($sql);
//     $stmt->bindValue(':inventoryID', $inventoryID, PDO::PARAM_INT);
//     $stmt->execute();
//         $insertRow = $stmt->rowCount();
//         $stmt->closeCursor();
//     } catch (PDOException $ex) {
//         return FALSE;
//     }
//     return $sql;
// }

function getMasterPallets($masterTagID, $customerSONumber)
{
    $connection = connWhitneyUser();
    try {
        $sql = 'SELECT 
        i.inventoryID
        , b.binName
        , i.batchNumber
        , q.inventoryQuantity
        , i.receiptDate
        , i.masterTagID
        , i.quarantine
        , i.shippingID
        , i.skuNumber
        , i.shippingID
        FROM inventory i
        LEFT JOIN (SELECT inv.inventoryID, inv.binLocationID
            FROM inventorylocation inv
                        INNER JOIN (SELECT inventoryID, max(inventoryLocationID) inventoryLocationID
                        FROM inventorylocation
                        GROUP BY inventoryID) ss
            ON inv.inventoryID = ss.inventoryID AND inv.inventoryLocationID = ss.inventoryLocationID) l
            ON i.inventoryID = l.inventoryID
        LEFT JOIN binlocation b
            ON l.binlocationID = b.binlocationID
        LEFT JOIN (SELECT inv.inventoryID, inv.inventoryQuantity, inv.creationDate FROM inventoryunitquantity inv
            INNER JOIN(SELECT inventoryID, max(inventoryQuantityID) inventoryQuantityID FROM inventoryunitquantity GROUP BY inventoryID) ss
                ON inv.inventoryID = ss.inventoryID AND inv.inventoryQuantityID = ss.inventoryQuantityID) q
            ON i.inventoryID = q.inventoryID 
        LEFT JOIN shipping s
        ON i.shippingID = s.shippingID
        LEFT JOIN pickpackitems ppi
            ON ppi.customerSONumber = s.customerSONumber 
            AND ppi.pickpackitemsID = s.pickpackitemsID
        WHERE ppi.customerSONumber = :customerSONumber AND i.masterTagID = :masterTagID';

        $stmt = $connection->prepare($sql);

        $stmt->bindValue(':masterTagID', $masterTagID, PDO::PARAM_INT);
        $stmt->bindValue(':customerSONumber', $customerSONumber, PDO::PARAM_INT);

        $stmt->execute();
        $data = $stmt->fetchAll();
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

function getMasterPalletList($customerSONumber)
{
    $connection = connWhitneyUser();
    try {
        $sql = '
        SELECT DISTINCT i.masterTagID, i.shippingID
        FROM inventory i
        LEFT JOIN shipping s
        ON i.shippingID = s.shippingID
        LEFT JOIN pickpackitems ppi
        ON ppi.customerSONumber = s.customerSoNumber and ppi.pickpackitemsID = s.pickpackitemsID
        WHERE ppi.customerSONumber = :customerSONumber AND i.masterTagID IS NOT NULL GROUP BY i.masterTagID ORDER BY i.masterTagID ASC;
        ';

        $stmt = $connection->prepare($sql);

        $stmt->bindValue(':customerSONumber', $customerSONumber, PDO::PARAM_STR);

        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

function getSkuMasterPalletList($customerSONumber, $skuNumber)
{
    $connection = connWhitneyUser();
    try {
        $sql = '
        SELECT 
        DISTINCT i.masterTagID
        , ppi.item
        FROM inventory i
                INNER JOIN (SELECT item from pickpackitems
                WHERE customerSONumber = :customerSONumber) ppi
        WHERE i.skuNumber = ppi.item AND
        i.masterTagID is not null AND
        i.skuNumber = :skuNumber
        ORDER BY i.masterTagID ASC';

        $stmt = $connection->prepare($sql);

        $stmt->bindValue(':customerSONumber', $customerSONumber, PDO::PARAM_STR);
        $stmt->bindValue(':skuNumber', $skuNumber, PDO::PARAM_STR);

        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

function getPartNumber($skuNumber)
{

    $connection = connWhitneyUser();
    try {
        $sql = '
    SELECT partNumber FROM itemsf
    WHERE skuNumber = :skuNumber;
    ';
        $stmt = $connection->prepare($sql);

        $stmt->bindValue(':skuNumber', $skuNumber, PDO::PARAM_STR);

        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

function getMasterTag($customerSONumber)
{
    $connection = connWhitneyUser();
    try {
        $sql = '
    SELECT 
    IFNULL( max(i.masterTagID), 0 ) AS currentMasterTag
    FROM inventory i
        LEFT JOIN shipping s
        ON i.shippingID = s.shippingID
        LEFT JOIN pickpackitems ppi
        ON ppi.customerSONumber = s.customerSONumber
        AND ppi.pickpackitemsID = s.pickpackitemsID
    WHERE ppi.customerSONumber = :customerSONumber ORDER BY i.masterTagID DESC
    ';
        $stmt = $connection->prepare($sql);

        $stmt->bindValue(':customerSONumber', $customerSONumber, PDO::PARAM_STR);

        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data[0];
}

function validateSerial($serialNumber)
{
    $connection = connWhitneyUser();
    try {
        $sql = 'SELECT inventoryID FROM inventory WHERE inventoryID = :serialNumber;';
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':serialNumber', $serialNumber, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}











function checkInv($serialNumber)
{
    $connection = connWhitneyUser();
    try {
        $sql = 'SELECT q.inventoryQuantity
        FROM inventory i
        LEFT JOIN (SELECT inv.inventoryID, inv.inventoryQuantity, inv.creationDate
                       FROM inventoryunitquantity inv
                       INNER JOIN(SELECT inventoryID, max(inventoryQuantityID) inventoryQuantityID
                                   FROM inventoryunitquantity
                                   GROUP BY inventoryID) ss
                       ON inv.inventoryID = ss.inventoryID AND inv.inventoryQuantityID = ss.inventoryQuantityID) q
        ON i.inventoryID = q.inventoryID
        WHERE i.inventoryID = :serialNumber';
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':serialNumber', $serialNumber, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetch();
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return !$data ? false : $data[0];
    // return $data;
}

function getPickedPallets($skuNumber, $shippingID)
{
    $connection = connWhitneyUser();
    try {
        $sql = 'select 
        inventoryID
        from inventory
        where shippingID = :shippingID
        AND skuNumber = :skuNumber';
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':shippingID', $shippingID, PDO::PARAM_INT);
        $stmt->bindParam(':skuNumber', $skuNumber, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetch();
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

function getInventoryData($inventoryID, $customerSONumber)
{
    $connection = connWhitneyUser();
    try {
        $sql = '
        SELECT i.skuNumber
        , itf.partNumber
        , i.companyID
        , i.combineToID
        , i.splitfromID
        , i.shippingID AS serialShippingID
        , i.masterTagID
        , i.phyInventoryUnitStatusID
        , ppi.ordered
        , ppi.pickPackItemsID
        , s.shippingID
        , pph.active AS headerStatus
        , i.active
        FROM inventory i
        LEFT JOIN pickpackheader pph
            ON pph.customerSONumber = :customerSONumber
        LEFT JOIN itemsf itf
            ON itf.skuNumber = i.skuNumber
            AND itf.skuNumber = i.skuNumber
        LEFT JOIN pickpackitems ppi
            ON ppi.item = i.skuNumber
            AND ppi.customerSONumber = :customerSONumber
        LEFT JOIN shipping s
            ON i.shippingID = s.shippingID
            AND ppi.customerSONumber = :customerSONumber
        WHERE i.inventoryID = :inventoryID
        ';
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':inventoryID', $inventoryID, PDO::PARAM_INT);
        $stmt->bindParam(':customerSONumber', $customerSONumber, PDO::PARAM_STR);
        $stmt->execute();
        $data = $stmt->fetch();
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

function getOrderStatus($customerSONumber)
{
    $connection = connWhitneyUser();
    try {
        $sql = '
        SELECT active FROM pickPackHeader where customerSONumber = :customerSONumber
        ';
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':customerSONumber', $customerSONumber, PDO::PARAM_STR);
        $stmt->execute();
        $data = $stmt->fetch();
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data[0];
}
