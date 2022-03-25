
<script type="text/javascript" src="/js/mobileNavbar.js"></script>



<?php 


if(isset($_SESSION['loggedIn'])) {
    if($_SESSION['loggedIn'] == true) {
        echo '
            <a href="#" onclick="openNav()" id="navBtn"><i class="bi bi-list"></i></a>

            <div id="nav-box">
                <a href="#" style="padding-top: 1em" onclick="closeNav()">
                    <i class="bi bi-arrow-up"></i>
                </a>
                <a href="/forklift/index.php">
                    <i class="bi bi-upc-scan"></i>
                    <p>Forklift</p>
                </a>';
                if($_SESSION['physicalInventory'] != 0){
                    echo '
                    <a href="/physicalInventoryInput/index.php?action=physicalInventoryInput" title="Physical Inventory">
                        <i class="bi bi-vector-pen"></i>
                        <p>Physical Inventory</p>
                    </a>';
                }
                echo '<a href="/logIn/index.php?action=logOut">
                    <i class="bi bi-box-arrow-left"></i>
                    <p>Logout</p>
                </a>
            </div>
        ';
    }
} 
?>