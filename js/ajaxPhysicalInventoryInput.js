/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


function setFocusToTextBox(){
    document.getElementById("scan").focus();
}

function serialScan(){
    
    var xmlhttp=new XMLHttpRequest();
    var audioError = new Audio('/audio/Error.wav');
    var audioBeep = new Audio('/audio/BeepStart.wav');
    var serial;
    var warned = 0;
    serial = document.getElementById("scan").value;
    xmlhttp.onreadystatechange=function() {
        if(xmlhttp.responseText == 'timedOut'){
            window.location.href = "/manager";
        }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {
            // alert(xmlhttp.responseText);
            // document.getElementById('ha_holder').innerHTML = xmlhttp.responseText;

            if(xmlhttp.responseText == -1) {
                // Error
                toast('This Tag is Deactivated!');
            }else if(xmlhttp.responseText == -2) {
                // Warning for Authorized Users
                warned = confirm('This tag is deactivated. Are you sure you want to bin locate it?');
                if(warned) {
                    xmlhttp.open("GET","/physicalInventoryInput/index.php?action=scanTag&serial="+serial+"&warned=1");
                    xmlhttp.send();
                }
            }
            
            if(xmlhttp.responseText != -1 && xmlhttp.responseText != -2) {
                var myArr = JSON.parse(xmlhttp.responseText);       
                if(myArr[0] == 1){
                    document.getElementById("errorMessage").innerHTML = 'SERIAL #: \"' + serial + '\" IS INVALID';
                    document.getElementById("scan").value = "";
                    document.getElementById("scan").focus();
                    audioError.play();
                }else if(myArr[0] == 2){
                    document.getElementById("errorMessage").innerHTML = 'ALREADY SCANNED Serial#:'+serial+' <br> Bin#: '+myArr[1];
                    document.getElementById("scan").value = "";
                    document.getElementById("scan").focus();
                    audioError.play();
                }else if(myArr[0] == 3){
                    document.getElementById("errorMessage").innerHTML = 'INVALID: \"' + serial + '\" IS A BIN LOCATION';
                    document.getElementById("scan").value = "";
                    document.getElementById("scan").focus();
                    audioError.play();
                }else if(myArr[0] == 4){
                    document.getElementById("errorMessage").innerHTML = 'INVALID CAT CODE:'+serial+' <br> CAT CODE: '+myArr[1];
                    document.getElementById("scan").value = "";
                    document.getElementById("scan").focus();
                    audioError.play();
                }else{
                    document.getElementById("scanBox").innerHTML = myArr[1];
                    document.getElementById("scanType").innerHTML = "Scan Bin";
                    document.getElementById("errorMessage").innerHTML = "";
                    document.getElementById("scan").focus();
                    audioBeep.play();
                }
            }
        }
           
            //busyIndicator(false);
    };
    
    xmlhttp.open("GET","/physicalInventoryInput/index.php?action=scanTag&serial="+serial+"&warned="+warned);
    xmlhttp.send();
    
    //busyIndicator(true);

}

function binScan(serial){
    var xmlhttp=new XMLHttpRequest();
    var audioError = new Audio('/audio/Error.wav');
    var audioBeep = new Audio('/audio/BeepEnd.wav');
    var bin;
    var scanBox = '<input type="text" name="scan" id="scan" class="scanTag" onfocusout="setFocusToTextBox()" onkeydown="Javascript: if (event.keyCode==13) serialScan();">';
    bin = document.getElementById("scan").value;
    
    xmlhttp.onreadystatechange=function() {
        if(xmlhttp.responseText == 'timedOut'){
            window.location.href = "/manager";
        }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {
            var myArr = JSON.parse(xmlhttp.responseText);
            if(myArr[0] == 1){
                document.getElementById("errorMessage").innerHTML = 'BIN LOC #: \"' + myArr[1] + '\" IS INVALID. SCAN A VALID BIN.';
                document.getElementById("scan").value = "";
                document.getElementById("scan").focus();
                audioError.play();
            }else if(myArr[0] == 2){
                document.getElementById("errorMessage").innerHTML = 'TRANSACTION DID NOT COMPLETE';
                document.getElementById("scanBox").innerHTML = scanBox;
                document.getElementById("scanType").innerHTML = "Scan Tag";
                document.getElementById("scan").value = "";
                document.getElementById("scan").focus();
                audioError.play();
            }else{
                document.getElementById("scanBox").innerHTML = scanBox;
                document.getElementById("scanType").innerHTML = "Scan Tag";
                document.getElementById("errorMessage").innerHTML = "";
                document.getElementById("scan").focus();
                audioBeep.play();
            }
            
            //busyIndicator(false);
        }
    };
    
    xmlhttp.open("GET","/physicalInventoryInput/index.php?action=scanBin&bin="+bin+"&serial="+serial);
    xmlhttp.send();
    
    //busyIndicator(true);
}



function recountView(action){
    //Goto Recount View
    window.location.assign("/physicalInventory/index.php?action="+action);
}