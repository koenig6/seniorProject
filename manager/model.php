<?php

require $_SERVER['DOCUMENT_ROOT'] . '/library/library.php';
require $_SERVER['DOCUMENT_ROOT'] . '/library/library3.php';
require $_SERVER['DOCUMENT_ROOT'] . '/library/model.php';
// Return UserName
function getUserName($userName){
    $connection = connWhitneyUser();
 try {
  $sql = "SELECT userName FROM users WHERE userName = :userName";
  $stmt = $connection->prepare($sql);
  $stmt->bindValue(':userName', $userName);
  $stmt->execute();
  $existingemail = $stmt->fetch(PDO::FETCH_NUM);
  $stmt->closeCursor();
} catch (PDOException $exc){
    // Send to error page with message
    $message = 'Sorry, there was an internal error with the server.';
    $_SESSION['message'] = $message;
    header('location: /500.php');
    exit;
}
    // Note, we are not interested in the email, only if it exists
    if(!empty($existingemail)){
       return TRUE;
    }else{
       return FALSE;
    }
}

// Register a new user
function addUser($employeeID
                    , $userGroupID
                    , $userName
                    , $password
                    , $loginDuration
                    , $securityID
                    , $createdBy
                    , $modifiedBy) {
    $connection = connWhitneyUser();
    try {
        $sql = 'INSERT INTO users (   employeeID
                                    , userGroupID
                                    , userName
                                    , password
                                    , loginDuration 
                                    , securityID
                                    , createdBy
                                    , creationDate
                                    , modifiedBy
                                    , modifiedDate)
                VALUES (              :employeeID
                                    , :userGroupID
                                    , :userName
                                    , :password
                                    , :loginDuration
                                    , :securityID
                                    , :createdBy
                                    , :creationDate
                                    , :modifiedBy
                                    , :modifiedDate)';
        $result = '2012-01-01';
        
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':employeeID', $employeeID, PDO::PARAM_INT);
        $stmt->bindParam(':userGroupID', $userGroupID, PDO::PARAM_INT);
        $stmt->bindParam(':userName', $userName, PDO::PARAM_STR);
        $stmt->bindParam(':password', $password, PDO::PARAM_STR);
        $stmt->bindParam(':loginDuration', $loginDuration, PDO::PARAM_INT);
        $stmt->bindParam(':securityID', $securityID, PDO::PARAM_INT);
        $stmt->bindParam(':createdBy', $createdBy, PDO::PARAM_INT);
        $stmt->bindParam(':creationDate', $result, PDO::PARAM_STR);
        $stmt->bindParam(':modifiedBy', $modifiedBy, PDO::PARAM_INT);
        $stmt->bindParam(':modifiedDate', $result, PDO::PARAM_STR);
        $stmt->execute();
        $insertRow = $stmt->rowCount();
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $insertRow;
}

/*
 * User Search query
 */
function userSearch($searchTerm) {
    $connection = connWhitneyUser();
    try {
        $sql = 'SELECT u.userID, CONCAT(e.firstName, \' \' , e.lastName) as Name, ug.userGroup, u.userName, u.loginDuration, s.securityLevel '
              .'FROM users u '
              .'LEFT JOIN employee e '
              .'ON u.employeeID = e.employeeID '
              .'LEFT JOIN usergroup ug '
              .'ON u.userGroupID = ug.userGroupID '
              .'LEFT JOIN security s '
              .'ON u.securityID = s.securityID '
              .'WHERE e.firstName LIKE \'%' . $searchTerm . '%\' OR '
              .'e.lastName LIKE \'%' . $searchTerm . '%\' OR '
              .'ug.userGroup LIKE \'%' . $searchTerm . '%\' OR '
              .'u.userName LIKE \'%' . $searchTerm . '%\' OR '
              .'u.loginDuration LIKE \'%' . $searchTerm . '%\' OR '
              .'s.securityLevel LIKE \'%' . $searchTerm . '%\' '
              .'ORDER BY e.firstName ASC';
        $stmt = $connection->prepare($sql);
        $stmt->execute();
        $items = $stmt->fetchAll();
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $items;
}

/*
 * Gather Data for create/edit user
 */

// Get data for a single individual
function getUser($userID) {
    $connection = connWhitneyUser();
    try {
     $sql = 'SELECT * FROM users WHERE userID = :userID';
        $stmt = $connection->prepare($sql);
        $stmt->bindValue(':userID', $userID, PDO::PARAM_INT);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $user;
}


/*
 *  Get data lists 
 */
// Employee ID with Name List
function getEmployeeIDList() {
    $connection = connWhitneyUser();
    try {
        $sql = 'SELECT employeeID, CONCAT(firstName, \' \' , lastName) as Name FROM employee ORDER BY firstName';
        $stmt = $connection->prepare($sql);
        $stmt->execute();
        $data = $stmt->fetchAll();
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}
// User Group List
function getUserGroupIDList() {
    $connection = connWhitneyUser();
    try {
     $sql = 'SELECT * FROM usergroup';
        $stmt = $connection->prepare($sql);
        $stmt->execute();
        $data = $stmt->fetchAll();
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

// Security List
function getSecurityIDList($securityID) {
    $connection = connWhitneyUser();
    try {
        $sql = 'SELECT * FROM security';
        
        if($securityID < 5) {
            $sql .= ' WHERE securityID < 5';  
        }elseif ($securityID < 6) {
            $sql .= ' WHERE securityID < 6';  
        }

        $stmt = $connection->prepare($sql);
        $stmt->execute();
        $data = $stmt->fetchAll();
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

/*
 * Update data for an individual
 */ 
function updateUser($userID
                            , $employeeID
                            , $userGroupID
                            , $userName
                            , $password
                            , $loginDuration
                            , $securityID
                            , $modifiedBy){
 $connection = connWhitneyUser();
 try {
  // Test if there is a value for a password (it is being reset)
  if(empty($password)){
        $sql = 'UPDATE users SET employeeID = :employeeID'
                . ', userGroupID = :userGroupID'
                . ', userName = :userName '
                . ', loginDuration = :loginDuration '
                . ', securityID = :securityID '
                . ', modifiedBy = :modifiedBy '
                . 'WHERE userID = :userID';
  } else {
   $sql = 'UPDATE users SET employeeID = :employeeID'
                . ', userGroupID = :userGroupID'
                . ', userName = :userName '
                . ', loginDuration = :loginDuration '
                . ', securityID = :securityID '
                . ', modifiedBy = :modifiedBy '
                . ', password = :password '
                . 'WHERE userID = :userID';
  }
 $stmt = $connection->prepare($sql);
 $stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
 $stmt->bindParam(':employeeID', $employeeID, PDO::PARAM_INT);
 $stmt->bindParam(':userGroupID', $userGroupID, PDO::PARAM_INT);
 $stmt->bindParam(':userName', $userName, PDO::PARAM_STR);
 $stmt->bindParam(':loginDuration', $loginDuration, PDO::PARAM_INT);
 $stmt->bindParam(':securityID', $securityID, PDO::PARAM_INT);
 $stmt->bindParam(':modifiedBy', $modifiedBy, PDO::PARAM_INT);
 if(!empty($password)){
 $stmt->bindParam(':password', $password, PDO::PARAM_STR);
 }
 $stmt->execute();
 $updateRow = $stmt->rowCount();
 $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $updateRow;
}

function employeeSearch($searchTerm) {
    $connection = connWhitneyUser();
    try {
        $sql = 'SELECT e.employeeID, CONCAT(e.FIRSTNAME, \' \', e.LASTNAME) AS name, es.EMPLOYEESTATUS, e.PRIMARYPHONE, e.active '
                . 'FROM employee e '
                . 'LEFT JOIN employeestatus es '
                . 'ON e.statusID = es.employeestatusID '
                . 'WHERE e.firstName LIKE \'%' . $searchTerm . '%\' OR '
                . 'e.lastName LIKE \'%' . $searchTerm . '%\' OR '
                . 'es.employeeStatus LIKE \'%' . $searchTerm . '%\' '
                . 'ORDER BY e.firstName ASC ';
        $stmt = $connection->prepare($sql);
        $stmt->execute();
        $data = $stmt->fetchAll();
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

function employeeNameSearch($searchTerm) {
    $connection = connWhitneyUser();
    try {
        $sql = 'SELECT e.employeeID, CONCAT(e.FIRSTNAME, \' \', e.LASTNAME) AS name
                FROM employee e
                WHERE e.firstName LIKE \'%' . $searchTerm . '%\' OR
                e.lastName LIKE \'%' . $searchTerm . '%\'
                ORDER BY e.firstName ASC
                LIMIT 10';
        $stmt = $connection->prepare($sql);
        $stmt->execute();
        $data = $stmt->fetchAll();
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

/*
 *  Get data lists 
 */

// Job title list
function getTitleIDList() {
    $connection = connWhitneyUser();
    try {
        $sql = 'SELECT * FROM jobtitle';
        $stmt = $connection->prepare($sql);
        $stmt->execute();
        $data = $stmt->fetchAll();
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

// Status ID List
function getStatusIDList() {
    $connection = connWhitneyUser();
    try {
     $sql = 'SELECT * FROM employeestatus';
        $stmt = $connection->prepare($sql);
        $stmt->execute();
        $data = $stmt->fetchAll();
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

//Get list of shifts
function getShiftIDList() {
    $connection = connWhitneyUser();
    try {
     $sql = 'SELECT * FROM shift';
        $stmt = $connection->prepare($sql);
        $stmt->execute();
        $data = $stmt->fetchAll();
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}

//Get Temp Agency List
function getTempAgencyList() {
    $connection = connWhitneyUser();
    try {
     $sql = 'SELECT * FROM tempagency';
        $stmt = $connection->prepare($sql);
        $stmt->execute();
        $data = $stmt->fetchAll();
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $data;
}


// Return UserName for verification of non-duplicate
function getEmployeeName($firstName, $lastName){
    $connection = connWhitneyUser();
 try {
  $sql = 'SELECT employeeID FROM employee WHERE firstName = :firstName AND lastName = :lastName';
  $stmt = $connection->prepare($sql);
  $stmt->bindValue(':firstName', $firstName);
  $stmt->bindValue(':lastName', $lastName);
  $stmt->execute();
  $data = $stmt->fetch(PDO::FETCH_NUM);
  $stmt->closeCursor();
} catch (PDOException $exc){
    // Send to error page with message
    $message = 'Sorry, there was an internal error with the server.';
    $_SESSION['message'] = $message;
    header('location: /500.php');
    exit;
}
  if(!empty($data)){
   return TRUE;
  } else {
   return FALSE;
  }
}

// Create a new Employee
function addEmployee($hireDate
                                , $firstName
                                , $lastName
                                , $addressLine1
                                , $addressLine2
                                , $city
                                , $state
                                , $zip
                                , $primaryPhone
                                , $secondaryPhone
                                , $titleID
                                , $statusID
                                , $shiftID    
                                , $tempAgencyID
                                , $active
                                , $employeeNotes
                                , $createdBY
                                , $modifiedBy
                                , $lunchDuration) {
    $connection = connWhitneyUser();
    try {
        $sql = 'INSERT INTO employee (hireDate
                                    , firstName
                                    , lastName
                                    , addressLine1
                                    , addressLine2 
                                    , city
                                    , state
                                    , zip
                                    , primaryPhone
                                    , secondaryPhone
                                    , titleID
                                    , statusID
                                    , shiftID
                                    , tempAgencyID
                                    , active
                                    , employeeNotes
                                    , createdBY
                                    , modifiedBy
                                    , lunchDuration)
                VALUES (              :hireDate
                                    , :firstName
                                    , :lastName
                                    , :addressLine1
                                    , :addressLine2
                                    , :city
                                    , :state
                                    , :zip
                                    , :primaryPhone
                                    , :secondaryPhone
                                    , :titleID
                                    , :statusID
                                    , :shiftID
                                    , :tempAgencyID
                                    , :active
                                    , :employeeNotes
                                    , :createdBY
                                    , :modifiedBy
                                    , :lunchDuration)';
        
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':hireDate', $hireDate, PDO::PARAM_STR);
        $stmt->bindParam(':firstName', $firstName, PDO::PARAM_STR);
        $stmt->bindParam(':lastName', $lastName, PDO::PARAM_STR);
        $stmt->bindParam(':addressLine1', $addressLine1, PDO::PARAM_STR);
        $stmt->bindParam(':addressLine2', $addressLine2, PDO::PARAM_STR);
        $stmt->bindParam(':city', $city, PDO::PARAM_STR);
        $stmt->bindParam(':state', $state, PDO::PARAM_STR);
        $stmt->bindParam(':zip', $zip, PDO::PARAM_INT);
        $stmt->bindParam(':primaryPhone', $primaryPhone, PDO::PARAM_INT);
        $stmt->bindParam(':secondaryPhone', $secondaryPhone, PDO::PARAM_INT);
        $stmt->bindParam(':titleID', $titleID, PDO::PARAM_INT);
        $stmt->bindParam(':statusID', $statusID, PDO::PARAM_INT);
        $stmt->bindParam(':shiftID', $shiftID, PDO::PARAM_INT);
        $stmt->bindParam(':tempAgencyID', $tempAgencyID, PDO::PARAM_INT);
        $stmt->bindParam(':active', $active, PDO::PARAM_INT);
        $stmt->bindParam(':employeeNotes', $employeeNotes, PDO::PARAM_STR);
        $stmt->bindParam(':createdBY', $createdBY, PDO::PARAM_INT);
        $stmt->bindParam(':modifiedBy', $modifiedBy, PDO::PARAM_INT);
        $stmt->bindParam(':lunchDuration', $lunchDuration, PDO::PARAM_INT);
        
        $stmt->execute();
        $insertRow = $stmt->rowCount();
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $insertRow;
}



function updateEmployee($employeeID
                            , $firstName
                            , $lastName
                            , $titleID
                            , $statusID
                            , $shiftID
                            , $active
                            , $employeeNotes
                            , $tempAgencyID
                            , $modifiedBy
                            , $departmentID
                            , $laborGroupID
                            , $tempEmployeeID
                            , $lunchDuration){
    $connection = connWhitneyUser();
    
    try {
       $sql = 'UPDATE employee SET 
                    firstName = :firstName
                  , lastName = :lastName
                  , titleID = :titleID
                  , statusID = :statusID
                  , shiftID = :shiftID
                  , active = :active
                  , employeeNotes = :employeeNotes
                  , tempAgencyID = :tempAgencyID
                  , modifiedBy = :modifiedBy
                  , departmentID = :departmentID
                  , laborGroupID = :laborGroupID
                  , tempEmployeeID = :tempEmployeeID
                  , lunchDuration = :lunchDuration
                  WHERE employeeID = :employeeID';
       $stmt = $connection->prepare($sql);
       $stmt->bindParam(':employeeID', $employeeID, PDO::PARAM_INT);
       $stmt->bindParam(':firstName', $firstName, PDO::PARAM_STR);
       $stmt->bindParam(':lastName', $lastName, PDO::PARAM_STR);
       $stmt->bindParam(':titleID', $titleID, PDO::PARAM_INT);
       $stmt->bindParam(':statusID', $statusID, PDO::PARAM_INT);
       $stmt->bindParam(':shiftID', $shiftID, PDO::PARAM_INT);
       $stmt->bindParam(':active', $active, PDO::PARAM_INT);
       $stmt->bindParam(':employeeNotes', $employeeNotes, PDO::PARAM_STR);
       $stmt->bindParam(':tempAgencyID', $tempAgencyID, PDO::PARAM_INT);
       $stmt->bindParam(':modifiedBy', $modifiedBy, PDO::PARAM_INT);
       $stmt->bindParam(':departmentID', $departmentID, PDO::PARAM_INT);
       $stmt->bindParam(':laborGroupID', $laborGroupID, PDO::PARAM_INT);
       $stmt->bindParam(':tempEmployeeID', $tempEmployeeID, PDO::PARAM_INT);
       $stmt->bindParam(':lunchDuration', $lunchDuration, PDO::PARAM_INT);
       $stmt->execute();
       $updateRow = $stmt->rowCount();
       $stmt->closeCursor();
    } catch (PDOException $ex) {
           return $ex;
    }
    $myvar = $stmt->errorInfo();
    // echo var_dump($myvar);
    return $updateRow;
    
}

function getPunches($employeeID, $startOfWeek, $endOfWeek, $lunchDuration = '1800'){
    $connection = connWhitneyUser();
    try {
     $sql = 'SELECT x.employeeID
            , x.punchout
            , x.punchin
            , x.punchDate
            , CASE WHEN x.time > 21600 THEN x.time - :lunchDuration ELSE x.time END AS time
            , x.punchID
            FROM(SELECT t.employeeID
                , SEC_TO_TIME(FLOOR((TIME_TO_SEC(punchout)+150)/300)*300) AS punchout
                , SEC_TO_TIME(FLOOR((TIME_TO_SEC(punchin)+150)/300)*300) AS punchin
                , punchDate
                , TIME_TO_SEC(
	                	timediff(
	                		CONCAT(TIME_FORMAT(punchout, \'%Y-%m-%d\'), " ", SEC_TO_TIME(FLOOR((TIME_TO_SEC(punchout)+150)/300)*300))
	                		, CONCAT(TIME_FORMAT(punchin, \'%Y-%m-%d\'), " ", SEC_TO_TIME(FLOOR((TIME_TO_SEC(punchin)+150)/300)*300))
	                		)
	                	) as time
                , punchID
                FROM timeClockPunch t
                LEFT JOIN employee e
                ON t.employeeID = e.employeeID
                WHERE t.employeeID = :employeeID
            AND punchDate >= :startOfWeek
            AND punchDate <= :endOfWeek) x';
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':employeeID', $employeeID, PDO::PARAM_INT);
        $stmt->bindParam(':startOfWeek', $startOfWeek, PDO::PARAM_STR);
        $stmt->bindParam(':endOfWeek', $endOfWeek, PDO::PARAM_STR);
        $stmt->bindParam(':lunchDuration', $lunchDuration, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetchAll();
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    
    return $data;
    
}

function getDailyHours($employeeID, $startOfWeek, $endOfWeek, $lunchDuration = '1800'){
    $connection = connWhitneyUser();
    try {
     $sql = 'SELECT 
                x.employeeID
                , CASE WHEN x.time > 21600 THEN sum(x.time - :lunchDuration) ELSE sum(x.time) END AS time
                , x.punchDate
                FROM(SELECT t.employeeID
                  , TIME_TO_SEC(
                        timediff(
                                CONCAT(TIME_FORMAT(punchout, \'%Y-%m-%d\'), " ", SEC_TO_TIME(FLOOR((TIME_TO_SEC(punchout)+150)/300)*300))
                        , CONCAT(TIME_FORMAT(punchin, \'%Y-%m-%d\'), " ", SEC_TO_TIME(FLOOR((TIME_TO_SEC(punchin)+150)/300)*300))
                                )
                        ) as time
                  , punchDate
                    FROM timeClockPunch t
                    LEFT JOIN employee e
                    ON t.employeeID = e.employeeID
                   WHERE t.employeeID = :employeeID
                    AND punchDate >= :startOfWeek
                    AND punchDate <= :endOfWeek
                    AND punchout is not null) x
                    GROUP BY punchDate;';
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':employeeID', $employeeID, PDO::PARAM_INT);
        $stmt->bindParam(':startOfWeek', $startOfWeek, PDO::PARAM_STR);
        $stmt->bindParam(':endOfWeek', $endOfWeek, PDO::PARAM_STR);
        $stmt->bindParam(':lunchDuration', $lunchDuration, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetchAll();
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    
    return $data;
    
}

function getMissingPunch($employeeID, $startOfWeek, $endOfWeek){
    $connection = connWhitneyUser();
    try {
     $sql = 'SELECT t.employeeID
          , IFNULL(punchOut, 1) as Missing
          , punchDate
            FROM timeClockPunch t
            LEFT JOIN employee e
            ON t.employeeID = e.employeeID
            WHERE t.employeeID = :employeeID
            AND punchDate >= :startOfWeek
            AND punchDate <= :endOfWeek
            GROUP BY punchDate';
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':employeeID', $employeeID, PDO::PARAM_INT);
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

function countMissingPunch($employeeID, $startOfWeek, $endOfWeek){
    $connection = connWhitneyUser();
    try {
     $sql = 'SELECT count(*)
            FROM timeClockPunch t
            LEFT JOIN employee e
            ON t.employeeID = e.employeeID
            WHERE t.employeeID = :employeeID
            AND punchDate >= :startOfWeek
            AND punchDate <= :endOfWeek
            AND punchOut IS NULL
            GROUP BY Punchout';
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':employeeID', $employeeID, PDO::PARAM_INT);
        $stmt->bindParam(':startOfWeek', $startOfWeek, PDO::PARAM_STR);
        $stmt->bindParam(':endOfWeek', $endOfWeek, PDO::PARAM_STR);
        $stmt->execute();
        $data = $stmt->fetch();
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    
    return $data;
    
}

function insertPunch($punchIn, $punchOut, $selectedDate, $employeeID, $userID, $timeStamp){
    $connection = connWhitneyUser();
    try {
        $sql = 'INSERT INTO timeClockPunch (   punchDate
                                    , punchIn
                                    , punchOut
                                    , employeeID
                                    , modifiedDate
                                    , modifiedBy)
                VALUES (              :punchDate
                                    , :punchIn
                                    , :punchOut
                                    , :employeeID
                                    , :timeStamp
                                    , :userID)';
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':punchDate', $selectedDate, PDO::PARAM_STR);
        $stmt->bindParam(':punchIn', $punchIn, PDO::PARAM_STR);
        $stmt->bindParam(':punchOut', $punchOut, PDO::PARAM_STR);
        $stmt->bindParam(':employeeID', $employeeID, PDO::PARAM_INT);
        $stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
        $stmt->bindParam(':timeStamp', $timeStamp, PDO::PARAM_INT);
        $stmt->execute();
        $insertRow = $stmt->rowCount();
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $insertRow;
}

function deletePunch($punchID){
    $connection = connWhitneyAdmin();
    
    try {
        $sql = 'DELETE FROM timeClockPunch
                WHERE punchID = :punchID';
        
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':punchID', $punchID, PDO::PARAM_INT);
        $stmt->execute();
        $deleteRow = $stmt->rowCount();
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $deleteRow;
}

function editPunches($punchID, $inPunch, $outPunch, $userID, $timeStamp){
    $connection = connWhitneyUser();
 try {
    $sql = 'UPDATE timeClockPunch SET 
                punchOut = :outPunch
               , punchIn = :inPunch
               , modifiedBy = :userID
               , modifiedDate = :timeStamp
               WHERE punchID = :punchID';
    $stmt = $connection->prepare($sql);
    $stmt->bindParam(':punchID', $punchID, PDO::PARAM_INT);
    $stmt->bindParam(':inPunch', $inPunch, PDO::PARAM_STR);
    $stmt->bindParam(':outPunch', $outPunch, PDO::PARAM_STR);
    $stmt->bindParam(':userID', $userID, PDO::PARAM_INT);
    $stmt->bindParam(':timeStamp', $timeStamp, PDO::PARAM_STR);
    $stmt->execute();
    $updateRow = $stmt->rowCount();
    $stmt->closeCursor();
 } catch (PDOException $ex) {
        return FALSE;
    }
    return $updateRow;
}

function updateKeyCardNumber($employeeID, $newKeyCard){
    $connection = connWhitneyUser();
 try {
    $sql = 'UPDATE employee SET 
            keyCardNumber = :newKeyCard
            WHERE employeeID = :employeeID';
    $stmt = $connection->prepare($sql);
    $stmt->bindParam(':employeeID', $employeeID, PDO::PARAM_INT);
    $stmt->bindParam(':newKeyCard', $newKeyCard, PDO::PARAM_INT);
    $stmt->execute();
    $updateRow = $stmt->rowCount();
    $stmt->closeCursor();
 } catch (PDOException $ex) {
        return FALSE;
    }
    return $updateRow;
}



function getPunch($punchID){
    $connection = connWhitneyUser();
    try {
     $sql = 'SELECT punchDate, punchIn, punchOut, employeeID
            FROM timeClockPunch t
            WHERE punchID = :punchID';
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':punchID', $punchID, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetch();
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    
    return $data;
    
}



function getJobClockDataByTimeClock($punchID){
    $connection = connWhitneyUser();
    try {
     $sql = 'SELECT advancedJobClockID
            FROM advancedJobClock
            WHERE timeClockID = :punchID';
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':punchID', $punchID, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetchAll();
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    
    return $data;
    
}

function insertBinLocation($binName, $binCatID) {
    $altBinLocation = 'LSSLP+' . $binName;
    $connection = connWhitneyUser();
    try {
        $sql = 'INSERT INTO binLocation VALUES ( DEFAULT, :binName, :binCatID, :altBinLocation )';
        $stmt = $connection->prepare($sql);
        $stmt->bindParam(':binName', $binName, PDO::PARAM_STR);
        $stmt->bindParam(':altBinLocation', $altBinLocation, PDO::PARAM_STR);
        $stmt->bindParam(':binCatID', $binCatID, PDO::PARAM_INT);
        $stmt->execute();
        $insertRow = $stmt->rowCount();
        $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $insertRow;
}

function setBinCategoryID($binID, $binCatID) {
    $connection = connWhitneyUser();
    try {
       $sql = 'UPDATE binLocation SET binCategoryID = :binCatID WHERE binLocationID = :binID';
       $stmt = $connection->prepare($sql);
       $stmt->bindParam(':binID', $binID, PDO::PARAM_INT);
       $stmt->bindParam(':binCatID', $binCatID, PDO::PARAM_INT);
       $stmt->execute();
       $updateRow = $stmt->rowCount();
       $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $updateRow;
}

function updateBinLocation($binID, $binName, $binCatID) {
    $connection = connWhitneyUser();
    try {
       $sql = 'UPDATE binLocation SET binName = :binName, binCategoryID = :binCatID WHERE binLocationID = :binID';
       $stmt = $connection->prepare($sql);
       $stmt->bindParam(':binName', $binName, PDO::PARAM_STR);
       $stmt->bindParam(':binID', $binID, PDO::PARAM_INT);
       $stmt->bindParam(':binCatID', $binCatID, PDO::PARAM_INT);
       $stmt->execute();
       $updateRow = $stmt->rowCount();
       $stmt->closeCursor();
    } catch (PDOException $ex) {
        return FALSE;
    }
    return $updateRow;
}
