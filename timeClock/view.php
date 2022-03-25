<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
        <meta name="Description" content="Whitney Database Fulfillment/Assembly">

        <title>Whitney</title>
        
        <link type="text/css" media="screen"  rel="stylesheet" href="/css/screenTC.css" />
        <script type="text/javascript" src="/js/date_time.js"></script>  
        <script type="text/javascript" src="/js/ajaxTimeClock.js"></script> 
        
        <div id="fadeStyle"></div>
    </head>
    
    <body class="background">
        <header>
            <div class="grid">
                <div class="unit one-third">
                    <div id="punchBox">
                        <input type="text" name="punch" id="punch" autocomplete="off" autofocus onkeydown="Javascript: if (event.keyCode==13) punch();">
                    </div>
                </div>
                <div class="unit one-third">
                    <span id="date_time"></span>
                </div>
            </div>
        </header>
        <main>
            <div class="clock">
            <div id="Date"></div>
              <ul>
                  <li id="hours"></li>
                  <li id="point">:</li>
                  <li id="min"></li>
                  <li id="point">:</li>
                  <li id="sec"></li>
              </ul>
            </div>
            <!-- Script for date/clock -->
            <script type="text/javascript">
                window.onload = date_time('date_time', 'hours', 'min', 'sec');
                window.onload = refreshSchedule();
            </script>
      
            
            <div id="schedule">
            <?php
                
                echo $scheduleTable;

            ?>
            </div>
        </main>
        <footer>
            <div id="punchOverlay">

            </div>
        </footer>    
    </body>

</html>