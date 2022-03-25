
<?php
    date_default_timezone_set('America/Los_Angeles');
    ob_start(); // ensures anything dumped out will be caught

    // do stuff here
    $url = 'logIn/index.php'; // this can be set based on whatever

    // clear out the output buffer
    while (ob_get_status()) 
    {
        ob_end_clean();
    }

    // no redirect
    header( "Location: $url" );
    header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1.
    header("Pragma: no-cache"); // HTTP 1.0.
    header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");