<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
        <meta name="Description" content="Whitney Database Fulfillment/Assembly">

        <title>Whitney</title>



        <link type="text/css" media="screen"  rel="stylesheet" href="/css/screenBC.css" />
        <script src="../js/library.js"></script>
        <script type="text/javascript" src="/js/ajaxPhysicalInventoryInput.js"></script> 
        
        
    </head>
    
    <body class="forklift">

        <header>
            
            <?php include $_SERVER['DOCUMENT_ROOT'] . '/module/header.php'; ?>
            
            <div id="snackbar" onclick="dismissToastNow()" onmouseover="dismissToast()"></div>
            
        </header>
            <div id='opacity'></div>
            

<!-- Default Action -->
            
            <div class="barcode" id="barcode">
                <button onclick="recountView('recount');">Recounts</button>
                
                <?php if($_SESSION['securityLevel'] > 2){ echo '<button onclick="recountView(\'escalate\');">Escalated</button>';} ?>
                
                <h1 id="scanType">Scan Tag</h1>
                <p id="errorMessage"></p>
                <div id="scanBox">
                    <input type="text" name="scan" id="scan" class="scanTag" onfocusout="setFocusToTextBox()" onkeydown="Javascript: if (event.keyCode==13) serialScan();">
                </div>
                
                <div id="ha_holder">
                    <div id="ha_1"></div>
                    <div id="ha_2"></div>
                    <div id="ha_3"></div>
                    <div id="ha_4"></div>
                    <div id="ha_5"></div>
                    <div id="ha_6"></div>
                    <div id="ha_7"></div>
                    <div id="ha_8"></div>
                    <div id="ha_9"></div>
                    <div id="ha_10"></div>
                    <div id="ha_11"></div>
                    <div id="ha_12"></div>
                    <div id="ha_13"></div>
                    <div id="ha_14"></div>
                    <div id="ha_15"></div>
                    <div id="ha_16"></div>
                    <div id="ha_17"></div>
                    <div id="ha_18"></div>
                    <div id="ha_19"></div>
                    <div id="ha_20"></div>
                    <div id="ha_21"></div>
                    <div id="ha_22"></div>
                    <div id="ha_23"></div>
                    <div id="ha_24"></div>
                    <div id="ha_25"></div>
                    <div id="ha_26"></div>
                    <div id="ha_27"></div>
                    <div id="ha_28"></div>
                    <div id="ha_29"></div>
                    <div id="ha_30"></div>
                </div>
            </div>

        </main>
        <footer>
            <?php include $_SERVER['DOCUMENT_ROOT'] . '/module/footer.php'; ?>
        </footer>    
    </body>
    <script type="text/javascript">window.onload = setFocusToTextBox();</script>
</html>