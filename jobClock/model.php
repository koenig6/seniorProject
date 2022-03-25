<?php

require $_SERVER['DOCUMENT_ROOT'] . '/library/library.php';
require $_SERVER['DOCUMENT_ROOT'] . '/library/model.php';

function getEmployeeAssigned(){
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

function getWorkOrderDetails($workOrderNumber){
    $connection = connWhitneyUser();
    try {
     $sql = 'SELECT wf.workOrderNumber
                , wf.skuNumber
                , wf.orderDate
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
                , w.active
                FROM workOrderf wf 
                LEFT JOIN workorder w 
                ON wf.workordernumber = w.workordernumber 
                WHERE wf.workorderNumber = :workOrderNumber';
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

function getAssignedLabor($workOrderNumber, $active = NULL){
    $connection = connWhitneyUser();
    try {
     $sql = 'SELECT ajc.advancedJobClockID
                ajc.workOrderNumber
                ajc.departmentID
                ajc.start
                ajc.stop
                ajc.createdBy
                ajc.creationDate
                ajc.modifiedBy
                ajc.modifiedDate
                FROM advancedJobClock ajc
                WHERE ajc.workorderNumber = :workOrderNumber ';
     
            if($active == 1){
                $sql .= 'AND ajc.stop IS NULL';
            }elseif($active == 0){
                $sql .= 'AND ajc.stop IS NOT NULL';
            }
     
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

function getWorkOrderLabor($workOrderNumber, $active){
    $connection = connWhitneyUser();
    try {
     $sql = 'SELECT ajc.advancedJobClockID
            , ajc.employeeID
            , ajc.departmentID
            , ajc.workOrderNumber
            , ajc.start
            , ajc.stop
            , ajc.lunch
            , CONCAT(e.firstName, " ", e.lastName) AS name
            , TIME_FORMAT(ajc.start, \'%h:%i %p\') AS startT
            , TIME_FORMAT(ajc.stop, \'%h:%i %p\') AS stopT
             FROM advancedJobClock ajc
             LEFT JOIN employee e
             ON e.employeeID = ajc.employeeID ';
        if($active){
            $sql .= 'WHERE ajc.workOrderNumber = :workOrderNumber AND ajc.stop IS NULL';
        }else{
            $sql .= 'WHERE ajc.workOrderNumber = :workOrderNumber AND ajc.stop IS NOT NULL AND TIMESTAMPDIFF(MINUTE,ajc.start,ajc.stop) > 5';
        }
             
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

function getLaborGroup($laborGroupID){
    $connection = connWhitneyUser();
    try {
     $sql = 'SELECT employeeID, CONCAT(firstName, \' \', lastName)
                FROM employee
                WHERE laborGroupID = :laborGroupID ';
     
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':laborGroupID', $laborGroupID, PDO::PARAM_STR);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

function assignLoadTag($inventoryID, $workOrderNumber, $userID){
    
        $connection = connWhitneyUser();
     try {
        $sql = 'UPDATE inventory
                SET workOrderNumber = :workOrderNumber
                , modifiedBy = :userID
                , modifiedDate = NOW()
                WHERE inventoryID = :inventoryID';
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':inventoryID', $inventoryID, PDO::PARAM_INT);
        $stmt->bindParam(':workOrderNumber', $workOrderNumber, PDO::PARAM_STR);
        $stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
        $stmt->execute();
        $updateRow = $stmt->rowCount();
        $stmt->closeCursor();
        
     } catch (PDOException $ex) {
            return FALSE;
        }
        return $updateRow;
}

function checkForDuplicate($workOrderNumber, $inventoryID){
    $connection = connWhitneyUser();
    try {
     $sql = 'SELECT inventoryID FROM inventory
             WHERE workOrderNumber = :workOrderNumber 
             AND inventoryID = :inventoryID';
     
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':workOrderNumber', $workOrderNumber, PDO::PARAM_STR);
        $stmt->bindParam(':inventoryID', $inventoryID, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

function getAssignedFinishGoods($workOrderNumber, $skuNumber){
    $connection = connWhitneyUser();
    try {
     $sql = 'SELECT @n := @n + 1 RowNumber 
            , i.inventoryID
            , q.inventoryQuantity
            FROM (select @n:=0) initvars, inventory i
            LEFT JOIN (SELECT inv.inventoryID, inv.inventoryQuantity
                                  FROM inventoryunitquantity inv
                                  INNER JOIN(SELECT inventoryID, max(inventoryQuantityID) inventoryQuantityID
                                              FROM (SELECT invs.inventoryID, invs.inventoryQuantityID
                                                    FROM inventoryUnitQuantity invs
                                                    INNER JOIN inventory i
                                                    ON invs.inventoryID = i.inventoryID
                                                    WHERE skuNumber = :skuNumber) invs
                                              GROUP BY inventoryID) ss
                                  ON inv.inventoryID = ss.inventoryID AND inv.inventoryQuantityID = ss.inventoryQuantityID
                                  INNER JOIN inventory i
                                  ON i.inventoryID = inv.inventoryID) q
                      ON i.inventoryID = q.inventoryID
            WHERE i.workOrderNumber = :workOrderNumber
            ORDER BY i.modifiedDate';
     
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

function getAssignedFinishGoodsSum($workOrderNumber, $skuNumber){
    $connection = connWhitneyUser();
    try {
     $sql = 'SELECT i.workOrderNumber
            , SUM(q.inventoryQuantity) AS total_qty
            FROM inventory i
            LEFT JOIN (SELECT inv.inventoryID, inv.inventoryQuantity
                                  FROM inventoryunitquantity inv
                                  INNER JOIN(SELECT inventoryID, max(inventoryQuantityID) inventoryQuantityID
                                              FROM (SELECT invs.inventoryID, invs.inventoryQuantityID
                                                    FROM inventoryUnitQuantity invs
                                                    INNER JOIN inventory i
                                                    ON invs.inventoryID = i.inventoryID
                                                    WHERE skuNumber = :skuNumber) invs
                                              GROUP BY inventoryID) ss
                                  ON inv.inventoryID = ss.inventoryID AND inv.inventoryQuantityID = ss.inventoryQuantityID
                                  INNER JOIN inventory i
                                  ON i.inventoryID = inv.inventoryID) q
                      ON i.inventoryID = q.inventoryID
            WHERE i.workOrderNumber = :workOrderNumber
            GROUP BY i.workOrderNumber;';
     
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':workOrderNumber', $workOrderNumber, PDO::PARAM_STR);
        $stmt->bindParam(':skuNumber', $skuNumber, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

function getAssignedHoursSum($workOrderNumber){
    $connection = connWhitneyUser();
    try {
     $sql = 'SELECT SEC_TO_TIME(sum((TIMESTAMPDIFF(SECOND, start, NOW()))))
            FROM advancedJobclock
            WHERE workOrderNumber = :workOrderNumber AND stop IS NULL';
     
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

function getLiveWorkOrderData($workOrderNumber){
    $connection = connWhitneyUser();
    try {
     $sql = 'SELECT SEC_TO_TIME((IFNULL(x.total_live_time, 0) + IFNULL(z.total_history_time, 0))) AS Grand_Total
            , y.total_qty
            , TIME_FORMAT(SEC_TO_TIME((IFNULL(x.total_live_time, 0) + IFNULL(z.total_history_time,0))/y.total_qty), "%H:%i:%s") AS current_time_study
            , items.timeStudy
            , TIME_TO_SEC(items.timeStudy) / ((IFNULL(x.total_live_time,0) + IFNULL(z.total_history_time,0))/y.total_qty) AS current_rate
            FROM workOrderf w
            LEFT JOIN
            (SELECT workOrderNumber, sum((TIMESTAMPDIFF(SECOND, start, NOW()))) AS total_live_time
            FROM advancedJobclock
            WHERE workOrderNumber = :workOrderNumber AND stop IS NULL) x
            ON w.workOrderNumber = w.workOrderNumber
            LEFT JOIN
            (SELECT workOrderNumber, sum((TIMESTAMPDIFF(SECOND, start, stop))) AS total_history_time
            FROM advancedJobclock
            WHERE workOrderNumber = :workOrderNumber AND stop IS NOT NULL) z
            ON w.workOrderNumber = z.workOrderNumber
            LEFT JOIN
            (SELECT i.workOrderNumber
            , SUM(q.inventoryQuantity) AS total_qty
            FROM inventory i
            LEFT JOIN (SELECT inv.inventoryID, inv.inventoryQuantity
                              FROM inventoryunitquantity inv
                              INNER JOIN(SELECT inventoryID, max(inventoryQuantityID) inventoryQuantityID
                                          FROM (SELECT invs.inventoryID, invs.inventoryQuantityID
                                                FROM inventoryUnitQuantity invs
                                                INNER JOIN inventory i
                                                ON invs.inventoryID = i.inventoryID
                                                WHERE skuNumber = (SELECT skuNumber FROM workOrderf WHERE workorderNumber = :workOrderNumber)
                                                ) invs
                                          GROUP BY inventoryID) ss
                              ON inv.inventoryID = ss.inventoryID AND inv.inventoryQuantityID = ss.inventoryQuantityID
                              INNER JOIN inventory i
                              ON i.inventoryID = inv.inventoryID) q
                  ON i.inventoryID = q.inventoryID
            WHERE i.workOrderNumber = :workOrderNumber
            GROUP BY i.workOrderNumber) y
            ON w.workOrderNumber = y.workOrderNumber
            LEFT JOIN items
            ON w.skuNumber = items.skuNumber
            WHERE w.workOrderNumber = :workOrderNumber';
     
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

function getActiveSummaryData($workOrderNumber = null){
    $connection = connWhitneyUser();
    try {
     $sql = 'SELECT ajc.workOrderNumber
                , itf.skuNumber
                , itf.DESC1
                , tot.Grand_Total
                , tot.total_qty
                , tot.total_live_time
                , tot.total_history_time
                , tot.current_time_study
                , tot.current_rate
                , ajc.employeeID
                , CONCAT(e.firstName, " ", e.lastName) AS name
                , ajc.start
                , ajc.stop
                , d.departmentName
                FROM advancedJobclock ajc
                LEFT JOIN workorderf wof
                ON ajc.workOrderNumber = wof.workOrderNumber
                LEFT JOIN workorder wo
                ON wof.workOrderNumber = wo.workOrderNumber
                LEFT JOIN itemsf itf
                ON wof.skuNumber = itf.skuNumber
                LEFT JOIN employee e
                ON ajc.employeeID = e.employeeID
                LEFT JOIN department d
                ON ajc.departmentID = d.departmentID
                LEFT JOIN
                    (SELECT w.workOrderNumber
                    , SEC_TO_TIME((IFNULL(x.total_live_time, 0) + IFNULL(z.total_history_time, 0))) AS Grand_Total
                    , TIME_FORMAT(SEC_TO_TIME(x.total_live_time), "%H:%i:%s") AS total_live_time
                    , TIME_FORMAT(SEC_TO_TIME(z.total_history_time), "%H:%i:%s") AS total_history_time
                    , y.total_qty
                    , TIME_FORMAT(SEC_TO_TIME((IFNULL(x.total_live_time, 0) + IFNULL(z.total_history_time,0))/y.total_qty), "%H:%i:%s") AS current_time_study
                    , items.timeStudy
                    , TIME_TO_SEC(items.timeStudy) / ((IFNULL(x.total_live_time,0) + IFNULL(z.total_history_time,0))/y.total_qty) AS current_rate
                    FROM workOrderf w
                    LEFT JOIN workorder ww
                    ON w.workOrderNumber = ww.workOrderNumber
                    LEFT JOIN
                    (SELECT workOrderNumber, sum((TIMESTAMPDIFF(SECOND, start, NOW()))) AS total_live_time
                    FROM advancedJobclock
                    WHERE start >= CURDATE() - 1 AND stop IS NULL
                    GROUP by workOrderNumber) x
                    ON w.workOrderNumber = x.workOrderNumber
                    LEFT JOIN
                    (SELECT workOrderNumber, sum((TIMESTAMPDIFF(SECOND, start, stop))) AS total_history_time
                    FROM advancedJobclock
                    WHERE start >= CURDATE() - 1 AND stop IS NOT NULL
                    GROUP by workOrderNumber) z
                    ON w.workOrderNumber = z.workOrderNumber
                    LEFT JOIN
                    (SELECT i.workOrderNumber
                    , SUM(q.inventoryQuantity) AS total_qty
                    FROM inventory i
                    LEFT JOIN (SELECT inv.inventoryID, inv.inventoryQuantity
                                      FROM inventoryunitquantity inv
                                      INNER JOIN(SELECT inventoryID, max(inventoryQuantityID) inventoryQuantityID
                                                  FROM (SELECT invs.inventoryID, invs.inventoryQuantityID
                                                        FROM inventoryUnitQuantity invs
                                                        INNER JOIN inventory i
                                                        ON invs.inventoryID = i.inventoryID) invs
                                                  GROUP BY inventoryID) ss
                                      ON inv.inventoryID = ss.inventoryID AND inv.inventoryQuantityID = ss.inventoryQuantityID
                                      INNER JOIN inventory i
                                      ON i.inventoryID = inv.inventoryID) q
                          ON i.inventoryID = q.inventoryID
                      WHERE i.workOrderNumber IS NOT NULL
                    GROUP BY i.workOrderNumber) y
                    ON w.workOrderNumber = y.workOrderNumber
                    LEFT JOIN items
                    ON w.skuNumber = items.skuNumber
                    WHERE scheduleDate >= CURDATE() - 1 
                                    AND scheduleDate <= CURDATE() + 1
                    GROUP BY workOrderNumber) tot
                ON ajc.workOrderNumber = tot.workOrderNumber
                WHERE ajc.stop IS NULL 
                AND ajc.start >= DATE_SUB(NOW(), INTERVAL 12 HOUR)
                AND wo.complete = false ';
            if($workOrderNumber){
                $sql .= 'AND ajc.workOrderNumber = :workOrderNumber ';
            }
                $sql .= 'AND ajc.workOrderNumber IS NOT NULL
                        ORDER BY ajc.departmentID, ajc.workOrderNumber, ajc.stop';
                
     
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':workOrderNumber', $workOrderNumber, PDO::PARAM_STR);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_BOTH);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

function totalActiveJobs(){
    $connection = connWhitneyUser();
    try {
     $sql = 'SELECT
             count(workOrderNumber) 
             FROM 
                (SELECT workOrderNumber 
                FROM advancedJobClock 
                WHERE start >= DATE_SUB(CURDATE(), INTERVAL 1 DAY)
                AND stop IS NULL 
                GROUP BY (workOrderNumber)) total_wo';
     
        $stmt = $connection->prepare($sql);
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

function getActiveJobs(){
    $connection = connWhitneyUser();
    try {
     $sql = 'SELECT workOrderNumber 
                FROM advancedJobClock 
                WHERE start >= DATE_SUB(CURDATE(), INTERVAL 1 DAY)
                AND stop IS NULL AND workOrderNumber IS NOT NULL
                GROUP BY workOrderNumber';
    
        $stmt = $connection->prepare($sql);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_NUM);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

function getUnAssignedEmployees(){
    $connection = connWhitneyUser();
    try {
     $sql = 'SELECT whosIn.employeeID
            , CONCAT(e.firstName, " ", e.lastName) AS name
            , d.departmentName
            , assigned.workOrderNumber
            , assigned.stop
            , e.titleID
            FROM 
            (SELECT * FROM timeClockPunch WHERE punchOut IS NULL AND punchIn > DATE_SUB(NOW(), INTERVAL 12 HOUR)) whosIn
            LEFT JOIN 
                (select * from advancedJobClock 
                where start >= DATE_SUB(NOW(), INTERVAL 12 HOUR)
                AND stop IS NULL) assigned
            ON whosIn.employeeID = assigned.employeeID
            LEFT JOIN employee e
            ON whosIn.employeeID = e.employeeID
            LEFT JOIN department d
            ON assigned.departmentID = d.departmentID
            WHERE (assigned.workOrderNumber IS NULL AND assigned.departmentID IS NULL)
            OR (assigned.workOrderNumber IS NULL AND assigned.departmentID IS NOT NULL)';
     
        $stmt = $connection->prepare($sql);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_BOTH);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

function getClockedOutEmployees(){
    $connection = connWhitneyUser();
    try {
     $sql = 'SELECT DISTINCT esc.employeeID, concat(e.firstName, \' \' ,e.lastName) as name, q.punchIn, q.punchOut
             FROM employeeschedule esc
             LEFT JOIN employee e
             ON esc.employeeID = e.employeeID
             LEFT JOIN (SELECT tcp.punchID, tcp.employeeID, tcp.punchIn, tcp.punchOut
               FROM timeClockPunch tcp
               INNER JOIN(SELECT employeeID, max(punchID) punchID
                           FROM timeClockPunch
                           WHERE punchIn >= DATE_SUB(NOW(), INTERVAL 12 HOUR) 
                           GROUP BY employeeID) ss
                ON tcp.employeeID = ss.employeeID AND tcp.punchID = ss.punchID) q
            ON esc.employeeID = q.employeeID
             WHERE q.punchOut IS NOT NULL
             ORDER BY name';
    
        $stmt = $connection->prepare($sql);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_BOTH);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

function getJobClockData($jobClockPunchID){
    $connection = connWhitneyUser();
    try {
     $sql = 'SELECT advancedJobClockID
             , employeeID
             , departmentID
             , workOrderNumber
             , start
             , stop
             , lunch
             , modifiedBy
             , modifiedDate
             , timeClockID
             FROM advancedJobClock
             WHERE advancedJobClockID = :jobClockPunchID';
    
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':jobClockPunchID', $jobClockPunchID, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_BOTH);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}