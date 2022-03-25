<?php

require $_SERVER['DOCUMENT_ROOT'] . '/library/library.php';
require $_SERVER['DOCUMENT_ROOT'] . '/library/model.php';






function getPastDue(){
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
                    , SEC_TO_TIME(TIME_TO_SEC(i.timeStudy) * wf.qtyOrdered)
             FROM workOrderf wf
             LEFT JOIN workOrder w
             ON wf.workOrderNumber = w.workOrderNumber
             LEFT JOIN itemsf itf
             ON wf.skuNumber = itf.skuNumber
             LEFT JOIN items = i
             ON wf.skuNumber = i.skuNumber
             WHERE w.scheduleDate < CURDATE() && w.complete = 0 && w.scheduleDate != \'0000-00-00\'';
     
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
    $stmt->bindParam(':newDate', $newDate, PDO::PARAM_INT);
    $stmt->bindParam(':workOrderNumber', $workOrderNumber, PDO::PARAM_STR);
    $stmt->execute();
    $updateRow = $stmt->rowCount();
    $stmt->closeCursor();
 } catch (PDOException $ex) {
        return FALSE;
    }
    return $updateRow;
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

function getUnscheduledWO($searchTerm){
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
                    , SEC_TO_TIME(TIME_TO_SEC(i.timeStudy) * wf.qtyOrdered)
             FROM workOrderf wf
             LEFT JOIN workOrder w
             ON wf.workOrderNumber = w.workOrderNumber
             LEFT JOIN itemsf itf
             ON wf.skuNumber = itf.skuNumber
             LEFT JOIN items = i
             ON wf.skuNumber = i.skuNumber
             WHERE w.complete = 0 AND 
              (itf.skuNumber LIKE \'%' . $searchTerm . '%\' OR
              itf.partNumber LIKE \'%' . $searchTerm . '%\' OR
              itf.DESC1 LIKE \'%' . $searchTerm . '%\' OR
              itf.DESC2 LIKE \'%' . $searchTerm . '%\' OR
              itf.DESC3 LIKE \'%' . $searchTerm . '%\' OR
              itf.DESC4 LIKE \'%' . $searchTerm . '%\' OR
              itf.DESC5 LIKE \'%' . $searchTerm . '%\' OR
              itf.DESC5 LIKE \'%' . $searchTerm . '%\' OR
              wf.workOrderNumber LIKE \'%' . $searchTerm . '%\' OR
              itf.CAT   LIKE \'%' . $searchTerm . '%\')
              ORDER BY w.hot DESC, wf.workOrderNumber ASC
	      LIMIT 0, 50';
     
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':date', $date, PDO::PARAM_STR);
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


function updateWorkOrder($workOrderNumber
                        , $crewLeader
                        , $completionDate
                        , $crewCount
                        , $quantity
                        , $completionTime
                        , $curTimeStamp
                        , $userID){
    
        $connection = connWhitneyUser();
     try {
        $sql = 'UPDATE workOrder SET 
                     crewLeader = :crewLeader
                   , completedDate = :completionDate
                   , crewCount = :crewCount
                   , quantityCompleted = :quantity
                   , timeCompleted = :completionTime
                   , modifiedBy = :createdBy
                   , modifiedDate = :scheduleDate
                   , scheduleDate = :completionDate
                WHERE workOrderNumber = :workOrderNumber';
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':workOrderNumber', $workOrderNumber, PDO::PARAM_STR);
        $stmt->bindParam(':crewLeader', $crewLeader, PDO::PARAM_INT);
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