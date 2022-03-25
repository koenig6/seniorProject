<?php

require $_SERVER['DOCUMENT_ROOT'] . '/library/library.php';
require $_SERVER['DOCUMENT_ROOT'] . '/library/model.php';

/*
 * Item Search query
 */
function itemSearch($searchTerm, $foresite = null) {
    $connection = connWhitneyUser();
    try {
        /*
         * Modified to use the max ID rather than the max creation date
         */
        
        $sql = 'SELECT i.skuNumber
                , c.companyName
                , i.partNumber
                , i.DESC1
                , i.DESC2
                , ifNull(i.QOH2, 0) - ifNull(i.QPK, 0) AS qav
                , ifNull(binTot.totalQty, 0) AS binQty -- Whitney BIN
                , ifNull(i.QOH2, 0) AS QOH2 -- ERP
                FROM itemsf i
                LEFT JOIN associatedcustomer a
                ON i.skuNumber = a.skuNumber
                LEFT JOIN customer c
                ON a.customerID = c.customerID
                LEFT JOIN (
                    SELECT i.skuNumber, COUNT(q.inventoryQuantity) AS palletCount, SUM(q.inventoryQuantity) AS totalQty
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
                            ON inv.inventoryID = ss.inventoryID AND inv.inventoryQuantityID = ss.inventoryQuantityID) q
                        ON i.inventoryID = q.inventoryID
                    WHERE s.inventoryStateID <> 2 AND s.inventoryStateID <> 3 AND s.inventoryStateID <> 6 AND i.active = 1
                    GROUP BY i.skuNumber) binTot
                ON i.skuNumber = binTot.skuNumber
                WHERE ( i.skuNumber LIKE \'%' . $searchTerm . '%\' OR
                c.companyName LIKE \'%' . $searchTerm . '%\' OR
                i.partNumber LIKE \'%' . $searchTerm . '%\' OR
                i.DESC1 LIKE \'%' . $searchTerm . '%\' OR
                i.DESC2 LIKE \'%' . $searchTerm . '%\' OR
                i.DESC3 LIKE \'%' . $searchTerm . '%\' OR
                i.DESC4 LIKE \'%' . $searchTerm . '%\' OR
                i.DESC5 LIKE \'%' . $searchTerm . '%\' OR
                i.DESC5 LIKE \'%' . $searchTerm . '%\' OR
                i.CAT   LIKE \'%' . $searchTerm . '%\' ) ';
                if($foresite == null) {
                    $sql .= 'AND i.source != "f" ';
                }
                $sql .= '
                ORDER BY i.skuNumber ASC
                LIMIT 0, 100';
        $stmt = $connection->prepare($sql);
        $stmt->execute();
        $items = $stmt->fetchAll();
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $items;
}

function getSkuList($searchTerm) {
    $connection = connWhitneyUser();
    try {
        /*
         * Modified to use the max ID rather than the max creation date
         */
        
        $sql = 'SELECT i.skuNumber
                FROM itemsf i
                LEFT JOIN customer c
                ON a.customerID = c.customerID
                WHERE i.skuNumber LIKE \'%' . $searchTerm . '%\' OR
                c.companyName LIKE \'%' . $searchTerm . '%\' OR
                i.partNumber LIKE \'%' . $searchTerm . '%\' OR
                i.DESC1 LIKE \'%' . $searchTerm . '%\' OR
                i.DESC2 LIKE \'%' . $searchTerm . '%\' OR
                i.DESC3 LIKE \'%' . $searchTerm . '%\' OR
                i.DESC4 LIKE \'%' . $searchTerm . '%\' OR
                i.DESC5 LIKE \'%' . $searchTerm . '%\' OR
                i.DESC5 LIKE \'%' . $searchTerm . '%\' OR
                i.CAT   LIKE \'%' . $searchTerm . '%\'
                ORDER BY i.skuNumber ASC limit 15';
        $stmt = $connection->prepare($sql);
        $stmt->execute();
        $items = $stmt->fetchAll();
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $items;
}

function getItemData($skuNumber){
    $connection = connWhitneyUser();
    try{
        $sql = 'SELECT f.skuNumber,
                       f.partNumber,
                       f.DESC1, 
                       f.DESC2,
                       f.DESC3,
                       f.DESC4,
                       f.DESC5,
                       f.PUT,
                       f.SCOST,
                       f.PFCTR,
                       f.QOH2,
                       f.QPK,
                       i.timeStudy,
                       l.paperColor,
                       i.palletQty,
                       i.kit,
                       i.component,
                       i.poRequired,
                       i.batchRequired,
                       i.billablePallet,
                       i.rackCharge,
                       i.bulkCharge,
                       i.caseCharge,
                       uc.userName,
                       i.creationDate,
                       um.userName,
                       i.modifiedDate,
                       i.palletNumberRequired,
                       i.customerRefNumber,
                       i.stockingUnit,
                       i.stockingRatio,
                       i.organic,
                       i.kosher,
                       i.alergen,
                       f.cat,
                       i.notStackable,
                       i.consumable,
                       i.caseQty,
                       i.palletPosition,
                       i.paperColorID,
                       i.timeStudyInSecs
                       FROM itemsf f
                       LEFT JOIN items i
                       ON f.skuNumber = i.skuNumber
                       LEFT JOIN labels l
                       ON i.paperColorID = l.labelID
                       LEFT JOIN users uc
                       ON i.createdBy = uc.userID
                       LEFT JOIN users um
                       ON i.modifiedBy = um.userID 
                       WHERE f.skuNumber = :skuNumber';
        $stmt = $connection->prepare($sql);
            $stmt->bindValue(':skuNumber', $skuNumber, PDO::PARAM_INT);
            $stmt->execute();
            $data = $stmt->fetch(PDO::FETCH_NUM);
            $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

function getItemDetails($skuNumber){
    $connection = connWhitneyUser();
    try {
     $sql = 'SELECT * FROM items WHERE skuNumber = :skuNumber';
        $stmt = $connection->prepare($sql);
        $stmt->bindValue(':skuNumber', $skuNumber, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

function getPaperColor() {
    $connection = connWhitneyUser();
    try {
     $sql = 'SELECT * FROM labels';
        $stmt = $connection->prepare($sql);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

function updateItem($skuNumber
                    , $paperColorID
                    , $palletQty
                    , $kit
                    , $component
                    , $rackCharge
                    , $bulkCharge
                    , $caseCharge
                    , $poRequired
                    , $batchRequired
                    , $modifiedBy
                    , $timeStudy
                    , $palletNumberRequired
                    , $customerRefNumber
                    , $stockingUnit
                    , $stockingRatio
                    , $organic
                    , $kosher
                    , $alergen
                    , $notStackable
                    , $consumable
                    , $caseQty
                    , $palletPosition){
    $connection = connWhitneyUser();
    try {
       $sql = 'UPDATE items SET 
                    paperColorID = :paperColorID
                  , palletQty = :palletQty
                  , kit = :kit
                  , component = :component
                  , rackCharge = :rackCharge
                  , bulkCharge = :bulkCharge
                  , caseCharge = :caseCharge
                  , poRequired = :poRequired
                  , batchRequired = :batchRequired
                  , customerRefNumber = :customerRefNumber
                  , stockingUnit = :stockingUnit
                  , stockingRatio = :stockingRatio
                  , organic = :organic
                  , kosher = :kosher
                  , alergen = :alergen
                  , modifiedBy = :modifiedBy
                  , timeStudyInSecs = :timeStudy
                  , palletNumberRequired = :palletNumberRequired
                  , notStackable = :notStackable
                  , consumable = :consumable
                  , caseQty = :caseQty
                  , palletPosition = :palletPosition
                  WHERE skuNumber = :skuNumber';
       $stmt = $connection->prepare($sql);
       $stmt->bindParam(':paperColorID', $paperColorID, PDO::PARAM_INT);
       $stmt->bindParam(':palletQty', $palletQty, PDO::PARAM_INT);
       $stmt->bindParam(':kit', $kit, PDO::PARAM_INT);
       $stmt->bindParam(':component', $component, PDO::PARAM_INT);
       $stmt->bindParam(':rackCharge', $rackCharge, PDO::PARAM_INT);
       $stmt->bindParam(':bulkCharge', $bulkCharge, PDO::PARAM_INT);
       $stmt->bindParam(':caseCharge', $caseCharge, PDO::PARAM_INT);
       $stmt->bindParam(':poRequired', $poRequired, PDO::PARAM_INT);
       $stmt->bindParam(':batchRequired', $batchRequired, PDO::PARAM_INT);
       $stmt->bindParam(':modifiedBy', $modifiedBy, PDO::PARAM_INT);
       $stmt->bindParam(':skuNumber', $skuNumber, PDO::PARAM_INT);
       $stmt->bindParam(':timeStudy', $timeStudy, PDO::PARAM_INT);
       $stmt->bindParam(':palletNumberRequired', $palletNumberRequired, PDO::PARAM_INT);
       $stmt->bindParam(':customerRefNumber', $customerRefNumber, PDO::PARAM_INT);
       $stmt->bindParam(':stockingUnit', $stockingUnit, PDO::PARAM_INT);
       $stmt->bindParam(':stockingRatio', $stockingRatio, PDO::PARAM_INT);
       $stmt->bindParam(':organic', $organic, PDO::PARAM_INT);
       $stmt->bindParam(':kosher', $kosher, PDO::PARAM_INT);
       $stmt->bindParam(':alergen', $alergen, PDO::PARAM_INT);

       $stmt->bindParam(':notStackable', $notStackable, PDO::PARAM_INT);
       $stmt->bindParam(':consumable', $consumable, PDO::PARAM_INT);
       $stmt->bindParam(':caseQty', $caseQty, PDO::PARAM_INT);
       $stmt->bindParam(':palletPosition', $palletPosition, PDO::PARAM_INT);
       $stmt->execute();
       $updateRow = $stmt->rowCount();
       $stmt->closeCursor();
    } catch (PDOException $ex) {
           return FALSE;
       }
       return $updateRow;
    
    
}

function updateItemf($skuNumber
                    , $partNumber
                    , $desc1
                    , $desc2
                    , $desc3
                    , $desc4
                    , $desc5
                    , $pricingUnit
                    , $pricingFactor
                    , $cats){

    $cats = empty($cats) ? null : $cats;

    $connection = connWhitneyUser();
    try {
       $sql = 'UPDATE itemsf SET 
                    partNumber = :partNumber
                  , desc1 = :desc1
                  , desc2 = :desc2
                  , desc3 = :desc3
                  , desc4 = :desc4
                  , desc5 = :desc5
                  , PUT = :pricingUnit
                  , PFCTR = :pricingFactor
                  , cat = :cats
                  WHERE skuNumber = :skuNumber';
       $stmt = $connection->prepare($sql);
       $stmt->bindParam(':partNumber', $partNumber, PDO::PARAM_STR);
       $stmt->bindParam(':desc1', $desc1, PDO::PARAM_STR);
       $stmt->bindParam(':desc2', $desc2, PDO::PARAM_STR);
       $stmt->bindParam(':desc3', $desc3, PDO::PARAM_STR);
       $stmt->bindParam(':desc4', $desc4, PDO::PARAM_STR);
       $stmt->bindParam(':desc5', $desc5, PDO::PARAM_STR);
       $stmt->bindParam(':pricingUnit', $pricingUnit, PDO::PARAM_STR);
       $stmt->bindParam(':pricingFactor', $pricingFactor, PDO::PARAM_STR);
       $stmt->bindParam(':skuNumber', $skuNumber, PDO::PARAM_INT);
       $stmt->bindParam(':cats', $cats, PDO::PARAM_STR);
       $stmt->execute();
       $updateRow = $stmt->rowCount();
       $stmt->closeCursor();
    } catch (PDOException $ex) {
           return FALSE;
       }
       return $updateRow;
    
}

// Get serial status
function getInventoryData($sku) {
    $connection = connWhitneyUser();
    try {
/*     
    Modified to use maximum ID rather than date.
*/        
    $sql = 'SELECT i.inventoryID, b.binName, q.inventoryQuantity, i.receiptDate, ivs.stateName, i.quarantine
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
    LEFT JOIN inventorystate ivs
    ON s.inventoryStateID = ivs.inventoryStateID
    WHERE s.inventoryStateID <> 2 AND s.inventoryStateID <> 3 AND s.inventoryStateID <> 6 AND i.skuNumber = :sku AND i.active = 1
     ORDER BY i.receiptDate, i.inventoryID ASC';
    
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

function addAssociatedCustomer($customerID, $skuNumber, $userID){
    $connection = connWhitneyUser();
    try {
        $sql = 'INSERT INTO associatedCustomer (
                                        skuNumber
                                        , customerID
                                        , createdBy
                                        , creationDate
                                        , modifiedBy
                                        , modifiedDate)
                                        
                    VALUES (            :skuNumber
                                        , :customerID
                                        , :userID
                                        , Now()
                                        , :userID
                                        , Now())';
    $stmt = $connection->prepare($sql);
    $stmt->bindParam(':customerID', $customerID, PDO::PARAM_INT);
    $stmt->bindParam(':skuNumber', $skuNumber, PDO::PARAM_INT);
    $stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
    $stmt->execute();
        $insertRow = $stmt->rowCount();
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $insertRow;
    
}

function addAssociatedVendor($vendorID, $skuNumber, $userID){
    $connection = connWhitneyUser();
    try {
        $sql = 'INSERT INTO associatedVendor (
                                        skuNumber
                                        , vendorID
                                        , createdBy
                                        , creationDate
                                        , modifiedBy
                                        , modifiedDate)
                                        
                    VALUES (            :skuNumber
                                        , :vendorID
                                        , :userID
                                        , Now()
                                        , :userID
                                        , Now())';
    $stmt = $connection->prepare($sql);
    $stmt->bindParam(':vendorID', $vendorID, PDO::PARAM_INT);
    $stmt->bindParam(':skuNumber', $skuNumber, PDO::PARAM_INT);
    $stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
    $stmt->execute();
        $insertRow = $stmt->rowCount();
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $insertRow;
    
}

function validateAssociatedCustomer($customerID, $skuNumber){
    $connection = connWhitneyUser();
    try {
     $sql = 'SELECT customerID FROM associatedCustomer WHERE skuNumber = :skuNumber AND customerID = :customerID';
        $stmt = $connection->prepare($sql);
        $stmt->bindValue(':skuNumber', $skuNumber, PDO::PARAM_INT);
        $stmt->bindValue(':customerID', $customerID, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

function validateAssociatedVendor($vendorID, $skuNumber){
    $connection = connWhitneyUser();
    try {
     $sql = 'SELECT vendorID FROM associatedVendor WHERE skuNumber = :skuNumber AND vendorID = :vendorID';
        $stmt = $connection->prepare($sql);
        $stmt->bindValue(':skuNumber', $skuNumber, PDO::PARAM_INT);
        $stmt->bindValue(':vendorID', $vendorID, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

function deleteAssociatedCustomer($associatedCustomerID){
    $connection = connWhitneyAdmin();
    
    try {
        $sql = 'DELETE FROM associatedCustomer
                WHERE associatedCustomerID = :associatedCustomerID';
        
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':associatedCustomerID', $associatedCustomerID, PDO::PARAM_INT);
        $stmt->execute();
        $deleteRow = $stmt->rowCount();
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $deleteRow;
}

function deleteAssociatedVendor($associatedVendorID){
    $connection = connWhitneyAdmin();
    
    try {
        $sql = 'DELETE FROM associatedVendor
                WHERE associatedVendorID = :associatedVendorID';
        
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':associatedVendorID', $associatedVendorID, PDO::PARAM_INT);
        $stmt->execute();
        $deleteRow = $stmt->rowCount();
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $deleteRow;
}

function getMissingItemDetails(){
    $connection = connWhitneyUser();
    try {
     $sql = 'SELECT x.skuNumber from itemsf x
            LEFT JOIN items y
            ON x.skuNumber = y.skuNumber
            WHERE y.skuNumber is null';
        $stmt = $connection->prepare($sql);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

function addNewItem($skuNumber, $userID){
    $connection = connWhitneyUser();
    try {
        $sql = 'INSERT INTO items (
                                        skuNumber
                                        , timeStudy
                                        , paperColorID
                                        , palletQty
                                        , component
                                        , kit
                                        , poRequired
                                        , batchRequired
                                        , billablePallet
                                        , palletNumberRequired
                                        , rackCharge
                                        , bulkCharge
                                        , caseCharge
                                        , createdBy
                                        , creationDate
                                        , modifiedBy
                                        , modifiedDate)
                                        
                    VALUES (            :skuNumber
                                        , 0
                                        , 1
                                        , 0
                                        , 1
                                        , 0
                                        , 0
                                        , 0
                                        , 0
                                        , 0
                                        , 0
                                        , 0
                                        , 0
                                        , :userID
                                        , Now()
                                        , :userID
                                        , Now())';
    $stmt = $connection->prepare($sql);
    $stmt->bindParam(':skuNumber', $skuNumber, PDO::PARAM_INT);
    $stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
    $stmt->execute();
        $insertRow = $stmt->rowCount();
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $insertRow;
    
}

// Get serial status
function detailedInventoryData($sku, $pick) {
    $connection = connWhitneyUser();
    try {
        $sql = 'SELECT i.inventoryID
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
            , b.binlocationID
            , b.binName
            , q.inventoryQuantity
            , i.workOrderNumber
            , invst.stateName
            , i.quarantine
            , i.hold
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
   LEFT JOIN inventoryState invst
   ON s.inventoryStateID = invst.inventoryStateID
   WHERE s.inventoryStateID <> 2 AND s.inventoryStateID <> 3 AND s.inventoryStateID <> 6 AND i.skuNumber = :sku AND i.active = 1';
    
        
    if ($pick == 0){
        $sql .= ' ORDER BY i.receiptDate ASC';
    }else{
        $sql .= ' AND s.inventoryStateID <> 1 ORDER BY i.receiptDate ASC';
    }
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

function insertItemDetailed($newSkuNumber
    , $timeStudy
    , $paperColor
    , $palletQty
    , $customerRefNumber
    , $stockingUnit
    , $stockingRatio
    , $organic
    , $kosher
    , $alergen
    , $rackCharge
    , $kit
    , $component
    , $poRequired
    , $batchRequired
    , $palletNumberRequired
    , $cats
    , $notStackable
    , $consumable
    , $caseQty
    , $palletPos) {

    $connection = connWhitneyUser();
    try {
        $sql = 'INSERT INTO items (
                                        skuNumber
                                        , timeStudyInSecs
                                        , paperColorID
                                        , palletQty
                                        , customerRefNumber
                                        , stockingUnit
                                        , stockingRatio
                                        , organic
                                        , kosher
                                        , alergen
                                        , rackCharge
                                        , kit
                                        , component
                                        , poRequired
                                        , batchRequired
                                        , palletNumberRequired
                                        , notStackable
                                        , consumable
                                        , caseQty
                                        , palletPosition)
                                VALUES (:newSkuNumber
                                        , :timeStudy
                                        , :paperColor
                                        , :palletQty
                                        , :customerRefNumber
                                        , :stockingUnit
                                        , :stockingRatio
                                        , :organic
                                        , :kosher
                                        , :alergen
                                        , :rackCharge
                                        , :kit
                                        , :component
                                        , :poRequired
                                        , :batchRequired
                                        , :palletNumberRequired
                                        , :notStackable
                                        , :consumable
                                        , :caseQty
                                        , :palletPos)';
    $stmt = $connection->prepare($sql);
    $stmt->bindParam(':newSkuNumber', $newSkuNumber, PDO::PARAM_INT);
    $stmt->bindParam(':timeStudy', $timeStudy, PDO::PARAM_INT);
    $stmt->bindParam(':paperColor', $paperColor, PDO::PARAM_INT);
    $stmt->bindParam(':palletQty', $palletQty, PDO::PARAM_INT);
    $stmt->bindParam(':customerRefNumber', $customerRefNumber, PDO::PARAM_INT);
    $stmt->bindParam(':stockingUnit', $stockingUnit, PDO::PARAM_STR);
    $stmt->bindParam(':stockingRatio', $stockingRatio, PDO::PARAM_STR);
    $stmt->bindParam(':organic', $organic, PDO::PARAM_INT);
    $stmt->bindParam(':kosher', $kosher, PDO::PARAM_INT);
    $stmt->bindParam(':alergen', $alergen, PDO::PARAM_INT);
    $stmt->bindParam(':rackCharge', $rackCharge, PDO::PARAM_INT);
    $stmt->bindParam(':kit', $kit, PDO::PARAM_INT);
    $stmt->bindParam(':component', $component, PDO::PARAM_INT);
    $stmt->bindParam(':poRequired', $poRequired, PDO::PARAM_INT);
    $stmt->bindParam(':batchRequired', $batchRequired, PDO::PARAM_INT);
    $stmt->bindParam(':palletNumberRequired', $palletNumberRequired, PDO::PARAM_INT);
    $stmt->bindParam(':notStackable', $notStackable, PDO::PARAM_INT);
    $stmt->bindParam(':consumable', $consumable, PDO::PARAM_INT);
    $stmt->bindParam(':caseQty', $caseQty, PDO::PARAM_INT);
    $stmt->bindParam(':palletPos', $palletPos, PDO::PARAM_INT);
    $stmt->execute();
        $insertRow = $stmt->rowCount();
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $sql;

}

function insertItem($newSkuNumber, $partNumber, $description1, $description2, $description3, $description4, $description5, $PUT, $cats, $PFCTR, $stockingUnit){
    $connection = connWhitneyUser();
    try {
        $sql = 'INSERT INTO itemsf (
                                        skuNumber
                                        , partNumber
                                        , DESC1
                                        , DESC2
                                        , DESC3
                                        , DESC4
                                        , DESC5
                                        , PUT
                                        , CAT
                                        , PFCTR
                                        , UNIT
                                        , source)
                                VALUES (:skuNumber
                                        , :partNumber
                                        , :DESC1
                                        , :DESC2
                                        , :DESC3
                                        , :DESC4
                                        , :DESC5
                                        , :pricingUnit
                                        , :cats
                                        , :PFCTR
                                        , :inventoryUnit
                                        , "W")';
    $stmt = $connection->prepare($sql);
    $stmt->bindParam(':skuNumber', $newSkuNumber, PDO::PARAM_STR);
    $stmt->bindParam(':partNumber', $partNumber, PDO::PARAM_STR);
    $stmt->bindParam(':DESC1', $description1, PDO::PARAM_STR);
    $stmt->bindParam(':DESC2', $description2, PDO::PARAM_STR);
    $stmt->bindParam(':DESC3', $description3, PDO::PARAM_STR);
    $stmt->bindParam(':DESC4', $description4, PDO::PARAM_STR);
    $stmt->bindParam(':DESC5', $description5, PDO::PARAM_STR);
    $stmt->bindParam(':pricingUnit', $PUT, PDO::PARAM_STR);
    $stmt->bindParam(':inventoryUnit', $stockingUnit, PDO::PARAM_STR);
    $stmt->bindParam(':cats', $cats, PDO::PARAM_STR);
    $stmt->bindParam(':PFCTR', $PFCTR, PDO::PARAM_STR);
    $stmt->execute();
        $insertRow = $stmt->rowCount();
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $sql;
    
}

function insertPOHeader($vendorAddressID, $ShipToAddressID, $orderDate, $requiredDate, $shipVia, $notes, $userID, $ref){
    $connection = connWhitneyUser();
    try {
        $sql = 'INSERT INTO purchaseOrder (
                                        customerID
                                        , customerShipToID
                                        , orderDate
                                        , requiredDate
                                        , shipVia
                                        , notes
                                        , createdBy
                                        , creationDate
                                        , modifiedBy
                                        , modifiedDate
                                        , purchaseOrderType
                                        , reference)
                    VALUES (            :vendorID
                                        , :vendorShipToID
                                        , :orderDate
                                        , :requiredDate
                                        , :shipVia
                                        , :notes
                                        , :userID
                                        , Now()
                                        , :userID
                                        , Now()
                                        , 1
                                        , :ref)';
    $stmt = $connection->prepare($sql);
    $stmt->bindParam(':vendorID', $vendorAddressID, PDO::PARAM_INT);
    $stmt->bindParam(':vendorShipToID', $ShipToAddressID, PDO::PARAM_INT);
    $stmt->bindParam(':orderDate', $orderDate, PDO::PARAM_STR);
    $stmt->bindParam(':requiredDate', $requiredDate, PDO::PARAM_STR);
    $stmt->bindParam(':shipVia', $shipVia, PDO::PARAM_INT);
    $stmt->bindParam(':notes', $notes, PDO::PARAM_STR);
    $stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
    $stmt->bindParam(':ref', $ref, PDO::PARAM_STR);
    $stmt->execute();
    $id = $connection->lastInsertId();
        $insertRow = $stmt->rowCount();
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $id;
}

function updatePO($customerAddress, $ShipToAddress, $poNo, $orderDate, $requiredDate, $refNo, $shipVia, $notes) {
    $connection = connWhitneyUser();
    try {
     $sql = 'UPDATE purchaseOrder
            SET 
            customerID = :customerAddress
            , customerShipToID = :ShipToAddress
            , orderDate = :orderDate
            , requiredDate = :requiredDate
            , reference = :refNo
            , shipVia = :shipVia
            , notes = :notes
        WHERE purchaseOrderID = :poNo;
     ';
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':poNo', $poNo, PDO::PARAM_STR);
        $stmt->bindParam(':orderDate', $orderDate, PDO::PARAM_STR);
        $stmt->bindParam(':requiredDate', $requiredDate, PDO::PARAM_STR);
        $stmt->bindParam(':refNo', $refNo, PDO::PARAM_STR);
        $stmt->bindParam(':shipVia', $shipVia, PDO::PARAM_STR);
        $stmt->bindParam(':notes', $notes, PDO::PARAM_STR);
        $stmt->bindParam(':ShipToAddress', $ShipToAddress, PDO::PARAM_STR);
        $stmt->bindParam(':customerAddress', $customerAddress, PDO::PARAM_STR);
        $stmt->execute();
        $updateRow = $stmt->rowCount();
        $stmt->closeCursor();
     } catch (PDOException $ex) {
            return FALSE;
        }
        return $updateRow;
     
}

function flagPoiItem($hold, $poiId) {
    $connection = connWhitneyUser();
    try {
     $sql = 
     'UPDATE purchaseOrderItems
      SET hold = :hold
      WHERE purchaseOrderItemsID = :poiId;
     ';
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':hold', $hold, PDO::PARAM_INT);
        $stmt->bindParam(':poiId', $poiId, PDO::PARAM_INT);
        $stmt->execute();
        $updateRow = $stmt->rowCount();
        $stmt->closeCursor();
     } catch (PDOException $ex) {
            return FALSE;
        }
        return $updateRow;
     
}

function getPOList($type, $complete = false){

    $complete = $complete ? 0 : 1;
    
    $connection = connWhitneyUser();
    try {
     $sql = 'SELECT purchaseOrderID, requiredDate, customerShipToID from purchaseOrder
            WHERE purchaseOrderType = :type AND complete != :complete';
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':type', $type, PDO::PARAM_INT);
        $stmt->bindParam(':complete', $complete, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

function getSearchedPO($search, $customerID, $start, $end, $complete){

    $complete = $complete ? 0 : 1;

    $connection = connWhitneyUser();
    try {
    $sql = 'SELECT 
            DISTINCT po.purchaseOrderID
            , po.requiredDate
            , po.customerShipToID 
            from purchaseOrder po
            LEFT JOIN purchaseOrderItems poi
            ON po.purchaseOrderID = poi.purchaseOrderID
            LEFT JOIN itemsf i
            ON i.skuNumber = poi.skuNumber
            WHERE po.purchaseOrderType = 1 AND po.complete != :complete';
            
            if($search != null) {
                $sql .= ' AND po.purchaseOrderID LIKE \'%' . $search . '%\' 
                OR po.reference LIKE \'%' . $search . '%\'
                OR poi.skuNumber LIKE \'%' . $search . '%\'
                OR i.partNumber LIKE \'%' . $search . '%\'';
            }

            if($start != null && $end != null) {
                $sql .= ' AND po.requiredDate BETWEEN CAST(:start AS DATE) AND CAST(:end AS DATE)';
            }

            if($customerID != false) { $sql .= ' AND po.customerID = :customer'; }

        $stmt = $connection->prepare($sql);
        if($start != null && $end != null) { 
            $stmt->bindParam(':start', $start, PDO::PARAM_STR); 
            $stmt->bindParam(':end', $end, PDO::PARAM_STR); 
        }
        if($customerID != false) { $stmt->bindParam(':customer', $customerID, PDO::PARAM_INT);  }
        $stmt->bindParam(':complete', $complete, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

function getPOData($poNumber){
    //PO Header
    $connection = connWhitneyUser();
    try {
     $sql = 'SELECT po.purchaseOrderID, po.customerID, po.customerShipToID, po.orderDate, po.requiredDate, po.shipVia, po.notes, po.reference, c.companyName, st.name, po.complete
            FROM purchaseOrder po
            LEFT JOIN customer c
            ON po.customerID = c.customerID
            LEFT JOIN shipto st
            ON po.customerShipToID = st.shiptono and po.customerID = st.cscode
            WHERE purchaseOrderID = :poNumber';
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':poNumber', $poNumber, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}




function addPOItem($poNo, $orderItemNo, $orderItemQty, $lineNo, $lotCode, $palletNumber, $hold){
    $connection = connWhitneyUser();
    try {
        $sql = 'INSERT INTO purchaseorderitems (
                                        purchaseOrderID
                                        , skuNumber
                                        , orderQty
                                        , lineItem
                                        , lotCode
                                        , palletNumber
                                        , hold)
                    VALUES (              :poNo
                                        , :orderItemNo
                                        , :orderItemQty
                                        , :lineNo
                                        , :lotCode
                                        , :palletNumber
                                        , :hold)';
    $stmt = $connection->prepare($sql);
    $stmt->bindParam(':poNo', $poNo, PDO::PARAM_STR);
    $stmt->bindParam(':orderItemNo', $orderItemNo, PDO::PARAM_INT);
    $stmt->bindParam(':orderItemQty', $orderItemQty, PDO::PARAM_INT);
    $stmt->bindParam(':lineNo', $lineNo, PDO::PARAM_INT);
    $stmt->bindParam(':lotCode', $lotCode, PDO::PARAM_STR);
    $stmt->bindParam(':palletNumber', $palletNumber, PDO::PARAM_STR);
    $stmt->bindParam(':hold', $hold, PDO::PARAM_INT);
    $stmt->execute();
        $insertRow = $stmt->rowCount();
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $insertRow;
    
}

function getOrderItem($orderNumber){
    $connection = connWhitneyUser();
    try {
        $sql = 'SELECT poi.purchaseOrderID
        , poi.skuNumber
        , poi.orderQty
        , poi.lineItem
        , itf.desc1
        , poi.purchaseOrderItemsID
        , poi.palletNumber
        , poi.lotCode
        , poi.complete
        , poi.hold
        , r.receiptQty
        from purchaseorderitems poi
        left join itemsf itf
        on poi.skuNumber = itf.skuNumber
        left join receiving r
        on r.purchaseOrderItemsID = poi.purchaseOrderItemsID
        WHERE purchaseOrderID = :orderNumber';
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':orderNumber', $orderNumber, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

function getItemNotes($skuNumber) {
    $connection = connWhitneyUser();
    try {
        $sql = 'SELECT notes
        from items
        WHERE skuNumber = :skuNumber';
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':skuNumber', $skuNumber, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data[0][0];
}

function updateItemNotes($skuNumber, $notes) {
    $connection = connWhitneyUser();
    try {
        $sql = 'UPDATE items set notes = :notes where skuNumber = :skuNumber';
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':skuNumber', $skuNumber, PDO::PARAM_INT);
        $stmt->bindParam(':notes', $notes, PDO::PARAM_STR);
        $stmt->execute();
        $updateRow = $stmt->rowCount();
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $updateRow;
}

function deletePOItem($purchaseOrderItemsID){
    $connection = connWhitneyAdmin();
    
    try {
        $sql = 'DELETE FROM purchaseorderitems
                WHERE purchaseOrderItemsID = :purchaseOrderItemsID';
        
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':purchaseOrderItemsID', $purchaseOrderItemsID, PDO::PARAM_INT);
        $stmt->execute();
        $deleteRow = $stmt->rowCount();
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $deleteRow;
}

function deletePO($poNo){
    $connection = connWhitneyAdmin();
    
    try {
        $sql = 'DELETE FROM purchaseOrder
                WHERE purchaseOrderID = :poNo';
        
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':poNo', $poNo, PDO::PARAM_INT);
        $stmt->execute();
        $deleteRow = $stmt->rowCount();
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $deleteRow;
}

function addBOMData($skuNumber, $parentSku, $qtyPer, $nextSeq){
    $connection = connWhitneyUser();
    try {
     $sql = 'INSERT INTO bom(
            parentItemNo
            , compItem_no
            , seqNo
            , noInSet
            , source)
            VALUES (
             :parentSku
            , :skuNumber
            , :nextSeq
            , :qtyPer
            , "W")';
    $stmt = $connection->prepare($sql);
    $stmt->bindParam(':skuNumber', $skuNumber, PDO::PARAM_INT);
    $stmt->bindParam(':parentSku', $parentSku, PDO::PARAM_INT);
    $stmt->bindParam(':qtyPer', $qtyPer, PDO::PARAM_INT);
    $stmt->bindParam(':nextSeq', $nextSeq, PDO::PARAM_INT);
    $stmt->execute();
        $insertRow = $stmt->rowCount();
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $insertRow;
    
}

function getNextSeq($skuNumber) {
    $connection = connWhitneyUser();
    try {
     $sql = 'SELECT 
            max(b.seqNo)
            from bom b
            WHERE b.parentItemNo = :skuNumber';
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


function deleteBOMItem($parentSku, $childeSku){
    $connection = connWhitneyAdmin();
    
    try {
        $sql = 'DELETE FROM bom
                WHERE parentItemNo = :parentSku AND compItem_NO = :childSku';
        
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':parentSku', $parentSku, PDO::PARAM_INT);
        $stmt->bindParam(':childSku', $childeSku, PDO::PARAM_INT);
        $stmt->execute();
        $deleteRow = $stmt->rowCount();
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $deleteRow;
}



function getWODataKit($skuNumber, $start, $end) {
    $connection = connWhitneyUser();
    try {
     $sql = 'select
     w.scheduleDate
     ,wf.workordernumber
     ,w.quantityCompleted
     ,wf.qtyOrdered
     ,w.completedDate
     ,w.complete
     ,w.approved
     FROM workorderf wf
     LEFT JOIN workorder w
     ON wf.workordernumber = w.workordernumber
     WHERE skuNumber = :skuNumber and (complete = 0 or (completedDate >= :start AND completedDate <= :end))
     Order By w.scheduleDate;
     ';
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':skuNumber', $skuNumber, PDO::PARAM_INT);
        $stmt->bindParam(':start', $start, PDO::PARAM_INT);
        $stmt->bindParam(':end', $end, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

function getWODataComp($skuNumber, $start, $end) {
    $connection = connWhitneyUser();
    try {
     $sql = 'select
     ifNULL(w.scheduleDate, "Not Scheduled")
     ,wf.workordernumber
     ,w.quantityCompleted
     ,CONCAT(IFNULL(x.eusage, 0), "/", CAST(wf.qtyOrdered * b.noInset AS int)) AS eUsage
     ,w.completedDate
     ,w.complete
     ,w.approved
     FROM workorderf wf
     LEFT JOIN workorder w
     ON wf.workordernumber = w.workordernumber
     LEFT JOIN bom b
     ON wf.skuNumber = b.parentItemNo
     LEFT JOIN (SELECT e.workordernumber
                 , SUM(e.quantity) AS eusage
                 FROM eusage e 
                 LEFT JOIN inventory i 
                 ON i.inventoryID = e.inventoryID 
                 WHERE i.skuNumber = :skuNumber 
                 GROUP BY e.workordernumber) x
     ON wf.workordernumber = x.workordernumber
     WHERE b.compitem_no = :skuNumber and (complete = 0 OR (completedDate >= :start AND completedDate <= :end))
     Order By w.scheduleDate;
     ';
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':skuNumber', $skuNumber, PDO::PARAM_INT);
        $stmt->bindParam(':start', $start, PDO::PARAM_INT);
        $stmt->bindParam(':end', $end, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

function  getPODataKit($skuNumber, $start, $end) {
    $connection = connWhitneyUser();
    try {
     $sql = 'select po.requiredDate, po.purchaseOrderID, CONCAT (IFNULL(x.recQty, 0), "/", poi.orderQty) AS recQty, DATE_FORMAT(x.date_received, "%m/%d/%Y") AS recDate, poi.complete
     FROM purchaseOrder po
     LEFT JOIN purchaseOrderItems poi
     ON po.purchaseOrderID = poi.purchaseOrderID
     LEFT JOIN (SELECT purchaseOrderItemsID, date_received, sum(receiptQty) AS recQty FROM receiving GROUP BY purchaseOrderItemsID) x
     ON poi.purchaseOrderItemsID = x.purchaseOrderItemsID
     WHERE poi.skuNumber = :skuNumber and (poi.complete = 0 OR (x.date_received >= :start AND x.date_received <= :end))
     Order By po.requiredDate;
     ';
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':skuNumber', $skuNumber, PDO::PARAM_INT);
        $stmt->bindParam(':start', $start, PDO::PARAM_INT);
        $stmt->bindParam(':end', $end, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

function getCountTotals($skuNumber) {
    $connection = connWhitneyUser();
    try {
     $sql = 'select
     b.compitem_no
     , CAST(SUM(wf.qtyOrdered * b.noInset) AS int) AS qpm
     , qoh.total_qpm as qoh
     , qoh2.total_qpm as qoh_quar
     FROM workorderf wf
     LEFT JOIN workorder w
     ON wf.workordernumber = w.workordernumber
     LEFT JOIN bom b
     ON wf.skuNumber = b.parentItemNo
     LEFT JOIN (select i.skuNumber
                , SUM(q.inventoryQuantity) as total_qpm
                FROM inventory i
                LEFT JOIN (SELECT inv.inventoryID, inv.inventoryQuantity, inv.creationDate
                    FROM inventoryunitquantity inv
                    INNER JOIN(SELECT inventoryID, max(inventoryQuantityID) inventoryQuantityID
                                FROM inventoryunitquantity
                                GROUP BY inventoryID) ss
                    ON inv.inventoryID = ss.inventoryID AND inv.inventoryQuantityID = ss.inventoryQuantityID) q
                ON i.inventoryID = q.inventoryID
                WHERE i.skuNumber = :skuNumber AND i.active = true
                GROUP BY i.skuNumber) qoh
     ON b.compitem_no = qoh.skuNumber
     LEFT JOIN (select i.skuNumber
                , SUM(q.inventoryQuantity) as total_qpm
                FROM inventory i
                LEFT JOIN (SELECT inv.inventoryID, inv.inventoryQuantity, inv.creationDate
                    FROM inventoryunitquantity inv
                    INNER JOIN(SELECT inventoryID, max(inventoryQuantityID) inventoryQuantityID
                                FROM inventoryunitquantity
                                GROUP BY inventoryID) ss
                    ON inv.inventoryID = ss.inventoryID AND inv.inventoryQuantityID = ss.inventoryQuantityID) q
                ON i.inventoryID = q.inventoryID
                WHERE i.skuNumber = :skuNumber and i.quarantine = 1
                GROUP BY i.skuNumber) qoh2
     ON b.compitem_no = qoh2.skuNumber
     WHERE b.compitem_no = :skuNumber and complete = 0
     GROUP BY b.compitem_no
     Order By w.scheduleDate;
     ';
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':skuNumber', $skuNumber, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }

    return $data;
}

function getQPMKit($skuNumber) {
    
    $connection = connWhitneyUser();
    try {
     $sql = 'select CAST(SUM(wf.qtyOrdered - ifNull(w.quantityCompleted, 0)) AS int)
     FROM workorderf wf
     LEFT JOIN workorder w
     ON wf.workordernumber = w.workordernumber
     WHERE skuNumber = :skuNumber and complete = 0
     GROUP BY wf.skuNumber
     Order By w.scheduleDate;
     ';
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':skuNumber', $skuNumber, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    $data = !$data || $data == null ? false : $data[0];
    return $data;
}

function getQPR($skuNumber) {
    $connection = connWhitneyUser();
    try {
     $sql = 'select poi.skuNumber
     , SUM(poi.orderQty - ifNull(x.recQty, 0)) AS QPR
     FROM purchaseOrder po
     LEFT JOIN purchaseOrderItems poi
     ON po.purchaseOrderID = poi.purchaseOrderID
     LEFT JOIN (SELECT purchaseOrderItemsID, date_received, sum(receiptQty) AS recQty FROM receiving GROUP BY purchaseOrderItemsID) x
     ON poi.purchaseOrderItemsID = x.purchaseOrderItemsID
     WHERE poi.skuNumber = :skuNumber and poi.complete = 0
     GROUP BY poi.skuNumber;
     ';
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

function getHeldItemCnt($lotCode) {
    $connection = connWhitneyUser();
    try {
        $sql = 'SELECT 
        COUNT(inventoryID)
        from inventory i
        WHERE batchNumber = :lotCode AND hold = 1';
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':lotCode', $lotCode, PDO::PARAM_STR);
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

function getFreeItemCnt($lotCode) {
    $connection = connWhitneyUser();
    try {
        $sql = 'SELECT 
        COUNT(inventoryID)
        from inventory i
        WHERE batchNumber = :lotCode AND hold = 0';
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':lotCode', $lotCode, PDO::PARAM_STR);
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

function getHeldItems($lotCode) {
    $connection = connWhitneyUser();
    try {
        $sql = 'SELECT inventoryID
        , skuNumber
        from inventory i
        WHERE batchNumber = :lotCode
        AND hold = 1';
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':lotCode', $lotCode, PDO::PARAM_STR);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

function getFreeItems($lotCode) {
    $connection = connWhitneyUser();
    try {
        $sql = 'SELECT inventoryID
        , skuNumber
        from inventory i
        WHERE batchNumber = :lotCode
        AND hold = 0';
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':lotCode', $lotCode, PDO::PARAM_STR);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

function releaseAllItems($lotCode) {
    $connection = connWhitneyUser();
    try {
        $sql = 'UPDATE inventory set hold = 0 where batchNumber = :lotCode';
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':lotCode', $lotCode, PDO::PARAM_STR);
        $stmt->execute();
        $updateRow = $stmt->rowCount();
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $updateRow;
}

function holdAllItems($lotCode) {
    $connection = connWhitneyUser();
    try {
        $sql = 'UPDATE inventory set hold = 1 where batchNumber = :lotCode';
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':lotCode', $lotCode, PDO::PARAM_STR);
        $stmt->execute();
        $updateRow = $stmt->rowCount();
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $updateRow;
}



// ***********************************
// *
// Needs SQL 
// *
// ***********************************


function getDetailedWODataComp($sku, $zero) {
    return Null;
}

function getDetailedWODataKit($sku, $zero) {
    return Null;
}

function getDetailedPOData() {
    return Null;
}

// ***********************************
// *
// Needs SQL  ^^^^^^^^^^^^^^^^^^
// *
// ***********************************

function updateApprovalStatus($workOrderNumbers, $approval) {
    $connection = connWhitneyUser();
    try {
        $sql = 'UPDATE workorder set approved = :approval where workOrderNumber in (:workOrderNumbers)';
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':workOrderNumbers', $workOrderNumbers, PDO::PARAM_STR);
        $stmt->bindParam(':approval', $approval, PDO::PARAM_INT);
        $stmt->execute();
        $updateRow = $stmt->rowCount();
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $updateRow;
}

function getCategories() {
    $connection = connWhitneyUser();
    try {
        $sql = 'SELECT DISTINCT CAT from itemsf WHERE CAT IS NOT NULL AND CAT != ""';
        $stmt = $connection->prepare($sql);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

function updatePOItem($purchaseOrderItemsID, $orderedQty, $lotNum, $pltNum, $hold) {
    $connection = connWhitneyUser();
    try {
        $sql = '
        UPDATE purchaseOrderItems SET 
        orderQty = :orderedQty
        , palletNumber = :pltNum
        , lotCode = :lotNum
        , hold = :hold
        WHERE purchaseOrderItemsID = :purchaseOrderItemsID;
        ';
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':purchaseOrderItemsID', $purchaseOrderItemsID, PDO::PARAM_INT);
        $stmt->bindParam(':orderedQty', $orderedQty, PDO::PARAM_INT);
        $stmt->bindParam(':lotNum', $lotNum, PDO::PARAM_INT);
        $stmt->bindParam(':pltNum', $pltNum, PDO::PARAM_INT);
        $stmt->bindParam(':hold', $hold, PDO::PARAM_INT);
        $stmt->execute();
        $updateRow = $stmt->rowCount();
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $updateRow;
}

function getPricingListID($skuNumber) {
    $connection = connWhitneyUser();
    try {
        $sql = 'SELECT pricingListID from items where skuNumber = :skuNumber';
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

function getPricingList() {
    $connection = connWhitneyUser();
    try {
        $sql = 'SELECT * FROM pricingList';
        $stmt = $connection->prepare($sql);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

function getPricing($pricingTypeID = false) {
    $connection = connWhitneyUser();
    try {
        $sql = 'SELECT * FROM pricingType WHERE active = 1';
        $sql .= $pricingTypeID == false ? '' : '  AND pricingTypeID = :pricingTypeID';
        $stmt = $connection->prepare($sql);
        if($pricingTypeID == false) { $stmt->bindParam(':pricingTypeID', $pricingTypeID, PDO::PARAM_INT); }
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

function getPricingData($skuNumber = null, $pricingListID = null) {
    $connection = connWhitneyUser();
    try {
        $sql = 'SELECT * FROM pricing';
        $sql .= $pricingListID != null ? ' WHERE pricingListID = :pricingListID' : '';
        $sql .= $skuNumber != null ? ' WHERE skuNumber = :skuNumber' : '';
        $stmt = $connection->prepare($sql);
        if($skuNumber != null) { $stmt->bindParam(':skuNumber', $skuNumber, PDO::PARAM_INT); };
        if($pricingListID != null) { $stmt->bindParam(':pricingListID', $pricingListID, PDO::PARAM_INT); };
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

function getPricingPriority($skuNumber) {
    $connection = connWhitneyUser();
    try {
        $sql = 'SELECT pricingPriorityOne, pricingPriorityTwo, pricingPriorityThree FROM items WHERE skuNumber = :skuNumber';
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':skuNumber', $skuNumber, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

function uploadPrice($pricingTypeID, $skuNumber, $price = 0, $userID) {
    $connection = connWhitneyUser();
    try {
        $sql = '
        INSERT INTO pricing VALUES (
            DEFAULT
            , 1
            , :pricingTypeID
            , 1
            , :skuNumber
            , NULL
            , :price
            , :userID
            , NOW()
            , :userID
            , NOW()
        )';
        // NULL is for future customerID
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':pricingTypeID', $pricingTypeID, PDO::PARAM_INT);
        $stmt->bindParam(':skuNumber', $skuNumber, PDO::PARAM_INT);
        $stmt->bindParam(':price', $price, PDO::PARAM_STR);
        $stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
        $stmt->execute();
        $updateRow = $stmt->rowCount();
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $updateRow;
}

function resetPricing($skuNumber) {
    $connection = connWhitneyAdmin();
    try {
        $sql = 'DELETE FROM pricing WHERE skuNumber = :skuNumber';
        
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':skuNumber', $skuNumber, PDO::PARAM_INT);
        $stmt->execute();
        $deleteRow = $stmt->rowCount();
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $deleteRow;
}

function updateItemPricing($skuNumber, $pricingListID, $pricingDesc = null) {
    $connection = connWhitneyUser();
    try {
        $sql = 'UPDATE items SET pricingListID = :pricingListID, billablePallet = 1, pricingDesc = ' . ( $pricingDesc == -1 ? 'NULL' : ':pricingDesc' ) . ' WHERE skuNumber = :skuNumber';
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':pricingListID', $pricingListID, PDO::PARAM_INT);
        $stmt->bindParam(':skuNumber', $skuNumber, PDO::PARAM_INT);
        if($pricingDesc != -1) {
            $stmt->bindParam(':pricingDesc', $pricingDesc, PDO::PARAM_STR);
        }
        $stmt->execute();
        $updateRow = $stmt->rowCount();
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $updateRow;
}

function getPriceData($pricingListID, $pricingTypeID, $skuNumber = null) {
    $connection = connWhitneyUser();
    try {
        $sql = 'SELECT price FROM pricing WHERE pricingListID = :pricingListID AND pricingTypeID = :pricingTypeID';
        $sql .= $pricingListID == 1 ? ' AND skuNumber = :skuNumber' : '';
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':pricingListID', $pricingListID, PDO::PARAM_INT);
        $stmt->bindParam(':pricingTypeID', $pricingTypeID, PDO::PARAM_INT);
        if($pricingListID == 1) { $stmt->bindParam(':skuNumber', $skuNumber, PDO::PARAM_INT); }
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

function updateBillable($billable) {
    $connection = connWhitneyUser();
    try {
        $sql = 'UPDATE items SET billablePallet = :billable WHERE skuNumber = :skuNumber';
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':billable', $billable, PDO::PARAM_INT);
        $stmt->bindParam(':skuNumber', $skuNumber, PDO::PARAM_INT);
        $stmt->execute();
        $updateRow = $stmt->rowCount();
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $updateRow;
}

function updatePriorities($skuNumber, $priority, $pricingTypeID) {
    $connection = connWhitneyUser();
    try {
        $sql = 'UPDATE items SET pricingPriority';
        $sql .= $priority;
        $sql .= ' = ' . ( $pricingTypeID == -1 ? 'NULL' : ':pricingTypeID' ) . '
        WHERE skuNumber = :skuNumber';
        $stmt = $connection->prepare($sql);
        if($pricingTypeID != -1) {
            $stmt->bindParam(':pricingTypeID', $pricingTypeID, PDO::PARAM_STR);
        }
        $stmt->bindParam(':skuNumber', $skuNumber, PDO::PARAM_INT);
        $stmt->execute();
        $updateRow = $stmt->rowCount();
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $updateRow;
}

function getPrices($priorities, $pricingListID, $skuNumber = false) {
    $connection = connWhitneyUser();
    try {
        $sql = 'SELECT price FROM pricing WHERE pricingListID = :pricingListID AND pricingTypeID IN (:one, :two, :three)' . ( $skuNumber != false ? ' AND skuNumber = :skuNumber' : '' ) . ' ORDER BY FIELD(pricingTypeID, :one, :two, :three);';
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':pricingListID', $pricingListID, PDO::PARAM_INT);
        $stmt->bindParam(':one', $priorities[0], PDO::PARAM_INT);
        $stmt->bindParam(':two', $priorities[1], PDO::PARAM_INT);
        $stmt->bindParam(':three', $priorities[2], PDO::PARAM_INT);
        if($skuNumber != false) {
            $stmt->bindParam(':skuNumber', $skuNumber, PDO::PARAM_INT);
        }
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

function getPricingDesc($skuNumber) {
    $connection = connWhitneyUser();
    try {
        $sql = 'SELECT pricingDesc from items where skuNumber = :skuNumber';
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