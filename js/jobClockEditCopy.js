/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function closeOverlay(){
    if(document.getElementById("overlay")){
        document.getElementById("overlay").innerHTML = "";
    }
}

function editEmployee(employeeID, callback){
    var xmlhttp=new XMLHttpRequest();
    var selectedDate = 0;
    if(document.getElementById("jceDate")){
        selectedDate = document.getElementById("jceDate").value;
    }
    
    xmlhttp.onreadystatechange=function() {
        if(xmlhttp.responseText == 'timedOut'){
            window.location.href = "/manager";
        }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {
            //alert(xmlhttp.responseText);
            if(checkTimeOut(xmlhttp.responseText)){

                window.location.href = "/logIn/index.php";
                return;
            }
            var myArr = JSON.parse(xmlhttp.responseText);
            
            document.getElementById("overlay").innerHTML = myArr[0];
            
            callback();
        }
    };

    xmlhttp.open("GET","/jobClock/index.php?action=editEmployeeTime&employeeID="+employeeID+"&selectedDate="+selectedDate);
    xmlhttp.send();
}

function editJobClockPunchView(ajcPunchId, type, tcpPunchId, ajcLastPunchId){
    var id;
    var timeId;
    var editHTML;
    var employeeID = document.getElementById("employeeID").value;
    
    if(type == 1 || type == 0){
        id = "jcePunch"+ajcPunchId;
        timeId = "jceTime"+ajcPunchId;
    }else if(type == 2){
        id = "lastPunch"+ajcPunchId;
        timeId = "lastTime"+ajcPunchId;
    }
    
    //Refresh View then display edit
    editEmployee(employeeID, function() {
        editHTML = document.getElementById(timeId).outerHTML;
        
        editHTML += '<a href="" onclick="editJobClockPunch('+ ajcPunchId + ', ' + type + ', ' + tcpPunchId + ', ' + ajcLastPunchId + '); return false;"><span> v</span></a>' + 
                '<a href="" onclick="editEmployee('+employeeID+', null); return false;"><span> O</span></a>';
        document.getElementById(id).innerHTML = editHTML;
        document.getElementById(timeId).removeAttribute("hidden");
    })
    
}

function editJobClockPunch(ajcPunchId, type, tcpPunchId, ajcLastPunchId){
    var xmlhttp=new XMLHttpRequest();
    var punchValue
    if(type == 1 || type == 0){
        punchValue = document.getElementById("jceTime"+ajcPunchId).value;
    }else if(type == 2){
        punchValue = document.getElementById("lastTime"+ajcPunchId).value;
    }
    
    var jceDate = document.getElementById("jceDate").value
    
    punchValue = jceDate + ' ' + punchValue
    
    //alert(punchValue);
    
    var employeeId = document.getElementById("employeeID").value;
    
    xmlhttp.onreadystatechange=function() {
        if(xmlhttp.responseText == 'timedOut'){
            window.location.href = "/manager";
        }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {
            
            if(checkTimeOut(xmlhttp.responseText)){

                window.location.href = "/logIn/index.php";
                return;
            }

            var myArr = JSON.parse(xmlhttp.responseText);
            
            if(myArr[0] == 0){
                document.getElementById("errorMessage").innerHTML = myArr[1];
            }else if(myArr[0] == -1){
                document.getElementById("errorMessage").innerHTML = myArr[1];
            }else if(myArr[0] == 1){
                //refresh
                editEmployee(employeeId, null);
            }
            
        }
    };

    xmlhttp.open("GET","/jobClock/index.php?action=editJobClockPunch&ajcPunchId="+ajcPunchId
            +"&punchValue="+punchValue
            +"&type="+type
            +"&tcpPunchId="+tcpPunchId+
            "&ajcLastPunchId="+ajcLastPunchId);
    xmlhttp.send();
}

function deleteJobClockPunchView(ajcPunchId, type, tcpPunchId, linkedPunchId){
    var employeeID = document.getElementById("employeeID").value;
    //Refresh View then display edit
    editEmployee(employeeID, function() {
        var workOrderNumber = document.getElementById("workOrderNumber"+ajcPunchId).value;
        var deleteButtonHTML = workOrderNumber + ' <button class="editEmployeeButton" ' +
                'onclick="deleteJobClockPunch(' + ajcPunchId + ', ' + type + ', ' + tcpPunchId + ', ' + linkedPunchId + ');"><span>8</span> Delete</button>' +
                        '<button class="editEmployeeButton cancelButton" onclick="cancelJobClockPunch(' + ajcPunchId + ');"><span>O</span> Cancel</button>';

        document.getElementById("jceWO" + ajcPunchId).innerHTML = deleteButtonHTML;
    })
}

function deleteJobClockPunch(ajcPunchId, type, tcpPunchId, linkedPunchId){
    var xmlhttp=new XMLHttpRequest();
    var employeeId = document.getElementById("employeeID").value;
    xmlhttp.onreadystatechange=function() {
        if(xmlhttp.responseText == 'timedOut'){
            window.location.href = "/manager";
        }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {

            if(checkTimeOut(xmlhttp.responseText)){

                window.location.href = "/logIn/index.php";
                return;
            }

            var myArr = JSON.parse(xmlhttp.responseText);
            
            if(myArr[0] == 0){
                document.getElementById("errorMessage").innerHTML = myArr[1];
            }else if(myArr[0] == 1){
                
                //refresh
                editEmployee(employeeId, null);
            }
            
        }
    };

    xmlhttp.open("GET","/jobClock/index.php?action=deleteJobClockPunch&ajcPunchId="+ajcPunchId
            +"&type="+type
            +"&tcpPunchId="+tcpPunchId+
            "&linkedPunchId="+linkedPunchId);
    xmlhttp.send();
}

function cancelJobClockPunch(adjPunchId){
    var employeeID = document.getElementById("employeeID").value;
    editEmployee(employeeID, null);
}


//*****Clock Out Job Clock and Time Clock*****//
function clockOut(punchId, timeClockID){
    var xmlhttp=new XMLHttpRequest();
    var employeeId = document.getElementById("employeeID").value;
    
    xmlhttp.onreadystatechange=function() {
        if(xmlhttp.responseText == 'timedOut'){
            window.location.href = "/manager";
        }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {
            //alert(xmlhttp.responseText);
            if(checkTimeOut(xmlhttp.responseText)){

                window.location.href = "/logIn/index.php";
                return;
            }
            
            var myArr = JSON.parse(xmlhttp.responseText);
            
            if(myArr[0] == 0){
                document.getElementById("errorMessage").innerHTML = myArr[1];
            }else if(myArr[0] == -1){
                document.getElementById("errorMessage").innerHTML = myArr[1];
            }else if(myArr[0] == 1){
                //refresh
                editEmployee(employeeId, null);
            }
            
        }
    };

    xmlhttp.open("GET","/jobClock/index.php?action=clockOut&punchID="+punchId+"&employeeID="+employeeId+"&timeClockID="+timeClockID);
    xmlhttp.send();
}

function viewEditLastPunch(ajcPunchId, type){
    var editHTML;
    var employeeID = document.getElementById("employeeID").value;
    var type = 2;
    
    //Refresh View then display edit
    editEmployee(employeeID, function() {
        editHTML = document.getElementById("lastTime").outerHTML;
        
        editHTML += '<a href="" onclick="editLastJobClockPunch('+ ajcPunchId + ', ' + type + '); return false;"><span> v</span></a>' + 
                '<a href="" onclick="editEmployee('+employeeID+', null); return false;"><span> O</span></a>';
        document.getElementById("lastPunch").innerHTML = editHTML;
        document.getElementById("lastTime").removeAttribute("hidden");
    })
}

function editLastJobClockPunch(ajcPunchId, type){
    var xmlhttp=new XMLHttpRequest();
    var punchValue = document.getElementById("lastTime").value;
    var employeeId = document.getElementById("employeeID").value;
    var ajcLastPunchId = null;
    
    xmlhttp.onreadystatechange=function() {
        if(xmlhttp.responseText == 'timedOut'){
            window.location.href = "/manager";
        }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {

            if(checkTimeOut(xmlhttp.responseText)){

                window.location.href = "/logIn/index.php";
                return;
            }

            var myArr = JSON.parse(xmlhttp.responseText);
            
            if(myArr[0] == 0){
                document.getElementById("errorMessage").innerHTML = myArr[1];
            }else if(myArr[0] == -1){
                document.getElementById("errorMessage").innerHTML = myArr[1];
            }else if(myArr[0] == 1){
                //refresh
                editEmployee(employeeId, null);
            }
            
        }
    };

    xmlhttp.open("GET","/jobClock/index.php?action=editJobClockPunch&ajcPunchId="+ajcPunchId
            +"&punchValue="+punchValue
            +"&type="+type
            +"&tcpPunchId="+tcpPunchId+
            "&ajcLastPunchId="+ajcLastPunchId);
    xmlhttp.send();
}

function viewDeleteLastPunch(ajcPunchId){
        var employeeID = document.getElementById("employeeID").value;
    //Refresh View then display edit
    editEmployee(employeeID, function() {
        var workOrderNumber = document.getElementById("workOrderNumber").value;
        var deleteButtonHTML = workOrderNumber + ' <button class="editEmployeeButton" onclick="removeJobClockOutPunch(' + ajcPunchId + ');"><span>8</span> Delete</button>' +
                        '<button class="editEmployeeButton cancelButton" onclick="cancelJobClockPunch(' + ajcPunchId + ');"><span>O</span> Cancel</button>';

        document.getElementById("lastPunch").innerHTML = deleteButtonHTML;
    })
    
    
}

function removeJobClockOutPunch(ajcPunchId){
    alert("removeOutpunch")
}

function viewNewJob(employeeId, lastPunchID){
    var editHTML;
    
    var timeStamp = getTime();
    
    //Refresh View then display edit
    editEmployee(employeeId, function(){
        editHTML = 'WO#: <input type="text" id="jceWONumber"><br>Time: <input type="time" id="insertTime" value="'+timeStamp+'">' + 
                '<a href="" onclick="insertJobClockPunch('+ employeeId + ', ' + lastPunchID + '); return false;"><span> v</span></a>' + 
                '<a href="" onclick="editEmployee(' + employeeId + ', null); return false;"><span> O</span></a>';
        document.getElementById("viewNewJob").innerHTML = editHTML;
    });
}

function insertJobClockPunch(employeeID, lastPunchID = null){
    var workOrderNumber = document.getElementById("jceWONumber").value;
    var insertTime = document.getElementById("insertTime").value;
    
    var xmlhttp=new XMLHttpRequest();
    xmlhttp.onreadystatechange=function() {
        if(xmlhttp.responseText == 'timedOut'){
            window.location.href = "/manager";
        }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {
            if(checkTimeOut(xmlhttp.responseText)){
                window.location.href = "/logIn/index.php";
                return;
            }
            var myArr = JSON.parse(xmlhttp.responseText);
            
            if(!myArr[0]){
                document.getElementById("errorMessage").innerHTML = myArr[1];
            }else{
                editEmployee(employeeID, null);
            }
            
        }
    };
    
    xmlhttp.open("GET","/jobClock/index.php?action=insertJobPunch&employeeID="+employeeID+"&workOrderNumber="+workOrderNumber+"&insertTime="+insertTime+"&lastPunchID="+lastPunchID);
    xmlhttp.send();
}

function changeDate(){
    var employeeID = document.getElementById("employeeID").value;
    editEmployee(employeeID, null);
}

function getTime() {
       var now = new Date();
       return (((now.getHours() < 10) ? ("0" + now.getHours()) : (now.getHours())) + ':' 
               + ((now.getMinutes() < 10) ? ("0" + now.getMinutes()) : (now.getMinutes())));
}



function viewEditDepartment(ajcPunchId, departmentID){
    var employeeID = document.getElementById("employeeID").value;
    
    var xmlhttp=new XMLHttpRequest();
    xmlhttp.onreadystatechange=function() {
        if(xmlhttp.responseText == 'timedOut'){
            window.location.href = "/manager";
        }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {
            if(checkTimeOut(xmlhttp.responseText)){
                window.location.href = "/logIn/index.php";
                return;
            }
            var myArr = JSON.parse(xmlhttp.responseText);
            
                editEmployee(employeeID, function() {
                    var workOrderNumber = document.getElementById("workOrderNumber"+ajcPunchId).value;
                    var editDepartments = workOrderNumber + " - " + myArr[0];
                    var cancelHTML = '<a href="" onclick="editDepartment(' + ajcPunchId + '); return false;"><span> v</span></a>'+
                                        '<a href="" onclick="cancelJobClockPunch(' + ajcPunchId + '); return false;"><span> O</span></a>';

                    document.getElementById("jceWO" + ajcPunchId).innerHTML = editDepartments + cancelHTML;
                })
        }
    };
    
    xmlhttp.open("GET","/jobClock/indexCopy.php?action=editDepartmentList&departmentID="+departmentID);
    xmlhttp.send();
}


function editDepartment(ajcPunchId){
    var employeeID = document.getElementById("employeeID").value;
    var departmentID = document.getElementById("departmentList").value;
    
    var xmlhttp=new XMLHttpRequest();
    xmlhttp.onreadystatechange=function() {
        if(xmlhttp.responseText == 'timedOut'){
            window.location.href = "/manager";
        }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {
            if(checkTimeOut(xmlhttp.responseText)){
                window.location.href = "/logIn/index.php";
                return;
            }
            //alert(xmlhttp.responseText);
            var myArr = JSON.parse(xmlhttp.responseText);
            
            if(myArr[0]){
                document.getElementById("errorMessage").innerHTML = myArr[0];
            }else{
                editEmployee(employeeID, null);
            }

        }
    };
    
    xmlhttp.open("GET","/jobClock/indexCopy.php?action=updateJobDepartment&departmentID="+departmentID+"&ajcPunchId="+ajcPunchId);
    xmlhttp.send();
    
    
}