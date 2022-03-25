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
    

    if (scanType >= 0){
        /******** SERIAL # is scanned ********/
        var xmlhttp=new XMLHttpRequest();
    
        xmlhttp.onreadystatechange=function() {
            if(xmlhttp.responseText == 'timedOut'){
                window.location.href = "/forklift";
            }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {

                if(xmlhttp.responseText == 1){
                    window.location.href = "/forklift/index.php?action=scan&serialID=" + scanInput;
                }else{
                    audio.play();
                }
            }
        };

        xmlhttp.open("GET","/forklift/index.php?action=scanValidation&scan=" + scanInput);
        xmlhttp.send();
        
    }else if(scanType == 'W'){
        /******** WORK ORDER # is scanned ********/
        window.location.href = "/jobClock/index.php?action=workOrder&workOrderNumber=" + scanData;
    }else if(scanType == 'E'){
        /******** EMPLOYEE # is scanned **********/
        window.location.href = "/jobClock/index.php?action=employee&employeeID=" + scanData;
    }else if(scanType == 'P'){
        alert("Future Feature: Goto Mass Tag Details");
    }else if(scanType == 'S'){
        /******** SKU # is scanned ***************/
        var xmlhttp=new XMLHttpRequest();
    
        xmlhttp.onreadystatechange=function() {
            if(xmlhttp.responseText == 'timedOut'){
                window.location.href = "/forklift";
            }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {

                if(xmlhttp.responseText == 1){
                    window.location.href = "/forklift/index.php?action=sku&serialID=" + scanData;
                }else{
                    audio.play();
                }
            }
        };

        xmlhttp.open("GET","/forklift/index.php?action=scanValidation&scan=" + scanInput);
        xmlhttp.send();
    }
}
    
    
