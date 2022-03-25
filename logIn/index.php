<?php
session_start();
/* 
 * Login 
 */
$ajax = false;
$userName = null;
$message = null;
$action = null;
$phyInvHeaderBuild = null;
$phyInvBuild = null;

require 'model.php';

if (isset($_POST['action'])) {
 $action = $_POST['action'];
} elseif (isset($_GET['action'])) {
 $action = $_GET['action'];
}

if (isset($_POST['userName'])){
    if(ltrim($_POST['userName'], 'E') > 9999){
        $action = 'autologin';
    }
 }

if ($action == 'login'){
    
    $userName = filterString($_POST['userName']);
    $password = filterString($_POST['password']);

    // Check the data
    if(empty($userName) || empty($password)){
     $message = 'You must supply a user name and password.';
     $action = 'gotoLogin';
    }
    
    // Proceed with login attempt, if no errors
    // Get the data from the database based on the email address
    if(!isset($message)){
        
        $loginData = loginUser($userName);
        $hashedPassword = $loginData != false ? $loginData['password'] : false;
        
        // Compare the passwords for a match
        $passwordMatch = comparePassword($password, $hashedPassword);
        
        // If there is a match, do the login
        if($passwordMatch){
            //build session
            buildSession($loginData, false);

            $action = 'mainMenu';
            // $message = merge();
        }else {
            // There was not a match, tell the user
            $_SESSION['message'] = 'Incorrect User Name or Password';
            $action = 'gotoLogin';
	    //$_SESSION['message'] = hashPassword('nw982601');
        }
    }
}elseif($action=='autologin'){

    $eloginNumber = filterString($_POST['userName']);

    //Drop first Char of string
    $loginNumber = ltrim($eloginNumber, 'E');
    
    //extract employeeID
    $employeeID = substr($loginNumber,0,-4);

    //Get Employee Data
    $employeeData = getEmployeeData($employeeID);
    if(!$employeeData){
        $_SESSION['message'] = 'Auto Login Failed.';
        echo 0;
    }else{
        //Get key Code
        //$keyCode = getConstant('loadTagKey');
        
        //extract Universal Key
        $employeeKey = substr($loginNumber,-4);
        //$universalKey = substr(substr($loginNumber,-12),6);
        
        //Validate login Number
        if($employeeKey == $employeeData['keyCardNumber']){
            $loginData = getUserByEmployeeID($employeeID);
            
        //build session
        buildSession($loginData, true);
            
            $action = 'mainMenu';
        }else {
        // There was not a match, tell the user
            $_SESSION['message'] = "Auto Login Failed :2";
            $action = 'gotoLogin';
        }

    }

  //$ajax = true;  
}elseif(is_null($action)){
    $action = 'gotoLogin';
    
}elseif (timedOut()) {
    $action = 'gotoLogin';
    
}elseif($_SESSION['loggedIn'] != true){
    $action = 'gotoLogin';
    
}elseif($_SESSION['loggedIn'] == False && $_SESSION['securityLevel'] < 2){
        $action = 'gotoLogin';
        
}elseif($action == 'logOut'){
    logOut();
    $action = 'gotoLogin';
}elseif($action == 'mainMenu'){
    
    $action = 'mainMenu';

    // get user's preferences, send as array

}elseif($action == 'manager'){
    //verify security level before user proceeds
    if($_SESSION['securityLevel'] >= 3){
        header('Location: /manager/index.php');
    }else{
        $message = 'Access Restricted';
        $action = 'mainMenu';
    }
}elseif($action == 'tags'){
    //verify security level before user proceeds
    if($_SESSION['securityLevel'] >= 2){
        header('Location: /tags/index.php');
    }else{
        $message = 'Access Restricted';
        $action = 'mainMenu';
    }
}elseif($action == 'custom'){
    //verify security level before user proceeds
    if($_SESSION['securityLevel'] >= 3){
        header('Location: /custom/index.php');
    }else{
        $message = 'Access Restricted';
        $action = 'mainMenu';
    }
}elseif($action == 'searchItems'){
    //verify security level before user proceeds
    if($_SESSION['securityLevel'] >= 1){
        header('Location: /inventory/index.php');
    }else{
        $message = 'Access Restricted';
        $action = 'mainMenu';
    }     
}elseif($action == 'production'){
    //verify security level before user proceeds
    if($_SESSION['securityLevel'] >= 2){
        header('Location: /production/index.php');
    }else{
        $message = 'Access Restricted';
        $action = 'mainMenu';
    }
}elseif($action == 'forklift'){
    //verify security level before user proceeds
    if($_SESSION['securityLevel'] >= 2){
        header('Location: /forklift/index.php');
    }else{
        $message = 'Access Restricted';
        $action = 'mainMenu';
    }
}elseif($action == 'physicalInventoryInput'){
    //verify security level before user proceeds
    //echo $_SESSION['physicalInventory'];
    if($_SESSION['physicalInventory'] == 1){
        header('Location: /physicalInventoryInput/index.php');
    }else{
        $message = 'Access Restricted';
        $action = 'mainMenu';
    }
}elseif($action == 'getUserLoginDuration') {
    echo $_SESSION['loginDuration'];
    $ajax = true;
}elseif($action == 'checkSession') {
    if($_SESSION['logOut'] = true) {
        echo true;
    }else {
        echo false;
    }
}else {  
        $action = 'gotoLogin';
}

if(!$ajax){
   
    include 'view.php';
}


function buildSession($loginData, $autoLogin){
    
    //get constants
    $constants = getConstants();
    
    //Get division constants
    $firstBin = $constants[2]['constantValue'];
    $lastBin = $constants[3]['constantValue'];
    $clientID = $constants[15]['constantValue'];
    $eUsage = $constants[16]['constantValue'];
    $physicalInventory = $constants[13]['constantValue'];
    $jobClockDeptPunch = $constants[19]["constantValue"];
    $lastMerge = $constants[1]["constantValue"];
    

    // Use the session for login data
    $_SESSION['loggedIn'] = TRUE;
    $_SESSION['firstName'] = $loginData['firstName'];
    $_SESSION['userID'] = $loginData['userID'];
    $_SESSION['loginDuration'] = $loginData['loginDuration'];
    $_SESSION['EXPIRES'] = time() + $_SESSION['loginDuration'];
    $_SESSION['logOut'] = false;
    $_SESSION['message'] = null;
    $_SESSION['physicalInventory'] = ($physicalInventory <> 0 ? 1 : 0);
    $_SESSION['firstBin'] = $firstBin;
    $_SESSION['lastBin'] = $lastBin;
    $_SESSION['clientID'] = $clientID;
    $_SESSION['userName'] = $loginData['userName'];
    $_SESSION['eUsage'] = $eUsage;
    $_SESSION['deptPunch'] = $jobClockDeptPunch;
    $_SESSION['lastMerge'] = $lastMerge;
    
    
    if ($autoLogin){
        $_SESSION['securityLevel'] = 2;
    }else{
        $_SESSION['securityLevel'] = $loginData['securityID'];
    }
}