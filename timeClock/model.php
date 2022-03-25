<?php

require $_SERVER['DOCUMENT_ROOT'] . '/library/library.php';
require $_SERVER['DOCUMENT_ROOT'] . '/library/model.php';

function getScheduledEmployees($selectedDate){
    $connection = connWhitneyUser();
    try {
     $sql = 'SELECT esc.employeeID,concat(e.firstName, \' \' ,e.lastName) as name, s.shift, q.punchIn, q.punchOut
             FROM employeeschedule esc
             LEFT JOIN shift s
             ON esc.shiftID = s.shiftID
             LEFT JOIN employee e
             ON esc.employeeID = e.employeeID
             LEFT JOIN (SELECT tcp.punchID, tcp.employeeID, tcp.punchIn, tcp.punchOut
               FROM timeClockPunch tcp
               INNER JOIN(SELECT employeeID, max(punchID) punchID
                           FROM timeClockPunch
                           WHERE punchDate = :selectedDate
                           GROUP BY employeeID) ss
                ON tcp.employeeID = ss.employeeID AND tcp.punchID = ss.punchID) q
            ON esc.employeeID = q.employeeID
             WHERE scheduleDate = :selectedDate
             ORDER BY s.shift, name;';
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

function verifyValidEmployeeID($employeeID){
    $connection = connWhitneyUser();
    try {
     $sql = 'SELECT employeeID FROM employee WHERE employeeID = :employeeID';
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':employeeID', $employeeID, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetch();
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    if (isset($data[0])){
        return TRUE;
    }else{
        return FALSE;
    }
    
}

function verifyEmployeeScheduled($employeeID, $curDate){
    $connection = connWhitneyUser();
    try {
     $sql = 'SELECT employeeScheduleID 
             FROM employeeSchedule 
             WHERE employeeID = :employeeID 
             AND scheduleDate = :curDate';
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':employeeID', $employeeID, PDO::PARAM_INT);
        $stmt->bindParam(':curDate', $curDate, PDO::PARAM_STR);
        $stmt->execute();
        $data = $stmt->fetch();
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    if (isset($data[0])){
        return TRUE;
    }else{
        return FALSE;
    }
    
}




function workedHours($employeeID, $curTime, $curDate){
    $connection = connWhitneyUser();
    try {
     $sql = 'SELECT TIMEDIFF(:curTime, punchIn), :curTime, punchIn
             FROM timeClockPunch 
             WHERE employeeID = :employeeID 
             AND punchDate = :curDate
             AND punchOut IS NULL';
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':employeeID', $employeeID, PDO::PARAM_INT);
        $stmt->bindParam(':curTime', $curTime, PDO::PARAM_STR);
        $stmt->bindParam(':curDate', $curDate, PDO::PARAM_STR);
        $stmt->execute();
        $data = $stmt->fetch();
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    
    //Convert elapsed time to seconds
    $sec = strtotime('1970-01-01 ' . $data[0] . 'UTC');
    return $sec;
}

function punchIn($employeeID, $timeStamp, $curDate){

    $connection = connWhitneyUser();
 try {
    $sql = 'INSERT INTO timeClockPunch
                (punchDate, punchIn, employeeID)
            VALUES (:curDate, :timeStamp, :employeeID)';

        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':employeeID', $employeeID, PDO::PARAM_INT);
        $stmt->bindParam(':timeStamp', $timeStamp, PDO::PARAM_STR);
        $stmt->bindParam(':curDate', $curDate, PDO::PARAM_STR);
        $stmt->execute();
        $id = $connection->lastInsertId();
        $insertRow = $stmt->rowCount();
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $id;
}

function sinceLastPunch($employeeID, $yesterday, $punch){
    $connection = connWhitneyUser();
    try {
     $sql = 'SELECT TIMEDIFF(:punch, punchOut), punchOut
             FROM timeClockPunch 
             WHERE employeeID = :employeeID 
             AND punchDate >= :yesterday
             ORDER BY punchOut DESC
             LIMIT 1';
     
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':employeeID', $employeeID, PDO::PARAM_INT);
        $stmt->bindParam(':yesterday', $yesterday, PDO::PARAM_STR);
        $stmt->bindParam(':punch', $punch, PDO::PARAM_STR);
        $stmt->execute();
        $data = $stmt->fetch();
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    //Convert elapsed time to seconds
    $sec = strtotime('1970-01-01 ' . $data[0] . 'UTC');
    return $sec;
    
}



function getIndvKeyCard($employeeID){
    $connection = connWhitneyUser();
    try {
     $sql = 'SELECT keyCardNumber FROM employee WHERE employeeID = :employeeID';
     
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':employeeID', $employeeID, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetch();
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    //Convert elapsed time to seconds

    return $data;
    
}


function checkEmployeeIn($employeeID, $curDate){
    $connection = connWhitneyUser();
    try {
     $sql = 'SELECT punchIn
             FROM timeClockPunch 
             WHERE employeeID = :employeeID 
             AND punchDate = :curDate
             AND punchOut IS NULL';
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':employeeID', $employeeID, PDO::PARAM_INT);
        $stmt->bindParam(':curDate', $curDate, PDO::PARAM_STR);
        $stmt->execute();
        $data = $stmt->fetch();
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    if(isset($data[0])){
        return TRUE;
    }else{
        return FALSE;
    }
}
