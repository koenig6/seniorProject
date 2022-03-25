<?php

require $_SERVER['DOCUMENT_ROOT'] . '/library/library.php';
require $_SERVER['DOCUMENT_ROOT'] . '/library/library3.php';
require $_SERVER['DOCUMENT_ROOT'] . '/library/model.php';


// Get serial status
function getDailyShippingReport($date, $status) {
    $connection = connWhitneyUser();
    $dateRange1 = date('Y-m-d', strtotime('-1 day', strtoTime($date)));
    $dateRange2 = date('Y-m-d', strtotime('+1 day', strtoTime($date)));
    /*
     * SQL modified to use max id rather than max creation date
     */
    
    try {
        $sql = 'SELECT i.inventoryID, i.skuNumber, i.batchNumber, i.expirationDate, it.DESC1, q.inventoryQuantity
                FROM `inventory` i
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
                LEFT JOIN itemsf it
                on i.skuNumber = it.skuNumber
                WHERE s.inventoryStateID = :status AND DATE(s.creationDate) > :dateRange1 AND Date(s.creationDate) < :dateRange2
                ORDER BY skuNumber';
        $stmt = $connection->prepare($sql);
        $stmt->bindValue(':dateRange1', $dateRange1, PDO::PARAM_STR);
        $stmt->bindValue(':dateRange2', $dateRange2, PDO::PARAM_STR);
        $stmt->bindValue(':status', $status, PDO::PARAM_STR);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}


function getDailyShippingSummary($date, $status) {
    $connection = connWhitneyUser();
    $dateRange1 = date('Y-m-d', strtotime('-1 day', strtoTime($date)));
    $dateRange2 = date('Y-m-d', strtotime('+1 day', strtoTime($date)));
    /*
     * SQL modified to use max id rather than max creation date
     */
    try {
        $sql = 'SELECT c.companyName, x.pallets, i.skuNumber, it.DESC1, count(i.skuNumber) AS totalPallets, IFNULL(y.bin_qty, 0), IFNULL(y.foresite, 0)
FROM `inventory` i
LEFT JOIN (SELECT inv.inventoryID, inv.inventoryStateID, inv.creationDate
            FROM inventorystatus inv
               INNER JOIN(SELECT inventoryID, max(inventoryStatusID) inventoryStatusID
                           FROM inventorystatus
                           GROUP BY inventoryID) ss
               ON inv.inventoryID = ss.inventoryID AND inv.inventoryStatusID = ss.inventoryStatusID) s
ON i.inventoryID = s.inventoryID
LEFT JOIN customer c
ON i.companyID = c.customerID
LEFT JOIN itemsf it
on i.skuNumber = it.skuNumber
LEFT JOIN (SELECT c.customerID, count(*) AS pallets
                FROM inventory i
                LEFT JOIN customer c
                ON i.companyID = c.customerID
                LEFT JOIN (SELECT inv.inventoryID, inv.inventoryStateID, inv.creationDate
                            FROM inventorystatus inv
                                INNER JOIN(SELECT inventoryID, max(inventoryStatusID) inventoryStatusID
                                            FROM inventorystatus
                                            GROUP BY inventoryID) ss
                                ON inv.inventoryID = ss.inventoryID AND inv.inventoryStatusID = ss.inventoryStatusID) s
                ON i.inventoryID = s.inventoryID
                WHERE s.inventoryStateID = 3 AND DATE(s.creationDate) > :dateRange1 AND Date(s.creationDate) < :dateRange2
                GROUP BY customerID) x
ON i.companyID = x.customerID
LEFT JOIN
        (SELECT
        i.skuNumber 
        , CONCAT(itf.SkuNumber, "-" , itf.cat) AS sku
        , itf.DESC1
        , Sum(q.inventoryQuantity) AS bin_qty
        , (itf.QOH2 - itf.QPK) AS foresite
        , Sum(q.inventoryQuantity) AS bin_qty_sum
        FROM inventory i
        LEFT JOIN (SELECT inv.inventoryID, inv.inventoryQuantity, inv.creationDate
                FROM inventoryunitquantity inv
                 INNER JOIN(SELECT inventoryID, max(inventoryQuantityID) inventoryQuantityID
                             FROM inventoryunitquantity
                             GROUP BY inventoryID) ss
                 ON inv.inventoryID = ss.inventoryID AND inv.inventoryQuantityID = ss.inventoryQuantityID) q
        ON q.inventoryID = i.inventoryID
        LEFT JOIN (SELECT inv.inventoryID, inv.inventoryStateID, inv.creationDate
           FROM inventorystatus inv
           INNER JOIN (SELECT inventoryID, max(inventoryStatusID) inventoryStatusID
                       FROM inventorystatus
                       GROUP BY inventoryID) ss
           ON inv.inventoryID = ss.inventoryID AND inv.inventoryStatusID = ss.inventoryStatusID) s
        ON i.inventoryID = s.inventoryID
        LEFT JOIN itemsf itf
        ON i.skuNumber = itf.skuNumber
        WHERE i.active = true AND s.inventoryStateID != 2 AND s.inventoryStateID != 6 AND s.inventoryStateID != 3
        GROUP BY i.skuNumber) y
ON it.skuNumber = y.skuNumber
WHERE s.inventoryStateID = :status AND DATE(s.creationDate) > :dateRange1 AND Date(s.creationDate) < :dateRange2
GROUP BY i.companyiD, i.skuNumber
ORDER BY c.companyName, i.skuNumber;';
        $stmt = $connection->prepare($sql);
        $stmt->bindValue(':dateRange1', $dateRange1, PDO::PARAM_STR);
        $stmt->bindValue(':dateRange2', $dateRange2, PDO::PARAM_STR);
        $stmt->bindValue(':status', $status, PDO::PARAM_STR);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

function getBillablePalletReport($customerID) {
    $connection = connWhitneyUser();
    /*
     * SQL modified to use max id rather than max creation date
     */
    try {
        $sql = 'SELECT i.skuNumber
                , itf.DESC1
                , itf.partNumber
                , it.rackCharge
                , c.companyName
                , count(i.inventoryID) AS palletCount
                , q.inventoryQuantity
                , (q.inventoryQuantity * count(i.inventoryID)) AS subTotal
                , (itf.QOH2 - itf.QPK) AS QOH
                FROM inventory i
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
                LEFT JOIN items it
                ON i.skuNumber = it.skuNumber
                LEFT JOIN itemsf itf
                ON i.skuNumber = itf.skuNumber
                LEFT JOIN customer c
                ON i.companyID = c.customerID
                WHERE i.companyID = :customerID 
                AND s.inventoryStateID != 2 
                AND s.inventoryStateID != 3 
                AND s.inventoryStateID != 6  
                AND i.active = TRUE 
                AND (NOT palletNumber = "Pending Shipment" OR palletNumber IS NULL)
                GROUP BY i.skuNumber, q.inventoryQuantity';
        $stmt = $connection->prepare($sql);
        $stmt->bindValue(':customerID', $customerID, PDO::PARAM_STR);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

function getBillableSkuTotals($customerID) {
    $connection = connWhitneyUser();
    /*
     * SQL modified to use max id rather than max creation date
     */    
    try {
        $sql = 'SELECT x.skuNumber, sum(x.palletCount) AS totalPallet, sum(x.subTotal) AS totalPieces, (x.rackCharge * sum(x.palletCount)) AS totalCharge, x.rackCharge
                FROM(SELECT i.skuNumber
                , itf.DESC1
                , itf.partNumber
                , it.rackCharge
                , c.companyName
                , count(i.inventoryID) AS palletCount
                , q.inventoryQuantity
                , (q.inventoryQuantity * count(i.inventoryID)) AS subTotal
                FROM inventory i
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
                LEFT JOIN items it
                ON i.skuNumber = it.skuNumber
                LEFT JOIN itemsf itf
                ON i.skuNumber = itf.skuNumber
                LEFT JOIN customer c
                ON i.companyID = c.customerID
                WHERE i.companyID = :customerID AND s.inventoryStateID != 2 
                AND s.inventoryStateID != 3 
                AND s.inventoryStateID != 6 
                AND i.active = TRUE 
                AND (NOT palletNumber = "Pending Shipment" OR palletNumber IS NULL)
                GROUP BY i.skuNumber, q.inventoryQuantity) x
                GROUP BY x.skuNumber';
        $stmt = $connection->prepare($sql);
        $stmt->bindValue(':customerID', $customerID, PDO::PARAM_STR);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

function getBillablePalletBatchReport($customerID) {
    $connection = connWhitneyUser();
    /*
     * SQL modified to use max id rather than max creation date
     */    
    try {
        $sql = 'SELECT i.skuNumber
                , itf.DESC1
                , itf.partNumber
                , it.rackCharge
                , c.companyName
                , count(i.inventoryID) AS palletCount
                , q.inventoryQuantity
                , (q.inventoryQuantity * count(i.inventoryID)) AS subTotal
                , i.batchNumber
                , i.expirationDate
                , i.palletNumber
                FROM inventory i
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
                LEFT JOIN items it
                ON i.skuNumber = it.skuNumber
                LEFT JOIN itemsf itf
                ON i.skuNumber = itf.skuNumber
                LEFT JOIN customer c
                ON i.companyID = c.customerID
                WHERE i.companyID = :customerID 
                AND s.inventoryStateID != 2 
                AND s.inventoryStateID != 3 
                AND s.inventoryStateID != 6 
                AND i.active = TRUE 
                AND (NOT palletNumber = "Pending Shipment" OR palletNumber IS NULL)
                GROUP BY i.skuNumber, q.inventoryQuantity, i.batchNumber, i.expirationDate, i.palletNumber
                ORDER BY i.skuNumber, i.batchNumber, i.palletNumber, i.expirationDate';
        $stmt = $connection->prepare($sql);
        $stmt->bindValue(':customerID', $customerID, PDO::PARAM_STR);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

function getBillablePalletBatchExport($customerID) {
    $connection = connWhitneyUser();
    /*
     * SQL modified to use max id rather than max creation date
     */    
    try {
        $sql = 'SELECT 
                i.skuNumber
                , itf.DESC1
                , itf.partNumber
                , c.companyName
                , count(i.inventoryID) AS palletCount
                , q.inventoryQuantity
                , (q.inventoryQuantity * count(i.inventoryID)) AS subTotal
                , i.batchNumber
                , i.expirationDate
                , i.palletNumber
                FROM inventory i
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
                LEFT JOIN items it
                ON i.skuNumber = it.skuNumber
                LEFT JOIN itemsf itf
                ON i.skuNumber = itf.skuNumber
                LEFT JOIN customer c
                ON i.companyID = c.customerID
                WHERE i.companyID = :customerID 
                AND s.inventoryStateID != 2 
                AND s.inventoryStateID != 3 
                AND s.inventoryStateID != 6 
                AND i.active = TRUE 
                AND (NOT palletNumber = "Pending Shipment" OR palletNumber IS NULL)
                GROUP BY i.skuNumber, q.inventoryQuantity, i.batchNumber, i.expirationDate, i.palletNumber
                ORDER BY i.skuNumber, i.batchNumber, i.palletNumber, i.expirationDate';
        $stmt = $connection->prepare($sql);
        $stmt->bindValue(':customerID', $customerID, PDO::PARAM_STR);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

function getBillableSkuBatchTotals($customerID) {
    $connection = connWhitneyUser();
    /*
     * SQL modified to use max id rather than max creation date
     */    
    try {
        $sql = 'SELECT x.skuNumber, sum(x.palletCount) AS totalPallet, sum(x.subTotal) AS totalPieces, (x.rackCharge * sum(x.palletCount)) AS totalCharge, x.rackCharge
                FROM(SELECT i.skuNumber
                , itf.DESC1
                , itf.partNumber
                , it.rackCharge
                , c.companyName
                , count(i.inventoryID) AS palletCount
                , q.inventoryQuantity
                , (q.inventoryQuantity * count(i.inventoryID)) AS subTotal
                , i.batchNumber
                , i.expirationDate
                , i.palletNumber
                FROM inventory i
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
                LEFT JOIN items it
                ON i.skuNumber = it.skuNumber
                LEFT JOIN itemsf itf
                ON i.skuNumber = itf.skuNumber
                LEFT JOIN customer c
                ON i.companyID = c.customerID
                WHERE i.companyID = :customerID 
                AND s.inventoryStateID != 2 
                AND s.inventoryStateID != 3 
                AND s.inventoryStateID != 6 
                AND i.active = TRUE 
                AND (NOT palletNumber = "Pending Shipment" OR palletNumber IS NULL)
                GROUP BY i.skuNumber, q.inventoryQuantity, i.batchNumber, i.expirationDate, i.palletNumber) x
                GROUP BY x.skuNumber';
        $stmt = $connection->prepare($sql);
        $stmt->bindValue(':customerID', $customerID, PDO::PARAM_STR);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;

}

function getBillableSubTotals($customerID) {
    $connection = connWhitneyUser();
    /*
     * SQL modified to use max id rather than max creation date
     */    
    try {
        $sql = 'SELECT x.rackCharge, count(x.rackCharge), (count(x.rackCharge) * x.rackCharge)
                FROM(SELECT i.skuNumber
                , itf.DESC1
                , itf.partNumber
                , it.rackCharge
                , c.companyName
                , q.inventoryQuantity
                , i.batchNumber
                , i.expirationDate
                , i.palletNumber
                FROM inventory i
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
                LEFT JOIN items it
                ON i.skuNumber = it.skuNumber
                LEFT JOIN itemsf itf
                ON i.skuNumber = itf.skuNumber
                LEFT JOIN customer c
                ON i.companyID = c.customerID
                WHERE i.companyID = :customerID 
                AND s.inventoryStateID != 2 
                AND s.inventoryStateID != 3 
                AND s.inventoryStateID != 6 
                AND i.active = TRUE  
                AND (NOT palletNumber = "Pending Shipment" OR palletNumber IS NULL)) x
                GROUP BY x.rackCharge';
        $stmt = $connection->prepare($sql);
        $stmt->bindValue(':customerID', $customerID, PDO::PARAM_STR);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

function getBillableTotals($customerID) {
    $connection = connWhitneyUser();
    /*
     * SQL modified to use max id rather than max creation date
     */   
    try {
        $sql = 'SELECT count(x.rackCharge), sum(x.rackCharge)
                FROM(SELECT i.skuNumber
                , itf.DESC1
                , itf.partNumber
                , it.rackCharge
                , c.companyName
                , q.inventoryQuantity
                , i.batchNumber
                , i.expirationDate
                , i.palletNumber
                FROM inventory i
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
                LEFT JOIN items it
                ON i.skuNumber = it.skuNumber
                LEFT JOIN itemsf itf
                ON i.skuNumber = itf.skuNumber
                LEFT JOIN customer c
                ON i.companyID = c.customerID
                WHERE i.companyID = :customerID 
                AND s.inventoryStateID != 2 
                AND s.inventoryStateID != 3 
                AND s.inventoryStateID != 6 
                AND i.active = TRUE 
                AND (NOT palletNumber = "Pending Shipment" OR palletNumber IS NULL)) x';
        $stmt = $connection->prepare($sql);
        $stmt->bindValue(':customerID', $customerID, PDO::PARAM_STR);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

// function getBillableCustomers(){
//     $connection = connWhitneyUser();

//     try {
//      $sql = 'SELECT DISTINCT a.customerID, c.companyName
//             FROM customer c
//             LEFT JOIN associatedcustomer a
//             ON a.customerID = c.customerID
//             LEFT JOIN items i
//             ON a.skuNumber = i.skuNumber
//             WHERE i.billablePallet = true
//             ORDER BY c.companyName';
//         $stmt = $connection->prepare($sql);
//         $stmt->execute();
//         $data = $stmt->fetchAll(PDO::FETCH_NUM);
//         $stmt->closeCursor();
//     } catch (PDOException $ex) {
//         return FALSE;
//     }
//     return $data;
// }

function getBillableVendors(){
    $connection = connWhitneyUser();

    try {
     $sql = 'SELECT DISTINCT a.vendorID, v.companyName
            FROM vendor v
            LEFT JOIN associatedvendor a
            ON a.vendorID = v.vendorID
            LEFT JOIN items i
            ON a.skuNumber = i.skuNumber
            WHERE i.billablePallet = true
            ORDER BY v.companyName';
        $stmt = $connection->prepare($sql);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

function getNewSkuNumberData($dateRange1, $dateRange2){
    $connection = connWhitneyUser();
    try {
     $sql = 'SELECT i.skuNumber
            , itf.DESC1
            , i.creationDate
            , i.timeStudy
            , l.paperColor
            , i.palletQty
            , i.component
            , i.kit
            , i.poRequired
            , i.batchRequired
            , i.palletNumberRequired
            , i.billablePallet
            , i.bulkCharge
            , i.rackCharge
            , i.caseCharge
            FROM items i
            LEFT JOIN itemsf itf
            ON i.skuNumber = itf.SkuNumber
            LEFT JOIN labels l
            ON i.paperColorID = l.labelID
            WHERE i.creationDate >= :dateRange1 AND i.creationDate <= :dateRange2';
        $stmt = $connection->prepare($sql);
        $stmt->bindValue(':dateRange1', $dateRange1, PDO::PARAM_STR);
        $stmt->bindValue(':dateRange2', $dateRange2, PDO::PARAM_STR);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
    
}

function getKitSkuNumbers(){
    
    $connection = connWhitneyUser();
    try {
     $sql = 'SELECT DISTINCT skuNumber
            FROM items
            WHERE kit = 1';
        $stmt = $connection->prepare($sql);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
    
}

function getTimeStudyData($skuNumber, $date1 = NULL, $date2 = NULL, $employeeID = NULL, $minQty = NULL){
        $connection = connWhitneyUser();
        
    if ($date1 != null){
        $date1 = "'$date1'";
    }
    
    if ($date2 != null){
        $date2 = "'$date2'";
    }
    try {
     $sql = 'SELECT itf.skuNumber
            , itf.DESC1
            , itf.DESC2
            , i.timeStudy
            , w.workOrderNumber
            , w.completedDate
            , CONCAT(e.firstName, \' \', e.lastName) AS name
            , w.quantityCompleted
            , w.crewCount
            , TIME_FORMAT(SEC_TO_TIME(TIME_TO_SEC(i.timeStudy) * w.quantityCompleted), \'%H:%i\') AS estTime
            , TIME_FORMAT(w.timeCompleted, \'%H:%i\') AS actTime
            , TIME_FORMAT(SEC_TO_TIME(TIME_TO_SEC(w.timeCompleted) / w.quantityCompleted), \'%H:%i:%s\') AS actTS
            , w.active
            FROM workOrderf wf
            LEFT JOIN workorder w
            ON wf.workOrderNumber = w.workOrderNumber
            LEFT JOIN employee e
            ON w.crewLeader = e.employeeID
            LEFT JOIN items i
            ON i.skuNumber = wf.skuNumber
            LEFT JOIN itemsf itf
            ON wf.skuNumber = itf.skuNumber 
            WHERE wf.skuNumber = :skuNumber AND w.complete = 1';
        if($employeeID != NULL){
            $sql .= ' AND w.crewLeader = :employeeID';
        }
        if($date1 != NULL && $date2 != null){
            $sql .= " AND w.completedDate >= $date1 AND w.completedDate <=  $date2";
        }elseif($date1 != null && $date2 == null){
            $sql .= " AND w.completedDate >= $date1";
        }elseif($date1 == null && $date2 != null){
            $sql .= " AND w.completedDate <= $date2";
        }
        
        if($employeeID != null){
            $sql .= " AND w.crewLeader = :employeeID";
        }
        
        if($minQty != null){
            $sql .= " AND w.quantityCompleted >= :minQty";
        }
            
        $stmt = $connection->prepare($sql);
        if($employeeID != NULL){
            $stmt->bindValue(':employeeID', $employeeID, PDO::PARAM_INT);
        }
        if($date1 != NULL){
            //$stmt->bindValue(':date1', $date1, PDO::PARAM_STR);
        }
        if($date2 != NULL){
            //$stmt->bindValue(':date2', $date2, PDO::PARAM_STR);
        }
        if($minQty != NULL){
            $stmt->bindValue(':minQty', $minQty, PDO::PARAM_INT);
        }
        $stmt->bindValue(':skuNumber', $skuNumber, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}


function getTimeStudyTotals($skuNumber, $date1 = NULL, $date2 = NULL, $employeeID = NULL, $minQty = null){
        $connection = connWhitneyUser();
        
    if ($date1 != null){
        $date1 = "'$date1'";
    }
    
    if ($date2 != null){
        $date2 = "'$date2'";
    }
    
    try {
     $sql = 'SELECT TIME_FORMAT(SEC_TO_TIME(AVG(wq.actTS)), \'%H:%i:%s\') avgTS, COUNT(wq.actTS) woCnt, CEIL(AVG(crewCount))
            FROM (SELECT
                    wf.skuNumber
                    , w.crewcount
                    , (TIME_TO_SEC(w.timeCompleted) / w.quantityCompleted) AS actTS
                    , w.active
                    FROM workOrderf wf
                    LEFT JOIN workOrder w
                    ON wf.workOrderNumber = w.workOrderNumber
                    WHERE wf.skuNumber = :skuNumber AND w.complete = 1 AND w.active = 1';
     
            if($employeeID != NULL){
                $sql .= ' AND w.crewLeader = :employeeID';
            }
            if($date1 != NULL && $date2 != null){
                $sql .= " AND w.completedDate >= $date1 AND w.completedDate <=  $date2";
            }elseif($date1 != null && $date2 == null){
                $sql .= " AND w.completedDate >= $date1";
            }elseif($date1 == null && $date2 != null){
                $sql .= " AND w.completedDate <= $date2";
            }

            if($employeeID != null){
                $sql .= " AND w.crewLeader = :employeeID";
            }

            if($minQty != null){
                $sql .= " AND w.quantityCompleted >= :minQty";
            }            
     
        $sql .= ') wq';
        $stmt = $connection->prepare($sql);
        if($employeeID != NULL){
            $stmt->bindValue(':employeeID', $employeeID, PDO::PARAM_INT);
        }
        if($date1 != NULL){
            //$stmt->bindValue(':date1', $date1, PDO::PARAM_STR);
        }
        if($date2 != NULL){
            //$stmt->bindValue(':date2', $date2, PDO::PARAM_STR);
        }
        if($minQty != NULL){
            $stmt->bindValue(':minQty', $minQty, PDO::PARAM_INT);
        }
        $stmt->bindValue(':skuNumber', $skuNumber, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
    
}


function activateWO($workOrderNumber){
    
    $connection = connWhitneyUser();
    try {
     $sql = 'UPDATE workOrder
            SET active = 1
            WHERE workOrderNumber = :workOrderNumber';
        $stmt = $connection->prepare($sql);
        $stmt->bindValue(':workOrderNumber', $workOrderNumber, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
    
}

function deactivateWO($workOrderNumber){
    
    $connection = connWhitneyUser();
    try {
     $sql = 'UPDATE workOrder
            SET active = 0
            WHERE workOrderNumber = :workOrderNumber';
        $stmt = $connection->prepare($sql);
        $stmt->bindValue(':workOrderNumber', $workOrderNumber, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
    
}

function getInventoryDiscrepancy($category, $includeZeros){
    $connection = connWhitneyUser();
    /*
     * SQL modified to use max id rather than max creation date
     */
    try {
     $sql = 'SELECT CONCAT(itf.SkuNumber, "-" , itf.cat) AS sku
            , itf.DESC1
            , IFNULL(x.bin_qty, 0) AS bin_qty
            , (itf.QOH2 - itf.QPK) AS FORESITE
            , CASE WHEN itf.QOH2 - itf.QPK >= 0 THEN (IFNULL(x.bin_qty_sum, 0) - (itf.QOH2 - itf.QPK)) 
            ELSE IFNULL(x.bin_qty_sum, 0) - 0 END AS Balance
            , ((IFNULL(x.bin_qty_sum, 0) - (itf.QOH2 - itf.QPK)) * (itf.SCOST/itf.PFCTR)) AS Dollar
            FROM itemsf itf
            LEFT JOIN
              (SELECT
              i.skuNumber 
              , CONCAT(itf.SkuNumber, "-" , itf.cat) AS sku
              , itf.DESC1
              , Sum(q.inventoryQuantity) AS bin_qty
              , (itf.QOH2 - itf.QPK) AS foresite
              , Sum(q.inventoryQuantity) AS bin_qty_sum
              FROM inventory i
              LEFT JOIN (SELECT inv.inventoryID, inv.inventoryQuantity, inv.creationDate
                      FROM inventoryunitquantity inv
                       INNER JOIN(SELECT inventoryID, max(inventoryQuantityID) inventoryQuantityID
                                   FROM inventoryunitquantity
                                   GROUP BY inventoryID) ss
                       ON inv.inventoryID = ss.inventoryID AND inv.inventoryQuantityID = ss.inventoryQuantityID) q
              ON q.inventoryID = i.inventoryID
              LEFT JOIN (SELECT inv.inventoryID, inv.inventoryStateID
                 FROM inventorystatus inv
                 INNER JOIN (SELECT inventoryID, max(inventoryStatusID) inventoryStatusID
                             FROM inventorystatus
                             GROUP BY inventoryID) ss
                 ON inv.inventoryID = ss.inventoryID AND inv.inventoryStatusID = ss.inventoryStatusID) s
              ON i.inventoryID = s.inventoryID
              LEFT JOIN itemsf itf
              ON i.skuNumber = itf.skuNumber
              WHERE i.active = true AND s.inventoryStateID != 2 AND s.inventoryStateID != 6 AND s.inventoryStateID != 3
              GROUP BY i.skuNumber) x
            ON itf.skuNumber = x.skuNumber ';

            if(isset($category)){
                for($i=0; $i < sizeof($category); $i++){
                    if($i == 0){
                        $sql .= 'WHERE (itf.cat = :' . $category[$i];
                    }else{
                        $sql .= ' OR itf.cat = :' . $category[$i];
                    }
                }
                $sql .= ')';
            }
            
            if(!$includeZeros){
                $sql .= 'AND (IFNULL(x.bin_qty_sum, 0) <> 0 OR (itf.QOH2 - itf.QPK) <> 0)';
            }
            
        $stmt = $connection->prepare($sql);
            if(isset($category)){
                for($i=0; $i < sizeof($category); $i++){
                    $stmt->bindValue(':' . $category[$i], $category[$i], PDO::PARAM_STR);
                }
            }
        
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

function getInventoryDiscrepancyCust(){
    $connection = connWhitneyUser();
    /*
     * SQL modified to use max id rather than max creation date
     */
    try {
     $sql = 'SELECT i.skuNumber
         , itf.DESC1, Sum(q.inventoryQuantity) AS BINQTY
         , (itf.QOH2 - itf.QPK) AS FORESITE
         , (Sum(q.inventoryQuantity) - (itf.QOH2 - itf.QPK)) AS Balance
         , ((Sum(q.inventoryQuantity) - (itf.QOH2 - itf.QPK)) * (SCOST/PFCTR)) AS Dollar
            FROM inventory i
            LEFT JOIN (SELECT inv.inventoryID, inv.inventoryQuantity, inv.creationDate
                    FROM inventoryunitquantity inv
                     INNER JOIN(SELECT inventoryID, max(inventoryQuantityID) inventoryQuantityID
                                 FROM inventoryunitquantity
                                 GROUP BY inventoryID) ss
                     ON inv.inventoryID = ss.inventoryID AND inv.inventoryQuantityID = ss.inventoryQuantityID) q
            ON q.inventoryID = i.inventoryID
            LEFT JOIN itemsf itf
            ON i.skuNumber = itf.skuNumber
            WHERE i.active = true AND itf.cat = \'F75\'
            GROUP BY i.skuNumber
            ORDER BY Dollar';
        $stmt = $connection->prepare($sql);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

function getWhosIn($temps = false){
    
    $connection = connWhitneyUser();
    try {
     $sql = 'SELECT CONCAT(e.firstName, \' \', e.lastName, \' @ \', tcp.punchIn)
            FROM timeClockPunch tcp
            LEFT JOIN employee e
            ON tcp.employeeID = e.employeeID
            WHERE punchOut IS NULL AND punchDate >= CURDATE() ';
            if($temps != false) {
                $sql .= 'AND e.tempEmployeeID IS NULL ';
            }
            $sql .= 'ORDER BY e.firstName';
        $stmt = $connection->prepare($sql);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
    
}


function getCategoryList(){
    
    $connection = connWhitneyUser();
    try {
     $sql = 'SELECT DISTINCT itf.CAT, IFNULL(c.category, "BLANK")
            FROM itemsf itf 
            LEFT JOIN categories c 
            ON itf.CAT = c.category_code';
        $stmt = $connection->prepare($sql);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
    
}

function getJobClockPunches($jobNumber){
    $connection = connWhitneyUser();
    try {
     $sql = 'SELECT 
                x.workOrderNumber
                , x.departmentName
                , x.start
                , x.stop
                , x.crewCount
                , x.lunch
                , FLOOR(x.total_time) + CASE WHEN (x.total_time - FLOOR(x.total_time)) < .125 THEN 0 
                WHEN (x.total_time - FLOOR(x.total_time)) > .125 AND (x.total_time - FLOOR(x.total_time)) < .375 THEN .25
                WHEN (x.total_time - FLOOR(x.total_time)) > .375 AND (x.total_time - FLOOR(x.total_time)) < .675 THEN .5 
                WHEN (x.total_time - FLOOR(x.total_time)) > .675 AND (x.total_time - FLOOR(x.total_time)) < .875 THEN .75 
                WHEN (x.total_time - FLOOR(x.total_time)) > .875 AND (x.total_time - FLOOR(x.total_time)) < 1 THEN 1
                END  AS total_time
                , x.userName
                FROM
                (SELECT
                sjc.workOrderNumber
                , d.departmentName
                , sjc.start
                , sjc.stop
                , sjc.crewCount
                , sjc.lunch
                , (((UNIX_TIMESTAMP(sjc.stop) - UNIX_TIMESTAMP(sjc.start))/3600) * sjc.crewCount) 
                - (sjc.crewCount * .5 * sjc.lunch) AS total_time
                , userName
                FROM simpleJobClock sjc
                LEFT JOIN department d
                ON sjc.departmentID = d.departmentID
                LEFT JOIN users u
                ON sjc.createdBy = u.userID
                WHERE sjc.workOrderNumber = :jobNumber
                ORDER BY sjc.start) x';
        $stmt = $connection->prepare($sql);
        $stmt->bindValue(':jobNumber', $jobNumber, PDO::PARAM_STR);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

function getDeptTotals($jobNumber){
    $connection = connWhitneyUser();
    try {
     $sql = 'SELECT 
            sjc.departmentName
            , SUM(sjc.total_time) AS dept_total
            FROM (SELECT 
               x.workOrderNumber
               , x.departmentName
               , x.start
               , x.stop
               , x.crewCount
               , x.lunch
               , FLOOR(x.total_time) + CASE WHEN (x.total_time - FLOOR(x.total_time)) < .125 THEN 0 
               WHEN (x.total_time - FLOOR(x.total_time)) > .125 AND (x.total_time - FLOOR(x.total_time)) < .375 THEN .25
               WHEN (x.total_time - FLOOR(x.total_time)) > .375 AND (x.total_time - FLOOR(x.total_time)) < .675 THEN .5 
               WHEN (x.total_time - FLOOR(x.total_time)) > .675 AND (x.total_time - FLOOR(x.total_time)) < .875 THEN .75 
               WHEN (x.total_time - FLOOR(x.total_time)) > .875 AND (x.total_time - FLOOR(x.total_time)) < 1 THEN 1
               END  AS total_time
               FROM
               (SELECT
               sjc.workOrderNumber
               , d.departmentName
               , sjc.start
               , sjc.stop
               , sjc.crewCount
               , sjc.lunch
               , (((UNIX_TIMESTAMP(sjc.stop) - UNIX_TIMESTAMP(sjc.start))/3600) * sjc.crewCount) 
               - (sjc.crewCount * .5 * sjc.lunch) AS total_time
               FROM simpleJobClock sjc
               LEFT JOIN department d
               ON sjc.departmentID = d.departmentID
               WHERE sjc.workOrderNumber = :jobNumber
               ORDER BY sjc.start) x) sjc
            GROUP BY sjc.departmentName';
        $stmt = $connection->prepare($sql);
        $stmt->bindValue(':jobNumber', $jobNumber, PDO::PARAM_STR);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

function getMaterialUsage($jobNumber){
    $connection = connWhitneyUser();
    try {
     $sql = 'SELECT e.workOrderNumber, i.skuNumber, itf.desc1, sum(e.quantity) AS qty
            FROM eusage e
            LEFT JOIN inventory i
            ON e.inventoryID = i.inventoryID
            LEFT JOIN itemsf itf
            ON i.skuNumber = itf.skuNumber
            WHERE e.workOrderNumber = CONCAT(:jobNumber, \'-1\')
            OR e.workOrderNumber = CONCAT(:jobNumber, \'-2\')
            OR e.workOrderNumber = CONCAT(:jobNumber, \'-3\')
            OR e.workOrderNumber = :jobNumber
            GROUP BY e.workOrderNumber, itf.skuNumber';
        $stmt = $connection->prepare($sql);
        $stmt->bindValue(':jobNumber', $jobNumber, PDO::PARAM_STR);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}


function getInventoryHistoryBySKU($skuNumber){
    $connection = connWhitneyUser();
    try {
     $sql = 'SELECT x.* 
            FROM
            (select
            inv.inventoryID
            , inv.skuNumber
            , inv.companyID
            , inv.receiptDate
            , inv.batchNumber
            , inv.expirationDate
            , inv.active
            , "STATUS CHANGE" AS type
            , u.userName
            , invs.creationDate
            , st.stateName AS value
            , e.workOrderNumber
            , e.quantity
            from inventory inv 
            LEFT JOIN inventorystatus invs ON inv.inventoryID = invs.inventoryID
            LEFT JOIN inventorystate st ON invs.inventoryStateID = st.inventoryStateID
            LEFT JOIN users u ON invs.createdBy = u.userID
            LEFT JOIN eusage e ON invs.inventoryID = e.inventoryID 
            AND (invs.creationDate <= DATE_ADD(e.creationDate, INTERVAL 1 SECOND) AND invs.creationDate >= DATE_SUB(e.creationDate, INTERVAL 1 SECOND))
            where skunumber = :skuNumber
            UNION
            select 
            inv.inventoryID
            , inv.skuNumber
            , inv.companyID
            , inv.receiptDate
            , inv.batchNumber
            , inv.expirationDate
            , inv.active
            , "LOCATION CHANGE" AS type
            , u.userName
            , invl.creationDate
            , b.binName AS value
            , NULL AS workOrderNumber
            , NULL AS quantity
            from inventory inv 
            LEFT JOIN inventorylocation invl ON inv.inventoryID = invl.inventoryID
            LEFT JOIN binlocation b ON invl.binlocationID = b.binLocationID
            LEFT JOIN users u ON invl.createdBy = u.userID
            where inv.skunumber = :skuNumber
            UNION
            select 
            inv.inventoryID
            , inv.skuNumber
            , inv.companyID
            , inv.receiptDate
            , inv.batchNumber
            , inv.expirationDate
            , inv.active
            , "QUANTITY CHANGE" AS type
            , u.userName
            , invq.creationDate
            , invq.inventoryQuantity AS value
            , e.workOrderNumber
            , e.quantity
            from inventory inv 
            LEFT JOIN inventoryUnitQuantity invq ON inv.inventoryID = invq.inventoryID
            LEFT JOIN users u ON invq.createdBy = u.userID
            LEFT JOIN eusage e ON invq.inventoryID = e.inventoryID 
            AND (invq.creationDate <= DATE_ADD(e.creationDate, INTERVAL 1 SECOND) AND invq.creationDate >= DATE_SUB(e.creationDate, INTERVAL 1 SECOND))
            where skunumber = :skuNumber) x
            WHERE x.value IS NOT NULL
            ORDER BY x.inventoryID, x.creationDate, x.type DESC';
        $stmt = $connection->prepare($sql);
        $stmt->bindValue(':skuNumber', $skuNumber, PDO::PARAM_STR);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

function getInventoryHistoryBySerial($serialNumber){
    $connection = connWhitneyUser();
    try {
     $sql = 'SELECT x.* 
            FROM
            (select
            inv.inventoryID
            , inv.skuNumber
            , inv.companyID
            , inv.receiptDate
            , inv.batchNumber
            , inv.expirationDate
            , inv.active
            , "STATUS CHANGE" AS type
            , u.userName
            , invs.creationDate
            , st.stateName AS value
            , e.workOrderNumber
            , e.quantity
            from inventory inv 
            LEFT JOIN inventorystatus invs ON inv.inventoryID = invs.inventoryID
            LEFT JOIN inventorystate st ON invs.inventoryStateID = st.inventoryStateID
            LEFT JOIN users u ON invs.createdBy = u.userID
            LEFT JOIN eusage e ON invs.inventoryID = e.inventoryID 
            AND (invs.creationDate <= DATE_ADD(e.creationDate, INTERVAL 1 SECOND) AND invs.creationDate >= DATE_SUB(e.creationDate, INTERVAL 1 SECOND))
            where inv.inventoryID = :serialNumber
            UNION
            select 
            inv.inventoryID
            , inv.skuNumber
            , inv.companyID
            , inv.receiptDate
            , inv.batchNumber
            , inv.expirationDate
            , inv.active
            , "LOCATION CHANGE" AS type
            , u.userName
            , invl.creationDate
            , b.binName AS value
            , NULL AS workOrderNumber
            , NULL AS quantity
            from inventory inv 
            LEFT JOIN inventorylocation invl ON inv.inventoryID = invl.inventoryID
            LEFT JOIN binlocation b ON invl.binlocationID = b.binLocationID
            LEFT JOIN users u ON invl.createdBy = u.userID
            where inv.inventoryID = :serialNumber
            UNION
            select 
            inv.inventoryID
            , inv.skuNumber
            , inv.companyID
            , inv.receiptDate
            , inv.batchNumber
            , inv.expirationDate
            , inv.active
            , "QUANTITY CHANGE" AS type
            , u.userName
            , invq.creationDate
            , invq.inventoryQuantity AS value
            , e.workOrderNumber
            , e.quantity
            from inventory inv 
            LEFT JOIN inventoryUnitQuantity invq ON inv.inventoryID = invq.inventoryID
            LEFT JOIN users u ON invq.createdBy = u.userID
            LEFT JOIN eusage e ON invq.inventoryID = e.inventoryID 
            AND (invq.creationDate <= DATE_ADD(e.creationDate, INTERVAL 1 SECOND) AND invq.creationDate >= DATE_SUB(e.creationDate, INTERVAL 1 SECOND))
            where inv.inventoryID = :serialNumber) x
            WHERE x.value IS NOT NULL
            ORDER BY x.inventoryID, x.creationDate, x.type DESC';
        $stmt = $connection->prepare($sql);
        $stmt->bindValue(':serialNumber', $serialNumber, PDO::PARAM_STR);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}


function getInventoryHistoryByBatch($batchNumber){
    $connection = connWhitneyUser();
    try {
        $sql = 'SELECT x.* 
        FROM
        (select
        inv.inventoryID
        , inv.skuNumber
        , inv.companyID
        , inv.receiptDate
        , inv.batchNumber
        , inv.expirationDate
        , inv.active
        , "STATUS CHANGE" AS type
        , u.userName
        , invs.creationDate
        , st.stateName AS value
        , e.workOrderNumber
        , e.quantity
        from inventory inv 
        LEFT JOIN inventorystatus invs ON inv.inventoryID = invs.inventoryID
        LEFT JOIN inventorystate st ON invs.inventoryStateID = st.inventoryStateID
        LEFT JOIN users u ON invs.createdBy = u.userID
        LEFT JOIN eusage e ON invs.inventoryID = e.inventoryID 
        AND (invs.creationDate <= DATE_ADD(e.creationDate, INTERVAL 1 SECOND) AND invs.creationDate >= DATE_SUB(e.creationDate, INTERVAL 1 SECOND))
        where inv.batchNumber = :batchNumber
        UNION
        select 
        inv.inventoryID
        , inv.skuNumber
        , inv.companyID
        , inv.receiptDate
        , inv.batchNumber
        , inv.expirationDate
        , inv.active
        , "LOCATION CHANGE" AS type
        , u.userName
        , invl.creationDate
        , b.binName AS value
        , NULL AS workOrderNumber
        , NULL AS quantity
        from inventory inv 
        LEFT JOIN inventorylocation invl ON inv.inventoryID = invl.inventoryID
        LEFT JOIN binlocation b ON invl.binlocationID = b.binLocationID
        LEFT JOIN users u ON invl.createdBy = u.userID
        where inv.batchNumber = :batchNumber
        UNION
        select 
        inv.inventoryID
        , inv.skuNumber
        , inv.companyID
        , inv.receiptDate
        , inv.batchNumber
        , inv.expirationDate
        , inv.active
        , "QUANTITY CHANGE" AS type
        , u.userName
        , invq.creationDate
        , invq.inventoryQuantity AS value
        , e.workOrderNumber
        , e.quantity
        from inventory inv 
        LEFT JOIN inventoryUnitQuantity invq ON inv.inventoryID = invq.inventoryID
        LEFT JOIN users u ON invq.createdBy = u.userID
        LEFT JOIN eusage e ON invq.inventoryID = e.inventoryID 
        AND (invq.creationDate <= DATE_ADD(e.creationDate, INTERVAL 1 SECOND) AND invq.creationDate >= DATE_SUB(e.creationDate, INTERVAL 1 SECOND))
        where inv.batchNumber = :batchNumber) x
        WHERE x.value IS NOT NULL
        ORDER BY x.inventoryID, x.creationDate, x.type DESC';
        $stmt = $connection->prepare($sql);
        $stmt->bindValue(':batchNumber', $batchNumber, PDO::PARAM_STR);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

function getInventoryHistoryByBatchBU($batchNumber){
    $connection = connWhitneyUser();
    try {
     $sql = 'SELECT x.* 
                FROM
                (select
                inv.inventoryID
                , inv.skuNumber
                , inv.companyID
                , inv.receiptDate
                , inv.batchNumber
                , inv.expirationDate
                , inv.active
                , "STATUS CHANGE" AS type
                , u.userName
                , invs.creationDate
                , st.stateName AS value
                , NULL AS workOrderNumber
                , NULL AS quantity
                from inventory inv 
                LEFT JOIN inventorystatus invs ON inv.inventoryID = invs.inventoryID
                LEFT JOIN inventorystate st ON invs.inventoryStateID = st.inventoryStateID
                LEFT JOIN users u ON invs.createdBy = u.userID
                where inv.batchNumber = :batchNumber
                UNION
                select 
                inv.inventoryID
                , inv.skuNumber
                , inv.companyID
                , inv.receiptDate
                , inv.batchNumber
                , inv.expirationDate
                , inv.active
                , "LOCATION CHANGE" AS type
                , u.userName
                , invl.creationDate
                , b.binName AS value
                , NULL AS workOrderNumber
                , NULL AS quantity
                from inventory inv 
                LEFT JOIN inventorylocation invl ON inv.inventoryID = invl.inventoryID
                LEFT JOIN binlocation b ON invl.binlocationID = b.binLocationID
                LEFT JOIN users u ON invl.createdBy = u.userID
                where inv.batchNumber = :batchNumber
                UNION
                select 
                inv.inventoryID
                , inv.skuNumber
                , inv.companyID
                , inv.receiptDate
                , inv.batchNumber
                , inv.expirationDate
                , inv.active
                , "QUANTITY CHANGE" AS type
                , u.userName
                , invq.creationDate
                , invq.inventoryQuantity AS value
                , NULL AS workOrderNumber
                , NULL AS quantity
                from inventory inv 
                LEFT JOIN inventoryUnitQuantity invq ON inv.inventoryID = invq.inventoryID
                LEFT JOIN users u ON invq.createdBy = u.userID
                where inv.batchNumber = :batchNumber) x
                WHERE x.value IS NOT NULL
                ORDER BY x.inventoryID, x.creationDate, x.type DESC';
        $stmt = $connection->prepare($sql);
        $stmt->bindValue(':batchNumber', $batchNumber, PDO::PARAM_STR);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

function getActiveUser(){
    $connection = connWhitneyUser();
    try {
     $sql = 'SELECT x.createdBY, u.userName, CONCAT(e.firstName, \' \' , e.lastName) as Name 
            FROM employee e
            LEFT JOIN users u
            ON e.employeeID = u.employeeID
            INNER JOIN 
            (SELECT DISTINCT createdBY FROM inventoryLocation
            UNION
            SELECT DISTINCT createdBY FROM inventorystatus
            UNION
            SELECT DISTINCT createdBY FROM inventoryunitquantity) x
            ON u.userID = x.createdBY
            ORDER BY u.userName';
        $stmt = $connection->prepare($sql);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}


function getForkLiftActivitySummary($startDate, $endDate, $userID){
    $connection = connWhitneyUser();
    
    $startDate = date('Y-m-d', strtotime($startDate));
    $endDate = date('Y-m-d', strtotime($endDate));
    try {
     $sql = 'SELECT
                work.userID
                , work.userName
                , sum(work.totalTrans) AS totalTrans
                , sum(IFNULL(hrs.time, 8)) AS totalTime
                , sum(work.totalTrans) / sum(IFNULL(hrs.time, 8)) AS avgTPH
                FROM
                (SELECT
                x.userID
                , x.userName
                , x.creationDate
                , sum(cnt) AS totalTrans 
                FROM
                (SELECT 
                DATE(i.creationDate) AS creationDate
                , "LOCATION" AS type
                , u.userID
                , u.userName
                , count(i.creationDate) AS cnt
                FROM inventoryLocation i
                LEFT JOIN users u
                ON i.createdBy = u.userID
                WHERE i.creationDate >= :startDate
                AND i.creationDate < :endDate
                GROUP BY DATE(i.creationDate), u.userID
                UNION
                SELECT 
                DATE(i.creationDate) AS creationDate
                , "STATUS" AS type
                , u.userID
                , u.userName
                , count(i.creationDate) AS cnt
                FROM inventoryStatus i
                LEFT JOIN users u
                ON i.createdBy = u.userID
                WHERE i.creationDate >= :startDate
                AND i.creationDate < :endDate
                GROUP BY DATE(i.creationDate), u.userID
                UNION
                SELECT
                DATE(i.creationDate) AS creationDate
                , "QUANTITY" AS type
                , u.userID
                , u.userName
                , count(i.creationDate) AS cnt
                FROM inventoryUnitQuantity i
                LEFT JOIN users u
                ON i.createdBy = u.userID
                WHERE i.creationDate >= :startDate
                AND i.creationDate < :endDate
                GROUP BY DATE(i.creationDate), u.userID) x
                GROUP BY x.userID, x.userName, x.creationDate) work
                LEFT JOIN
                (SELECT 
                x.userID
                , CASE WHEN x.time > 21600 THEN (sum(x.time - 1800)/3600) ELSE (sum(x.time)/3600) END AS time
                , x.punchDate
                FROM
                (SELECT u.userID
                  , TIME_TO_SEC(timediff(punchout, punchin)) as time
                  , punchDate
                    FROM timeClockPunch t
                    LEFT JOIN employee e
                    ON t.employeeID = e.employeeID
                    LEFT JOIN users u
                    ON e.employeeID = u.employeeID
                    WHERE punchDate >= :startDate
                    AND punchDate < :endDate
                    AND punchout IS NOT NULL
                    AND u.userID IS NOT NULL) x
                    GROUP BY x.punchDate, x.userID) hrs
                ON work.userID = hrs.userID AND work.creationDate = hrs.punchDate ';
                if($userID > 0){
                    $sql .= ' WHERE work.userID = :userID
                    GROUP BY work.userID';
                }else{
                    $sql .= ' GROUP BY work.userID';
                }
        $stmt = $connection->prepare($sql);
        $stmt->bindValue(':startDate', $startDate, PDO::PARAM_STR);
        $stmt->bindValue(':endDate', $endDate, PDO::PARAM_STR);
        if($userID > 0){
            $stmt->bindValue(':userID', $userID, PDO::PARAM_INT);
        }
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}


function getForkLiftActivityByDay($startDate, $endDate, $userID){
    $connection = connWhitneyUser();
    
    $startDate = date('Y-m-d', strtotime($startDate));
    $endDate = date('Y-m-d', strtotime($endDate));

    try {
     $sql = 'SELECT
work.userID
, work.creationDate
, work.userName
, work.totalTrans
, IFNULL(hrs.time, 8) AS hours
, (work.totalTrans / IFNULL(hrs.time, 8)) AS tph
FROM
(SELECT
x.userID
, x.userName
, x.creationDate
, sum(cnt) AS totalTrans 
FROM
(SELECT 
DATE(i.creationDate) AS creationDate
, "LOCATION" AS type
, u.userID
, u.userName
, count(i.creationDate) AS cnt
FROM inventoryLocation i
LEFT JOIN users u
ON i.createdBy = u.userID
WHERE i.creationDate >= :startDate
AND i.creationDate < :endDate
GROUP BY DATE(i.creationDate), u.userID
UNION
SELECT 
DATE(i.creationDate) AS creationDate
, "STATUS" AS type
, u.userID
, u.userName
, count(i.creationDate) AS cnt
FROM inventoryStatus i
LEFT JOIN users u
ON i.createdBy = u.userID
WHERE i.creationDate >= :startDate
AND i.creationDate < :endDate
GROUP BY DATE(i.creationDate), u.userID
UNION
SELECT
DATE(i.creationDate) AS creationDate
, "QUANTITY" AS type
, u.userID
, u.userName
, count(i.creationDate) AS cnt
FROM inventoryUnitQuantity i
LEFT JOIN users u
ON i.createdBy = u.userID
WHERE i.creationDate >= :startDate
AND i.creationDate < :endDate
GROUP BY DATE(i.creationDate), u.userID) x
GROUP BY x.userID, x.userName, x.creationDate) work
LEFT JOIN
(SELECT 
x.userID
, CASE WHEN x.time > 21600 THEN (sum(x.time - 1800)/3600) ELSE (sum(x.time)/3600) END AS time
, x.punchDate
FROM
(SELECT u.userID
  , TIME_TO_SEC(timediff(punchout, punchin)) as time
  , punchDate
    FROM timeClockPunch t
    LEFT JOIN employee e
    ON t.employeeID = e.employeeID
    LEFT JOIN users u
    ON e.employeeID = u.employeeID
    WHERE punchDate >= :startDate
    AND punchDate < :endDate
    AND punchout IS NOT NULL
    AND u.userID IS NOT NULL) x
    GROUP BY x.punchDate, x.userID) hrs
ON work.userID = hrs.userID AND work.creationDate = hrs.punchDate; ';
                if($userID > 0){
                    $sql .= ' WHERE work.userID = :userID
                                GROUP BY work.userID';
                }else{
                    $sql .= ' GROUP BY work.userID';
                }
        $stmt = $connection->prepare($sql);
        $stmt->bindValue(':startDate', $startDate, PDO::PARAM_STR);
        $stmt->bindValue(':endDate', $endDate, PDO::PARAM_STR);
        if($userID > 0){
            $stmt->bindValue(':userID', $userID, PDO::PARAM_INT);
        }
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

function getJobSummaryTotals($date1, $date2){
    $connection = connWhitneyUser();
    try {
     $sql = 'SELECT d.departmentName
                , SUM(sjc.total_time) AS dept_total
                , d.departmentID
                FROM (SELECT
                      sjc.workOrderNumber
                      , sjc.departmentID
                      , (((UNIX_TIMESTAMP(sjc.stop) - UNIX_TIMESTAMP(sjc.start))/3600) * sjc.crewCount) - (sjc.crewCount * .5 * sjc.lunch) AS total_time
                      FROM simpleJobClock sjc
                      WHERE sjc.creationDate < :date2 AND sjc.creationDate > :date1
                      ORDER BY sjc.start) sjc
                LEFT JOIN department d
                ON sjc.departmentID = d.departmentID
                GROUP BY d.departmentName';
        $stmt = $connection->prepare($sql);
        $stmt->bindValue(':date1', $date1, PDO::PARAM_STR);
        $stmt->bindValue(':date2', $date2, PDO::PARAM_STR);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}


function getDepartmentTotals($date1, $date2){
    $connection = connWhitneyUser();

    try {
     $sql = 'SELECT DISTINCT sjc.workOrderNumber
            , itf.DESC1
            , Round(total_hours.tot_hrs, 2) AS total_hours
            , Round(Act_Hrs_Prototyping.tot_hrs, 2) AS Act_Hrs_Prototyping
            , Round(Act_Hrs_Setup.tot_hrs, 2) AS Act_Hrs_Setup
            , Round(Act_Hrs_Processing.tot_hrs, 2) AS Act_Hrs_Processing
            , Round(Act_Hrs_Assembly.tot_hrs, 2) AS Act_Hrs_Assembly
            , Round(Act_Hrs_StainPaint.tot_hrs, 2) AS Act_Hrs_StainPaint
            , Round(Act_Hrs_Sanding.tot_hrs, 2) AS Act_Hrs_Sanding
            , Round(Act_Hrs_PackOut.tot_hrs, 2) AS Act_Hrs_PackOut
            , Round(Act_Hrs_ShippingReceiving.tot_hrs, 2) AS Act_Hrs_ShippingReceiving
            , Round(Act_Hrs_Crating.tot_hrs, 2) AS Act_Hrs_Crating
            , Round(Act_Hrs_Forklift.tot_hrs, 2) AS Act_Hrs_Forklift
            FROM simpleJobClock sjc
            LEFT JOIN workOrderf wo
            ON wo.workOrderNumber = sjc.workordernumber
            LEFT JOIN
                  (SELECT hrs_to_sum.workOrderNumber, SUM(hrs_to_sum.total_time) AS tot_hrs FROM 
                        (SELECT sjc.workordernumber,
                        (((UNIX_TIMESTAMP(sjc.stop) - UNIX_TIMESTAMP(sjc.start))/3600) * sjc.crewCount) - (sjc.crewCount * .5 * sjc.lunch) AS total_time
                        FROM simpleJobClock sjc
                        WHERE sjc.creationDate <= :date2 AND sjc.creationDate >= :date1) hrs_to_sum
                        GROUP BY hrs_to_sum.workordernumber) AS total_hours
            ON wo.workordernumber = total_hours.workOrderNumber
            LEFT JOIN
                  (SELECT hrs_to_sum.workOrderNumber, SUM(hrs_to_sum.total_time) AS tot_hrs FROM 
                        (SELECT sjc.workordernumber,
                        (((UNIX_TIMESTAMP(sjc.stop) - UNIX_TIMESTAMP(sjc.start))/3600) * sjc.crewCount) - (sjc.crewCount * .5 * sjc.lunch) AS total_time
                        FROM simpleJobClock sjc
                        WHERE sjc.creationDate <= :date2 AND sjc.creationDate >= :date1 AND sjc.departmentID = 7) hrs_to_sum
                        GROUP BY hrs_to_sum.workordernumber) AS Act_Hrs_Setup
            ON wo.workordernumber = Act_Hrs_Setup.workOrderNumber
            LEFT JOIN
                  (SELECT hrs_to_sum.workOrderNumber, SUM(hrs_to_sum.total_time) AS tot_hrs FROM 
                        (SELECT sjc.workordernumber,
                        (((UNIX_TIMESTAMP(sjc.stop) - UNIX_TIMESTAMP(sjc.start))/3600) * sjc.crewCount) - (sjc.crewCount * .5 * sjc.lunch) AS total_time
                        FROM simpleJobClock sjc
                        WHERE sjc.creationDate <= :date2 AND sjc.creationDate >= :date1 AND sjc.departmentID IN (28, 20)) hrs_to_sum
                        GROUP BY hrs_to_sum.workordernumber) AS Act_Hrs_Prototyping
            ON wo.workordernumber = Act_Hrs_Prototyping.workOrderNumber
            LEFT JOIN
                  (SELECT hrs_to_sum.workOrderNumber, SUM(hrs_to_sum.total_time) AS tot_hrs FROM 
                              (SELECT sjc.workordernumber,
                              (((UNIX_TIMESTAMP(sjc.stop) - UNIX_TIMESTAMP(sjc.start))/3600) * sjc.crewCount) - (sjc.crewCount * .5 * sjc.lunch) AS total_time
                              FROM simpleJobClock sjc
                              WHERE sjc.creationDate <= :date2 AND sjc.creationDate >= :date1 AND sjc.departmentID IN (1, 16, 10, 11, 9, 26, 25, 24)) hrs_to_sum
                        GROUP BY hrs_to_sum.workordernumber) AS Act_Hrs_Processing
            ON wo.workOrderNumber = Act_Hrs_Processing.workOrderNumber
            LEFT JOIN
                  (SELECT hrs_to_sum.workOrderNumber, SUM(hrs_to_sum.total_time) AS tot_hrs FROM 
                              (SELECT sjc.workordernumber,
                              (((UNIX_TIMESTAMP(sjc.stop) - UNIX_TIMESTAMP(sjc.start))/3600) * sjc.crewCount) - (sjc.crewCount * .5 * sjc.lunch) AS total_time
                              FROM simpleJobClock sjc
                              WHERE sjc.creationDate <= :date2 AND sjc.creationDate >= :date1 AND sjc.departmentID IN (2, 32, 17, 33)) hrs_to_sum
                        GROUP BY hrs_to_sum.workordernumber) AS Act_Hrs_Assembly
            ON wo.workOrderNumber = Act_Hrs_Assembly.workOrderNumber
            LEFT JOIN
                  (SELECT hrs_to_sum.workOrderNumber, SUM(hrs_to_sum.total_time) AS tot_hrs FROM 
                              (SELECT sjc.workordernumber,
                              (((UNIX_TIMESTAMP(sjc.stop) - UNIX_TIMESTAMP(sjc.start))/3600) * sjc.crewCount) - (sjc.crewCount * .5 * sjc.lunch) AS total_time
                              FROM simpleJobClock sjc
                              WHERE sjc.creationDate <= :date2 AND sjc.creationDate >= :date1 AND sjc.departmentID IN (29, 30, 21, 22)) hrs_to_sum
                        GROUP BY hrs_to_sum.workordernumber) AS Act_Hrs_StainPaint
            ON wo.workOrderNumber = Act_Hrs_StainPaint.workOrderNumber
            LEFT JOIN
                  (SELECT hrs_to_sum.workOrderNumber, SUM(hrs_to_sum.total_time) AS tot_hrs FROM 
                              (SELECT sjc.workordernumber,
                              (((UNIX_TIMESTAMP(sjc.stop) - UNIX_TIMESTAMP(sjc.start))/3600) * sjc.crewCount) - (sjc.crewCount * .5 * sjc.lunch) AS total_time
                              FROM simpleJobClock sjc
                              WHERE sjc.creationDate <= :date2 AND sjc.creationDate >= :date1 AND sjc.departmentID IN (34, 36)) hrs_to_sum
                        GROUP BY hrs_to_sum.workordernumber) AS Act_Hrs_Sanding
            ON wo.workOrderNumber = Act_Hrs_Sanding.workOrderNumber
            LEFT JOIN
                  (SELECT hrs_to_sum.workOrderNumber, SUM(hrs_to_sum.total_time) AS tot_hrs FROM 
                              (SELECT sjc.workordernumber,
                              (((UNIX_TIMESTAMP(sjc.stop) - UNIX_TIMESTAMP(sjc.start))/3600) * sjc.crewCount) - (sjc.crewCount * .5 * sjc.lunch) AS total_time
                              FROM simpleJobClock sjc
                              WHERE sjc.creationDate <= :date2 AND sjc.creationDate >= :date1 AND sjc.departmentID IN (5, 37, 18, 38)) hrs_to_sum
                        GROUP BY hrs_to_sum.workordernumber) AS Act_Hrs_PackOut
            ON wo.workOrderNumber = Act_Hrs_PackOut.workOrderNumber
            LEFT JOIN
                  (SELECT hrs_to_sum.workOrderNumber, SUM(hrs_to_sum.total_time) AS tot_hrs FROM 
                              (SELECT sjc.workordernumber,
                              (((UNIX_TIMESTAMP(sjc.stop) - UNIX_TIMESTAMP(sjc.start))/3600) * sjc.crewCount) - (sjc.crewCount * .5 * sjc.lunch) AS total_time
                              FROM simpleJobClock sjc
                              WHERE sjc.creationDate <= :date2 AND sjc.creationDate >= :date1 AND sjc.departmentID IN (31, 23)) hrs_to_sum
                        GROUP BY hrs_to_sum.workordernumber) AS Act_Hrs_ShippingReceiving
            ON wo.workOrderNumber = Act_Hrs_ShippingReceiving.workOrderNumber
            LEFT JOIN
                  (SELECT hrs_to_sum.workOrderNumber, SUM(hrs_to_sum.total_time) AS tot_hrs FROM 
                              (SELECT sjc.workordernumber,
                              (((UNIX_TIMESTAMP(sjc.stop) - UNIX_TIMESTAMP(sjc.start))/3600) * sjc.crewCount) - (sjc.crewCount * .5 * sjc.lunch) AS total_time
                              FROM simpleJobClock sjc
                              WHERE sjc.creationDate <= :date2 AND sjc.creationDate >= :date1 AND sjc.departmentID IN (27, 19)) hrs_to_sum
                        GROUP BY hrs_to_sum.workordernumber) AS Act_Hrs_Crating
            ON wo.workOrderNumber = Act_Hrs_Crating.workOrderNumber
            LEFT JOIN
                  (SELECT hrs_to_sum.workOrderNumber, SUM(hrs_to_sum.total_time) AS tot_hrs FROM 
                              (SELECT sjc.workordernumber,
                              (((UNIX_TIMESTAMP(sjc.stop) - UNIX_TIMESTAMP(sjc.start))/3600) * sjc.crewCount) - (sjc.crewCount * .5 * sjc.lunch) AS total_time
                              FROM simpleJobClock sjc
                              WHERE sjc.creationDate <= :date2 AND sjc.creationDate >= :date1 AND sjc.departmentID = 6) hrs_to_sum
                        GROUP BY hrs_to_sum.workordernumber) AS Act_Hrs_Forklift
            ON wo.workOrderNumber = Act_Hrs_Forklift.workOrderNumber
            LEFT JOIN workOrderf wof
            ON wof.workordernumber = wo.workordernumber
            LEFT JOIN itemsf itf
            ON wof.skuNumber = itf.skuNumber
            WHERE sjc.creationDate <= :date2 AND sjc.creationDate >= :date1 AND sjc.workOrderNumber IS NOT NULL AND total_hours.tot_hrs IS NOT NULL';

        $stmt = $connection->prepare($sql);
        $stmt->bindValue(':date1', $date1, PDO::PARAM_STR);
        $stmt->bindValue(':date2', $date2, PDO::PARAM_STR);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

function getDepartmentGTotal($date1, $date2){
    $connection = connWhitneyUser();
    try {
     $sql = 'SELECT SUM(sjc.total_time) AS dept_total
                FROM (SELECT
                      sjc.workOrderNumber
                      , sjc.departmentID
                      , (((UNIX_TIMESTAMP(sjc.stop) - UNIX_TIMESTAMP(sjc.start))/3600) * sjc.crewCount) - (sjc.crewCount * .5 * sjc.lunch) AS total_time
                      FROM simpleJobClock sjc
                      WHERE sjc.creationDate < :date1 AND sjc.creationDate > :date2
                      ORDER BY sjc.start) sjc';
        $stmt = $connection->prepare($sql);
        $stmt->bindValue(':date1', $date1, PDO::PARAM_STR);
        $stmt->bindValue(':date2', $date2, PDO::PARAM_STR);
        $stmt->execute();
        $data = $stmt->fetch();
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}


function getWorkOrderTotal($date1, $date2){
    $connection = connWhitneyUser();
    try {
     $sql = 'SELECT d.departmentName
                , Round(SUM(sjc.total_time), 2) AS dept_total
                , d.departmentID
                FROM (SELECT
                      sjc.workOrderNumber
                      , sjc.departmentID
                      , (((UNIX_TIMESTAMP(sjc.stop) - UNIX_TIMESTAMP(sjc.start))/3600) * sjc.crewCount) - (sjc.crewCount * .5 * sjc.lunch) AS total_time
                      FROM simpleJobClock sjc
                      WHERE sjc.creationDate <= :date2 AND sjc.creationDate >= :date1
                      ORDER BY sjc.start) sjc
                LEFT JOIN department d
                ON sjc.departmentID = d.departmentID
                GROUP BY d.departmentName';
        $stmt = $connection->prepare($sql);
        $stmt->bindValue(':date1', $date1, PDO::PARAM_STR);
        $stmt->bindValue(':date2', $date2, PDO::PARAM_STR);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}


function getPickPackOrder($orderNumber){
    $connection = connWhitneyUser();
    try {
     $sql = 'SELECT pph.active
                , ppla.customerSONumber
                , pph.customerPO
                , ppi.item
                , ppi.description
                , ppla.batchNumber
                , ppla.assignedQty
                FROM pickPackHeader pph
                LEFT JOIN pickPackItems ppi
                ON ppi.customerSONumber = pph.customerSONumber
                LEFT JOIN pickPackLotAssign ppla
                ON ppla.pickPackItemsID = ppi.pickPackItemsID
                LEFT JOIN pickPack
                WHERE ppla.customerSONumber = :orderNumber';
        $stmt = $connection->prepare($sql);
        $stmt->bindValue(':orderNumber', $orderNumber, PDO::PARAM_STR);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

function getPickPackRange($date1, $date2){
    $connection = connWhitneyUser();
    try {
     $sql = 'SELECT pph.active
            , ppla.customerSONumber
            , pph.customerPO
            , ppi.item
            , ppi.description
            , ppla.batchNumber
            , ppla.assignedQty
            FROM pickPackHeader pph
            LEFT JOIN pickPackItems ppi
            ON ppi.customerSONumber = pph.customerSONumber
            LEFT JOIN pickPackLotAssign ppla
            ON ppla.pickPackItemsID = ppi.pickPackItemsID
            WHERE ppla.creationDate > :date1 AND ppla.creationDate < :date2';
        $stmt = $connection->prepare($sql);
        $stmt->bindValue(':date1', $date1, PDO::PARAM_STR);
        $stmt->bindValue(':date2', $date2, PDO::PARAM_STR);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

function getMissingPunches($date1, $date2){
    $connection = connWhitneyUser();
    try {
     $sql = 'SELECT workOrderNumber
            , start
            , stop
            FROM simpleJobClock
            WHERE creationDate >= :date1 AND creationDate <= :date2 AND stop IS NULL AND workordernumber IS NOT NULL';
     
        $stmt = $connection->prepare($sql);
        $stmt->bindValue(':date1', $date1, PDO::PARAM_STR);
        $stmt->bindValue(':date2', $date2, PDO::PARAM_STR);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}


function getWipHours($date1, $date2){
    $connection = connWhitneyUser();
    try {
     $sql = 'SELECT x.workOrderNumber, x.userName, SUM(x.totalTime) AS totalTime
                FROM (
                      SELECT sjc.workOrderNumber
                      , u.userID
                      , u.userName
                      , sjc.creationDate
                      , (((UNIX_TIMESTAMP(sjc.stop) - UNIX_TIMESTAMP(sjc.start))/3600) * sjc.crewCount) 
                            - (sjc.crewCount * .5 * sjc.lunch) AS totalTime
                      FROM simpleJobClock sjc
                      LEFT JOIN workorder wo
                      ON wo.workordernumber = sjc.workordernumber
                      LEFT JOIN users u
                      ON sjc.createdBy = u.userID
                      WHERE sjc.creationDate >= :date1 AND sjc.creationDate <= :date2 AND wo.complete = false) x
                GROUP BY workOrderNumber';
     
        $stmt = $connection->prepare($sql);
        $stmt->bindValue(':date1', $date1, PDO::PARAM_STR);
        $stmt->bindValue(':date2', $date2, PDO::PARAM_STR);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

function getEusageWIP($date1, $date2){
    $connection = connWhitneyUser();
    try {
     $sql = 'SELECT e.workOrderNumber, itf.skuNumber, i.inventoryID, itf.DESC1, itf.UNIT, sum(quantity) AS usage_quantity, w.complete  
            FROM eusage e
            LEFT JOIN inventory i
            ON i.inventoryID = e.inventoryID
            LEFT JOIN itemsf itf
            ON i.skuNumber = itf.skuNumber
            LEFT JOIN workOrder w
            ON w.workordernumber = e.workOrderNumber
            WHERE e.modifiedDate >= :date1 AND e.modifiedDate <= :date2 AND w.complete = false
            GROUP BY e.workOrderNumber, itf.skuNumber';
     
        $stmt = $connection->prepare($sql);
        $stmt->bindValue(':date1', $date1, PDO::PARAM_STR);
        $stmt->bindValue(':date2', $date2, PDO::PARAM_STR);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}


function getDescrepancyData($startOfWeek, $endOfWeek){
    $connection = connWhitneyUser();
    
    $startOfWeek = date('Y-m-d', strtotime($startOfWeek));
    $endOfWeek = date('Y-m-d', strtotime($endOfWeek));
    
    try {
     $sql = 'SELECT DISTINCT tcp.employeeID 
             FROM timeClockPunch tcp
             LEFT JOIN employee e
             ON tcp.employeeID = e.employeeID
             WHERE tcp.punchDate >= :startOfWeek && tcp.punchDate <= :endOfWeek && e.statusID != 1
             ORDER BY e.tempAgencyID, e.lastName';
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':startOfWeek', $startOfWeek, PDO::PARAM_STR);
        $stmt->bindParam(':endOfWeek', $endOfWeek, PDO::PARAM_STR);
        $stmt->execute();
        $data = $stmt->fetchAll();
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    
    return $data;
}

function getBillablePalletBatchExport2($customerID) {
    $connection = connWhitneyUser();
    /*
     * SQL modified to use max id rather than max creation date
     */    
    try {
        $sql = 'SELECT 
                i.skuNumber
                , itf.DESC1
                , itf.unit
                , itf.partNumber 
                , c.companyName AS company_name
                , count(i.inventoryID) AS pallet_Count
                , q.inventoryQuantity AS qty
                , (q.inventoryQuantity * count(i.inventoryID)) AS sub_Total
                , iFNull(i.batchNumber, \'\') AS Lot_Code
                , iFNull(i.expirationDate, \'\') AS  Expiration_Date
                , iFNull(i.palletNumber, \'\') AS  Pallet_Number
                , CASE WHEN NOT i.workOrderNumber > 0 THEN i.workOrderNumber ELSE \'\' END AS Note
                FROM inventory i
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
                LEFT JOIN items it
                ON i.skuNumber = it.skuNumber
                LEFT JOIN itemsf itf
                ON i.skuNumber = itf.skuNumber
                LEFT JOIN customer c
                ON i.companyID = c.customerID
                WHERE i.companyID = :customerID
                AND s.inventoryStateID != 2 
                AND s.inventoryStateID != 3 
                AND s.inventoryStateID != 6 
                AND i.active = TRUE 
                AND (NOT palletNumber = "Pending Shipment" OR palletNumber IS NULL)
                GROUP BY i.skuNumber, q.inventoryQuantity, i.batchNumber, i.expirationDate, i.palletNumber
                ORDER BY i.skuNumber, i.batchNumber, i.palletNumber, i.expirationDate';
        $stmt = $connection->prepare($sql);
        $stmt->bindValue(':customerID', $customerID, PDO::PARAM_STR);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

function getReceipts($batchNumber) {
    $connection = connWhitneyUser();
    try {
        $sql = 'select DISTINCT receiptID, batchNumber from inventory where batchNumber = :batchNumber';
        $stmt = $connection->prepare($sql);
        $stmt->bindValue(':batchNumber', $batchNumber, PDO::PARAM_STR);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

function getReceiptData($receiptID) {
    $connection = connWhitneyUser();
    try {
        $sql = 'select 
        DISTINCT receiptID
        , date_received
        , purchaseOrderItemsID
        , receiptQty
        , complete
        from receiving 
        where receiptID = :receiptID';
        $stmt = $connection->prepare($sql);
        $stmt->bindValue(':receiptID', $receiptID, PDO::PARAM_STR);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

function getPurchaseOrdersPlusItems($purchaseOrderItemsID) {
    $connection = connWhitneyUser();
    try {
        $sql = '
        select 
        po.purchaseOrderID
        , po.orderDate
        , po.requiredDate
        , po.reference
        , poi.purchaseOrderItemsID
        , poi.skuNumber
        , poi.orderQty
        , poi.lineItem
        , poi.palletNumber
        , poi.lotCode
        , poi.complete 
        from purchaseOrder po
        LEFT JOIN purchaseOrderItems poi
        ON poi.purchaseOrderID = po.purchaseOrderID
        where poi.purchaseOrderItemsID = :purchaseOrderItemsID';
        $stmt = $connection->prepare($sql);
        $stmt->bindValue(':purchaseOrderItemsID', $purchaseOrderItemsID, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

function getReportSkuData($skuNumber) {
    $connection = connWhitneyUser();
    try {
        $sql = '
        select skuNumber
        , partNumber
        , DESC1
        , DESC2
        , UNIT
        from itemsf 
        where skuNumber = :skuNumber;';
        $stmt = $connection->prepare($sql);
        $stmt->bindValue(':skuNumber', $skuNumber, PDO::PARAM_STR);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

function getRawInv($batchNumber, $receiptID) {
    $connection = connWhitneyUser();
    try {
        $sql = '    
        select 
        inventoryID
        , skuNumber
        , receiptDate
        , batchNumber
        , expirationDate
        , creationDate
        , receiptID
        , quarantine
        , combinetoID
        , splitfromID
        from inventory
        where batchNumber = :batchNumber and receiptID = :receiptID';
        $stmt = $connection->prepare($sql);
        $stmt->bindValue(':batchNumber', $batchNumber, PDO::PARAM_STR);
        $stmt->bindValue(':receiptID', $receiptID, PDO::PARAM_STR);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

function getRawInvHistory($batchNumber) {
    $connection = connWhitneyUser();
    try {
        $sql = 'SELECT x.* 
        FROM
        (select
        inv.inventoryID
        , inv.receiptDate
        , inv.batchNumber
        , inv.expirationDate
        , inv.active
        , "STATUS CHANGE" AS type
        , u.userName
        , invs.creationDate
        , st.stateName AS value
        , e.workOrderNumber
        , e.quantity
        from inventory inv 
        LEFT JOIN inventorystatus invs ON inv.inventoryID = invs.inventoryID
        LEFT JOIN inventorystate st ON invs.inventoryStateID = st.inventoryStateID
        LEFT JOIN users u ON invs.createdBy = u.userID
        LEFT JOIN eusage e ON invs.inventoryID = e.inventoryID 
        AND (invs.creationDate <= DATE_ADD(e.creationDate, INTERVAL 1 SECOND) AND invs.creationDate >= DATE_SUB(e.creationDate, INTERVAL 1 SECOND))
        where inv.batchNumber = :batchNumber
        UNION
        select 
        inv.inventoryID
        , inv.receiptDate
        , inv.batchNumber
        , inv.expirationDate
        , inv.active
        , "LOCATION CHANGE" AS type
        , u.userName
        , invl.creationDate
        , b.binName AS value
        , NULL AS workOrderNumber
        , NULL AS quantity
        from inventory inv 
        LEFT JOIN inventorylocation invl ON inv.inventoryID = invl.inventoryID
        LEFT JOIN binlocation b ON invl.binlocationID = b.binLocationID
        LEFT JOIN users u ON invl.createdBy = u.userID
        where inv.batchNumber = :batchNumber
        UNION
        select 
        inv.inventoryID
        , inv.receiptDate
        , inv.batchNumber
        , inv.expirationDate
        , inv.active
        , "QUANTITY CHANGE" AS type
        , u.userName
        , invq.creationDate
        , invq.inventoryQuantity AS value
        , e.workOrderNumber
        , e.quantity
        from inventory inv 
        LEFT JOIN inventoryUnitQuantity invq ON inv.inventoryID = invq.inventoryID
        LEFT JOIN users u ON invq.createdBy = u.userID
        LEFT JOIN eusage e ON invq.inventoryID = e.inventoryID 
        AND (invq.creationDate <= DATE_ADD(e.creationDate, INTERVAL 1 SECOND) AND invq.creationDate >= DATE_SUB(e.creationDate, INTERVAL 1 SECOND))
        where inv.batchNumber = :batchNumber) x
        WHERE x.value IS NOT NULL
        ORDER BY x.inventoryID, x.creationDate, x.type DESC;
        ';
        $stmt = $connection->prepare($sql);
        $stmt->bindValue(':batchNumber', $batchNumber, PDO::PARAM_STR);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

function getFinishedGoods($workOrderNumber) {
    $connection = connWhitneyUser();
    try {
        $sql = 'select 
        inventoryID
        , skuNumber
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
        , workordernumber
        , receiptID
        , quarantine
        , combinetoID
        , splitfromID
        , ShippingID
        , masterTagID
        from inventory
        where workordernumber = :workordernumber;
        ';
        $stmt = $connection->prepare($sql);
        $stmt->bindValue(':workordernumber', $workOrderNumber, PDO::PARAM_STR);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

function getFinishedGoodHistory($batchNumber) {
    $connection = connWhitneyUser();
    try {
        $sql = 'SELECT x.* 
        FROM
        (select
        inv.inventoryID
        , inv.skuNumber
        , inv.companyID
        , inv.receiptDate
        , inv.batchNumber
        , inv.expirationDate
        , inv.active
        , "STATUS CHANGE" AS type
        , u.userName
        , invs.creationDate
        , st.stateName AS value
        , e.workOrderNumber
        , e.quantity
        from inventory inv 
        LEFT JOIN inventorystatus invs ON inv.inventoryID = invs.inventoryID
        LEFT JOIN inventorystate st ON invs.inventoryStateID = st.inventoryStateID
        LEFT JOIN users u ON invs.createdBy = u.userID
        LEFT JOIN eusage e ON invs.inventoryID = e.inventoryID 
        AND (invs.creationDate <= DATE_ADD(e.creationDate, INTERVAL 1 SECOND) AND invs.creationDate >= DATE_SUB(e.creationDate, INTERVAL 1 SECOND))
        where inv.batchNumber = :batchNumber
        UNION
        select 
        inv.inventoryID
        , inv.skuNumber
        , inv.companyID
        , inv.receiptDate
        , inv.batchNumber
        , inv.expirationDate
        , inv.active
        , "LOCATION CHANGE" AS type
        , u.userName
        , invl.creationDate
        , b.binName AS value
        , NULL AS workOrderNumber
        , NULL AS quantity
        from inventory inv 
        LEFT JOIN inventorylocation invl ON inv.inventoryID = invl.inventoryID
        LEFT JOIN binlocation b ON invl.binlocationID = b.binLocationID
        LEFT JOIN users u ON invl.createdBy = u.userID
        where inv.batchNumber = :batchNumber
        UNION
        select 
        inv.inventoryID
        , inv.skuNumber
        , inv.companyID
        , inv.receiptDate
        , inv.batchNumber
        , inv.expirationDate
        , inv.active
        , "QUANTITY CHANGE" AS type
        , u.userName
        , invq.creationDate
        , invq.inventoryQuantity AS value
        , e.workOrderNumber
        , e.quantity
        from inventory inv 
        LEFT JOIN inventoryUnitQuantity invq ON inv.inventoryID = invq.inventoryID
        LEFT JOIN users u ON invq.createdBy = u.userID
        LEFT JOIN eusage e ON invq.inventoryID = e.inventoryID 
        AND (invq.creationDate <= DATE_ADD(e.creationDate, INTERVAL 1 SECOND) AND invq.creationDate >= DATE_SUB(e.creationDate, INTERVAL 1 SECOND))
        where inv.batchNumber = :batchNumber) x
        WHERE x.value IS NOT NULL
        ORDER BY x.inventoryID, x.creationDate, x.type DESC;
        ';
        $stmt = $connection->prepare($sql);
        $stmt->bindValue(':batchNumber', $batchNumber, PDO::PARAM_STR);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

function getInvSummaryReport($companyID = false, $hold = false, $quarantine = false, $skuNumber = false, $productCode = false) {
    $connection = connWhitneyUser();
    try {
        $sql = "
        SELECT
        ifNull(i.skuNumber, '')
        , ifNull(itf.DESC1, '')
        , ifNull(it.stockingUnit, '')
        , CASE WHEN itf.partNumber = '' THEN it.customerRefNumber ELSE itf.partNumber END AS part_number
        , ifNull(it.customerRefNumber, '')
        , ifNull(c.companyName, '') AS company_name
        , ifNull(count(i.inventoryID), '') AS pallet_Count
        , iFNull(i.batchNumber, '') AS Lot_Code
        , CASE WHEN q.inventoryQuantity = NULL THEN 0 ELSE trim(q.inventoryQuantity / ifNull(it.stockingRatio, 1)) +0 END AS qty
        , CASE WHEN q.inventoryQuantity = NULL THEN 0 ELSE trim((q.inventoryQuantity / ifNull(it.stockingRatio, 1) * count(i.inventoryID))) +0 END AS sub_Total
        FROM inventory i
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
        LEFT JOIN items it
        ON i.skuNumber = it.skuNumber
        LEFT JOIN itemsf itf
        ON i.skuNumber = itf.skuNumber
        LEFT JOIN customer c
        ON i.companyID = c.customerID
        LEFT JOIN quarantine qu
        ON i.inventoryID = qu.inventoryID
        LEFT JOIN reasonCode rc
        ON rc.reasonCodeID = qu.reasonCodeID WHERE ";
        if($companyID != false) {
            $sql .= 'i.companyID = :companyID AND ';
        }
        if($hold != false) {
            $sql .= 'i.hold = :hold AND ';
        }
        if($quarantine != false) {
            $sql .= 'i.quarantine = :quarantine AND ';
        }
        if($skuNumber != false) {
            $sql .= 'i.skuNumber = :skuNumber AND ';
        }
        if($productCode != false) {
            $sql .= 'itf.CAT = :productCode AND ';
        }
        $sql .= '
        s.inventoryStateID != 2
        AND s.inventoryStateID != 3
        AND s.inventoryStateID != 6
        GROUP BY i.skuNumber, itf.DESC1, it.stockingUnit, part_number, it.customerRefNumber, company_name, Lot_Code;
        ';
        $stmt = $connection->prepare($sql);
        if($companyID != false) {
            $stmt->bindValue(':companyID', $companyID, PDO::PARAM_INT);
        }
        if($hold != false) {
            $stmt->bindValue(':hold', $hold, PDO::PARAM_INT);
        }
        if($quarantine != false) {
            $stmt->bindValue(':quarantine', $quarantine, PDO::PARAM_INT);
        }
        if($skuNumber != false) {
            $stmt->bindValue(':skuNumber', $skuNumber, PDO::PARAM_INT);
        }
        if($productCode != false) {
            $stmt->bindValue(':productCode', $productCode, PDO::PARAM_STR);
        }
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

function getInvDiscrepancyReport($companyID = false, $hold = false, $quarantine = false, $skuNumber = false, $productCode = false) {
    $connection = connWhitneyUser();
    try {
        $sql = "
        SELECT
        ifNull(i.skuNumber, '')
        , ifNull(itf.DESC1, '')
        , ifNull(it.stockingUnit, '')
        , CASE WHEN itf.partNumber = '' THEN it.customerRefNumber ELSE itf.partNumber END AS part_number
        , ifNull(it.customerRefNumber, '')
        , ifNull(c.companyName, '') AS company_name
        , ifNull(count(i.inventoryID), '') AS pallet_Count
        , iFNull(i.batchNumber, '') AS Lot_Code
        , ifNull(binTot.totalQty, 0) AS BIN
        , ifNull(itf.QOH2, 0) AS ERP
        , ifNull(binTot.totalQty, 0) - ifNull(itf.QOH2, 0) AS Discrepancy
        FROM inventory i
        LEFT JOIN (SELECT inv.inventoryID, inv.inventoryStateID, inv.creationDate
        FROM inventorystatus inv
        INNER JOIN(SELECT inventoryID, max(inventoryStatusID) inventoryStatusID
        FROM inventorystatus
        GROUP BY inventoryID) ss
        ON inv.inventoryID = ss.inventoryID AND inv.inventoryStatusID = ss.inventoryStatusID) s
        ON i.inventoryID = s.inventoryID

        LEFT JOIN (
            SELECT i.skuNumber, COUNT(qty.inventoryQuantity) AS palletCount, SUM(qty.inventoryQuantity) AS totalQty
            FROM `inventory` i
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
                    ON inv.inventoryID = ss.inventoryID AND inv.inventoryQuantityID = ss.inventoryQuantityID) qty
                ON i.inventoryID = qty.inventoryID
            WHERE s.inventoryStateID <> 2 AND s.inventoryStateID <> 3 AND s.inventoryStateID <> 6 AND i.active = 1
            GROUP BY i.skuNumber) binTot
        ON i.skuNumber = binTot.skuNumber

        LEFT JOIN (SELECT inv.inventoryID, inv.inventoryQuantity, inv.creationDate
        FROM inventoryunitquantity inv
        INNER JOIN(SELECT inventoryID, max(inventoryQuantityID) inventoryQuantityID
        FROM inventoryunitquantity
        GROUP BY inventoryID) ss
        ON inv.inventoryID = ss.inventoryID AND inv.inventoryQuantityID = ss.inventoryQuantityID) q
        ON i.inventoryID = q.inventoryID
        LEFT JOIN items it
        ON i.skuNumber = it.skuNumber
        LEFT JOIN itemsf itf
        ON i.skuNumber = itf.skuNumber
        LEFT JOIN customer c
        ON i.companyID = c.customerID
        LEFT JOIN quarantine qu
        ON i.inventoryID = qu.inventoryID
        LEFT JOIN reasonCode rc
        ON rc.reasonCodeID = qu.reasonCodeID WHERE ";
        if($companyID != false) {
            $sql .= 'i.companyID = :companyID AND ';
        }
        if($hold != false) {
            $sql .= 'i.hold = :hold AND ';
        }
        if($quarantine != false) {
            $sql .= 'i.quarantine = :quarantine AND ';
        }
        if($skuNumber != false) {
            $sql .= 'i.skuNumber = :skuNumber AND ';
        }
        if($productCode != false) {
            $sql .= 'itf.CAT = :productCode AND ';
        }
        $sql .= '
        s.inventoryStateID != 2
        AND s.inventoryStateID != 3
        AND s.inventoryStateID != 6
        GROUP BY Lot_Code, i.skuNumber, itf.DESC1, i.receiptDate;
        ';
        $stmt = $connection->prepare($sql);
        if($companyID != false) {
            $stmt->bindValue(':companyID', $companyID, PDO::PARAM_INT);
        }
        if($hold != false) {
            $stmt->bindValue(':hold', $hold, PDO::PARAM_INT);
        }
        if($quarantine != false) {
            $stmt->bindValue(':quarantine', $quarantine, PDO::PARAM_INT);
        }
        if($skuNumber != false) {
            $stmt->bindValue(':skuNumber', $skuNumber, PDO::PARAM_INT);
        }
        if($productCode != false) {
            $stmt->bindValue(':productCode', $productCode, PDO::PARAM_STR);
        }
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

function getInvDetailedReport($companyID = false, $hold = false, $quarantine = false, $skuNumber = false, $productCode = false) {
    $connection = connWhitneyUser();
    try {
        $sql = "
        SELECT
        i.inventoryID
        , ifNull(c.companyName, '') AS company_name
        , ifNull(i.skuNumber, '') AS skuNumber
        , ifNull(itf.DESC1, '') AS DESC1
        , ifNull(itf.DESC2, '') AS DESC2
        , ifNull(b.binName, '') AS binName
        , ifNull(it.stockingUnit, '') AS stockingQty
        , CASE WHEN q.inventoryQuantity = NULL THEN 0 ELSE trim(q.inventoryQuantity / ifNull(it.stockingRatio, 1)) +0 END AS qty
        , iFNull(i.batchNumber, '') AS Lot_Code
        , iFNull(i.palletNumber, '') AS  Pallet_Number
        , i.receiptDate
        , s.creationDate AS last_status_change
        , invs.stateName
        , DATEDIFF(NOW(), i.receiptDate) AS age
        , CASE WHEN i.quarantine =  TRUE THEN 'Q' ELSE '' END AS quarantine
        , ifNull(rc.reasonCodeName, '') AS reason
        FROM inventory i
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
        LEFT JOIN (SELECT inv.inventoryID, inv.binLocationID
                    FROM inventorylocation inv
                                INNER JOIN (SELECT inventoryID, max(inventoryLocationID) inventoryLocationID
                                FROM inventorylocation
                                GROUP BY inventoryID) ss
                    ON inv.inventoryID = ss.inventoryID AND inv.inventoryLocationID = ss.inventoryLocationID) l
        ON i.inventoryID = l.inventoryID
        LEFT JOIN `binlocation` b
        ON l.binlocationID = b.binlocationID
        LEFT JOIN inventoryState invs
        ON s.inventoryStateID = invs.inventorystateID
        LEFT JOIN items it
        ON i.skuNumber = it.skuNumber
        LEFT JOIN itemsf itf
        ON i.skuNumber = itf.skuNumber
        LEFT JOIN customer c
        ON i.companyID = c.customerID
        LEFT JOIN quarantine qu
        ON i.inventoryID = qu.inventoryID
        LEFT JOIN reasonCode rc
        ON rc.reasonCodeID = qu.reasonCodeID
        WHERE ";
        if($companyID != false) {
            $sql .= 'i.companyID = :companyID AND ';
        }
        if($hold != false) {
            $sql .= 'i.hold = :hold AND ';
        }
        if($quarantine != false) {
            $sql .= 'i.quarantine = :quarantine AND ';
        }
        if($skuNumber != false) {
            $sql .= 'i.skuNumber = :skuNumber AND ';
        }
        if($productCode != false) {
            $sql .= 'itf.CAT = :productCode AND ';
        }
        $sql .= 's.inventoryStateID != 2
        AND s.inventoryStateID != 3
        AND s.inventoryStateID != 6
        AND i.active = 1
        AND (NOT palletNumber = "Pending Shipment" OR palletNumber IS NULL)
        ORDER BY i.skuNumber, i.batchNumber, i.palletNumber, i.expirationDate
        ';
        $stmt = $connection->prepare($sql);
        if($companyID != false) {
            $stmt->bindValue(':companyID', $companyID, PDO::PARAM_INT);
        }
        if($hold != false) {
            $stmt->bindValue(':hold', $hold, PDO::PARAM_INT);
        }
        if($quarantine != false) {
            $stmt->bindValue(':quarantine', $quarantine, PDO::PARAM_INT);
        }
        if($skuNumber != false) {
            $stmt->bindValue(':skuNumber', $skuNumber, PDO::PARAM_INT);
        }
        if($productCode != false) {
            $stmt->bindValue(':productCode', $productCode, PDO::PARAM_STR);
        }
        $stmt->execute();
        // $headers = $stmt -> getColumnHeaders(0);
        // $headers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $data = $stmt->fetchAll(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    // $data = array_push($headers, $data);
    return $data;
}

function getColumnHeaders($tableName) {
    $connection = connWhitneyUser();
    try {
        $sql = 'SELECT column_name
        FROM information_schema.columns
        WHERE table_name = :tableName';
        $stmt = $connection->prepare($sql);
        $stmt->bindValue(':tableName', $tableName, PDO::PARAM_STR);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

function getCustomQueries() {
    $connection = connWhitneyUser();
    try {
        $sql = 'SELECT customQueryID, queryName FROM customQuery;';
        $stmt = $connection->prepare($sql);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

function getCustomQuerySql($id) {
    $connection = connWhitneyUser();
    try {
        $sql = 'SELECT sqlText, sqlText2 FROM customQuery WHERE customQueryID = :customQueryID';
        $stmt = $connection->prepare($sql);
        $stmt->bindValue(':customQueryID', $id, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    $data = $data != false ? $data : false;
    return $data;
}

function getCustomQueryVars($id) {
    $connection = connWhitneyUser();
    try {
        $sql = 'SELECT *
        FROM customQueryVars
        WHERE customQueryID = :customQueryID;
        ';
        $stmt = $connection->prepare($sql);
        $stmt->bindValue(':customQueryID', $id, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_NUM);
        $stmt->closeCursor(); 
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

function buildCustomReport($sql, $data, $varNames) {
    $connection = connWhitneyUser();
    try {
        $stmt = $connection->prepare($sql);
        for($i = 0; $i < sizeof($varNames); $i++) {
            if($data[$i] != null) {
                $stmt->bindValue(':' . trim($varNames[$i]), $data[$i], PDO::PARAM_STR);
            }
        }
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}