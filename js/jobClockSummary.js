/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */



function allowDrop(ev) {
    ev.preventDefault();
}

function drag(ev) {
    ev.dataTransfer.setData("text", ev.target.id);
}

function drop(ev) {

    var transferInput;
    var originId;
    ev.preventDefault();
    
    deptPunch = document.getElementById("deptPunch").value;
    transferInput = ev.dataTransfer.getData("text");
    transferId = extractId(transferInput);  
    transferCode = extractCode(transferInput);
    
    targetInput = ev.target.parentElement.id;
    targetId = extractId(targetInput);
    targetCode = extractCode(targetInput);
    
    originInput = document.getElementById(transferInput).parentElement.parentElement.id;
    originId = extractId(originInput);
    originCode = extractCode(originInput);


    if(originInput == "productionSchedule"){
        originId = originInput;
    }else if(targetInput == "productionSchedule"){
        targetId = targetInput;
    }
    
    if(ev.target.className == "workOrderSection" && (transferCode == "s" || transferCode == "w")){
        workOrderSwap(transferId, ev);
    }else if(ev.target.parentElement.id == "productionSchedule" && transferCode == "u" &&  ev.target.className == "activeWO"){
        return;
    }else if(targetId == "productionSchedule" && transferCode == "e"){
        viewDepts(transferId, targetId, originId, true);
    }else if((transferCode == "e" || transferCode == "u") && deptPunch == 1 && (targetCode == "w" || targetCode == "u")){
        viewDepts(transferId, targetId, originId, true);
    }else if(ev.target.className == "activeWO" && transferCode == "e"){
        employeeAdd(transferId, targetId, originId, false);
    }else if(ev.target.className == "activeWO" && transferCode == "u" && deptPunch == 0){
        employeeAdd(transferId, targetId, originId, false);
    }
    
}

function punchWithDepartment(transferId, targetId, originId, departmentId){
    transferId = document.getElementById("transferId").value;
    targetId = document.getElementById("targetId").value;
    originId = document.getElementById("originId").value;
    
    employeeAdd(transferId, targetId, originId, departmentId, false);
}

function extractId(input){
    var id;
    id = input.toString();
    
    strLen = id.length;
    id = id.substring(1, strLen);
    return id
}

function extractCode(input){
    var id;
    id = input.toString();
    
    code = id[0];
    return code;
}

function workOrderSwap(workOrderNumber, ev){
    
    var xmlhttp=new XMLHttpRequest();
    
    xmlhttp.onreadystatechange=function() {
        if(xmlhttp.responseText == 'timedOut'){
            window.location.href = "/manager";
        }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {
            //Check if timed out
            if(checkTimeOut(xmlhttp.responseText)){

                window.location.href = "/logIn/index.php";
                return;
            }
            var myArr = JSON.parse(xmlhttp.responseText);
            
            woID = "w" + workOrderNumber;
            
            if(document.getElementById(woID)){
                document.getElementById(woID).innerHTML = "";
                document.getElementById(woID).id = "";
            }
            
            if(ev.target.className = "workOrderSection"){
                ev.target.innerHTML = myArr[0];
                ev.target.id = woID;
                ev.target.setAttribute("draggable" , true);
                ev.target.setAttribute("ondragstart", "drag(event)");
            }

        }
    };

    xmlhttp.open("GET","/jobClock/index.php?action=workOrderSwap&workOrderNumber="+workOrderNumber);
    xmlhttp.send();
    
}

function employeeAdd(employeeID, workOrderNumber, originWO, departmentId = false){

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

            if(!myArr[3]){
                if(document.getElementById("w" + workOrderNumber)){
                    if(document.getElementById("w" + workOrderNumber).className = "workOrderSection"){
                        document.getElementById("w" + workOrderNumber).innerHTML = myArr[0];
                        document.getElementById("w" + workOrderNumber).setAttribute("draggable" , true);
                        document.getElementById("w" + workOrderNumber).setAttribute("ondragstart", "drag(event)");


                        if(originWO == "productionSchedule" || !originWO){
                            document.getElementById("productionSchedule").innerHTML = myArr[2];
                        }else{
                            document.getElementById("w" + originWO).innerHTML = myArr[1];
                        }
                    }

                }else{
                    document.getElementById("productionSchedule").innerHTML = myArr[2];
                    document.getElementById("w" + originWO).innerHTML = myArr[1];
                }
            }else{
                if(workOrderNumber == "productionSchedule"){
                    document.getElementById("deptAssign").innerHTML = "";
                }else{
                    document.getElementById("deptAssign"+workOrderNumber).innerHTML = "";
                }
                alert("Error: " + myArr[3]);
            }
        }
    };

    xmlhttp.open("GET","/jobClock/index.php?action=summaryAddEmployee&workOrderNumber="+workOrderNumber+"&employeeID="+employeeID+"&originWO="+originWO+"&departmentID="+departmentId);
    xmlhttp.send();
    
}


function flip(id) {
    
    if(document.getElementById(id).className == "workOrderSection"){
        document.getElementById(id).className += " flipped";
    }else{
        document.getElementById(id).className = "workOrderSection";
    }
    
}


function closeOverlay(){
    if(document.getElementById("overlay")){
        document.getElementById("overlay").innerHTML = "";
    }
}

function closeDeptOverlay(){
    if(document.getElementsByClassName("deptAssign")){
        document.getElementsByClassName("deptAssign").setAttribute("hidden", "");
    }
}

function addToDept(departmentID){
    var xmlhttp=new XMLHttpRequest();
    
    var employeeID = document.getElementById("employeeID").value;
    var workOrderNumber = document.getElementById("workOrderOrigin").value;
    
    xmlhttp.onreadystatechange=function() {
        if(xmlhttp.responseText == 'timedOut'){
            window.location.href = "/manager";
        }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {
            if(checkTimeOut(xmlhttp.responseText)){

                window.location.href = "/logIn/index.php";
                return;
            }
            var myArr = JSON.parse(xmlhttp.responseText);
            document.getElementById("w" + workOrderNumber).innerHTML = myArr[0];
            document.getElementById("productionSchedule").innerHTML = myArr[1];
        }
    };

    xmlhttp.open("GET","/jobClock/index.php?action=addToDept&employeeID="+employeeID+"&workOrderNumber="+workOrderNumber+"&departmentID="+departmentID);
    xmlhttp.send();
}


function viewDepts(transferId, targetId, originId, workOrderAdd){
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
            
            if(targetId == "productionSchedule"){
                document.getElementById("deptAssign").innerHTML = myArr[0];
            }else{
                document.getElementById("deptAssign"+targetId).innerHTML = myArr[0];
            }
            
            
        }
    };

    xmlhttp.open("GET","/jobClock/index.php?action=viewDept&transferId="+transferId+"&targetId="+targetId+"&originId="+originId+"&workOrderAdd="+workOrderAdd);
    xmlhttp.send();
}

function refreshSummary(){
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
            
            alert
            workOrders = myArr[0];
            cnt = 1;
            for(var i = 0; i < myArr[0].length; i++){
                if(document.getElementById("w"+workOrders[i])){
                    if(!document.getElementById("deptAssign"+workOrders[i]).innerHTML){
                        document.getElementById("w"+workOrders[i]).innerHTML = myArr[i + 1];
                    }
                }else{
                    document.getElementById("openSection"+cnt).innerHTML = myArr[i + 1];
                    cnt += 1;
                }
                
            }
            
            if(!document.getElementById("deptAssign").innerHTML){
                document.getElementById("productionSchedule").innerHTML = myArr[workOrders.length + 1];
            }
            
            setTimeout('refreshSummary();','5000');
        }
    };

    xmlhttp.open("GET","/jobClock/index.php?action=refreshSummary");
    xmlhttp.send();
}


