<?php

require $_SERVER['DOCUMENT_ROOT'] . '/library/library.php';

// Get Associated Companies corrosponding to provided SKU#
function getSkuData($skuNumber) {
    $connection = connWhitneyUser();
    try {
     $sql = 'SELECT skuNumber, paperColorID, palletQty, poRequired, batchRequired, component
             FROM items
             WHERE skuNumber = :skuNumber';
     
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':skuNumber', $skuNumber, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}
//Create new Inventory Unit with corrosponding tables
function createNewInventory(
                            $skuNumber
                                        , $companyID
                                        , $userID
                                        , $receiptDate
                                        , $palletQty
                                        , $poNumber
                                        , $batchNumber
                                        , $expDate){
    $connection = connWhitneyUser();
    try {
        $sql = 'INSERT INTO inventory (  skuNumber
                                       , companyID
                                       , receiptDate
                                       , poNumber
                                       , batchNumber
                                       , expirationDate
                                       , active
                                       , createdBy
                                       , modifiedBy)
                   VALUES (              :skuNumber
                                       , :companyID
                                       , :receiptDate
                                       , :poNumber
                                       , :batchNumber
                                       , :expDate
                                       , 0
                                       , :createdBy
                                       , :modifiedBy)';

        $stmt = $connection->prepare($sql);
           $stmt->bindParam(':skuNumber', $skuNumber, PDO::PARAM_INT);
           $stmt->bindParam(':companyID', $companyID, PDO::PARAM_INT);
           $stmt->bindParam(':receiptDate', $receiptDate, PDO::PARAM_STR);
           $stmt->bindParam(':poNumber', $poNumber, PDO::PARAM_STR);
           $stmt->bindParam(':batchNumber', $batchNumber, PDO::PARAM_STR);
           $stmt->bindParam(':expDate', $expDate, PDO::PARAM_STR);
           $stmt->bindParam(':createdBy', $userID, PDO::PARAM_INT);
           $stmt->bindParam(':modifiedBy', $userID, PDO::PARAM_INT);
           $stmt->execute();
           $insertRow = $stmt->rowCount();
           $serial = $connection->lastInsertId();
        
        $sql = 'INSERT INTO inventoryStatus (  inventoryID
                                       , inventoryStateID
                                       , createdBy)
                   VALUES (              :serial
                                       , 5
                                       , :createdBy)';

        $stmt = $connection->prepare($sql);
           $stmt->bindParam(':serial', $serial, PDO::PARAM_INT);
           $stmt->bindParam(':createdBy', $userID, PDO::PARAM_INT);
           $stmt->execute();
           $insertRow = $stmt->rowCount();


        $sql = 'INSERT INTO inventoryUnitQuantity (  inventoryID
                                        , inventoryQuantity
                                        , createdBy)
                    VALUES (              :serial
                                        , :palletQty
                                        , :createdBy)';

         $stmt = $connection->prepare($sql);
            $stmt->bindParam(':serial', $serial, PDO::PARAM_INT);
            $stmt->bindParam(':palletQty', $palletQty, PDO::PARAM_INT);
            $stmt->bindParam(':createdBy', $userID, PDO::PARAM_INT);
            $stmt->execute();
            $insertRow = $stmt->rowCount();
            $stmt->closeCursor();
        
    }catch (PDOException $ex){
        return FALSE;
        
    }
    return $serial;
}

function getTagData($serialNumber){
    $connection = connWhitneyUser();
    try {
     $sql = 'SELECT i.skuNumber
                    , c.companyName
                    , i.batchNumber
                    , i.expirationDate
                    , i.receiptDate
                    , it.unit
                    , it.DESC1
                    , it.DESC2
                    , it.DESC3
                    , q.inventoryQuantity
                    , i.inventoryiD
                    , q.creationDate
             FROM inventory i
             LEFT JOIN customer c
             ON i.companyID = c.customerID
             LEFT JOIN itemsf it
             ON i.skuNumber = it.skuNumber
             LEFT JOIN inventoryUnitQuantity q
             ON i.inventoryID = q.inventoryID
             WHERE i.inventoryID = :serialNumber 
             AND q.creationDate = (select max(creationDate) 
                                   from inventoryunitquantity 
                                   where inventoryID = :serialNumber)';
     
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':serialNumber', $serialNumber, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}