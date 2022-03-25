<?php
    require $_SERVER['DOCUMENT_ROOT'] . '/library/library.php';
    require $_SERVER['DOCUMENT_ROOT'] . '/library/model.php';
// Login user
function loginUser($userName){
    $connection = connWhitneyUser();
 
    try{
     $sql = "SELECT u.userName, e.firstName, u.password, u.securityID, u.loginDuration, u.userID
            FROM users u
            LEFT JOIN employee e
            ON u.employeeID = e.employeeID
            WHERE userName = :userName";
     $stmt = $connection->prepare($sql);
     $stmt->bindValue(':userName', $userName,PDO::PARAM_STR);
     $stmt->execute();
     $custInfo = $stmt->fetch(PDO::FETCH_ASSOC);
     $stmt->closeCursor();
   } catch (PDOException $exc){
       return FALSE;
   } 

   // Returns the hashed password
   return $custInfo;
}
