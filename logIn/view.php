<!DOCTYPE html>
<html>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/module/head.php'; ?>
    <script type="text/javascript" src="/js/jquery-3.2.1.min.js"></script>
    <script type="text/javascript" src="/js/scannerInputDetection.js"></script>
    <script type="text/javascript" src="/js/globalScan.js"></script>

    <script type="text/javascript" src="/js/widgets.js"></script>

    <script type="text/javascript">
        function timeClockCheck(trigger){
            
        var num = parseInt(trigger.substring(1, trigger.length));
        
            if (trigger == 'test' || trigger == 'time'){
                window.location.assign("/timeClock/index.php");
            }else if (trigger == 'nw'){
                window.location.assign("/test/index.php");
            }else if (trigger == 'job'){
                window.location.assign("/jobClock/index.php");
            }else if (num > 9999){
                document.getElementById("loginForm").submit();
            }

        }
       

    </script>


<script>
      window.Promise ||
        document.write(
          '<script src="https://cdn.jsdelivr.net/npm/promise-polyfill@8/dist/polyfill.min.js"><\/script>'
        )
      window.Promise ||
        document.write(
          '<script src="https://cdn.jsdelivr.net/npm/eligrey-classlist-js-polyfill@1.2.20171210/classList.min.js"><\/script>'
        )
      window.Promise ||
        document.write(
          '<script src="https://cdn.jsdelivr.net/npm/findindex_polyfill_mdn"><\/script>'
        )
</script>


    <link rel="stylesheet" href="login.css" type="text/css">
    <link rel="stylesheet" href="../css/navigation.css" type="text/css">

    <!-- Bootstrap -->
    <!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-+0n0xVW2eSR5OomGNYDnhzAbDsOXxcvSN1TPprVMTNDbiYZCxYbOOl7+AMvyTG2x" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4" crossorigin="anonymous"></script> -->


    <body class="background">
        <!-- <header>
            <nav></nav>
        </header> -->
        <main>
<!--   Login View   -->
            <div id="busy"></div>
            <?php if($action == 'gotoLogin') : ?>
            
                <?php 

                    // $versionNo = getVersionNo();        
                    

                  //   if($versionNo == NULL || $versionNo == '') {
                  //     echo '';
                  // }else {
                  //     echo $versionNo;
                  // }
                ?>
                

                <form method="post" action=".">
                  <div class="container">
                    <div class="login">
                      <div id="errorMessage">
                        <?php if(isset($_SESSION['message'])){echo'<p class="errorMessage">' . $_SESSION['message'] . '</p>';}?>
                      </div>
                      <h1><i class="bi bi-file-lock2 lock"></i></h1>
                      <h1>Login</h1>
                      <p class="versionNumber"><sub>Whitney v2.1.8</sub></p>
                      <input name="userName" type="text" placeholder="Username" value="<?php echo $userName ?>" id="userName" onchange="timeClockCheck(this.value)" required autofocus>
                      <input name="password" type="password" class="password" placeholder="Password" required>
                      <button class="btn btn-outline-light" type="submit" name="action" id="action" value="login">Login <i class="bi bi-box-arrow-right"></i></button>
                    </div>
                  </div>

<!-- 
                  <h1>Login <i>Beta_1</i> 
                  </h1>
                  <ul>
                   
                      <li>
                          <label for="userName">User Name:</label>
                          <input type="text" name="userName" placeholder="Enter User Name" value="<?php echo $userName ?>" id="userName" onchange="timeClockCheck(this.value)" autofocus required>
                      </li>
                      <li>
                          <label for="password">Password:</label>
                          <input type="password" name="password" placeholder="Enter Password" id="password" required>
                      </li>             
                  </ul>
                  <button type="submit" name="action" id="action" value="login"><span>e</span></button> -->



                </form>
            <?php elseif($action == 'mainMenu') : ?>

            <div id="viewport" class="viewport">

            <?php include $_SERVER['DOCUMENT_ROOT'] . '/module/sideNav.php'; ?>
            
            <style>
                .db-contain {
                    padding: 10px;
                }
                .db {
                    border-radius: 10px;
                    color: white;
                }
                .db-header {
                    margin: 15px 25px;
                }
                .db-logo {
                    display: block;
                    margin: 30vh auto;
                    width: 60%;
                }

                .db-dark {
                    background-color: #1e1e1e;
                }
                .db-light {
                    background-color: #4fa9fb;
                }
            </style>

            <div class="grid db-contain">
                <div class="unit whole db db-dark">
                    <h1 class="db-header">Welcome <?php echo $_SESSION['firstName'] ?>!</h1>
                </div>

                <div class="grid">
                    <div class="unit one-quarter">
                        <div class="grid">
                            


                        </div>
                    </div>
                    <div class="unit half">
                        <img class="db-logo" src="/image/WhitneySolutionsHeader.png">
                    </div>
                    <div class="unit one-quarter">
                        <div class="grid">


                        
                        </div>
                    </div>
                </div>
            </div>

            <!-- <div class="loginStartBox">
                <img class="logo" src="/image/WhitneySolutionsHeader.png">
                <div class="welcomeScrollBox"><p>Welcome <?php echo $_SESSION['firstName'] ?>!</p></div>
            </div> -->
                
            <?php endif ?>
            </div>
        </main>
        <footer>
            <?php include $_SERVER['DOCUMENT_ROOT'] . '/module/footer.php'; ?>
        </footer>
    </body>
</html>