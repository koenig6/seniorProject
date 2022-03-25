<?php
    date_default_timezone_set('America/Los_Angeles');
    //echo 'action: ' . $action; 
//    echo 'expireTime: ' . $_SESSION['loggedIn'] . '<br>';
//    echo 'actualTime: ' . time();
    if (!isset($_SESSION)){
        session_start();
        $_SESSION['loggedin'] = false;
        $action = 'gotoLogin';
    }
?>

    <?php if(isset($_SESSION['loggedIn']) && $_SESSION['loggedIn'] == true){
        echo '<nav><ul>';
        echo '<li><h1>' . lastMerge() . '</h1>';
        echo'<li><a href="/logIn/index.php?action=logOut" title="Click to Logout">
        <span class="icon-logout">M</span><span class="caption" id="caption-logout"></span></a>';
        echo'<li><a href="/logIn/index.php?action=mainMenu" title="Click for content home">
        <span class="icon-account">H</span><span class="caption" id="caption-account"></span></a>';
        echo '<li><h1>Welcome ' . $_SESSION['firstName'] . '</h1>'; 
        echo '</ul></nav>';
    }
    ?>

    

