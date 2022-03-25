/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function buildmenu(y) {
    //Set Toggle to false
    var toggle = false;

    //Check to see if clicked subNav is already open, if it is set toggle true.
    if (y === 1 && document.getElementById("subNavProd").style.display === "block"
        || y === 2 && document.getElementById("subNavManager").style.display === "block"
        || y === 3 && document.getElementById("subNavCustom").style.display === "block"
        || y === 4 && document.getElementById("subNavInventory").style.display === "block") {
        toggle = true;
    }

    closeSubNav();

    if (!toggle) {
        openSubNav(y, toggle);
    }
}

function closeSubNav() {
    //Close All SubNav Components
    try {

        //code that causes an error
        document.getElementById("viewport").style.paddingLeft = "0vw";
    } catch (e) { }

    subReset();

}

function openSubNav(y) {
    try {

        //code that causes an error
        document.getElementById("viewport").style.paddingLeft = "70px";
    } catch (e) { };

    document.getElementById("subMenu").style.width = "73px";
    if (y === 1) {
        document.getElementById("subNavProd").style.display = "block";
    } else if (y === 2) {
        document.getElementById("subNavManager").style.display = "block";
    } else if (y === 3) {
        document.getElementById("subNavCustom").style.display = "block";
    } else if (y === 4) {
        document.getElementById("subNavInventory").style.display = "block";
    }

}


function subReset() {
    document.getElementById("subMenu").style.width = "0vw";
    document.getElementById("viewport").style.paddingLeft = "0vw";
    document.getElementById("subNavProd").style.display = "none";
    document.getElementById("subNavManager").style.display = "none";
    document.getElementById("subNavCustom").style.display = "none";
    document.getElementById("subNavInventory").style.display = "none";
}



function menuStack(x) {
    x.classList.toggle("change");
}

function productionSub() {
    document.getElementById("subMenu").style.width = "73px";
    document.getElementById("subNavProd").style.display = "block";
}

function managerSub() {
    document.getElementById("subMenu").style.width = "73px";
    document.getElementById("subNavManager").style.display = "block";
}

function customSub() {
    document.getElementById("subMenu").style.width = "73px";
    document.getElementById("subNavCustom").style.display = "block";
}

    // OK Acknowledge Alert Handling

function ok(message) {

    var audio = new Audio('/audio/Error.wav');
    audio.play();

    document.getElementById('ok').className="show";
    document.getElementById('okMessage').innerHTML = message;
}
    // Response close OK Acknowledge Alert
function okR() {
    document.getElementById('ok').className="hide";
    document.getElementById('okMessage').innerHTML = '';
}

function AYS(message) {
    document.getElementById('AYS').className="show";
    document.getElementById('AYSmessage').innerHTML = message;
}

function AYSyes() {
    document.getElementById('AYS').classList.remove('show');
    document.getElementById('AYSmessage').innerHTML = '';
    document.getElementById('AYSresponse').value = 1;
}

function AYScancel() {
    document.getElementById('AYS').classList.remove('show');
    document.getElementById('AYSmessage').innerHTML = '';
    document.getElementById('AYSresponse').value = 0;
}

    // Scroll To Top Handling

function jumpUp(location) {
    document.getElementById(location).scrollTop = 0;
}

function holdAll(lotCode, noHolds) {

    var xmlhttp = new XMLHttpRequest();

    xmlhttp.onreadystatechange = function () {
        if (xmlhttp.responseText == "timedOut") {
        window.location.href = "#";
        } else if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
    
        busyIndicator(false);

        // alert(xmlhttp.responseText)
        document.getElementById('globalOverlay').innerHTML = 
        '<div class="overlay holdOverlay"><a href="#" onclick="closeOverlayGlobal()" id="exit"><span class="viewportRedBtn">x</span></a>' + xmlhttp.responseText + "</div>";
        }
        
    };
    if(noHolds) {
        xmlhttp.open("GET", "/inventory/index.php?action=noHolds&lotCode="+lotCode);
    }else {
        xmlhttp.open("GET", "/inventory/index.php?action=holdAllItems&lotCode="+lotCode);
    }

    xmlhttp.send();
    
    busyIndicator(true);

}

function submitLotCodeSearch(e) {
    var key = e.keyCode || e.which;
    if (key === 13) {
        releaseHold(true);
    }
}

function releaseHold(lotCode, results, release) {

    subNav(-1); // Close Sidebar

    release = (release) ? 1 : 0;
    // release = 1;

    if(release) {
        lotCode = document.getElementById('lotCode').value;
        // lotCode = 'GO98729';
        results = null;
    }else {
        if(lotCode) {
            lotCode = document.getElementById('searchLotCode').value;
            if(lotCode == '') {
                toast('Lot Code is Required');
                return;
            }
        }else {
            lotCode = false;
        }
        results = results ?? false;
    }

    // alert(lotCode);
    // alert(results);
    // alert(release);

    var xmlhttp = new XMLHttpRequest();

    xmlhttp.onreadystatechange = function () {
      if (xmlhttp.responseText == "timedOut") {
        window.location.href = "#";
      } else if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
  
        busyIndicator(false);

        // alert(xmlhttp.responseText)

        if(xmlhttp.responseText == 'noholds') {
            // toast('No Items on Hold');
            holdAll(lotCode, true);
        }else if(xmlhttp.responseText == false) {
            toast('Lot Code Invalid');
        }else {
            document.getElementById('globalOverlay').innerHTML = 
            '<div class="overlay holdOverlay"><a href="#" onclick="closeOverlayGlobal()" id="exit"><span class="viewportRedBtn">x</span></a>' + xmlhttp.responseText + "</div>";
        }
      }
    };
    xmlhttp.open("GET", "/inventory/index.php?action=releaseHold&lotCode="+lotCode+'&results='+results+'&release='+release);
    xmlhttp.send();
  
    busyIndicator(true);

}

function printDiv() {
    var divContents = document.getElementById("printThis").innerHTML;
    var a = window.open('', "iFrame");
    a.document.write('<html>');
    a.document.write('<body>');
    a.document.write(divContents);
    a.document.write('</body></html>');
    a.document.close();
    a.print();
}

// function print(html) {

//     document.getElementById('globalOverlay').innerHTML = '<iframe class="hide" name="iframe" id="iframe"></iframe>';

//     var doc = document.getElementById('iframe').contentWindow.document;
//     doc.open();
//     doc.write(html);
//     doc.close();

//     window.frames["iframe"].focus();
//     window.frames["iframe"].print();

//     setTimeout(() => {  closeOverlayGlobal(); }, 500);

// }

function printElement() { 
    content = document.getElementById('printThis').innerHTML;

    var newWin = window.frames['printf'];
    newWin.document.write(content);
    newWin.document.close();

    window.frames["printf"].focus();
    window.frames["printf"].print();
} 

// TODO 3 Modals: small, wide, tall ( This mess needs to be fixed )

function overlay(content, tallContent = false, thinContent = false) {
    document.getElementById('globalOverlay').classList.add("globalOverlayFill");
    document.getElementById('globalOverlay').innerHTML = 
    '<div class="modal-body ' + ( tallContent ? 'modal-tall' : '' ) + ' ' + ( thinContent ? 'modal-thin' : '' ) + '">' +
    '<div id="exitBtn"><a href="#" onclick="closeOverlayGlobal()"><i class="bi bi-x-circle"></i></a></div>' +
    content +
    '</div>';
}

function buildOverlay(html, width = false) {
    width = ((width != false) ? width : '30vw') 
    document.getElementById('globalOverlay').innerHTML = 
    `<div class="overlay anim-scale-up" style="width: ` + width + `"><a href="#" onclick="closeOverlayGlobal()" id="exit"><span class="viewportRedBtn">x</span></a>` + html + `</div>`;
}

function buildAutoReportOverlay(html) {
    document.getElementById('globalOverlay').innerHTML = '<div class="overlay overlayAutoReport"><a href="#" onclick="closeOverlayGlobal()" id="exit"><span class="viewportRedBtn">x</span></a>' + html + "</div>";

    return true;
}

function closeOverlayGlobal() {
    document.getElementById('globalOverlay').classList.remove("globalOverlayFill");
    document.getElementById('globalOverlay').innerHTML = '';
}

function toggleFilter(id) {
    var button = document.getElementById(id + 'tf');
    var input = document.getElementById(id);

    if(input.value == 1) {
        button.classList.remove('tfActive');
        input.value = 0;
    }else {
        button.classList.add('tfActive');
        input.value = 1;
    }
}

function subNav(menu) {
    var subnav = document.getElementById('subnav');
    var menuId = document.getElementById('menuId');
    var items = document.getElementById('submenuitems');

    if(menu == -1 || subnav.classList.contains('subnav-extend') && menuId.value == menu) {
        subnav.classList.remove('subnav-extend');
    }else {
        switch(menu) {
            case 0:
                menuId.value = 0;
                items.innerHTML = `
                <a href="/inventory/index.php?action=purchaseOrderMenu" onclick="" class="navLink"><i class="bi bi-cart3"></i><p>Receiving</p></a>
                <a href="/inventory/index.php" onclick="" class="navLink"><i class="bi bi-search"></i><p>Search</p></a>
                <a href="/custom/index.php?action=caveManMenu" onclick="" class="navLink"><i class="bi bi-truck"></i><p>Shipping</p></a>
                <a onclick="releaseHold()" class="navLink"><i class="bi bi-flag"></i><p>Holds</p></a>
                `;
                break
            case 1:
                // <a href="/production/index.php?action=productionSchedule" onclick="" class="navLink"><i class="bi bi-gear-wide-connected"></i><p>Production Schedule</p></a>
                menuId.value = 1;

                // Production subNav
                items.innerHTML = `
                <a onclick="autoReschedule()" class="navLink"><i class="bi bi-gear-wide-connected"></i><p>Production Schedule</p></a>
                <a href="/production/index.php?action=woConfirmationMenu" class="navLink"><i class="bi bi-check"></i><p>Confirmation</p></a>
                <a href="/production/index.php?action=woRejectsMenu" class="navLink"><i class="bi bi-x"></i><p>Rejects</p></a>
                <a href="/production/index.php?action=employeeScheduler" onclick="" class="navLink"><i class="bi bi-person-plus"></i><p>Employee Scheduler</p></a>
                <a onclick="loadModuleModal('completeWO')" class="navLink"><i class="bi bi-clock"></i><p>Complete WO</p></a>
                <a href="/production/index.php?action=sJobClock" onclick="" class="navLink"><i class="bi bi-stopwatch"></i><p>Job Clock</p></a>
                <a href="/production/index.php?action=jobClockSummary" onclick="" class="navLink"><i class="bi bi-speedometer2"></i><p>Dash Board</p></a>








                
                <a href="/production/index.php?action=jobClockSummaryCopy" onclick="" class="navLink"><i class="bi bi-speedometer2"></i><p>Dash-Board-Test</p></a>













                <a href="#" onclick="goToWorkOrder(0)" title="Go To WO"class="navLink"><i class="bi bi-binoculars"></i><p>GoTo Work Order</p></a>
                `;
                break
            case 2:
                menuId.value = 2;
                items.innerHTML = `
                <a href="/manager/index.php?action=searchEmployees" onclick="" class="navLink"><i class="bi bi-people"></i><p>Employee Menu</p></a>
                <a href="/manager/index.php?action=searchUsers" onclick="" class="navLink"><i class="bi bi-person"></i><p>Users Menu</p></a>
                <a href="/manager/index.php?action=timeClockEditor" onclick="" class="navLink"><i class="bi bi-clock"></i><p>Time Clock</p></a>
                <a href="/manager/index.php?action=physicalInventory" onclick="" class="navLink"><i class="bi bi-vector-pen"></i><p>Physical Inventory</p></a>
                <a href="/manager/index.php?action=reports" onclick="" class="navLink"><i class="bi bi-clock"></i><p>Reports</p></a>
                <a href="/manager/index.php?action=binLocations" onclick="" class="navLink"><i class="bi bi-box"></i><p>Bin Locrations</p></a>
                `;
                break
            case 3:
                menuId.value = 3;
                items.innerHTML = `
                <a href="/custom/index.php?action=smallWorldMenu" onclick="" class="navLink"><i class="bi bi-globe"></i><p>Small World</p></a>
                `;
                break
            case 4:
                menuId.value = 4;
                items.innerHTML = `
                
                `;
                break
        }
        subnav.classList.add('subnav-extend');
    }
}

function toggleDropdown() {
    document.getElementById("dropContent").classList.toggle('show');
}