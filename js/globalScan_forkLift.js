/* 
 * Route global scan to menu.
 */

function scanRouter(scanInput){
    var scanType;
    var scanData;
    var inputString;
    var strLen;

    inputString = scanInput.toString();
    strLen = inputString.length;
    scanData = inputString.substring(1,strLen);
    scanType = inputString[0];
    serialID = document.getElementById("serialID");
    skuNumber = document.getElementById("skuNumber");
    document.getElementById("scan").value = scanInput
    
    if (scanInput >= 0 || scanType == 'S'){
        if(serialID != null){
            serialID = document.getElementById("serialID").innerHTML;
            validateBin(serialID);
        }else if(skuNumber != null){
            scan(1);
        }else{
            scan();
        }
        
        
        
    }else if(scanType == 'W'){
        /******** WORK ORDER # is scanned ********/
        window.location.href = "/jobClock/index.php?action=workOrder&workOrderNumber=" + scanData;
        
    }else if(scanType == 'E'){
        /******** EMPLOYEE # is scanned **********/
        var workOrderNumber = document.getElementById('workOrderNumber').value;
        var xmlhttp=new XMLHttpRequest();
        
        xmlhttp.onreadystatechange=function() {
            if(xmlhttp.responseText == 'timedOut'){
                window.location.href = "/jobClock";
            }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {
                loadView(xmlhttp.responseText);
                
                setTimeout(clearOverlay, 2000);
            }
        };
        
        xmlhttp.open("GET","/jobClock/index.php?action=clockInEmployee&keyCard="+scanData+"&workOrderNumber="+workOrderNumber);
        xmlhttp.send();
    }else if(scanType == 'G'){
        /******** EMPLOYEE # is scanned **********/
        var workOrderNumber = document.getElementById('workOrderNumber').value;
        var xmlhttp=new XMLHttpRequest();
        
        xmlhttp.onreadystatechange=function() {
            if(xmlhttp.responseText == 'timedOut'){
                window.location.href = "/jobClock";
            }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {
                loadView(xmlhttp.responseText);
                
                setTimeout(clearOverlay, 4000);
            }
        };
        
        xmlhttp.open("GET","/jobClock/index.php?action=clockInGroup&groupID="+scanData+"&workOrderNumber="+workOrderNumber);
        xmlhttp.send();
    }
}
    
function clearOverlay()
{
    document.getElementById("overlay").innerHTML='';
}

function loadView(viewJSON){
    var myArr = JSON.parse(viewJSON);
    if(myArr[0]){
        document.getElementById("overlay").innerHTML = myArr[0];
    }
    document.getElementById("workOrderHeader").innerHTML = myArr[1];
    document.getElementById("activeLaborList").innerHTML = myArr[2];
    document.getElementById("laborHistoryList").innerHTML = myArr[3];
    document.getElementById("productionQtyList").innerHTML = myArr[4];
    
    //Load headers
    document.getElementById("productionQtyHeader").innerHTML = myArr[5];
    
}

function refreshWorkOrder(){
    var xmlhttp=new XMLHttpRequest();
    var workOrderNumber = document.getElementById('workOrderNumber').value;
    
    xmlhttp.onreadystatechange=function() {
        if(xmlhttp.responseText == 'timedOut'){
            window.location.href = "/advancedJobClock";
        }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {
                loadView(xmlhttp.responseText);
                setTimeout('refreshWorkOrder();','3000');
        }
    };
    xmlhttp.open("GET","/jobClock/index.php?action=refresh&workOrderNumber="+workOrderNumber);
    xmlhttp.send();
}
