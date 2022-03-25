/* global selection */
var selection = -1;
var list = 10;
//Build Drop Down for Employee Search
function buildDropDown(searchTerm, event)
{   

    if(event.keyCode == 40){
        if(selection < (list - 1)){
            selection++;
        }
    }else if(event.keyCode == 38){
        if(selection > -1){
            selection--;
        }
    }else{
        selection = -1;
        clearDropDown();
    }
    
    
        var xmlhttp=new XMLHttpRequest();
            xmlhttp.onreadystatechange=function() {
                if(xmlhttp.responseText == 'timedOut'){
                    window.location.href = "/manager";
                }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {
                    var myArr = JSON.parse(xmlhttp.responseText);
                    list = myArr[0];
                    document.getElementById("dropDown").innerHTML=myArr[1];
                    if(selection != -1){
                        document.getElementById("employeeID").value = myArr[2];
                    }
                    
                }
            };
            xmlhttp.open("GET","/manager/index.php?action=buildDropDown&searchTerm="+searchTerm+"&selection="+selection);
            xmlhttp.send();
}

function clearDropDown(){
        document.getElementById("dropDown").innerHTML='<div></div>';
}

function expandHours(employeeID, payPeriodDay, payPeriodDate, insert){
    
     var xmlhttp=new XMLHttpRequest();
     var jsDate = new Date(payPeriodDate);
     var convDate = jsDate.toISOString();
    // var convDate = payPeriodDate;
     var minusIcon = '<a href="#" onclick="contractHours(' + employeeID + ', ' + payPeriodDay + ', ' + payPeriodDate + ');"><span>0</span></a>';
     var iconLoc = "expandIcon" + payPeriodDay;
     var timeLoc = "expandTime" + payPeriodDay;
            xmlhttp.onreadystatechange=function() {
                if(xmlhttp.responseText == 'timedOut'){
                    window.location.href = "/manager";
                }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {
                    document.getElementById(iconLoc).innerHTML= minusIcon;
                    document.getElementById(timeLoc).innerHTML = xmlhttp.responseText;
                    
                }
            };
            if(insert){
                xmlhttp.open("GET","/manager/index.php?action=insertTimeView&employeeID="+employeeID+"&payPeriodDay="+payPeriodDay+"&payPeriodDate="+convDate);
            }else{
                xmlhttp.open("GET","/manager/index.php?action=expandTime&employeeID="+employeeID+"&payPeriodDay="+payPeriodDay+"&payPeriodDate="+convDate);
            }
            
            xmlhttp.send();
}

function contractHours(employeeID, payPeriodDay, payPeriodDate){
    
    var plusIcon = '<a href="#" onclick="expandHours(' + employeeID + ', ' + payPeriodDay + ', ' + payPeriodDate + ');"><span>1</span></a>';
    var iconLoc = "expandIcon" + payPeriodDay;
    var timeLoc = "expandTime" + payPeriodDay;
    
    document.getElementById(iconLoc).innerHTML= plusIcon;
    document.getElementById(timeLoc).innerHTML = '<div id=expandTime></div>';
    
}

function insertPunch(employeeID, payPeriodDate, payPeriodDay, convDate){
    //Create and convert punch date for php
    var jsDate = new Date(convDate);
    var convDate2 = jsDate.toISOString();
    
    var inPunchConv = null;
    var outPunchConv = null;
    
    inPunchConv = document.getElementById("inPunch").value;
    
    if(inPunchConv){
        inPunchConv = payPeriodDate + " " + document.getElementById("inPunch").value + ":00";  
    }else{
        inPunchConv = null;
    }
    
    outPunchConv = document.getElementById("outPunch").value;
    
    if(outPunchConv){
        outPunchConv = payPeriodDate + " " + document.getElementById("outPunch").value + ":00";
    }else{
        outPunchConv = null;
    }
    

    if(inPunch === null){
        document.getElementById("errorMessage").innerHTML = "In punch Required";
    }else{
    
        //Expand the hours of selected date
        var xmlhttp=new XMLHttpRequest();
                xmlhttp.onreadystatechange=function() {
                    if(xmlhttp.responseText == 'timedOut'){
                        window.location.href = "/manager";
                    }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {
                        if(xmlhttp.responseText == 0){
                            document.getElementById("message").innerHTML = "Server Error";
                        }else if(xmlhttp.responseText == 2){
                            document.getElementById("message").innerHTML = "Error: Invalid Punch";
                        }else{
                            refreshEditor(employeeID, convDate2, payPeriodDay, convDate);
                        }
                    }
                };

            xmlhttp.open("GET","/manager/index.php?action=insertPunch&punchIn="+inPunchConv+"&punchOut="+outPunchConv+"&selectedDate="+payPeriodDate+"&employeeID="+employeeID);
            xmlhttp.send();
    }
    
}

function formatDate(date){
        year = date.getFullYear();
        month = date.getMonth();
        months = new Array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');
        d = date.getDate();
        
        return months[month] + ' ' + d + ', ' + year;
}

function refreshEditor(employeeID, convDate, payPeriodDay, payPeriodDate){
    var xmlhttp=new XMLHttpRequest();
        xmlhttp.onreadystatechange=function() {
            if(xmlhttp.responseText == 'timedOut'){
                window.location.href = "/manager";
            }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {
                    var myArr = JSON.parse(xmlhttp.responseText);
                    document.getElementById("punchEditorHeader").innerHTML = myArr[0];
                    document.getElementById("punchHours").innerHTML = myArr[1];
                    expandHours(employeeID, payPeriodDay, payPeriodDate, 0);
                }
            };
            
        xmlhttp.open("GET","/manager/index.php?action=refreshEditor&employeeID="+employeeID+"&selectedDate="+convDate);
        xmlhttp.send();
}

function deletePunch(punchID, employeeID, payPeriodDate, payPeriodDay){
    //Create and convert punch date for php
    var jsDate = new Date(payPeriodDate);
    var convDate = jsDate.toISOString();
    var xmlhttp=new XMLHttpRequest();
        xmlhttp.onreadystatechange=function() {
            if(xmlhttp.responseText == 'timedOut'){
                window.location.href = "/manager";
            }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {
                if(!xmlhttp.responseText){
                    document.getElementById("errorMessage").innerHTML = "server error";
                }else{
                    refreshEditor(employeeID, convDate, payPeriodDay, payPeriodDate);
                }
            }
        };

    xmlhttp.open("GET","/manager/index.php?action=deletePunch&punchID="+punchID);
    xmlhttp.send();
}

function viewInsertOutPunch(addPunch, punchID, employeeID, convDate, payPeriodDay ){

    document.getElementById(addPunch).innerHTML = '<input type="time" name="insertOutPunch" id="insertOutPunch" placeholder="OUT">'
            + '<a href="#" id="checkMark" onclick="insertOutPunch(' + punchID + ', ' + employeeID + ', ' + convDate + ', ' + payPeriodDay + ');"><span>v</span></a>'
            + '<a href="#" id="cancel" onclick="expandHours(' + employeeID + ', ' +payPeriodDay + ', ' + convDate + ', 0)"><span>O</span></a>';
}

function editPunchView(addPunchOut, addPunchIn, punchID, employeeID, payPeriodDate, payPeriodDay, curPunchIn, curPunchOut, payPeriodDate2){
    //reset editor
    //refreshEditor(employeeID, convDate, payPeriodDay, convDate);
    //Create IDs
    var inPunchID = 'insertIn' + punchID;
    var outPunchID = 'insertOut' + punchID;
    
    document.getElementById(addPunchIn).innerHTML = '<input type="time" id="' + inPunchID +'" name="insertInPunch" value=' + curPunchIn + '>';
    if(!curPunchOut){
        curPunchOut = null;
    }
    
    document.getElementById(addPunchOut).innerHTML = '<input type="time" id="' + outPunchID +'" name="insertOutPunch" value=' + curPunchOut + '>'
            + '<a href="#" id="checkMark" onclick="editPunches(' + punchID + ', ' + employeeID + ', \'' + payPeriodDate2 + '\', ' + payPeriodDay + ', \'' + inPunchID + '\', \'' + outPunchID + '\', ' + payPeriodDate + ');"><span>v</span></a>'
            + '<a href="#" id="cancel" onclick="expandHours(' + employeeID + ', ' + payPeriodDay + ', ' + payPeriodDate + ', 0)"><span>O</span></a>';

}

function insertOutPunch(punchID, employeeID, payPeriodDate, payPeriodDay){

    //Create and convert punch date for php
    var convDate = new Date(payPeriodDate);
    
    outPunchConv = document.getElementById("insertOutPunch").value;
    
    if(outPunchConv){
        outPunchConv = convDate.getFullYear()+ "-" + (convDate.getMonth()+ 1) + "-" + convDate.getDate() + " " + document.getElementById("insertOutPunch").value + ":00";
    }else{
        outPunchConv = null;
    }


    //Use AJAX to insert out punch and then refresh
    var xmlhttp=new XMLHttpRequest();
                xmlhttp.onreadystatechange=function() {
                    if(xmlhttp.responseText == 'timedOut'){
                        window.location.href = "/manager";
                    }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {
                        if(!xmlhttp.responseText){
                            document.getElementById("errorMessage").innerHTML = "server error";
                        }else{
                                refreshEditor(employeeID, convDate, payPeriodDay, payPeriodDate);
                        }
                    }
                };

    xmlhttp.open("GET","/manager/index.php?action=insertOutPunch&punchID="+punchID+"&punchOut="+outPunchConv);
    xmlhttp.send();
            
}

function editPunches(punchID, employeeID, payPeriodDate, payPeriodDay, inPunchID, outPunchID, payPeriodDate2){

   //Create and convert punch date for php
    var jsDate = new Date(payPeriodDate2);
    var convDate = jsDate.toISOString();
    var inPunchConv = null;
    var outPunchConv = null;
    
    inPunchConv = document.getElementById(inPunchID).value;
    
    if(inPunchConv){
        inPunchConv = payPeriodDate + " " + document.getElementById(inPunchID).value + ":00";  
    }else{
        inPunchConv = null;
    }
    
    outPunchConv = document.getElementById(outPunchID).value;
    
    if(outPunchConv){
        outPunchConv = payPeriodDate + " " + document.getElementById(outPunchID).value + ":00";
    }else{
        outPunchConv = null;
    }
    
    
    //Use AJAX to insert out punch and then refresh
    var xmlhttp=new XMLHttpRequest();
                xmlhttp.onreadystatechange=function() {
                    if(xmlhttp.responseText == 'timedOut'){
                        window.location.href = "/manager";
                    }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {
                        
                        var myArr = JSON.parse(xmlhttp.responseText);
                        if(myArr[0] == 0){
                            document.getElementById("message").innerHTML = "Server Error";
                        }else if(myArr[0] == 2){
                            document.getElementById("message").innerHTML = "Error: Invalid Punch";
                        }else{
                            refreshEditor(employeeID, convDate, payPeriodDay, payPeriodDate2);
                        }
                    }
                };
                
            xmlhttp.open("GET","/manager/index.php?action=editPunch&punchID="+punchID+"&punchOut="+outPunchConv+"&punchIn="+inPunchConv);
            xmlhttp.send();
            
}


