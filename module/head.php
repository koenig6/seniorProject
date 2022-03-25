    <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
            <meta name="Description" content="Whitney Database Fulfillment/Assembly">

            <!-- <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
            <meta http-equiv="Pragma" content="no-cache" /> -->
            <!-- <meta http-equiv="Expires" content="0" /> -->
            <meta http-equiv="Pragma" content="no-cache">

            <title>Whitney</title>

        <!-- Global Stylesheet -->
        <link type="text/css" media="screen"  rel="stylesheet" href="/css/screen.css" />

        <!-- Dash board stylesheet -->
        <link rel="stylesheet" rel="stylesheet" href="/css/advJobClock.css">

        <!-- Apex Charts -->
        <!-- <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
        <link type="text/css" media="screen"  rel="stylesheet" href="/apex/samples/assets/styles.css" /> -->

        <!-- Global JS -->
        <script type="text/javascript" src="/js/library.js"></script>

        <!-- Printer Utility -->
        <script type="text/javascript" src="/js/qz-tray.js"></script>

        <!-- Print Styling for tables, etc. -->
        <link type="text/css" media="print"  rel="stylesheet" href="/css/print_1.css" />
    </head>
    <div id="globalOverlay"></div>

    <script type="text/javascript">

        function idleTimer() {
            var xmlhttp = new XMLHttpRequest();
            xmlhttp.onreadystatechange = function() {
                if (this.readyState == 4 && this.status == 200) {
                    var timeout = this.responseText; // Time in seconds
                    var t;

                    //window.onload = resetTimer;
                    window.onmousemove = resetTimer; // catches mouse movements
                    window.onmousedown = resetTimer; // catches mouse movements
                    window.onclick = resetTimer;     // catches mouse clicks
                    window.onscroll = resetTimer;    // catches scrolling
                    window.onkeypress = resetTimer;  //catches keyboard actions

                    function logout() {
                        window.location.href="/logIn/index.php?action=logOut";  // Adapt to actual logout script
                    }
                    
                    function darken() {
                        document.getElementById('globalOverlay').classList.add("darken");
                    }
                    
                    function resetTimer() {
                        clearTimeout(t);
                        if(document.getElementById('globalOverlay').classList.contains("darken")) {
                            document.getElementById('globalOverlay').classList.remote("darken");
                        }
                        t = setTimeout(logout, timeout*1000);  // time is in milliseconds (1000 is 1 second)
                        t = setTimeout(darken, (timeout-45)*1000);  // time is in milliseconds (1000 is 1 second)
                    }
                }
                    
            };
            xmlhttp.open("GET", "/login/index.php?action=getUserLoginDuration");
            xmlhttp.send();
        }
        var path = window.location.pathname;
        if(!path.includes('/logIn/index.php')) {
            idleTimer();
        }

    </script>