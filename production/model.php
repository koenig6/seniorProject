<?php

require $_SERVER['DOCUMENT_ROOT'] . '/library/library.php';
require $_SERVER['DOCUMENT_ROOT'] . '/library/model.php';






function getPastDue($date, $desc = false){
    $connection = connWhitneyUser();
    try {
     $sql = 'SELECT wf.workOrderNumber
     , wf.skuNumber
     , wf.requiredDate
     , wf.qtyOrdered
     , wf.notes
     , w.completedDate
     , w.quantityCompleted
     , w.crewLeader
     , w.timeCompleted
     , w.notes
     , w.complete
     , w.scheduleDate
     , w.crewCount
     , w.hot
     , itf.DESC1
     , itf.DESC2
     , itf.DESC3
     , (i.timeStudyInSecs * wf.qtyOrdered)/w.crewCount
     , (i.timeStudyInSecs * w.quantityCompleted)/w.crewCount
     , NULL
     , NULL
     , NULL
     , NULL
     , NULL
     , c.companyName
     , itf.CAT
     , i.timeStudyInSecs
     , pc.productCodeDesc
     , wf.user
FROM workOrderf wf
LEFT JOIN workOrder w
ON wf.workOrderNumber = w.workOrderNumber
LEFT JOIN itemsf itf
ON wf.skuNumber = itf.skuNumber
LEFT JOIN items = i
ON wf.skuNumber = i.skuNumber
LEFT JOIN associatedCustomer ac
ON ac.skuNumber = i.skuNumber
LEFT JOIN customer c
ON c.customerID = ac.customerID
LEFT JOIN productCodes pc
ON pc.productCode = itf.CAT
AND pc.active = 1
WHERE w.scheduleDate < CURDATE() AND w.complete = 0 AND w.scheduleDate != :date 
ORDER BY w.scheduleDate ';

if($desc) {
    $sql .= 'DESC';
}else {
    $sql .= 'ASC';
}
     
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':date', $date, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}



function getEmployees(){
    $connection = connWhitneyUser();
    try {
     $sql = 'SELECT employeeID, CONCAT(firstName, \' \' , lastName) as Name FROM employee WHERE titleID > 5';
        $stmt = $connection->prepare($sql);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

function getShifts(){
    $connection = connWhitneyUser();
    try {
     $sql = 'SELECT * FROM shift';
        $stmt = $connection->prepare($sql);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

function getLastScheduleDate(){
    $connection = connWhitneyUser();
    try {
     $sql = 'SELECT max(scheduleDate)  FROM employeeschedule';
        $stmt = $connection->prepare($sql);
        $stmt->execute();
        $data = $stmt->fetchColumn();
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

function getLastSchedule($lastDate){
    $connection = connWhitneyUser();
    try {
     $sql = 'SELECT employeeID, shiftID
             FROM employeeSchedule es
             WHERE es.scheduleDate = :lastDate';
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':lastDate', $lastDate, PDO::PARAM_STR);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

function deleteSchedule($scheduleDate){
    $connection = connWhitneyAdmin();
    
    try {
        $sql = 'DELETE FROM employeeSchedule
                WHERE scheduleDate = :scheduleDate';
        
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':scheduleDate', $scheduleDate, PDO::PARAM_STR);
        $stmt->execute();
        $deleteRow = $stmt->rowCount();
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $deleteRow;
}

function getScheduleCount($selectedDate){
    $connection = connWhitneyUser();
    try {
     $sql = 'SELECT * 
             FROM employeeSchedule es
             LEFT JOIN employee e';
        $stmt = $connection->prepare($sql);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

function getUnscheduledEmployees($selectedDate){
    $connection = connWhitneyUser();
    try {
    
     $sql = 'SELECT e.employeeID
                  , concat(e.firstName, \' \' ,e.lastName) as name
                  , es.employeeStatus
                  , jt.jobtitle
                  , esc.employeeScheduleID
             FROM employee e
             LEFT JOIN employeestatus es 
                ON e.statusID = es.employeeStatusID
             LEFT JOIN jobtitle jt
                ON e.titleID = jt.jobtitleID
             LEFT JOIN (SELECT * FROM employeeschedule WHERE scheduleDate = :selectedDate) esc
             ON e.employeeID = esc.employeeID
             WHERE esc.scheduleDate IS NULL AND e.statusID < 7 AND e.active = 1';
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':selectedDate', $selectedDate, PDO::PARAM_STR);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

function getScheduledEmployees($selectedDate){
    $connection = connWhitneyUser();
    try {
     $sql = 'SELECT esc.employeeID
            ,concat(e.firstName, \' \' ,e.lastName) as name
            ,s.shift
             FROM employeeschedule esc
             LEFT JOIN shift s
             ON esc.shiftID = s.shiftID
             LEFT JOIN employee e
             ON esc.employeeID = e.employeeID
             WHERE scheduleDate = :selectedDate';
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':selectedDate', $selectedDate, PDO::PARAM_STR);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

function updateCrewLeader($crewLeader, $workOrderNumber){

    $connection = connWhitneyUser();
 try {
    $sql = 'UPDATE workOrder SET 
                 crewLeader = :crewLeader
            WHERE workOrderNumber = :workOrderNumber';
    $stmt = $connection->prepare($sql);
    $stmt->bindParam(':crewLeader', $crewLeader, PDO::PARAM_INT);
    $stmt->bindParam(':workOrderNumber', $workOrderNumber, PDO::PARAM_STR);
    $stmt->execute();
    $updateRow = $stmt->rowCount();
    $stmt->closeCursor();
 } catch (PDOException $ex) {
        return FALSE;
    }
    return $updateRow;
}

function addEmployeeToSchedule($employeeID, $shiftID, $scheduleDate, $userID){
    $connection = connWhitneyUser();
 try {
    $sql = 'INSERT INTO employeeSchedule
                (employeeID, shiftID, scheduleDate, createdBy)
            VALUES (:employeeID, :shiftID, :scheduleDate, :createdBy)';
    
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':employeeID', $employeeID, PDO::PARAM_INT);
        $stmt->bindParam(':shiftID', $shiftID, PDO::PARAM_INT);
        $stmt->bindParam(':scheduleDate', $scheduleDate, PDO::PARAM_STR);
        $stmt->bindParam(':createdBy', $userID, PDO::PARAM_INT);
        $stmt->execute();
        $insertRow = $stmt->rowCount();
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $insertRow;
    
    
}

function removeEmployeeFromSchedule($employeeID,$scheduleDate){
    $connection = connWhitneyAdmin();
    
    try {
        $sql = 'DELETE FROM employeeSchedule
                WHERE scheduleDate = :scheduleDate AND employeeID = :employeeID';
        
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':scheduleDate', $scheduleDate, PDO::PARAM_STR);
        $stmt->bindParam(':employeeID', $employeeID, PDO::PARAM_INT);
        $stmt->execute();
        $deleteRow = $stmt->rowCount();
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $deleteRow;
}

function updateNotes($notes, $workOrderNumber){
    $connection = connWhitneyUser();
 try {
    $sql = 'UPDATE workOrder SET 
                 notes = :notes
            WHERE workOrderNumber = :workOrderNumber';
    $stmt = $connection->prepare($sql);
    $stmt->bindParam(':notes', $notes, PDO::PARAM_STR);
    $stmt->bindParam(':workOrderNumber', $workOrderNumber, PDO::PARAM_STR);
    $stmt->execute();
    $updateRow = $stmt->rowCount();
    $stmt->closeCursor();
 } catch (PDOException $ex) {
        return FALSE;
    }
    return $updateRow;
}

function updateCrewCount($crewCount, $workOrderNumber){
    $connection = connWhitneyUser();
 try {
    $sql = 'UPDATE workOrder SET 
                 crewCount = :crewCount
            WHERE workOrderNumber = :workOrderNumber';
    $stmt = $connection->prepare($sql);
    $stmt->bindParam(':crewCount', $crewCount, PDO::PARAM_INT);
    $stmt->bindParam(':workOrderNumber', $workOrderNumber, PDO::PARAM_STR);
    $stmt->execute();
    $updateRow = $stmt->rowCount();
    $stmt->closeCursor();
 } catch (PDOException $ex) {
        return FALSE;
    }
    return $updateRow;
}

function updateScheduleDate($newDate, $workOrderNumber){
    $connection = connWhitneyUser();
 try {
    $sql = 'UPDATE workOrder SET 
                 scheduleDate = :newDate
            WHERE workOrderNumber = :workOrderNumber';
    $stmt = $connection->prepare($sql);
    $stmt->bindParam(':newDate', $newDate, PDO::PARAM_STR);
    $stmt->bindParam(':workOrderNumber', $workOrderNumber, PDO::PARAM_STR);
    $stmt->execute();
    $updateRow = $stmt->rowCount();
    $stmt->closeCursor();
 } catch (PDOException $ex) {
        return FALSE;
    }
    return $newDate;
}

function updateHot($hot, $workOrderNumber){
    $connection = connWhitneyUser();
 try {
    $sql = 'UPDATE workOrder SET 
                 complete = 0
                 ,hot = :hot
            WHERE workOrderNumber = :workOrderNumber';
    $stmt = $connection->prepare($sql);
    $stmt->bindParam(':hot', $hot, PDO::PARAM_INT);
    $stmt->bindParam(':workOrderNumber', $workOrderNumber, PDO::PARAM_STR);
    $stmt->execute();
    $updateRow = $stmt->rowCount();
    $stmt->closeCursor();
 } catch (PDOException $ex) {
        return FALSE;
    }
    return $updateRow;
}

function completeWO($complete, $workOrderNumber){
    $connection = connWhitneyUser();
 try {
    $sql = 'UPDATE workOrder SET 
                 complete = :complete
            WHERE workOrderNumber = :workOrderNumber';
    $stmt = $connection->prepare($sql);
    $stmt->bindParam(':complete', $complete, PDO::PARAM_INT);
    $stmt->bindParam(':workOrderNumber', $workOrderNumber, PDO::PARAM_STR);
    $stmt->execute();
    $updateRow = $stmt->rowCount();
    $stmt->closeCursor();
 } catch (PDOException $ex) {
        return FALSE;
    }
    return $updateRow;
}

function deleteWorkOrder($workOrderNumber){
    $connection = connWhitneyAdmin();
    
    try {
        $connection->beginTransaction();
        
        $sql1 = 'DELETE FROM workOrder
                WHERE workOrderNumber = :workOrderNumber';
        
        $sql2 = 'DELETE FROM workOrderf
                WHERE workOrderNumber = :workOrderNumber';
        
        $stmt1 = $connection->prepare($sql1);
        $stmt2 = $connection->prepare($sql2);
        
        $stmt1->bindParam(':workOrderNumber', $workOrderNumber, PDO::PARAM_STR);
        $stmt2->bindParam(':workOrderNumber', $workOrderNumber, PDO::PARAM_STR);
        
        $stmt1->execute();
        $stmt2->execute();
        
        $deleteRow1 = $stmt1->rowCount();
        $deleteRow2 = $stmt2->rowCount();
        
        $stmt1->closeCursor();
        $stmt2->closeCursor();
        
        $connection->commit();
    } catch (PDOException $ex) {
        $connection->rollBack();
        return FALSE;
    }
    return true;
}

function getAllOpenWO($searchTerm = false){
    $searchTerm = $searchTerm == false ? false : '%'.$searchTerm.'%';
    $connection = connWhitneyUser();
    try {
        $sql = 'SELECT wf.workOrderNumber
        , wf.skuNumber
        , wf.requiredDate
        , wf.qtyOrdered
        , wf.notes
        , w.completedDate
        , w.quantityCompleted
        , w.crewLeader
        , w.timeCompleted
        , w.notes
        , w.complete
        , w.scheduleDate
        , w.crewCount
        , w.hot
        , itf.DESC1
        , itf.DESC2
        , itf.DESC3
        , (i.timeStudyInSecs * wf.qtyOrdered)/w.crewCount
        , (i.timeStudyInSecs * w.quantityCompleted)/w.crewCount
        , NULL
        , i.timeStudy
        , w.shift
        , w.priority
        , NULL
        , c.companyName
        , itf.CAT
        , i.timeStudyInSecs
        , pc.productCodeDesc
        , wf.user
    FROM workOrderf wf
    LEFT JOIN workOrder w
    ON wf.workOrderNumber = w.workOrderNumber
    LEFT JOIN itemsf itf
    ON wf.skuNumber = itf.skuNumber
    LEFT JOIN items = i
    ON wf.skuNumber = i.skuNumber
        LEFT JOIN associatedCustomer ac
    ON ac.skuNumber = i.skuNumber
    LEFT JOIN customer c
    ON c.customerID = ac.customerID
    LEFT JOIN productCodes pc
    ON pc.productCode = itf.CAT
    AND pc.active = 1
    WHERE w.complete = 0';
    if($searchTerm != false) {
        $sql .= ' AND (w.workOrderNumber LIKE :searchTerm OR
        wf.skuNumber LIKE :searchTerm OR
        itf.DESC1 LIKE :searchTerm OR c.companyName LIKE :searchTerm)';
    }
    $sql .= ' ORDER BY itf.skuNumber, w.scheduleDate limit 150';

    // ! The Limit was added so that the header filter wasn't so sluggish


// and w.approved = 1
     
        $stmt = $connection->prepare($sql);
        if($searchTerm != false) {
            $stmt->bindParam(':searchTerm', $searchTerm, PDO::PARAM_STR);
        }
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

function getCompWO($searchTerm){
    
    $searchTerm = $searchTerm == false ? false : '%'.$searchTerm.'%';
    $connection = connWhitneyUser();
    try {
        $sql = 'SELECT DISTINCT wf.workOrderNumber
        , wf.skuNumber
        , wf.requiredDate
        , wf.qtyOrdered
        , wf.notes
        , w.completedDate
        , w.quantityCompleted
        , w.crewLeader
        , w.timeCompleted
        , w.notes
        , w.complete
        , w.scheduleDate
        , w.crewCount
        , w.hot
        , itf.DESC1
        , itf.DESC2
        , itf.DESC3
        , (i.timeStudyInSecs * wf.qtyOrdered)/w.crewCount
        , (i.timeStudyInSecs * w.quantityCompleted)/w.crewCount
        , NULL
        , i.timeStudy
        , w.shift
        , w.priority
        , NULL
        , c.companyName
        , itf.CAT
        , i.timeStudyInSecs
        , pc.productCodeDesc
        , wf.user
    FROM workOrderf wf
    LEFT JOIN workOrder w
    ON wf.workOrderNumber = w.workOrderNumber
    LEFT JOIN itemsf itf
    ON wf.skuNumber = itf.skuNumber
    LEFT JOIN items = i
    ON wf.skuNumber = i.skuNumber
    LEFT JOIN bom b
    ON wf.skuNumber = b.parentItemNo
    LEFT JOIN associatedCustomer ac
    ON ac.skuNumber = i.skuNumber
    LEFT JOIN customer c
    ON c.customerID = ac.customerID
    LEFT JOIN productCodes pc
    ON pc.productCode = itf.CAT
    AND pc.active = 1
    WHERE w.complete = 0 and b.compItem_no = :searchTerm and w.approved = 1';
    if($searchTerm != false) {
        $sql .= ' AND (w.workOrderNumber LIKE :searchTerm OR
        wf.skuNumber LIKE :searchTerm OR
        itf.DESC1 LIKE :searchTerm OR c.companyName LIKE :searchTerm)';
    }
    $sql .= 'ORDER BY itf.skuNumber, w.scheduleDate
    LIMIT 0, 50';
     
        $stmt = $connection->prepare($sql);
        if($searchTerm != false) {
            $stmt->bindParam(':searchTerm', $searchTerm, PDO::PARAM_STR);
        }
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

function getUnscheduledWO($searchTerm){
    
    $searchTerm = $searchTerm == false ? false : '%'.$searchTerm.'%';
    $connection = connWhitneyUser();
    try {
     $sql = 'SELECT wf.workOrderNumber
     , wf.skuNumber
     , wf.requiredDate
     , wf.qtyOrdered
     , wf.notes
     , w.completedDate
     , w.quantityCompleted
     , w.crewLeader
     , w.timeCompleted
     , w.notes
     , w.complete
     , w.scheduleDate
     , w.crewCount
     , w.hot
     , itf.DESC1
     , itf.DESC2
     , itf.DESC3
     , (i.timeStudyInSecs * wf.qtyOrdered)/w.crewCount
     , (i.timeStudyInSecs * w.quantityCompleted)/w.crewCount
     , NULL
     , i.timeStudy
     , w.shift
     , w.priority
     , NULL
     , c.companyName
     , itf.CAT
     , i.timeStudyInSecs
     , pc.productCodeDesc
     , wf.user
FROM workOrderf wf
LEFT JOIN workOrder w
ON wf.workOrderNumber = w.workOrderNumber
LEFT JOIN itemsf itf
ON wf.skuNumber = itf.skuNumber
LEFT JOIN items = i
ON wf.skuNumber = i.skuNumber
LEFT JOIN associatedCustomer ac
ON ac.skuNumber = i.skuNumber
LEFT JOIN customer c
ON c.customerID = ac.customerID
LEFT JOIN productCodes pc
ON pc.productCode = itf.CAT
AND pc.active = 1
WHERE w.complete = 0 AND w.scheduleDate IS NULL';
if($searchTerm != false) {
        $sql .= ' AND (w.workOrderNumber LIKE :searchTerm OR
        wf.skuNumber LIKE :searchTerm OR
        itf.DESC1 LIKE :searchTerm OR c.companyName LIKE :searchTerm)';
}
$sql .= 'ORDER BY itf.skuNumber, w.scheduleDate';

// AND w.approved = 1 
     
        $stmt = $connection->prepare($sql);
        if($searchTerm != false) {
            $stmt->bindParam(':searchTerm', $searchTerm, PDO::PARAM_STR);
        }
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}


// Get ALL unscheduled work orders

function getAllUnscheduledWOs($searchTerm = false){
    $connection = connWhitneyUser();
    try {
            $sql = 'SELECT wf.workOrderNumber
            , wf.skuNumber
            , wf.requiredDate
            , wf.qtyOrdered
            , wf.notes
            , w.completedDate
            , w.quantityCompleted
            , w.crewLeader
            , w.timeCompleted
            , w.notes
            , w.complete
            , w.scheduleDate
            , w.crewCount
            , w.hot
            , itf.DESC1
            , itf.DESC2
            , itf.DESC3
            , (i.timeStudyInSecs * wf.qtyOrdered)/w.crewCount
            , (i.timeStudyInSecs * w.quantityCompleted)/w.crewCount
            , NULL
            , i.timeStudy
            , w.shift
            , w.priority
            , i.consumable
            , c.companyName
            , itf.CAT
            , i.timeStudyInSecs
            , pc.productCodeDesc
            , wf.user
    FROM workOrderf wf
    LEFT JOIN workOrder w
    ON wf.workOrderNumber = w.workOrderNumber
    LEFT JOIN itemsf itf
    ON wf.skuNumber = itf.skuNumber
    LEFT JOIN items = i
    ON wf.skuNumber = i.skuNumber
    LEFT JOIN associatedCustomer ac
    ON ac.skuNumber = i.skuNumber
    LEFT JOIN customer c
    ON c.customerID = ac.customerID
    LEFT JOIN productCodes pc
    ON pc.productCode = itf.CAT
    AND pc.active = 1
    WHERE w.scheduleDate is NULL AND wf.skuNumber is not null AND wf.workOrderNumber is not null AND w.complete = 0';
    if($searchTerm != false) {
        $sql .= ' AND w.workOrderNumber = :searchTerm';
    }
    $sql .= ' ORDER BY w.shift, wf.skuNumber, w.priority';

// AND w.approved = 1
     
        $stmt = $connection->prepare($sql);
        if($searchTerm != false) {
            $stmt->bindParam(':searchTerm', $searchTerm, PDO::PARAM_STR);
        }
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

function checkSearchType($searchTerm){
    $connection = connWhitneyUser();
    try {
     $sql = 'SELECT wf.workOrderNumber
                    , w.complete
                    , w.scheduleDate
             FROM workOrderf wf
             LEFT JOIN workOrder w
             ON wf.workOrderNumber = w.workOrderNumber
             LEFT JOIN itemsf itf
             ON wf.skuNumber = itf.skuNumber
             LEFT JOIN items = i
             ON wf.skuNumber = i.skuNumber
             WHERE (wf.workOrderNumber LIKE :searchTerm)
              ORDER BY itf.skuNumber, w.scheduleDate';
     
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':searchTerm', $searchTerm, PDO::PARAM_STR);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

function validateWorkOrderNumber($workOrderNumber){
    $connection = connWhitneyUser();
    try {
     $sql = 'SELECT workOrderNumber 
             FROM workOrderf
             WHERE workOrderNumber = :workOrderNumber';
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':workOrderNumber', $workOrderNumber, PDO::PARAM_STR);
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

function getWOScheduledDate($workOrderNumber){
    $connection = connWhitneyUser();
    try {
     $sql = 'SELECT scheduleDate 
             FROM workOrder
             WHERE workOrderNumber = :workOrderNumber';
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':workOrderNumber', $workOrderNumber, PDO::PARAM_STR);
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}


function updateWorkOrder($workOrderNumber
                        , $crewLeader = false
                        , $completionDate
                        , $crewCount
                        , $quantity
                        , $completionTime
                        , $curTimeStamp
                        , $userID){
    
        $connection = connWhitneyUser();
     try {
        $sql = 'UPDATE workOrder SET ';
        if($crewLeader != false) {
            $sql .= 'crewLeader = :crewLeader, ';
        }
        $sql .= '
                   completedDate = :completionDate
                   , crewCount = :crewCount
                   , quantityCompleted = :quantity
                   , timeCompletedInSecs = :completionTime
                   , modifiedBy = :createdBy
                   , modifiedDate = :scheduleDate
                   , scheduleDate = :completionDate
                WHERE workOrderNumber = :workOrderNumber';
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':workOrderNumber', $workOrderNumber, PDO::PARAM_STR);
        if($crewLeader != false) {
            $stmt->bindParam(':crewLeader', $crewLeader, PDO::PARAM_INT);
        }
        $stmt->bindParam(':completionDate', $completionDate, PDO::PARAM_STR);
        $stmt->bindParam(':crewCount', $crewCount, PDO::PARAM_INT);
        $stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
        $stmt->bindParam(':completionTime', $completionTime, PDO::PARAM_STR);
        $stmt->bindParam(':scheduleDate', $curTimeStamp, PDO::PARAM_STR);
        $stmt->bindParam(':createdBy', $userID, PDO::PARAM_INT);
        $stmt->execute();
        $updateRow = $stmt->rowCount();
        $stmt->closeCursor();
     } catch (PDOException $ex) {
            return FALSE;
        }
        return $updateRow;
    
}

function updateCompletionData($workOrderNumber
                        , $completionDate
                        , $quantity
                        , $completionTime
                        , $curTimeStamp
                        , $userID){
    
        $connection = connWhitneyUser();
     try {
        $sql = 'UPDATE workOrder SET 
                   completedDate = :completionDate
                   , quantityCompleted = :quantity
                   , timeCompleted = :completionTime
                   , modifiedBy = :createdBy
                   , modifiedDate = :scheduleDate
                WHERE workOrderNumber = :workOrderNumber';
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':workOrderNumber', $workOrderNumber, PDO::PARAM_STR);
        $stmt->bindParam(':completionDate', $completionDate, PDO::PARAM_STR);
        $stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
        $stmt->bindParam(':completionTime', $completionTime, PDO::PARAM_STR);
        $stmt->bindParam(':scheduleDate', $curTimeStamp, PDO::PARAM_STR);
        $stmt->bindParam(':createdBy', $userID, PDO::PARAM_INT);
        $stmt->execute();
        $updateRow = $stmt->rowCount();
        $stmt->closeCursor();
     } catch (PDOException $ex) {
            return FALSE;
        }
        return $updateRow;
    
}

function getMissingWorkorderDetails(){
    $connection = connWhitneyUser();
    try {
     $sql = 'SELECT x.workOrderNumber from workorderf x
            LEFT JOIN workorder y
            ON x.workOrderNumber = y.workOrderNumber
            WHERE y.workOrderNumber is null';
        $stmt = $connection->prepare($sql);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

function insertMissingWO($workOrderNumber, $userID){
    $connection = connWhitneyUser();
 try {
    $sql = 'INSERT INTO workorder
                (workOrderNumber, active, complete, createdBy, createdDate, modifiedBy, modifiedDate)
            VALUES (:workOrderNumber, 1, 0, :userID, Now(), :userID, Now())';
    
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':workOrderNumber', $workOrderNumber, PDO::PARAM_STR);
        $stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
        $stmt->execute();
        $insertRow = $stmt->rowCount();
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $insertRow;
    
    
}


function getSubComponent($workOrderNumber){
    $connection = connWhitneyUser();
    try {
     $sql = 'SELECT s.subComponentID
                    , s.skuNumber
                    , s.type
                    , s.partName
                    , s.qtyPer
                    , s.dim1
                    , s.dim2
                    , s.dim3
                    , s.stockSkuNumber
                    , s.stockQuantity * (s.qtyPer * qtyOrdered) AS totalStock
                    , w.workOrderNumber
                    , w.skuNumber
                    , w.qtyOrdered
                    , i.DESC1
                    , ii.DESC1
                    , s.qtyPer * qtyOrdered AS totalQty
             FROM workOrderf w
             LEFT JOIN subComponent s
             ON w.skuNumber = s.skuNumber
             LEFT JOIN itemsf i
             ON w.skuNumber = i.skuNumber
             LEFT JOIN itemsf ii
             ON s.stockskuNumber = ii.skuNumber
             WHERE w.workOrderNumber = :workOrderNumber AND s.subComponentID IS NOT NULL
             ORDER BY s.type, s.stockSkuNumber, s.subComponentID';
     
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':workOrderNumber', $workOrderNumber, PDO::PARAM_STR);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

function getCurrentCrewCount(){
    
    $connection = connWhitneyUser();
    try {
     $sql = 'SELECT count(*)
            FROM timeClockPunch tcp
            WHERE punchOut IS NULL AND punchDate > CURDATE() - 1';
        $stmt = $connection->prepare($sql);
        $stmt->execute();
        $data = $stmt->fetch();
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
    
}

function getCurrentAssignedCrew(){
    
    $connection = connWhitneyUser();
    try {
     $sql = 'SELECT sum(crewCount)
            FROM simpleJobClock sjc
            WHERE stop IS NULL AND start > CURDATE() - 1';
        $stmt = $connection->prepare($sql);
        $stmt->execute();
        $data = $stmt->fetch();
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
    
}

function getCurrentJobs(){
    
    $connection = connWhitneyUser();
    try {
     $sql = 'SELECT wf.workOrderNumber
                    , itf.DESC1
                    , IFNULL(SUM(sjc.crewCount), 0)
             FROM workOrderf wf
             LEFT JOIN workOrder w
             ON wf.workOrderNumber = w.workOrderNumber
             LEFT JOIN itemsf itf
             ON wf.skuNumber = itf.skuNumber
             LEFT JOIN (SELECT workOrderNumber, crewCount FROM simpleJobClock WHERE stop IS NULL) sjc
             ON wf.workOrderNumber = sjc.workOrderNumber
             WHERE w.scheduleDate = CURDATE() AND w.complete = False
             GROUP BY wf.workOrderNumber, itf.DESC1
             ORDER BY wf.workOrderNumber';
        $stmt = $connection->prepare($sql);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
    
}

function getJobPunches($workOrderNumber){
    
    $connection = connWhitneyUser();
    try {
        
     $sql = 'SELECT tcp.simpleJobClockID, tcp.workOrderNumber, d.departmentName, tcp.start, tcp.stop, tcp.crewCount, tcp.lunch, u.userName
            FROM simpleJobClock tcp
            LEFT JOIN department d
            ON tcp.departmentID = d.departmentID 
            LEFT JOIN users u
            ON tcp.createdBy = u.userID ';
     
            if($workOrderNumber){
                $sql .= 'WHERE tcp.workOrderNumber = :workOrderNumber ';
            }Else{
                $sql .= 'WHERE tcp.workOrderNumber IS NULL ';
            }
            
            $sql .= 'ORDER BY tcp.start DESC';

        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':workOrderNumber', $workOrderNumber, PDO::PARAM_STR);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
    
}


function getJobPunch($punchID){
    
    $connection = connWhitneyUser();
    try {
     $sql = 'SELECT tcp.simpleJobClockID, tcp.workOrderNumber, d.departmentName, tcp.start, tcp.stop, tcp.crewCount, tcp.lunch
            FROM simpleJobClock tcp
            LEFT JOIN department d
            ON tcp.departmentID = d.departmentID
            WHERE tcp.simpleJobClockID = :punchID';
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':punchID', $punchID, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
    
}



//Simple Job Clock Punch Out
function jobPunchOut($jobPunchID, $stopPunch, $userID){

    $connection = connWhitneyUser();
 try {
    $sql = 'UPDATE simpleJobClock SET 
                 stop = :stopPunch
                 , modifiedBy = :userID
                 , modifiedDate = NOW()
            WHERE simpleJobClockID = :jobPunchID';
    $stmt = $connection->prepare($sql);
    $stmt->bindParam(':jobPunchID', $jobPunchID, PDO::PARAM_INT);
    $stmt->bindParam(':stopPunch', $stopPunch, PDO::PARAM_STR);
    $stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
    $stmt->execute();
    $updateRow = $stmt->rowCount();
    $stmt->closeCursor();
 } catch (PDOException $ex) {
        return FALSE;
    }
    return $updateRow;
}

function updateLunch($jobPunchID, $toggle, $userID){

    $connection = connWhitneyUser();
 try {
    $sql = 'UPDATE simpleJobClock SET 
                 lunch = :toggle
                 , modifiedBy = :userID
                 , modifiedDate = NOW()
            WHERE simpleJobClockID = :jobPunchID';
    $stmt = $connection->prepare($sql);
    $stmt->bindParam(':jobPunchID', $jobPunchID, PDO::PARAM_INT);
    $stmt->bindParam(':toggle', $toggle, PDO::PARAM_STR);
    $stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
    $stmt->execute();
    $updateRow = $stmt->rowCount();
    $stmt->closeCursor();
 } catch (PDOException $ex) {
        return FALSE;
    }
    return $updateRow;
}

function getWorkOrderData($woNo, $compQty){
    
    $connection = connWhitneyUser();
    try {
     $sql = 'SELECT wf.workOrderNumber
    , bom.compItem_no
    , itf2.DESC1
    , itf2.unit
    , bom.noInSet
    , CAST(Round(:compQty * bom.noInSet, 2) AS int) AS est_usage
    , CAST(ifNull(act.actual_usage, 0) AS int) AS actual_usage
    , NULL
    , NULL
    , NULL
    , itm.consumable
    , itf.skuNumber
    FROM workOrderf wf
    LEFT JOIN bom
    ON wf.skuNumber = bom.parentItemNo
    LEFT JOIN itemsf itf
    ON itf.skuNumber = bom.parentItemNo
    LEFT JOIN itemsf itf2
    ON itf2.skuNumber = bom.compItem_no
    LEFT JOIN
        (SELECT i.skuNumber ,sum(quantity) as actual_usage
        FROM eUsage e
        LEFT JOIN inventory i
        ON i.inventoryID = e.inventoryID
        WHERE e.workordernumber = :woNo
        GROUP BY e.workordernumber, i.skuNumber) act
    ON act.skuNumber = bom.compItem_no
    LEFT JOIN items itm
    ON itm.skuNumber = bom.compItem_no
    WHERE wf.workOrderNumber = :woNo';
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':woNo', $woNo, PDO::PARAM_STR);
        $stmt->bindParam(':compQty', $compQty, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
    
}

function getUsageData($woNo, $compQty, $skuNumber){
    
    $connection = connWhitneyUser();
    try {
     $sql = '
     SELECT x.skuNumber
    , itf.DESC1
    , itf.unit
    , IFNULL(bom.noInSet, "N/A")
    , IFNULL(CAST(Round(:compQty * bom.noInSet, 2) AS int), 0) AS est_usage
    , IFNULL(CAST(ifNull(act.actual_usage, 0) AS int), 0) AS actual_usage
    , IFNULL(CAST(Round(:compQty * bom.noInSet, 2) AS int), 0) - IFNULL(CAST(ifNull(act.actual_usage, 0) AS int), 0) AS adj
    , itm.consumable
    , itf.source
    FROM 
    (
        SELECT b.compItem_No AS skuNumber
        FROM bom b
        WHERE parentItemNo = :skuNumber
    UNION
        SELECT DISTINCT i.skuNumber AS skuNumber
        FROM eUsage e
        LEFT JOIN inventory i
        ON i.inventoryID = e.inventoryID
        WHERE e.workordernumber = :woNo) x
    LEFT JOIN 
    (   SELECT compItem_No AS skuNumber
        , bom.noInSet
        FROM bom
        WHERE parentItemNo = :skuNumber
    ) AS bom
    ON x.skuNumber = bom.skuNumber
    LEFT JOIN itemsf itf 
    ON itf.skuNumber = x.skuNumber
    LEFT JOIN
        (SELECT i.skuNumber ,sum(quantity) as actual_usage
        FROM eUsage e
        LEFT JOIN inventory i
        ON i.inventoryID = e.inventoryID
        WHERE e.workordernumber = :woNo
        GROUP BY e.workordernumber, i.skuNumber) act
    ON act.skuNumber = x.skuNumber
    LEFT JOIN items itm
    ON itm.skuNumber = x.skuNumber
     ';
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':woNo', $woNo, PDO::PARAM_STR);
        $stmt->bindParam(':compQty', $compQty, PDO::PARAM_INT);
        $stmt->bindParam(':skuNumber', $skuNumber, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
    
}


function insertERPAdjustLog($erpItemNo, $quantity, $erpOrder, $erpOrderPart, $comment){
    $erpWarehouseCode = 'SFLP';
    $erpReasonCode = '95';
    $connection = connWhitneyUser();
 try {
    $sql = 'INSERT INTO erpAdjustLog
                (erpItemNo
                , erpWarehouseCode
                , quantity
                , erpOrder
                , erpOrderPart
                , erpReasonCode
                )
            VALUES (
              :erpItemNo
            , :erpWarehouseCode
            , :quantity
            , :erpOrder
            , :erpOrderPart
            , :erpReasonCode
            )';
    
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':erpItemNo', $erpItemNo, PDO::PARAM_STR);
        $stmt->bindParam(':erpWarehouseCode', $erpWarehouseCode, PDO::PARAM_STR);
        $stmt->bindParam(':quantity', $quantity, PDO::PARAM_STR);
        $stmt->bindParam(':erpOrder', $erpOrder, PDO::PARAM_STR);
        $stmt->bindParam(':erpOrderPart', $erpOrderPart, PDO::PARAM_STR);
        $stmt->bindParam(':erpReasonCode', $erpReasonCode, PDO::PARAM_STR);
        $stmt->execute();
        $insertRow = $stmt->rowCount();
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $insertRow;
    
    
}

function changeShift($shift, $workOrderNumber) {

        $connection = connWhitneyUser();
     try {
        $sql = 'UPDATE workOrder SET 
                     shift = :shift
                WHERE workOrderNumber = :workOrderNumber';
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':shift', $shift, PDO::PARAM_INT);
        $stmt->bindParam(':workOrderNumber', $workOrderNumber, PDO::PARAM_STR);
        $stmt->execute();
        $data = $stmt->rowCount();
        $stmt->closeCursor();
     } catch (PDOException $ex) {
            return FALSE;
        }
        return $data;
}

function changePriority($priority, $workOrderNumber) {

    $connection = connWhitneyUser();
 try {
    $sql = 'UPDATE workOrder SET 
                 priority = :priority
            WHERE workOrderNumber = :workOrderNumber';
    $stmt = $connection->prepare($sql);
    $stmt->bindParam(':priority', $priority, PDO::PARAM_INT);
    $stmt->bindParam(':workOrderNumber', $workOrderNumber, PDO::PARAM_STR);
    $stmt->execute();
    $data = $stmt->rowCount();
    $stmt->closeCursor();
 } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

function getWOSkuRundownData($workOrderNumber, $skuNumber) {
    $connection = connWhitneyUser();
    try {
    $sql = '
    SELECT 
    b.parentItemNo as kitSku
    , e.eUsageID
    , e.inventoryID
    , i.skuNumber
    , itf.desc1
    , e.workOrderNumber
    , b.noInSet
    , wf.qtyOrdered
    , itf2.desc1 as kitdesc1
    , b.noInSet * wf.qtyOrdered As qtyNeeded
    , e.quantity
    , itf.UNIT
    , i.batchNumber
    , i.expirationDate
    , e.creationDate FROM eusage e
    LEFT JOIN inventory i ON i.inventoryID = e.inventoryID 
    LEFT JOIN itemsf itf ON itf.skuNumber = i.skuNumber 
    LEFT JOIN workOrderf wf ON wf.workOrderNumber = e.workOrderNumber 
    LEFT JOIN bom b ON b.compItem_No = i.skuNumber
    LEFT JOIN itemsf itf2 ON itf2.skuNumber = b.parentItemNo
    WHERE e.workOrderNumber = :workOrderNumber AND i.skuNumber = :skuNumber
    Group By e.eUsageID, i.batchNumber
    ';

    $stmt = $connection->prepare($sql);
    $stmt->bindParam(':workOrderNumber', $workOrderNumber, PDO::PARAM_STR);
    $stmt->bindParam(':skuNumber', $skuNumber, PDO::PARAM_INT);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_NUM);
    $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

function getApprovalStatus($workOrderNumber) {
    $connection = connWhitneyUser();
    try {
    $sql = 'select approved from workOrder where workOrderNumber = :workOrderNumber';

    $stmt = $connection->prepare($sql);
    $stmt->bindParam(':workOrderNumber', $workOrderNumber, PDO::PARAM_INT);
    $stmt->execute();
    $data = $stmt->fetch(PDO::FETCH_NUM);
    $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

function getWorkOrderDialogue($workOrderNumber) {
    $connection = connWhitneyUser();
    try {
    $sql = '
    SELECT wod.dialogueID
    , wod.note
    , u.userName
    , wod.creationDate
    FROM workOrderDialogue wod
    LEFT JOIN users u
    ON wod.createdBy = u.userID
    WHERE workOrderNumber = :workOrderNumber;
    ';
    $stmt = $connection->prepare($sql);
    $stmt->bindParam(':workOrderNumber', $workOrderNumber, PDO::PARAM_STR);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_NUM);
    $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

function insertWorkOrderDialogue($userID, $workOrderNumber, $message) {
    $connection = connWhitneyUser();
    try {
    $sql = '
    INSERT INTO workOrderDialogue (workOrderNumber, note, createdBy)
    VALUES (:workOrderNumber, :message, :userID);
    ';
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
        $stmt->bindParam(':workOrderNumber', $workOrderNumber, PDO::PARAM_STR);
        $stmt->bindParam(':message', $message, PDO::PARAM_STR);
        $stmt->execute();
        $insertRow = $stmt->rowCount();
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $insertRow;
}

function updatePastDues() {
    $connection = connWhitneyUser();
    try {
    $sql = '
    UPDATE workOrder SET scheduleDate = NOW() WHERE active = 1 AND complete = 0 AND scheduleDate IS NOT NULL AND scheduleDate < NOW();
    ';
    $stmt = $connection->prepare($sql);
    $stmt->bindParam(':priority', $priority, PDO::PARAM_INT);
    $stmt->bindParam(':workOrderNumber', $workOrderNumber, PDO::PARAM_STR);
    $stmt->execute();
    $data = $stmt->rowCount();
    $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

function getWOAwaitingConfirmation($searchTerm = false, $complete) {
    $searchTerm = $searchTerm == false ? false : '%'.$searchTerm.'%';
    $connection = connWhitneyUser();
    try {
    $sql = 'SELECT w.completedDate
    , itf.CAT
    , w.workOrderNumber
    , wf.skuNumber
    , w.priority
    , c.companyName
    , itf.DESC1
    , w.quantityCompleted
    , wf.qtyOrdered
    , wf.notes
    , w.crewCount
    , wf.requiredDate
    , i.timeStudyInSecs
    , (i.timeStudyInSecs * wf.qtyOrdered)/w.crewCount
    , (i.timeStudyInSecs * w.quantityCompleted)/w.crewCount
    , w.complete
    , pc.productCodeDesc
    , wf.user
    FROM workOrderf wf
    LEFT JOIN workOrder w
    ON wf.workOrderNumber = w.workOrderNumber
    LEFT JOIN itemsf itf
    ON wf.skuNumber = itf.skuNumber
    LEFT JOIN items = i
    ON wf.skuNumber = i.skuNumber
    LEFT JOIN associatedCustomer ac
    ON ac.skuNumber = i.skuNumber
    LEFT JOIN customer c
    ON c.customerID = ac.customerID
    LEFT JOIN productCodes pc
    ON pc.productCode = itf.CAT
    AND pc.active = 1
    WHERE w.complete = :complete';
    if($searchTerm != false) {
        $sql .= ' AND ( w.completedDate LIKE :searchTerm OR
        itf.CAT LIKE :searchTerm OR
        w.workOrderNumber LIKE :searchTerm OR
        i.skuNumber LIKE :searchTerm OR
        c.companyName LIKE :searchTerm OR
        itf.DESC1 LIKE :searchTerm OR
        wf.notes LIKE :searchTerm )';
    }
    $sql .= ' ORDER BY w.completedDate DESC limit 150';
        
    $stmt = $connection->prepare($sql);
    if($searchTerm != false) {
        $stmt->bindParam(':searchTerm', $searchTerm, PDO::PARAM_STR);
    }
    $stmt->bindParam(':complete', $complete, PDO::PARAM_INT);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_NUM);
    $stmt->closeCursor();

    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}