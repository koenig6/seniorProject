<!DOCTYPE html>
<html>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/module/head.php'; ?>
    <script type="text/javascript" src="/js/ajaxCustom.js"></script>
    <script type="text/javascript" src="/js/jquery-2.1.4.js"></script>
    <link type="text/css" media="print"  rel="stylesheet" href="/css/print.css" />

    <link rel="stylesheet" href="../css/shipping.css">

    <script type="text/javascript" src="/js/SideNav.js"></script>
    <body class="background">
        <div id="overlay"></div>
        <div id="busy"></div>

            <?php include $_SERVER['DOCUMENT_ROOT'] . '/module/sideNav.php'; ?>

    
        <main>
            <div id="viewport" class="viewport">
            <div id="overlayPanel"></div>
            <div id="customView">
                <div id="errorMessage"></div>
                <?php
                    // Display custom sub menu
                    if($action == 'custom'){
                        echo $customMenu;
                    }elseif($action == 'smallWorldMenu'){
                        echo $smallWorldMenu;
                    }elseif($action == '')
                ?>
           </div>
           </div>
        </main>
        <footer>
            <?php include $_SERVER['DOCUMENT_ROOT'] . '/module/footer.php'; ?>
        </footer>    
    </body>
</html>