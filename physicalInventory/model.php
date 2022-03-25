<?php

require $_SERVER['DOCUMENT_ROOT'] . '/library/library.php';
require $_SERVER['DOCUMENT_ROOT'] . '/library/model.php';


// Get serial data
function getPhyInvData($type) {
    $connection = connWhitneyUser();
    
    
    try {
     $sql = 'SELECT
     y.skuNumber
     , y.Counted
     , y.QOH
     , y.Discrepancy
     , y.DDiscrepancy
     , y.physicalInventoryStatus
     , y.DESC1
     , y.DESC2
     , y.physicalInventoryStatusID
     , y.userName
     , y.userID
     FROM
     (SELECT
     itm.skuNumber
     , IFNULL(x.Counted, 0) AS Counted
     , FLOOR(itf.QOH2) AS QOH
     , IFNULL(x.Discrepancy, 0 - itf.QOH2) AS Discrepancy
     , IFNULL(x.DDiscrepancy, (0 - itf.QOH2) * (itf.SCOST / itf.PFCTR)) AS DDiscrepancy
     , phys.physicalInventoryStatus
     , itf.DESC1
     , itf.DESC2
     , phys.physicalInventoryStatusID
     , x.userName
     , x.userID
     FROM items itm
     LEFT JOIN
         (SELECT 
         inv.skuNumber
           , IFNULL(SUM(phy.physicalInventoryQuantity), 0) AS Counted
           , FLOOR(IFNULL(SUM(phy.physicalInventoryQuantity), 0) - itf.QOH2) AS Discrepancy
           , FLOOR((IFNULL(SUM(phy.physicalInventoryQuantity), 0) - itf.QOH2) * (itf.SCOST / itf.PFCTR)) AS DDiscrepancy
           , u.userName
           , u.userID
         FROM inventory inv
         LEFT JOIN physicalInventory phy
         ON phy.inventoryID = inv.inventoryID
         LEFT JOIN itemsf itf
         ON itf.skuNumber = inv.skuNumber
         LEFT JOIN items itm
         ON itm.skuNumber = itf.skuNumber
         LEFT JOIN users u
         ON itm.phyInventoryRecountUserID = u.userID
         WHERE itm.phyInventorySkuStatusID = :type AND inv.phyInventoryUnitStatusID != 0 AND inv.phyInventoryUnitStatusID != 11
         GROUP BY itf.skuNumber) x
         ON itm.skuNumber = x.skuNumber
     LEFT JOIN itemsf itf
     ON itm.skuNumber = itf.skuNumber
     LEFT JOIN physicalinventorystatus phys
     ON phys.physicalInventoryStatusID = itm.phyInventorySkuStatusID
     LEFT JOIN (SELECT DISTINCT companyID, skuNumber FROM inventory) c
     ON itm.skuNumber = c.skuNumber';
      
    //  13473

     if($type == 0){
     $sql .= ' WHERE (itf.QOH2 > 0 AND itm.phyInventorySkuStatusID = :type ';
             if($_SESSION['cats']){
                 $sql .= 'AND (' . $_SESSION['cats'] . ')';
             }
     $sql .= ')';
    }else{
        $sql .= ' WHERE ((x.counted > 0 OR itf.QOH2 > 0) AND itm.phyInventorySkuStatusID = :type) OR itm.phyInventorySkuStatusID = :type';
    }

    $sql .= ') y GROUP BY 
            y.skuNumber
            , y.Counted
            , y.QOH
            , y.Discrepancy
            , y.DDiscrepancy
            , y.physicalInventoryStatus
            , y.DESC1
            , y.DESC2
            , y.physicalInventoryStatusID
            , y.userName
            , y.userID';

        $stmt = $connection->prepare($sql);
        $stmt->bindValue(':type', $type, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

// Get serial data
function getSkuPhyInvData($skuNumber) {
    $connection = connWhitneyUser();
    try {
     $sql = 'SELECT
        itf.skuNumber
        , IFNULL(x.Counted, 0) AS Counted
        , FLOOR(itf.QOH2) AS QOH
        , IFNULL(x.Discrepancy, 0) AS Discrepancy
        , IFNULL(x.DDiscrepancy, 0) AS DDiscrepancy
        , x.physicalInventoryStatus
        , itf.DESC1
        , itf.DESC2
        , x.physicalInventoryStatusID
        , x.userName
        , x.userID
        , x.batchNumber
        FROM itemsf itf
        LEFT JOIN
        (SELECT
        inv.skuNumber
        , IFNULL(SUM(phy.physicalInventoryQuantity), 0) AS Counted
        , FLOOR(IFNULL(SUM(phy.physicalInventoryQuantity), 0) - itf.QOH2) AS Discrepancy
        , FLOOR((IFNULL(SUM(phy.physicalInventoryQuantity), 0) - itf.QOH2) * (itf.SCOST / itf.PFCTR)) AS DDiscrepancy
        , phys.physicalInventoryStatus
        , phys.physicalInventoryStatusID
        , u.userName
        , u.userID
        , inv.batchNumber
        FROM inventory inv
        LEFT JOIN physicalInventory phy
        ON phy.inventoryID = inv.inventoryID
        LEFT JOIN itemsf itf
        ON itf.skuNumber = inv.skuNumber
        LEFT JOIN items itm
        ON itm.skuNumber = itf.skuNumber
        LEFT JOIN physicalinventorystatus phys
        ON phys.physicalInventoryStatusID = itm.phyInventorySkuStatusID
        LEFT JOIN users u
        ON itm.phyInventoryRecountUserID = u.userID
        WHERE inv.skuNumber = :skuNumber  AND inv.phyInventoryUnitStatusID != 0 AND inv.phyInventoryUnitStatusID != 11
        GROUP BY inv.skuNumber) x
        ON itf.skuNumber = x.skuNumber
        WHERE itf.skuNumber = :skuNumber;';
        $stmt = $connection->prepare($sql);
        $stmt->bindValue(':skuNumber', $skuNumber, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

function physicalInventoryCount() {
    $connection = connWhitneyUser();
    try {
     $sql = 'SELECT 
            p.physicalInventoryStatusID
            , CASE
                  WHEN i.phyInventorySkuStatusID = 0 THEN 
                        (SELECT
                        COUNT(i.phyInventorySkuStatusID)
                        FROM items i
                        LEFT JOIN itemsf itf
                        ON i.skuNumber = itf.skuNumber
                        LEFT JOIN (SELECT DISTINCT companyID, skuNumber FROM inventory) c
                        ON i.skuNumber = c.skuNumber
                        WHERE i.phyInventorySkuStatusID = 0 AND itf.QOH2 > 0 ';
                        if($_SESSION['cats']){
                            $sql .= 'AND (' . $_SESSION['cats'] . ')';
                        }
                            $sql .= ')
                  ELSE
                        COUNT(i.phyInventorySkuStatusID) 
                  END  AS Count
            FROM physicalInventoryStatus p
            LEFT JOIN items i
            ON p.physicalInventoryStatusID = i.phyInventorySkuStatusID
            WHERE physicalInventoryStatusID < 11 
            GROUP BY physicalInventoryStatusID;';
        $stmt = $connection->prepare($sql);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}


function updateSkuInvStatus($status, $skuNumber){
    $connection = connWhitneyUser();
 try {
    $sql = 'UPDATE items SET 
            phyInventorySkuStatusID = :status
            WHERE skuNumber = :skuNumber';
    $stmt = $connection->prepare($sql);
    $stmt->bindParam(':status', $status, PDO::PARAM_INT);
    $stmt->bindParam(':skuNumber', $skuNumber, PDO::PARAM_INT);
    $stmt->execute();
    $updateRow = $stmt->rowCount();
    $stmt->closeCursor();
 } catch (PDOException $ex) {
        return FALSE;
    }
    return $updateRow;
}

function updateRecountAssignment($skuNumber, $userID){
    $connection = connWhitneyUser();
 try {
    $sql = 'UPDATE items SET 
            phyInventoryRecountUserID = :userID
            WHERE skuNumber = :skuNumber';
    $stmt = $connection->prepare($sql);
    $stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
    $stmt->bindParam(':skuNumber', $skuNumber, PDO::PARAM_INT);
    $stmt->execute();
    $updateRow = $stmt->rowCount();
    $stmt->closeCursor();
 } catch (PDOException $ex) {
        return FALSE;
    }
    return $updateRow;
}

function getSkuDetails($sku) {
    $connection = connWhitneyUser();
    
    /*
     * Modified SQL to use max ID rather than max creationDate
     */
    try {
     $sql = 'SELECT i.inventoryID
        , pi.physicalInventoryID
        , i.skuNumber
        , pi.physicalInventoryQuantity
        , b.binlocationID
        , b.binName
        , st.stateName
        , u.userName
        , pi.binlocationID
        , bb.binName AS writeIn
        , i.active
        , pst.physicalInventoryStatus
        , i.phyInventoryUnitStatusID
        , pir.originalQuantity
        , pir.recountQuantity
        , us.userName
        , q.inventoryQuantity
        , i.batchNumber
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
LEFT JOIN inventorystate st
ON s.inventoryStateID = st.inventoryStateID
LEFT JOIN physicalinventory pi
ON pi.inventoryID = i.inventoryID
LEFT JOIN users u
ON pi.countedBy = u.userID
LEFT JOIN `binlocation` bb
ON pi.binlocationID = bb.binlocationID
LEFT JOIN physicalinventorystatus pst
ON i.phyInventoryUnitStatusID = pst.physicalInventoryStatusID
LEFT JOIN physicalInventoryRecount pir
ON i.inventoryID = pir.inventoryID
LEFT JOIN users us
ON pir.countedBy = us.userID
WHERE i.skuNumber = :sku 
AND (pi.physicalInventoryID IS NOT NULL 
    OR Active = TRUE 
    OR s.inventoryStateID = 1 
    OR s.inventoryStateID = 4 
    OR s.inventoryStateID = 5)
ORDER BY i.phyInventoryUnitStatusID ASC;';

     
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

function getSerialDetails($serialID) {
    $connection = connWhitneyUser();
    /*
     * Modified SQL to use max ID rather than max creationDate
     */
    try {
     $sql = 'SELECT i.inventoryID
         , pi.physicalInventoryID
         , i.skuNumber
         , pi.physicalInventoryQuantity
         , b.binlocationID
         , b.binName
         , st.stateName
         , u.userName
         , pi.binlocationID
         , bb.binName AS writeIn
         , i.active
         , pst.physicalInventoryStatus
         , i.phyInventoryUnitStatusID
         , pir.originalQuantity
         , pir.recountQuantity
         , us.userName
         , q.inventoryQuantity
         , i.batchNumber
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
LEFT JOIN inventorystate st
ON s.inventoryStateID = st.inventoryStateID
LEFT JOIN physicalinventory pi
ON pi.inventoryID = i.inventoryID
LEFT JOIN users u
ON pi.countedBy = u.userID
LEFT JOIN `binlocation` bb
ON pi.binlocationID = bb.binlocationID
LEFT JOIN physicalinventorystatus pst
ON i.phyInventoryUnitStatusID = pst.physicalInventoryStatusID
LEFT JOIN physicalInventoryRecount pir
ON i.inventoryID = pir.inventoryID
LEFT JOIN users us
ON pir.countedBy = us.userID
WHERE i.inventoryID = :serialID
AND (pi.physicalInventoryID IS NOT NULL 
    OR Active = TRUE 
    OR s.inventoryStateID = 1 
    OR s.inventoryStateID = 4 
    OR s.inventoryStateID = 5)
ORDER BY i.phyInventoryUnitStatusID DESC;';

     
        $stmt = $connection->prepare($sql);
        $stmt->bindValue(':serialID', $serialID, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}


function updateCountStatus($serialID, $statusID){
    $connection = connWhitneyUser();
 try {
    $sql = 'UPDATE inventory SET 
            phyInventoryUnitStatusID = :statusID
            WHERE inventoryID = :serialID';
    $stmt = $connection->prepare($sql);
    $stmt->bindParam(':statusID', $statusID, PDO::PARAM_INT);
    $stmt->bindParam(':serialID', $serialID, PDO::PARAM_INT);
    $stmt->execute();
    $updateRow = $stmt->rowCount();
    $stmt->closeCursor();
 } catch (PDOException $ex) {
        return FALSE;
    }
    return $updateRow;
}

function recountExtData($serialID) {
    $connection = connWhitneyUser();
    /*
     * Modified SQL to use max ID rather than max creationDate
     */
    try {
        $sql = 'SELECT
                inv.inventoryID
                , inv.skuNumber
                , inv.phyInventoryUnitStatusID
                , q.inventoryQuantity
                , pir.*
                , u.userName
                FROM inventory inv
                LEFT JOIN physicalInventoryRecount pir
                ON inv.inventoryID = pir.inventoryID
                LEFT JOIN (SELECT inv.inventoryID, inv.inventoryQuantity, inv.creationDate
                    FROM inventoryunitquantity inv
                    INNER JOIN(SELECT inventoryID, max(inventoryQuantityID) inventoryQuantityID
                                FROM inventoryunitquantity
                                GROUP BY inventoryID) ss
                    ON inv.inventoryID = ss.inventoryID AND inv.inventoryQuantityID = ss.inventoryQuantityID) q
                ON inv.inventoryID = q.inventoryID
                LEFT JOIN users u
                ON pir.countedBy = u.userID
                WHERE inv.inventoryID = :serialID;';
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':serialID', $serialID, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}


function insertRecount($serialID
                    , $newQty
                    , $orgQty
                    , $userID) {
    $connection = connWhitneyUser();
    try {
        $sql = 'INSERT INTO physicalInventoryRecount(   
                                    inventoryID
                                    , originalQuantity
                                    , recountQuantity
                                    , countedBy)
                VALUES (              :serialID
                                    , :orgQty
                                    , :newQty
                                    , :userID)';
        
        $sql2 = 'UPDATE inventory SET phyInventoryUnitStatusID = 4
                WHERE inventoryID = :serialID';
        
        $connection->beginTransaction();
        
        $stmt1 = $connection->prepare($sql);
        $stmt1->bindParam(':serialID', $serialID, PDO::PARAM_INT);
        $stmt1->bindParam(':newQty', $newQty, PDO::PARAM_INT);
        $stmt1->bindParam(':orgQty', $orgQty, PDO::PARAM_INT);
        $stmt1->bindParam(':userID', $userID, PDO::PARAM_INT);
        $stmt1->execute();
        $insertRow = $stmt1->rowCount();
        $stmt1->closeCursor();
        
        $stmt2 = $connection->prepare($sql2);
        $stmt2->bindParam(':serialID', $serialID, PDO::PARAM_INT);
        $stmt2->execute();
        $updateRow = $stmt2->rowCount();
        $stmt2->closeCursor();
        
    } catch (PDOException $ex) {
        $connection->rollBack();
        return FALSE;
    }
    $connection->commit();
    return $insertRow;
}


function updateRecount($serialID, $newQty, $userID){
    $connection = connWhitneyUser();
 try {
    $sql = 'UPDATE physicalInventoryRecount SET 
            recountQuantity = :newQty,
            countedBy = :userID
            WHERE inventoryID = :serialID';
    $stmt = $connection->prepare($sql);
        $stmt->bindParam(':serialID', $serialID, PDO::PARAM_INT);
        $stmt->bindParam(':newQty', $newQty, PDO::PARAM_INT);
        $stmt->bindParam(':userID', $userID, PDO::PARAM_STR);
    $stmt->execute();
    $updateRow = $stmt->rowCount();
    $stmt->closeCursor();
    
 } catch (PDOException $ex) {
        return FALSE;
    }
    
    return $updateRow;
}


function validateRecountSubmit($skuNumber) {
    $connection = connWhitneyUser();
    try {
        $sql = 'SELECT COUNT(*) FROM inventory WHERE skuNumber = :skuNumber AND active = TRUE AND phyInventoryUnitStatusID = 0;';
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':skuNumber', $skuNumber, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}



function updateQuantity($serialID, $recountQty){
    $connection = connWhitneyUser();
 try {
    $sql = 'UPDATE physicalInventory SET 
            physicalInventoryQuantity = :newQty
            WHERE inventoryID = :serialID';
    $stmt = $connection->prepare($sql);
        $stmt->bindParam(':serialID', $serialID, PDO::PARAM_INT);
        $stmt->bindParam(':newQty', $recountQty, PDO::PARAM_INT);
    $stmt->execute();
    $updateRow = $stmt->rowCount();
    $stmt->closeCursor();
 } catch (PDOException $ex) {
        return FALSE;
    }
    return $updateRow;
}

function updateWriteIn($serialID, $writeIn){
    $connection = connWhitneyUser();
 try {
    $sql = 'UPDATE physicalInventory SET 
            binlocationID = :writeIn
            WHERE inventoryID = :serialID';
    $stmt = $connection->prepare($sql);
        $stmt->bindParam(':serialID', $serialID, PDO::PARAM_INT);
        $stmt->bindParam(':writeIn', $writeIn, PDO::PARAM_INT);
    $stmt->execute();
    $updateRow = $stmt->rowCount();
    $stmt->closeCursor();
 } catch (PDOException $ex) {
        return FALSE;
    }
    return $updateRow;
}


function getRecount($serialID) {
    $connection = connWhitneyUser();
    try {
        $sql = 'SELECT * FROM physicalInventoryRecount WHERE inventoryID = :serialID;';
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':serialID', $serialID, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

function getPhysicalInventoryEntry($serialID) {
    $connection = connWhitneyUser();
    try {
        $sql = 'SELECT * FROM physicalInventory WHERE inventoryID = :serialID;';
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':serialID', $serialID, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}


function getStatusChangeCounted($invDate) {
    $connection = connWhitneyUser();
    /*
     * Modified SQL to use max ID rather than max creationDate
     */
    try {
        $sql = 'SELECT count(*)
FROM `inventory` i
LEFT JOIN (SELECT inv.inventoryID, inv.inventoryStateID, inv.creationDate
            FROM inventorystatus inv
               INNER JOIN(SELECT inventoryID, max(inventoryStatusID) inventoryStatusID
                           FROM inventorystatus
                           GROUP BY inventoryID) ss
               ON inv.inventoryID = ss.inventoryID AND inv.inventoryStatusID = ss.inventoryStatusID) s
ON i.inventoryID = s.inventoryID
LEFT JOIN physicalinventory pi
ON pi.inventoryID = i.inventoryID
LEFT JOIN itemsf itf
ON i.skuNumber = itf.skuNumber
LEFT JOIN (SELECT DISTINCT companyID, skuNumber FROM inventory) c
ON i.skuNumber = c.skuNumber
WHERE (s.creationDate < :invDate OR s.creationDate IS NULL) 
AND (i.phyInventoryUnitStatusID = 0 OR i.phyInventoryUnitStatusID = 11) 
AND (i.active = TRUE OR (s.inventoryStateID != 2 AND s.inventoryStateID != 3 ))';
        if($_SESSION['cats']){
                            $sql .= 'AND (' . $_SESSION['cats'] . ')';
                        }
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':invDate', $invDate, PDO::PARAM_STR);
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

function getStatusChangeCounted2($invDate) {
    $connection = connWhitneyUser();
    /*
     * Modified SQL to use max ID rather than max creationDate
     */
    try {
        $sql = 'SELECT count(*)
FROM `inventory` i
LEFT JOIN (SELECT inv.inventoryID, inv.inventoryStateID, inv.creationDate
            FROM inventorystatus inv
               INNER JOIN(SELECT inventoryID, max(inventoryStatusID) inventoryStatusID
                           FROM inventorystatus
                           GROUP BY inventoryID) ss
               ON inv.inventoryID = ss.inventoryID AND inv.inventoryStatusID = ss.inventoryStatusID) s
ON i.inventoryID = s.inventoryID
LEFT JOIN physicalinventory pi
ON pi.inventoryID = i.inventoryID
WHERE (s.creationDate < :invDate OR s.creationDate IS NULL) 
AND (i.phyInventoryUnitStatusID != 0 AND i.phyInventoryUnitStatusID != 11) 
AND (s.inventoryStateID != 4);';
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':invDate', $invDate, PDO::PARAM_STR);
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

function getBinChangeCount($invDate) {
    $connection = connWhitneyUser();
    /*
     * Modified SQL to use max ID rather than max creationDate
     */
    try {
        $sql = 'SELECT COUNT(*)
            FROM `inventory` i
            LEFT JOIN (SELECT inv.inventoryID, inv.binLocationID, inv.creationDate
                        FROM inventorylocation inv
                            INNER JOIN (SELECT inventoryID, max(inventoryLocationID) inventoryLocationID
                            FROM inventorylocation
                            GROUP BY inventoryID) ss
                        ON inv.inventoryID = ss.inventoryID AND inv.inventoryLocationID = ss.inventoryLocationID) l
            ON i.inventoryID = l.inventoryID
            LEFT JOIN `binlocation` b
            ON l.binlocationID = b.binlocationID
            LEFT JOIN physicalinventory pi
            ON pi.inventoryID = i.inventoryID
            WHERE pi.binLocationID IS NOT NULL AND (pi.binLocationID != l.binLocationID OR l.binlocationID IS NULL) AND (l.creationDate < :invDate OR l.creationDate IS NULL)';

        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':invDate', $invDate, PDO::PARAM_STR);
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}


function getQtyChangeCount($invDate) {
    $connection = connWhitneyUser();
    /*
     * Modified SQL to use max ID rather than max creationDate
     */
    try {
        $sql = 'SELECT count(*)
        FROM `inventory` i
        LEFT JOIN (SELECT inv.inventoryID, inv.inventoryQuantity, inv.creationDate
                    FROM inventoryunitquantity inv
                    INNER JOIN(SELECT inventoryID, max(inventoryQuantityID) inventoryQuantityID
                                FROM inventoryunitquantity
                                GROUP BY inventoryID) ss
                    ON inv.inventoryID = ss.inventoryID AND inv.inventoryQuantityID = ss.inventoryQuantityID) q
        ON i.inventoryID = q.inventoryID
        LEFT JOIN physicalinventory pi
        ON pi.inventoryID = i.inventoryID
        LEFT JOIN physicalInventoryRecount r
        ON i.inventoryID = r.inventoryID
        WHERE (q.creationDate < :invDate OR q.creationDate IS NULL) AND r.recountQuantity != q.inventoryQuantity AND r.originalQuantity IS NOT NULL';
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':invDate', $invDate, PDO::PARAM_STR);
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}


function getStatusChangeLost($invDate) {
    $connection = connWhitneyUser();
    /*
     * Modified SQL to use max ID rather than max creationDate
     */
    try {
        $sql = 'SELECT i.inventoryID
FROM `inventory` i
LEFT JOIN (SELECT inv.inventoryID, inv.inventoryStateID, inv.creationDate
            FROM inventorystatus inv
               INNER JOIN(SELECT inventoryID, max(inventoryStatusID) inventoryStatusID
                           FROM inventorystatus
                           GROUP BY inventoryID) ss
               ON inv.inventoryID = ss.inventoryID AND inv.inventoryStatusID = ss.inventoryStatusID) s
ON i.inventoryID = s.inventoryID
LEFT JOIN physicalinventory pi
ON pi.inventoryID = i.inventoryID
LEFT JOIN itemsf itf
ON i.skuNumber = itf.skuNumber
LEFT JOIN (SELECT DISTINCT companyID, skuNumber FROM inventory) c
ON i.skuNumber = c.skuNumber
WHERE (s.creationDate < :invDate OR s.creationDate IS NULL) 
AND (i.phyInventoryUnitStatusID = 0 OR i.phyInventoryUnitStatusID = 11) 
AND (i.active = TRUE OR (s.inventoryStateID != 2 AND s.inventoryStateID != 3 ))';
        
    if($_SESSION['cats']){
        $sql .= ' AND (' . $_SESSION['cats'] . ')';
    }
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':invDate', $invDate, PDO::PARAM_STR);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}


function getStatusChangeFound($invDate) {
    $connection = connWhitneyUser();
    /*
     * Modified SQL to use max ID rather than max creationDate
     */
    try {
        $sql = 'SELECT i.inventoryID
FROM `inventory` i
LEFT JOIN (SELECT inv.inventoryID, inv.inventoryStateID, inv.creationDate
            FROM inventorystatus inv
               INNER JOIN(SELECT inventoryID, max(inventoryStatusID) inventoryStatusID
                           FROM inventorystatus
                           GROUP BY inventoryID) ss
               ON inv.inventoryID = ss.inventoryID AND inv.inventoryStatusID = ss.inventoryStatusID) s
ON i.inventoryID = s.inventoryID
LEFT JOIN physicalinventory pi
ON pi.inventoryID = i.inventoryID
WHERE (s.creationDate < :invDate OR s.creationDate IS NULL)
AND (i.phyInventoryUnitStatusID != 0 AND i.phyInventoryUnitStatusID != 11) 
AND (s.inventoryStateID != 4);';
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':invDate', $invDate, PDO::PARAM_STR);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}


function getWriteIn($invDate) {
    $connection = connWhitneyUser();
    /*
     * Modified SQL to use max ID rather than max creationDate
     */
    try {
        $sql = 'SELECT i.inventoryID
, l.binlocationID AS ORG_LOC
, pi.binlocationID AS WRITE_IN
, l.creationDate
FROM `inventory` i
LEFT JOIN (SELECT inv.inventoryID, inv.binLocationID, inv.creationDate
            FROM inventorylocation inv
                   INNER JOIN (SELECT inventoryID, max(inventoryLocationID) inventoryLocationID
                   FROM inventorylocation
                   GROUP BY inventoryID) ss
               ON inv.inventoryID = ss.inventoryID AND inv.inventoryLocationID = ss.inventoryLocationID) l
ON i.inventoryID = l.inventoryID
LEFT JOIN `binlocation` b
ON l.binlocationID = b.binlocationID
LEFT JOIN physicalinventory pi
ON pi.inventoryID = i.inventoryID
WHERE pi.binLocationID IS NOT NULL AND (pi.binLocationID != l.binLocationID OR l.binlocationID IS NULL) AND (l.creationDate < :invDate OR l.creationDate IS NULL)';
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':invDate', $invDate, PDO::PARAM_STR);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}


function getQtyChange($invDate) {
    $connection = connWhitneyUser();
    /*
     * Modified SQL to use max ID rather than max creationDate
     */
    try {
        $sql = 'SELECT i.inventoryID
        , r.recountQuantity
        FROM `inventory` i
        LEFT JOIN (SELECT inv.inventoryID, inv.inventoryQuantity, inv.creationDate
                    FROM inventoryunitquantity inv
               INNER JOIN(SELECT inventoryID, max(inventoryQuantityID) inventoryQuantityID
                           FROM inventoryunitquantity
                           GROUP BY inventoryID) ss
               ON inv.inventoryID = ss.inventoryID AND inv.inventoryQuantityID = ss.inventoryQuantityID) q
        ON i.inventoryID = q.inventoryID
        LEFT JOIN physicalinventory pi
        ON pi.inventoryID = i.inventoryID
        LEFT JOIN physicalInventoryRecount r
        ON i.inventoryID = r.inventoryID
        WHERE (q.creationDate < :invDate OR q.creationDate IS NULL) AND r.recountQuantity != q.inventoryQuantity AND r.originalQuantity IS NOT NULL';
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':invDate', $invDate, PDO::PARAM_STR);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

function setInventoryComplete(){
    $connection = connWhitneyUser();
    try {
       $sql = "UPDATE constants SET constantValue = 0 WHERE constantName = 'physicalInventory'" ;
       $stmt = $connection->prepare($sql);
       $stmt->bindParam(':status', $status, PDO::PARAM_INT);
       $stmt->bindParam(':skuNumber', $skuNumber, PDO::PARAM_INT);
       $stmt->execute();
       $updateRow = $stmt->rowCount();
       $stmt->closeCursor();
    } catch (PDOException $ex) {
           return FALSE;
       }
       return $updateRow;
}

function clearInventory() {
    $connection = connWhitneyAdmin();
    try {
        $sql = 'DELETE FROM physicalInventory;';
        
        $sql2 = 'UPDATE inventory SET phyInventoryUnitStatusID = 0;';
        
        $sql3 = 'UPDATE items SET phyInventorySkuStatusID = 0;';
        
        $sql4 = 'DELETE FROM physicalInventoryRecount;';
        
        $sql5 = "UPDATE constants SET constantValue = NOW() WHERE constantName = 'physicalInventory'";
        
        $connection->beginTransaction();
        
        $stmt1 = $connection->prepare($sql);
        $stmt1->execute();
        $updateRow1 = $stmt1->rowCount();
        $stmt1->closeCursor();
        
        $stmt2 = $connection->prepare($sql2);
        $stmt2->execute();
        $updateRow2 = $stmt2->rowCount();
        $stmt2->closeCursor();
        
        $stmt3 = $connection->prepare($sql3);
        $stmt3->execute();
        $updateRow3 = $stmt3->rowCount();
        $stmt3->closeCursor();
        
        $stmt4 = $connection->prepare($sql4);
        $stmt4->execute();
        $updateRow4 = $stmt4->rowCount();
        $stmt4->closeCursor();
        
        $stmt5 = $connection->prepare($sql5);
        $stmt5->execute();
        $updateRow4 = $stmt5->rowCount();
        $stmt5->closeCursor();
        
    } catch (PDOException $ex) {
        $connection->rollBack();
        return FALSE;
    }
    $connection->commit();
    return TRUE;
}



function getCategoryList(){
    
    $connection = connWhitneyUser();
    try {
     $sql = 'SELECT DISTINCT itf.CAT, IFNULL(c.category, "BLANK")
            FROM itemsf itf 
            LEFT JOIN categories c 
            ON itf.CAT = c.category_code
            WHERE CAT IS NOT NULL AND CAT <> \'\'
            ORDER BY itf.CAT';
        $stmt = $connection->prepare($sql);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
    
}

function insertCats($cats) {
    
    
    $connection = connWhitneyUser();
    try {
        $sql = 'UPDATE constants SET constantValue = "' . $cats . '" WHERE constantID = 26';
                     
        $connection->beginTransaction();
        
        $stmt1 = $connection->prepare($sql);
        $stmt1->bindParam(':cats', $cats, PDO::PARAM_INT);
        $stmt1->execute();
        $insertRow = $stmt1->rowCount();
        $stmt1->closeCursor();
        
    } catch (PDOException $ex) {
        $connection->rollBack();
        return FALSE;
    }
    $connection->commit();
    return $insertRow;
}

function getPhysicalInventoryCat() {
    $connection = connWhitneyUser();
    try{
        $sql = "SELECT
        itm.skuNumber
        , x.Counted AS Counted
        , FLOOR(itf.QOH2) AS QOH
        , IFNULL(x.Discrepancy, 0 - itf.QOH2) AS Discrepancy
        , IFNULL(x.DDiscrepancy, (0 - itf.QOH2) * (itf.SCOST / itf.PFCTR)) AS DDiscrepancy
        , phys.physicalInventoryStatus
        , itf.DESC1
        , itf.DESC2
        , phys.physicalInventoryStatusID
        , x.userName
        , x.userID
        FROM items itm
        LEFT JOIN
            (SELECT 
                inv.skuNumber
                  , IFNULL(SUM(phy.physicalInventoryQuantity), 0) AS Counted
                  , FLOOR(IFNULL(SUM(phy.physicalInventoryQuantity), 0) - itf.QOH2) AS Discrepancy
                  , FLOOR((IFNULL(SUM(phy.physicalInventoryQuantity), 0) - itf.QOH2) * (itf.SCOST / itf.PFCTR)) AS DDiscrepancy
                  , u.userName
                  , u.userID
                FROM inventory inv
                LEFT JOIN physicalInventory phy
                ON phy.inventoryID = inv.inventoryID
                LEFT JOIN itemsf itf
                ON itf.skuNumber = inv.skuNumber
                LEFT JOIN items itm
                ON itm.skuNumber = itf.skuNumber
                LEFT JOIN users u
                ON itm.phyInventoryRecountUserID = u.userID
                WHERE itm.phyInventorySkuStatusID = 5 AND inv.phyInventoryUnitStatusID != 0 AND inv.phyInventoryUnitStatusID != 11
                GROUP BY itf.skuNumber) x
            ON itm.skuNumber = x.skuNumber
      LEFT JOIN itemsf itf
      ON itm.skuNumber = itf.skuNumber
      LEFT JOIN (SELECT DISTINCT companyID, skuNumber FROM inventory) c
      ON x.skuNumber = c.skuNumber
      LEFT JOIN physicalinventorystatus phys
      ON phys.physicalInventoryStatusID = itm.phyInventorySkuStatusID";
        
        if($_SESSION['cats']){
            $sql .= " WHERE (" . $_SESSION['cats'] . ")";
        }
        
        $stmt = $connection->prepare($sql);
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_NUM);
            $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

function resetInventory() {
    $connection = connWhitneyUser();
    try {
        $sql = "UPDATE constants SET constantValue = null WHERE constantID = 14" ;
        $stmt = $connection->prepare($sql);
        $stmt->execute();
        $updateRow = $stmt->rowCount();
        $stmt->closeCursor();
    } catch (PDOException $ex) {
            return FALSE;
        }
        return $updateRow;
}

function getBatchInfo($skuNumber) {

    $connection = connWhitneyUser();
    try {
        $sql = 'SELECT ifnull(y.batchNumber, "-") AS Batch_Number
        , ifNull(x.counted, 0) AS Counted
        , ifNull(y.QOH, 0) AS QOH
        , (ifNull(x.counted, 0) - ifNull(y.QOH, 0)) AS Discrepancy
        FROM
        (SELECT inv.skuNumber
        , inv.batchNumber
        , IFNULL(SUM(phy.physicalInventoryQuantity), 0) AS Counted
        FROM inventory inv
        LEFT JOIN physicalInventory phy
        ON phy.inventoryID = inv.inventoryID
        LEFT JOIN itemsf itf
        ON itf.skuNumber = inv.skuNumber
        LEFT JOIN items itm
        ON itm.skuNumber = itf.skuNumber
        WHERE inv.phyInventoryUnitStatusID != 0 AND inv.phyInventoryUnitStatusID != 11 AND inv.skuNumber = :skuNumber
        GROUP BY inv.skuNumber, inv.batchNumber) x
        RIGHT JOIN (
            SELECT
            inv.skuNumber
            , inv.batchNumber
            , IFNULL(SUM(q.inventoryQuantity), 0) AS QOH
            FROM inventory inv
            LEFT JOIN (SELECT inv.inventoryID, inv.inventoryQuantity, inv.creationDate
                    FROM inventoryunitquantity inv
                    INNER JOIN(SELECT inventoryID, max(inventoryQuantityID) inventoryQuantityID
                                FROM inventoryunitquantity
                                GROUP BY inventoryID) ss
                    ON inv.inventoryID = ss.inventoryID AND inv.inventoryQuantityID = ss.inventoryQuantityID) q
            ON inv.inventoryID = q.inventoryID
            LEFT JOIN (SELECT inv.inventoryID, inv.inventoryStateID
                    FROM inventorystatus inv
                    INNER JOIN(SELECT inventoryID, max(inventoryStatusID) inventoryStatusID
                                FROM inventorystatus
                                GROUP BY inventoryID) ss
                    ON inv.inventoryID = ss.inventoryID AND inv.inventoryStatusID = ss.inventoryStatusID) s
            ON inv.inventoryID = s.inventoryID
            LEFT JOIN itemsf itf
            ON itf.skuNumber = inv.skuNumber
            LEFT JOIN items itm
            ON itm.skuNumber = itf.skuNumber
            WHERE inv.skuNumber = :skuNumber 
                    AND (inv.active = TRUE 
                    OR s.inventoryStateID = 1 
                    OR s.inventoryStateID = 4 
                    OR s.inventoryStateID = 5)
            GROUP BY inv.skuNumber, inv.batchNumber) y
        ON x.skuNumber = y.skuNumber AND x.batchNumber = y.batchNumber';
    
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':skuNumber', $skuNumber, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetchAll();
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}