<?php

require $_SERVER['DOCUMENT_ROOT'] . '/library/library.php';
require $_SERVER['DOCUMENT_ROOT'] . '/library/model.php';


function getInvItemStatus($serialID) {
    $connection = connWhitneyUser();
    /*
     * Modified SQL to use max ID rather than max creationDate
     */
    try {
     $sql = 'SELECT inventoryStateID FROM inventorystatus WHERE inventoryID = :serialID';

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

function getSerialData($serialID){
    $connection = connWhitneyUser();
    /*
     * Modified SQL to use max ID rather than max creationDate
     */
    try {
     $sql = 'SELECT i.inventoryID
        , pi.physicalInventoryID
        , q.inventoryQuantity
        , b.binlocationID
        , b.binName
        , pi.binlocationID
        , bb.binName AS writeIn
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
        LEFT JOIN (SELECT inv.inventoryID, inv.inventoryQuantity, inv.creationDate
                    FROM inventoryunitquantity inv
                    INNER JOIN(SELECT inventoryID, max(inventoryQuantityID) inventoryQuantityID
                                FROM inventoryunitquantity
                                GROUP BY inventoryID) ss
                    ON inv.inventoryID = ss.inventoryID AND inv.inventoryQuantityID = ss.inventoryQuantityID) q
        ON i.inventoryID = q.inventoryID
        LEFT JOIN physicalinventory pi
        ON pi.inventoryID = i.inventoryID
        LEFT JOIN `binlocation` bb
        ON pi.binlocationID = bb.binlocationID
        WHERE pi.inventoryID = :serialID';

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


// Get serial data
function getInventoryData($serialID) {
    $connection = connWhitneyUser();
    /*
     * Modified SQL to use max ID rather than max creationDate
     */
    try {
     $sql = 'SELECT i.inventoryID, b.binlocationID, q.inventoryQuantity, i.skuNumber, i.companyID
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
LEFT JOIN (SELECT inv.inventoryID, inv.inventoryQuantity, inv.creationDate
            FROM inventoryunitquantity inv
               INNER JOIN(SELECT inventoryID, max(inventoryQuantityID) inventoryQuantityID
                           FROM inventoryunitquantity
                           GROUP BY inventoryID) ss
               ON inv.inventoryID = ss.inventoryID AND inv.inventoryQuantityID = ss.inventoryQuantityID) q
ON i.inventoryID = q.inventoryID
WHERE i.inventoryID = :serialID';
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


function validateCatCust($serialID){
    $connection = connWhitneyUser();
    /*
     * Modified SQL to use max ID rather than max creationDate
     */
    try {
     $sql = 'SELECT c.inventoryID
        FROM `inventory` c
        LEFT JOIN itemsf itf
        ON c.skuNumber = itf.skuNumber
        WHERE c.inventoryID = :serialID';

 if($_SESSION['cats']){
     $sql .= ' AND (' . $_SESSION['cats'] . ')';
 }

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
