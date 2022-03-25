
function modifyEmployeeSchedule(employeeID, add){
    var xmlhttp=new XMLHttpRequest();
    var shiftID=document.getElementById("selectedShift").value;
    var scheduleDate=document.getElementById("selectedDate").value;

    xmlhttp.onreadystatechange=function() {
        if(xmlhttp.responseText == 'timedOut'){
            window.location.href = "/production";
        }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {
            document.getElementById("scheduleTables").innerHTML=xmlhttp.responseText;
        }
    }
    if (add){
        xmlhttp.open("GET","/production/index.php?action=addEmployeeToSchedule&employeeID="+employeeID +
                "&shiftID="+shiftID +
                "&scheduleDate=" +
                "&ajax=true" +
                "&scheduleDate=" + scheduleDate ,true);
    }else{
        xmlhttp.open("GET","/production/index.php?action=removeEmployeeToSchedule&employeeID="+employeeID+"&shiftID="+shiftID+"&scheduleDate="+scheduleDate,true);
    }
    xmlhttp.send();
}
        
function updateCompletionData(workOrderNumber, selectedDate){
    var jsDate = new Date(selectedDate);

    if (!document.getElementById("completionDate").value){
        var convSelectedDate = null;
        
    }else{
        var completionDate = new Date(document.getElementById("completionDate").value + " " + "00:00:00");
        //Convert time stamp for PHP
        var convCompDate = completionDate.toISOString();
        var convSelectedDate = jsDate.toISOString();
    }

    var completionQty=document.getElementById("completionQty").value;
    var EditElapsedHrs=document.getElementById("EditElapsedHrs").value;
    var EditElapsedMins=document.getElementById("EditElapsedMins").value;

    var xmlhttp=new XMLHttpRequest();

    xmlhttp.onreadystatechange=function() {
        if(xmlhttp.responseText == 'timedOut'){
            window.location.href = "/production";
        }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {
            if(!selectedDate){
                window.location.href = "/production/index.php?action=workOrder&workOrderNumber=" + workOrderNumber;
            }else{
                window.location.href = "/production/index.php?action=productionSchedule&returnDate=" + convSelectedDate;
            }
        }
    }

    xmlhttp.open("GET","/production/index.php?action=updateCompletionData&workOrderNumber="+workOrderNumber
            +"&selectedDate="+convSelectedDate
            +"&completionDate="+convCompDate
            +"&completionQty="+completionQty
            +"&EditElapsedHrs="+EditElapsedHrs
            +"&EditElapsedMins="+EditElapsedMins);
    xmlhttp.send();
}


function addNewJobInput(){
    var htmlstr = '<div class="overlay" id="dataEntryForm">\n\
                    <a href="/production/index.php?action=sJobClock" id="exit"><span>x</span></a>\n\
                <h1>Enter/Scan Job Number</h1>\n\
                <div class="grid">\n\
                <div class="unit two-thirds">\n\
                    <ul>\n\
                            <li><h2 id="errorMessage"></h2></li>\n\
                        <li>\n\
                            <label for="jobNumber">Enter Job Number:</label>\n\
                            <input type="text" id="jobNumber" name="jobNumber" onkeydown="Javascript: if (event.keyCode==13) addNewJob();" autofocus />\n\
                        </li>\n\
                    </ul>\n\
                </div>\n\
                </div>\n\
                <button type="submit" name="action" id="action" onclick="addNewJob()"><span>i</span></button></div>';
    
    document.getElementById("sjcOverlay").innerHTML=htmlstr;

}

function addNewJob(){
    var xmlhttp=new XMLHttpRequest();
    var jobNumber = document.getElementById("jobNumber").value;

    xmlhttp.onreadystatechange=function() {
        if(xmlhttp.responseText == 'timedOut'){
            window.location.href = "/production";
        }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {
            var myArr = JSON.parse(xmlhttp.responseText);
            
            if(myArr[0] == 0){
                document.getElementById("errorMessage").innerHTML = "Error: Work Order Does not exsist.";
            }else if(myArr[0] == -1){
                document.getElementById("errorMessage").innerHTML = "Error: Work Order has been completed.";
            }else{
                document.getElementById('sjcCurrentJobs').innerHTML = myArr[0];
                document.getElementById('sjcPunches').innerHTML = myArr[1];
                document.getElementById('sjcOverlay').innerHTML = "";
            }
            
            busyIndicator(false);
        }
    }
    
    xmlhttp.open("GET","/production/index.php?action=addNewJob&jobNumber="+jobNumber);
    xmlhttp.send();
    
    busyIndicator(true);
}

function startJobTimeView(jobNumber){
    var xmlhttp=new XMLHttpRequest();
    
    xmlhttp.onreadystatechange=function() {
        if(xmlhttp.responseText == 'timedOut'){
            window.location.href = "/production";
        }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {
            var myArr = JSON.parse(xmlhttp.responseText);
            
            document.getElementById('sjcOverlay').innerHTML = myArr[0];
            document.getElementById('sjcCurrentJobs').innerHTML = myArr[1];
            
            busyIndicator(false);
        }
    }
    
    xmlhttp.open("GET","/production/index.php?action=startJobTimeView&jobNumber="+jobNumber);
    xmlhttp.send();
    
    busyIndicator(true);

}


function startJobTimeView(jobNumber){
    var xmlhttp=new XMLHttpRequest();
    
    xmlhttp.onreadystatechange=function() {
        if(xmlhttp.responseText == 'timedOut'){
            window.location.href = "/production";
        }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {
            var myArr = JSON.parse(xmlhttp.responseText);
            
            document.getElementById('sjcOverlay').innerHTML = myArr[0];
            
            busyIndicator(false);
        }
    }
    
    xmlhttp.open("GET","/production/index.php?action=startJobTimeView&jobNumber="+jobNumber);
    xmlhttp.send();
    
    busyIndicator(true);

}

function startJobTime(jobNumber){
    
    var xmlhttp=new XMLHttpRequest();
    var department = document.getElementById("department").value;
    var crewCount = document.getElementById("crewCount").value;
    var startTime = document.getElementById("startTime").value;
    var d = new Date(startTime);
    var n = d.valueOf();
    xmlhttp.onreadystatechange=function() {
        if(xmlhttp.responseText == 'timedOut'){
            window.location.href = "/production";
        }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {
            var myArr = JSON.parse(xmlhttp.responseText);

            document.getElementById('sjcPunches').innerHTML = myArr[0];
            document.getElementById('sjcCurrentJobs').innerHTML = myArr[1];
            document.getElementById('sjcHeader').innerHTML = myArr[2];
            document.getElementById('sjcOverlay').innerHTML = "";
            
            busyIndicator(false);
        }
    }
    
    xmlhttp.open("GET","/production/index.php?action=startJobTime&jobNumber="+jobNumber+"&department="+department+"&crewCount="+crewCount+"&startTime="+startTime);
    xmlhttp.send();
    
    busyIndicator(true);
}

function displayJobPunches(jobNumber){
    var xmlhttp=new XMLHttpRequest();

    xmlhttp.onreadystatechange=function() {
        if(xmlhttp.responseText == 'timedOut'){
            window.location.href = "/production";
        }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {

            var myArr = JSON.parse(xmlhttp.responseText); 
            
            document.getElementById('sjcPunches').innerHTML = myArr[0];
            
            busyIndicator(false);
        }
    }
    
    xmlhttp.open("GET","/production/index.php?action=displayJobPunches&jobNumber="+jobNumber);
    xmlhttp.send();
    
    busyIndicator(true);
}


function deleteJobPunch(jobPunchID, jobNumber){
    
    var xmlhttp=new XMLHttpRequest();

    xmlhttp.onreadystatechange=function() {
        if(xmlhttp.responseText == 'timedOut'){
            window.location.href = "/production";
        }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {
            var myArr = JSON.parse(xmlhttp.responseText); 
            
            document.getElementById('sjcPunches').innerHTML = myArr[0];
            document.getElementById('sjcCurrentJobs').innerHTML = myArr[1];
            
            busyIndicator(false);
        }
    }
    
    xmlhttp.open("GET","/production/index.php?action=deleteJobPunch&jobPunchID="+jobPunchID+"&jobNumber="+jobNumber);
    xmlhttp.send();
    
    busyIndicator(true);
}


function stopJobPunchView2(jobPunchID, firstPunch, jobNumber){
    
    var divID = "stopJob"+jobPunchID;
    var inputID = "time"+jobPunchID;
    var d = new Date();
    d.setUTCHours(12);
    var n = d.toISOString();
    n = n.substring(0, n.length - 8);
    var stopHTML = firstPunch + " - <input type=\"datetime-local\" id=\""+ inputID + "\" name=\"endTime\" value=\"" + n + "\"> \n\
<a href=\"javascript:;\" onclick=\"addStopPunch(" + jobPunchID + ", \'" + jobNumber + "\')\" title=\"Delete Job Punch\"><span>v</span></a>\n\
<a href=\"javascript:;\" onclick=\"displayJobPunches(\'" + jobNumber + "\')\" title=\"Cancel\"><span>O</span></button>";
    
    document.getElementById(divID).innerHTML = stopHTML;
    
}


function stopJobPunchView(jobPunchID, firstPunch, jobNumber){
    
    var xmlhttp=new XMLHttpRequest();
    var divID = "stopJob"+jobPunchID;
    var inputID = "time"+jobPunchID;

    xmlhttp.onreadystatechange=function() {
        if(xmlhttp.responseText == 'timedOut'){
            window.location.href = "/production";
        }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {
            var n = xmlhttp.responseText; 
            var stopHTML = firstPunch + " - <input type=\"datetime-local\" id=\""+ inputID + "\" name=\"endTime\" value=\"" + n + "\"> \n\
                <a href=\"javascript:;\" onclick=\"addStopPunch(" + jobPunchID + ", \'" + jobNumber + "\')\" title=\"Confirm\"><span>v</span></a>\n\
                <a href=\"javascript:;\" onclick=\"displayJobPunches(\'" + jobNumber + "\')\" title=\"Cancel\"><span>O</span></button>";
    
                document.getElementById(divID).innerHTML = stopHTML;
        }
    };
    
    xmlhttp.open("GET","/production/index.php?action=getServerTime");
    xmlhttp.send();

}

function addStopPunch(jobPunchID, jobNumber){
    
    var xmlhttp=new XMLHttpRequest();
    var stopPunch = document.getElementById("time"+jobPunchID).value;

    xmlhttp.onreadystatechange=function() {
        if(xmlhttp.responseText == 'timedOut'){
            window.location.href = "/production";
        }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {
            var myArr = JSON.parse(xmlhttp.responseText); 
            
            document.getElementById('sjcPunches').innerHTML = myArr[0];
            document.getElementById('sjcCurrentJobs').innerHTML = myArr[1];
            
            busyIndicator(false);
        }
    };
    
    xmlhttp.open("GET","/production/index.php?action=addStopPunch&stopPunch="+stopPunch+"&jobNumber="+jobNumber+"&jobPunchID="+jobPunchID);
    xmlhttp.send();

    busyIndicator(true);
}

function toggleLunch(jobPunchID, toggle){
    
    var xmlhttp=new XMLHttpRequest();
    
    xmlhttp.onreadystatechange=function() {
        if(xmlhttp.responseText == 'timedOut'){
            window.location.href = "/production";
        }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {
            var myVar = xmlhttp.responseText; 
            if(myVar == 1){
                document.getElementById(jobPunchID).className = "iconToggled";
            }else{
                document.getElementById(jobPunchID).className = "";
            }
            
            busyIndicator(false);
        }
    };
    
    xmlhttp.open("GET","/production/index.php?action=toggleLunch&jobPunchID="+jobPunchID+"&toggle="+toggle);
    xmlhttp.send();

    busyIndicator(true);
}

function completeJob(jobNumber){
    window.location.href = "/production/index.php?action=completeJob&jobNumber="+jobNumber
}


function completeWO(woNo, compQty, skuNumber) {
    
//    alert(compQty);
//    alert(woNo);
    
    var xmlhttp=new XMLHttpRequest();
    
    xmlhttp.onreadystatechange=function() {
        if(xmlhttp.responseText == 'timedOut'){
            window.location.href = "/production";
        }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {
            
            document.getElementById("overlay").innerHTML =
            '<div class="overlay">' +
            '<a href="#" onclick="closeOverlay()" id="exit"><span>x</span></a>' +
            xmlhttp.responseText +
            "</div>";

           busyIndicator(false);
        }
    };
    
    xmlhttp.open("GET","/production/index.php?action=newCompleteWO&woNo="+woNo+"&compQty="+compQty+'&skuNumber='+skuNumber);
    xmlhttp.send();

   busyIndicator(true);
}

function closeOverlay() {
  document.getElementById("overlay").innerHTML = "";
}

function submitWOConfirm(woNo, compQty) {
    
    var xmlhttp=new XMLHttpRequest();

    var selectedDate = document.getElementById('selectedDate').value;
        
    xmlhttp.onreadystatechange=function() {
        if(xmlhttp.responseText == 'timedOut'){
            window.location.href = "/production";
        }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {
            
            document.getElementById("confirmFooter").innerHTML = xmlhttp.responseText;

               busyIndicator(false);
        }
    };

    xmlhttp.open("GET","/production/index.php?action=submitWOConfirm&woNo="+woNo+'&compQty='+compQty);
    xmlhttp.send();

       busyIndicator(true);
}

function cancelWOSubmitConfirm(woNo, compQty) {
    
    var xmlhttp=new XMLHttpRequest();

    var selectedDate = document.getElementById('selectedDate').value;
        
    xmlhttp.onreadystatechange=function() {
        if(xmlhttp.responseText == 'timedOut'){
            window.location.href = "/production";
        }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {
            
            document.getElementById("confirmFooter").innerHTML = xmlhttp.responseText;

               busyIndicator(false);
        }
    };

    xmlhttp.open("GET","/production/index.php?action=cancelWOSubmitConfirm&woNo="+woNo+'&compQty='+compQty);
    xmlhttp.send();

       busyIndicator(true);
}

function completeWOSubmit(woNo, compQty) {
    var selectedDate = document.getElementById('selectedDate').value;
    var xmlhttp=new XMLHttpRequest();
    xmlhttp.onreadystatechange=function() {
        if(xmlhttp.responseText == 'timedOut'){
            window.location.href = "/production";
        }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {
            var myArr = JSON.parse(xmlhttp.responseText);
            if(myArr[0]) {
                toast("Sorry, you are unauthorized to do this.");
            }else {
                document.getElementById("prodList").innerHTML = myArr[1];
                // closeOverlay()
                closeOverlayGlobal();
            }
            busyIndicator(false);
        }
    };
    xmlhttp.open("GET","/production/index.php?action=complete&woNo="+woNo+'&compQty='+compQty+'&selectedDate='+selectedDate);
    xmlhttp.send();
    busyIndicator(true);
}

function reviewCompleteWO(woNo, compQty, skuNumber, desc) {
    
//    alert(compQty);
//    alert(woNo);
    
    var selectedDate = xmlhttp=new XMLHttpRequest('selectedDate');
    
    xmlhttp.onreadystatechange=function() {
        if(xmlhttp.responseText == 'timedOut'){
            window.location.href = "/production";
        }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {
            
            document.getElementById("overlay").innerHTML =
            '<div class="overlay">' +
            '<a href="#" onclick="closeOverlay()" id="exit"><span>x</span></a>' +
            xmlhttp.responseText +
            "</div>";

        //    busyIndicator(false);
        }
    };
    
    xmlhttp.open("GET","/production/index.php?action=reviewCompleteWO&woNo="+woNo+"&compQty="+compQty+'&skuNumber='+skuNumber+'&desc='+desc);
    xmlhttp.send();

//    busyIndicator(true);
}

function setToHot(hot, woNo, row, completeStatus) {

    // alert(hot + ', ' + woNo);
        
    // alert(completeStatus);

    var selectedDate = document.getElementById("selectedDate").value;
        
    var xmlhttp=new XMLHttpRequest();
        
    xmlhttp.onreadystatechange=function() {
        if(xmlhttp.responseText == 'timedOut'){
            window.location.href = "/production";
        }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {
            
            document.getElementById("myonoffswitch").checked = false;

            document.getElementById("prodList").innerHTML = xmlhttp.responseText;
            // alert(xmlhttp.responseText);

            busyIndicator(false);
        }
    };

    xmlhttp.open("GET","/production/index.php?action=hot&hot="+hot+"&workOrderNumber="+woNo+"&selectedDate="+selectedDate+'&complete='+completeStatus);
    xmlhttp.send();

    busyIndicator(true);
}

// function setToHot(hot, woNo) {

//     var menuID = document.getElementById("menuID").value;

//     // alert(hot + ', ' + woNo);
//     if(menuID == 'today') {
//         var search = document.getElementById("selectedDate").value;
//     }else if(menuID == 'calander') {
//         var search = document.getElementById("searchTerm").value;
//     }else if(menuID == 'past') {
//         // Past Due
//     }

//     var xmlhttp=new XMLHttpRequest();
        
//     xmlhttp.onreadystatechange=function() {
//         if(xmlhttp.responseText == 'timedOut'){
//             window.location.href = "/production";
//         }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {
            
//             document.getElementById("prodList").innerHTML = xmlhttp.responseText;
//             // alert(xmlhttp.responseText);

//             busyIndicator(false);
//         }
//     };

//     xmlhttp.open("GET","/production/index.php?action=hot&hot="+hot+"&workOrderNumber="+woNo+"&search="+search+'&menuID='+menuID);
//     xmlhttp.send();

//     busyIndicator(true);
// }






// window.onload = init;

// function init() {
// 	if (window.Event) {
// 	document.captureEvents(Event.MOUSEMOVE);
// 	}
// 	document.onmousemove = getCursorXY;
// }

// function getCursorXY(e) {
// 	document.getElementById('cursorX').value = (window.Event) ? e.pageX : event.clientX + (document.documentElement.scrollLeft ? document.documentElement.scrollLeft : document.body.scrollLeft);
// 	document.getElementById('cursorY').value = (window.Event) ? e.pageY : event.clientY + (document.documentElement.scrollTop ? document.documentElement.scrollTop : document.body.scrollTop);
// }

// function showProdNotes(notes) {

//     alert(notes);

//     x = document.getElementById("cursorX").value;
//     y = document.getElementById("cursorY").value;

//     document.getElementById("notesDiv").style.left = x+"px";
//     document.getElementById("notesDiv").style.top = y+"px";

//     document.getElementById("notesDiv").classList.add('show');
//     // if(notes == null) {
//         document.getElementById("notesDiv").innerHTML = '<p id="notesDivText"> No Notes. </p>';
//     // }else {
//     //     document.getElementById("notesDiv").innerHTML = '<p id="notesDivText">' + notes + '</p>';
//     // }
// }

// function hideProdNotes() {
//     document.getElementById("notesDiv").classList.remove('show');
//     document.getElementById("notesDiv").innerHTML = '';
// }

function showProdNotes(workOrderNumber, notes) {

    // alert(workOrderNumber);
    var xmlhttp=new XMLHttpRequest();
    xmlhttp.onreadystatechange=function() {
        if(xmlhttp.responseText == 'timedOut'){
            window.location.href = "/production";
        }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {
            var html = xmlhttp.responseText;
            overlay(html);
            setTimeout(() => {
                document.getElementById("end").scrollIntoView();
            }, 200)
            busyIndicator(false);
        }
    };
    xmlhttp.open("GET","/production/index.php?action=productionNotes&notes="+notes+'&workOrderNumber='+workOrderNumber);
    xmlhttp.send();
    busyIndicator(true);
}


// ! Production Schedule Search Moved to library.js

// * As well as complete


function deleteWorkOrder(woNo, compQty = 0, complete) {
    
    if(complete == 1) {
        toast('Order Completed', 'error');
    }else if(compQty > 0) {
        toast('Order in progress', 'error');
    }else {
        var r = confirm('Are you sure you want to delete WO' + woNo + '?');

        if(r == true) {
            var selectedDate = document.getElementById("selectedDate").value;
        
            var xmlhttp=new XMLHttpRequest();
                
            xmlhttp.onreadystatechange=function() {
                if(xmlhttp.responseText == 'timedOut'){
                    window.location.href = "/production";
                }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {

                    toast('WO# ' + woNo + ' was deleted successfully', 'success');
                    
                    // document.getElementById("prodList").innerHTML = xmlhttp.responseText;

                    location.reload();
                    
                    busyIndicator(false);
                }
            };
        
            xmlhttp.open("GET","/production/index.php?action=deleteWorkOrder&woNo="+woNo+'&selectedDate='+selectedDate);
            xmlhttp.send();
        
            busyIndicator(true);
        }
    }
    
}

function editProdItem(woNo) {
        
    var xmlhttp=new XMLHttpRequest();
        
    xmlhttp.onreadystatechange=function() {
        if(xmlhttp.responseText == 'timedOut'){
            window.location.href = "/production";
        }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {


            overlay(xmlhttp.responseText, false, true);

            //    busyIndicator(false);
        }
    };

    xmlhttp.open("GET","/production/index.php?action=completeWOEx&woNo="+woNo);
    xmlhttp.send();

    //    busyIndicator(true);
}

function printWorkOrder(woNo) {
            
    var selectedDate = document.getElementById("selectedDate").value;

    var xmlhttp=new XMLHttpRequest();
        
    xmlhttp.onreadystatechange=function() {
        if(xmlhttp.responseText == 'timedOut'){
            window.location.href = "/production";
        }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {
            
            document.getElementById("prodList").innerHTML = xmlhttp.responseText;

               busyIndicator(false);
        }
    };

    xmlhttp.open("GET","/production/index.php?action=printWorkOrder&workOrderNumber="+woNo+'&selectedDate='+selectedDate);
    xmlhttp.send();

    busyIndicator(true);
}

function viewProdItem(skuNumber, currentView) {
                
    var xmlhttp=new XMLHttpRequest();
        
    

    xmlhttp.onreadystatechange=function() {
        if(xmlhttp.responseText == 'timedOut'){
            window.location.href = "/production";
        }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {
            
            window.location.href = "/inventory/index.php?action=viewItem&skuNumber="+skuNumber;

        }
    };


    xmlhttp.open("GET","/production/index.php?action=viewItem&skuNumber="+skuNumber+'&currentView='+currentView);
    xmlhttp.send();

    busyIndicator(true);

}

// function goBack(from) {

//     var myArr = from.split(',');
            
//     var xmlhttp=new XMLHttpRequest();
        
//     xmlhttp.onreadystatechange=function() {
//         if(xmlhttp.responseText == 'timedOut'){
//             window.location.href = "/production";
//         }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {

//                 busyIndicator(false);
//         }
//     };

//     if(myArr[0] == 'production'){
//         xmlhttp.open("GET","/production/index.php?action=productionSchedule&returnDate="+myArr[1]);
//         xmlhttp.send();
//     }

//         busyIndicator(true);
// }


function statusOH(parentSku, childSku, compQty, complete) {
    var xmlhttp=new XMLHttpRequest();
        
    xmlhttp.onreadystatechange=function() {
        if(xmlhttp.responseText == 'timedOut'){
            window.location.href = "/production";
        }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {
            
            busyIndicator(false);

            document.getElementById("overlay").innerHTML =
            '<div id="overlay" class="overlay">' +
            '<a href="#" onclick="closeOverlay()" id="exit"><span>x</span></a>' +
            xmlhttp.responseText +
            "</div>";
        }
    };

    xmlhttp.open("GET","/production/index.php?action=statusOH&parentSku="+parentSku+'&complete='+complete+'&childSku='+childSku+'&compQty='+compQty);
    xmlhttp.send();

    busyIndicator(true);
}

function removeSearchItem() {
    var filteredAry = ary.filter(function(e) { return e !== 'seven' })
}

function submitProdSearchSku(clear, search) {

    var searchListInput = document.getElementById('searchListInput');
    var searchList = document.getElementById('searchList');
    var arr = searchListInput.value ? JSON.parse(searchListInput.value) : [];
    
    var searchTerm = search ? search : searchTerm
    searchTerm = clear ? '' : document.getElementById('selectedDate').value;
    
    // searchList.innerHTML += '<div class="searchItem">' + searchTerm + ' <a onclick="removeSearchItem(\'' + searchTerm + '\')"><i class="bi bi-x-circle redIcon"></i></a></div>';
 
    // arr.push( searchTerm );

    // alert(arr);

    // var stringify = JSON.stringify(arr);

    // searchListInput.value = stringify;

    // arr.forEach(function (infoArray, index) {
    //     var line = infoArray.join(",");
    //     lineArray.push(index == 0 ? "data:text/csv;charset=utf-8," + line : line);
    // });
    // var csvContent = lineArray.join("\n");
    
    // alert(csvContent);

    
    // searchListInput.value = JSON.parse(arr);
    // document.getElementById('selectedDate').value = null;






    var xmlhttp=new XMLHttpRequest();
        
    xmlhttp.onreadystatechange=function() {
        if(xmlhttp.responseText == 'timedOut'){
            window.location.href = "/production";
        }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {
            // alert(xmlhttp.responseText);
            var myArr = JSON.parse(xmlhttp.responseText);

            if(xmlhttp.responseText) {
                document.getElementById("prodList").innerHTML = myArr[0];
                document.getElementById("showToggle").innerHTML = myArr[1];
            }

               busyIndicator(false);
        }
    };

    xmlhttp.open("GET","/production/index.php?action=searchClick&searchTerm="+searchTerm);
    xmlhttp.send();

    busyIndicator(true);
}

function checkSearchType() {

    searchTerm = document.getElementById("sttInput").value;

    var xmlhttp=new XMLHttpRequest();
        
    xmlhttp.onreadystatechange=function() {
        if(xmlhttp.responseText == 'timedOut'){
            window.location.href = "/production";
        }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {

            // alert(xmlhttp.responseText);

            var myArr = JSON.parse(xmlhttp.responseText);

            workOrderNumber = myArr[1];
            completeStatus = myArr[2];
            oldDate = myArr[3];
            
            if(myArr[0] == 0) {

                var today = new Date();
                var dd = today.getDate();

                var mm = today.getMonth()+1; 
                var yyyy = today.getFullYear();
                
                if(dd<10) 
                {
                    dd='0'+dd;
                } 

                if(mm<10) 
                {
                    mm='0'+mm;
                } 

                today = dd+'-'+mm+'-'+yyyy;

                updateScheduleDate(today, workOrderNumber, null, completeStatus, 0, oldDate);

                document.getElementById("sttInput").value = '';

                // toast('This is a WO! - workOrderNumber: ' + workOrderNumber + ' - complete: ' + completeStatus);

            }else if(myArr[0] == 1) {

                // submitProdSearchSku(0, workOrderNumber);

                toast('Not a WO#!');

            }else {
                // toast('Error @ checkSearchType();');
            }


            busyIndicator(false);

        }
    };

    xmlhttp.open("GET","/production/index.php?action=checkSearchType&searchTerm="+searchTerm);
    xmlhttp.send();

    busyIndicator(true);


}

function pastDue(searchTerm) {

    var xmlhttp=new XMLHttpRequest();
        
    xmlhttp.onreadystatechange=function() {
        if(xmlhttp.responseText == 'timedOut'){
            window.location.href = "/production";
        }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {
            
            // if(xmlhttp.responseText) {
            // document.getElementById("productionItems").innerHTML = xmlhttp.responseText;
            // }

               busyIndicator(false);
        }
    };

    xmlhttp.open("GET","/production/index.php?action=pastDue&searchTerm="+searchTerm);
    xmlhttp.send();

    busyIndicator(true);
}

function updateScheduleDate(newDate, workOrderNumber, r, completeStatus, searchMenu, stt = false) {

    var today = formatDate(new Date());

    // alert(newDate);
    // alert(today);

    if(stt == false) {
        var oldDate = document.getElementById("selectedDate").value;
    }else {
        var oldDate = stt;
    }

    var xmlhttp=new XMLHttpRequest();    
    xmlhttp.onreadystatechange=function() {
        if(xmlhttp.responseText == 'timedOut'){
            window.location.href = "/production";
        }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {
            // var myArr = JSON.parse(xmlhttp.responseText);
            // alert(xmlhttp.responseText);
            if(newDate == today) {
                prodScheduleSearch(newDate);
                goToWorkOrder(true, workOrderNumber)
            }else if(xmlhttp.responseText == 0) {
                toast('You cannot reschedule completed work orders!');
                document.getElementById(workOrderNumber + 'i').value = oldDate;
            }else if(xmlhttp.responseText == 1) {
                toast('You cannot reschedule work orders that are not approved!');
                document.getElementById(workOrderNumber + 'i').value = oldDate;
            }else {
                document.getElementById('woNum_'+workOrderNumber).innerHTML = xmlhttp.responseText;
                if(searchMenu = false) {
                    if(completeStatus == 0) {    
                        deleteTableRow('woNum_'+workOrderNumber);
                    }
                    document.getElementById("myonoffswitch").checked = false;
                }
            }

            busyIndicator(false);
        }
    };

    xmlhttp.open("GET","/production/index.php?action=updateScheduleDate&newDate="+newDate+'&workOrderNumber='+workOrderNumber+'&oldDate='+oldDate+'&completeStatus='+completeStatus+'&searchMenu='+searchMenu);
    xmlhttp.send();

    busyIndicator(true);
}

function deleteRow(r) {
    var row = r.parentNode.parentNode;
    var idx = row.rowIndex;
    var table = row.parentNode;
    table.deleteRow(idx);
}

function insertRow() {
var x=document.getElementById('prodList').insertRow(0);
var y = x.insertCell(0);
var z = x.insertCell(1);
y.innerHTML="New Cell1";
z.innerHTML="New Cell2";
}

function undoDate(oldDate, workOrderNumber) {

    var xmlhttp=new XMLHttpRequest();
        
    xmlhttp.onreadystatechange=function() {
        if(xmlhttp.responseText == 'timedOut'){
            window.location.href = "/production";
        }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {
            
            document.getElementById("productionItems").innerHTML = xmlhttp.responseText;
            
               busyIndicator(false);
        }
    };

    xmlhttp.open("GET","/production/index.php?action=undoDate&oldDate="+oldDate+'&workOrderNumber='+workOrderNumber);
    xmlhttp.send();

    busyIndicator(true);
}

function enterSubmitProdSearch(e) {
    var key = e.keyCode || e.which;
    if (key === 13) {
        submitProdSearchSku();
        // checkSearchType();
    }
  }


function pdCloseSlider() {
    // document.getElementById('pdBtn').classList.remove('disable');

    // Close Slider
    document.getElementById('pastDueSlider').classList.add('pdSlideDown');
    document.getElementById('pastDueSlider').classList.remove('pdSlideUp');
    document.getElementById('pdSlideStatus').value = 0;
    
    // Rebuild Page
    setTimeout(() => {
        // prodScheduleSearch(document.getElementById('selectedDate').value);
        // ? Weird things were happening to the icons when the page was rebuild, reload fixes it
        location.reload();
    }, 1000)
    
}

function pastDueSlider() {

    // selectedDate = document.getElementById('selectedDate').value;

    pdSlideStatus = document.getElementById('pdSlideStatus').value;

    if(pdSlideStatus == 1) {
        pdCloseSlider();
    }else {

        // document.getElementById('pdBtn').classList.add('disable');
    
        // toast(pdSlideStatus);
    
        var xmlhttp=new XMLHttpRequest();
            
        xmlhttp.onreadystatechange=function() {
            if(xmlhttp.responseText == 'timedOut'){
                window.location.href = "/production";
            }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {

                // alert(xmlhttp.responseText);

                document.getElementById('pdSlideStatus').value = 1;
                document.getElementById('pastDueSlider').classList.remove('pdSlideDown');
                document.getElementById('pastDueSlider').classList.add('pdSlideUp');
                
                document.getElementById("pdSliderContent").innerHTML = xmlhttp.responseText;
    
                busyIndicator(false);
    
            }
        };
    
        xmlhttp.open("GET","/production/index.php?action=pastDueSlider");
        xmlhttp.send();
    
        busyIndicator(true);
    }
}

function changeSearchSetting(c) {

    if(c.checked) {
    state = 1;
    }else {
    state = 0;
    }

    selectedDate = document.getElementById("selectedDate").value;

    // alert(selectedDate);

    var xmlhttp=new XMLHttpRequest();
    
    xmlhttp.onreadystatechange=function() {
    if(xmlhttp.responseText == 'timedOut'){
        window.location.href = "/production";
    }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {
        
        document.getElementById("prodList").innerHTML = xmlhttp.responseText;

        busyIndicator(false);

    }
    };

    xmlhttp.open("GET","/production/index.php?action=changeSearchSetting&state="+state+'&selectedDate='+selectedDate);
    xmlhttp.send();

    busyIndicator(true);
}


function pdDescAscToggle(state) {

    // if(c.checked) {
    //     state = 1;
    // }else {
    //     state = 0;
    // }

    selectedDate = document.getElementById("selectedDate").value;

    // alert(state);

    var xmlhttp=new XMLHttpRequest();
        
    xmlhttp.onreadystatechange=function() {
        if(xmlhttp.responseText == 'timedOut'){
            window.location.href = "/production";
        }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {
            
            document.getElementById("pdList").innerHTML = xmlhttp.responseText;

            busyIndicator(false);

        }
    };

    xmlhttp.open("GET","/production/index.php?action=toggleSortPd&state="+state+'&selectedDate='+selectedDate);
    xmlhttp.send();

    busyIndicator(true);
}

function changeShift(shift, workOrderNumber, complete, reload = true) {

    if(complete) {
        toast('This WO is completed.', 'bad');
        return;
    }

    selectedDate = document.getElementById("selectedDate").value;

    // alert(selectedDate);

    var xmlhttp=new XMLHttpRequest();
        
    xmlhttp.onreadystatechange=function() {
        if(xmlhttp.responseText == 'timedOut'){
            window.location.href = "/production";
        }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {
            if(reload) {
                document.getElementById("prodList").innerHTML = xmlhttp.responseText;
            }else {
                var message = 'Successfully moved to shift ' + (shift == 1 ? 1 : 2)
                document.getElementById('indirectShiftChange_'+workOrderNumber).innerHTML = message;
            }
            
            busyIndicator(false);
        }
    };

    xmlhttp.open("GET","/production/index.php?action=changeShift&shift="+shift+'&workOrderNumber='+workOrderNumber+'&selectedDate='+selectedDate+'&reload='+reload);
    xmlhttp.send();

    busyIndicator(true);
}

function changePriority(priority, workOrderNumber) {
    var xmlhttp=new XMLHttpRequest();

    selectedDate = document.getElementById("selectedDate").value;
        
    // alert(priority.value);
    // alert(workOrderNumber);

    priority = priority.value;
    
    xmlhttp.onreadystatechange=function() {
        if(xmlhttp.responseText == 'timedOut'){
            window.location.href = "/production";
        }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {
            
            document.getElementById("prodList").innerHTML = xmlhttp.responseText;

            busyIndicator(false);

        }
    };

    xmlhttp.open("GET","/production/index.php?action=changePriority&priority="+priority+'&workOrderNumber='+workOrderNumber+'&selectedDate='+selectedDate);
    xmlhttp.send();

    busyIndicator(true);
}

// invRollForward($parentSku, $qtyPer, $qoh, $childSku)
function openInvRollForward(parentSku, qtyPer, childSku, woNumber, compQty) {
    var xmlhttp=new XMLHttpRequest();

    xmlhttp.onreadystatechange=function() {
        if(xmlhttp.responseText == 'timedOut'){
            window.location.href = "/production";
        }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {

            busyIndicator(false);

            var save = document.getElementById("overlay").innerHTML;

            // alert(compQty);

            document.getElementById("overlay").innerHTML =
            '<div class="overlay">' +
            '<a href="#" onclick="statusOH(' + woNumber + ', ' + parentSku + ', ' + compQty + ', 0)" class="goBackBtn"><span><</span></a>' +
            xmlhttp.responseText +
            "</div>";

            
        }
    };

    xmlhttp.open("GET","/production/index.php?action=invRollForward&parentSku="+parentSku+'&qtyPer='+qtyPer+'&childSku='+childSku);
    xmlhttp.send();

    busyIndicator(true);
}

function goBackToStatusOH(save) {
    document.getElementById("overlay").innerHTML =
            '<div class="overlay">' +
            save +
            "</div>";
}

function checkPdScroll() {

    // toast('je;[');

    var jumpBtn = document.getElementById('jumpBtn');
  
    var y = document.getElementById('pdList').scrollTop;
  
    // document.getElementById("return").value = y;
  
    if(y != 0) {
      jumpBtn.className = 'show';
    }else {
      jumpBtn.className = 'hide';
    }
  
}

function prodToday() {
    var xmlhttp=new XMLHttpRequest();

    xmlhttp.onreadystatechange=function() {
        if(xmlhttp.responseText == 'timedOut'){
            window.location.href = "/production";
        }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {

            busyIndicator(false);

            document.getElementById("viewport").innerHTML = xmlhttp.responseText;
            
        }
    };

    xmlhttp.open("GET","/production/index.php?action=productionSchedule&today=1");
    xmlhttp.send();

    busyIndicator(true);
}

// Go To Workorder moved to Library.js

function skuRundown(workOrderNumber, skuNumber) {

    var xmlhttp=new XMLHttpRequest();

    xmlhttp.onreadystatechange=function() {
        if(xmlhttp.responseText == 'timedOut'){
            window.location.href = "/production";
        }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {

            busyIndicator(false);

            // alert(xmlhttp.responseText);

            if(!xmlhttp.responseText) {
                toast('Something went wrong...');
            }else {
                document.getElementById("skuRundown").innerHTML = xmlhttp.responseText;
            }   
        }
    };

    xmlhttp.open("GET","/production/index.php?action=buildRundown&workOrderNumber="+workOrderNumber+'&skuNumber='+skuNumber);
    xmlhttp.send();

    busyIndicator(true);
}

function countCharacters() {
    var message = document.getElementById('message');
    var cnt = document.getElementById('current');
    var container = document.getElementById('charCnt');


    cnt.innerHTML = message.value.length;
    container.className = '';
    if(cnt.innerHTML == 254) {
        container.classList.add('maxed');
    }else if(cnt.innerHTML > 200) {
        container.classList.add('closeToMax');
    }else if(cnt.innerHTML < 200) {
        
    }
}

function refreshProdDialogue(workOrderNumber, notes) {
    var cnt = document.getElementById('crewCnt_'+workOrderNumber).value;

    var xmlhttp=new XMLHttpRequest();
    xmlhttp.onreadystatechange=function() {
        if(xmlhttp.responseText == 'timedOut'){
            window.location.href = "/production";
        }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {

            if(xmlhttp.responseText) {
                // toast('Crew Updated', 'success');
                // Rebuild Page
                setTimeout(() => {
                    prodScheduleSearch(document.getElementById('selectedDate').value);
                }, 100)
            }else {
                toast('Crew Failed to Update', 'bad');
            }
            
            busyIndicator(false);
        }
    };
    xmlhttp.open("GET","/production/index.php?action=updateLaborCnt&workOrderNumber="+workOrderNumber+'&cnt='+cnt);
    xmlhttp.send();
    busyIndicator(true);
}

function postComment(workOrderNumber, notes) {
    var message = document.getElementById('message').value;
    var productionDialogue = document.getElementById("productionDialogue");

    if(message == '') {
        toast('The message cannot be empty.');
        return;
    }else {
        
        var today = new Date();
        var month = (today.getMonth()+1);
        month = month < 10 ? '0'+month : month;
        var date = today.getFullYear()+'-'+month+'-'+today.getDate();
        var time = today.getHours() + ":" + today.getMinutes() + ":" + today.getSeconds();
        var dateTime = date+' '+time;
        
        var xmlhttp=new XMLHttpRequest();
        xmlhttp.onreadystatechange=function() {
            if(xmlhttp.responseText == 'timedOut'){
                window.location.href = "/production";
            }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {
    
                if(xmlhttp.responseText == 0) {
                    toast('Message failed to post.');
                }else {
                    // toast('Message Posted Successfully.');
                    var audio = new Audio('/audio/BeepEnd.wav');
                    audio.play();
    
                    document.getElementById("end").remove();
                    
                    if(productionDialogue.innerHTML == 'No Dialogue...') {
                        productionDialogue.innerHTML = '';
                    }
                    document.getElementById('message').value = '';
    
                    document.getElementById("productionDialogue").innerHTML +=
                    '<div class="dialogueLi">' +
                        '<p>' + xmlhttp.responseText + ' @ ' + dateTime + '</p>' +
                        '<div class="message">' + message + '</div>' +
                    '</div><div id="end"></div>';
    
                    document.getElementById("end").scrollIntoView();
                }
    
                busyIndicator(false);
            }
        };
        xmlhttp.open("GET","/production/index.php?action=postProdMessage&workOrderNumber="+workOrderNumber+'&message='+message);
        xmlhttp.send();
        busyIndicator(true);
    }
}

function laborCntChange(workOrderNumber) {
    
    var cntBox = document.getElementById('crewCntBox_'+workOrderNumber);
    var cnt = document.getElementById('crewCnt_'+workOrderNumber).value;

    cntBox.innerHTML = '<a title="Update Crew" onclick="updateLaborCnt(' + cnt + ', ' + workOrderNumber + ')" class="viewportBlueBtn"><i class="bi bi-check-circle"></i></a>';

}

function updateLaborCnt(workOrderNumber) {

    var cnt = document.getElementById('crewCnt_'+workOrderNumber).value;

    var xmlhttp=new XMLHttpRequest();
    xmlhttp.onreadystatechange=function() {
        if(xmlhttp.responseText == 'timedOut'){
            window.location.href = "/production";
        }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {

            if(xmlhttp.responseText) {
                // toast('Crew Updated', 'success');
                // Rebuild Page
                setTimeout(() => {
                    prodScheduleSearch(document.getElementById('selectedDate').value);
                }, 100)
            }else {
                toast('Crew Failed to Update', 'bad');
            }
            
            busyIndicator(false);
        }
    };
    xmlhttp.open("GET","/production/index.php?action=updateLaborCnt&workOrderNumber="+workOrderNumber+'&cnt='+cnt);
    xmlhttp.send();
    busyIndicator(true);
}
function workOrderRundown(workOrderNumber, compQty, skuNumber, desc, completeStatus) {
    var xmlhttp=new XMLHttpRequest();
    xmlhttp.onreadystatechange=function() {
        if(xmlhttp.responseText == 'timedOut'){
            window.location.href = "/production";
        }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {
            overlay(xmlhttp.responseText);
            busyIndicator(false);
        }
    };
    xmlhttp.open("GET","/production/index.php?action=workOrderRundown&workOrderNumber="+workOrderNumber+'&compQty='+compQty+'&skuNumber='+skuNumber+'&desc='+desc+'&completeStatus='+completeStatus);
    xmlhttp.send();
    busyIndicator(true);
}

function confirmCompletion(workOrderNumber, woImperfection, compQty, completionStatus = 2) {
    var xmlhttp=new XMLHttpRequest();
    xmlhttp.onreadystatechange=function() {
        if(xmlhttp.responseText == 'timedOut'){
            window.location.href = "/production";
        }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {
            // alert(xmlhttp.responseText);
            switch(parseInt(xmlhttp.responseText)) {
                case 0:
                    toast('WO Completion Failed', 'bad');
                    searchWOConfirm(completionStatus, 0, workOrderNumber);
                    break;
                case 1:
                    toast('WO Completion Confirmed', 'success');
                    searchWOConfirm(completionStatus, 1);
                    break;
                case 2:
                    toast('Sorry, you are not authorized to do that.', 'bad');
                    searchWOConfirm(completionStatus, 0, workOrderNumber);
                    break;
            }

            closeOverlayGlobal();
            busyIndicator(false);
        }
    };
    xmlhttp.open("GET","/production/index.php?action=confirmWOCompletion&workOrderNumber="+workOrderNumber+'&compQty='+compQty+'&woImperfection='+(woImperfection ? 1 : 0));
    xmlhttp.send();
    busyIndicator(true);
}

function setCompletionStatus(workOrderNumber, completeStatus, currentStatus) {
    if(completeStatus == 0) {
        var message = 'Are you sure you want to push back WO#: ' + workOrderNumber + ' into the schedule?';
    }else {
        var message = 'Are you sure you want to reject WO#: ' + workOrderNumber + '?';
    }
    if(confirm(message)) {
        var xmlhttp=new XMLHttpRequest();
        xmlhttp.onreadystatechange=function() {
            if(xmlhttp.responseText == 'timedOut'){
                window.location.href = "/production";
            }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {
                // alert(xmlhttp.responseText);
                switch(parseInt(xmlhttp.responseText)) {
                    case 0:
                        toast('Failed to push WO back', 'bad');
                        break;
                    case 1:
                        toast(( completeStatus == 3 ? 'WO Rejected' : 'WO Pushed Back' ), 'success');
                            searchWOConfirm(currentStatus, null);
                            closeOverlayGlobal();
                            break;
                    case 2:
                        toast('Sorry, you are not authorized to do that.', 'bad');
                        break;
                }
                
                busyIndicator(false);
            }
        };
        xmlhttp.open("GET","/production/index.php?action=setCompletionStatus&workOrderNumber="+workOrderNumber+'&complete='+completeStatus);
        xmlhttp.send();
        busyIndicator(true);
    }
}

function searchWOConfirm(completionStatus = 2, reset = false, search = false) {
    var input = !search ? document.getElementById('searchWOInput').value : search;
    // var completionStatus = document.getElementById('completionStatus').value;
    // alert(completionStatus);s
    var xmlhttp=new XMLHttpRequest();
    xmlhttp.onreadystatechange=function() {
        if(xmlhttp.responseText == 'timedOut'){
            window.location.href = "/production";
        }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {
            // alert(xmlhttp.responseText);
            document.getElementById('completedWOPH').innerHTML = xmlhttp.responseText;
            if(reset) document.getElementById('searchWOInput').value = '';
            busyIndicator(false);
        }
    };
    xmlhttp.open("GET","/production/index.php?action=searchWOConfirm&input="+(reset ? 0 : input)+'&completionStatus='+completionStatus);
    xmlhttp.send();
    busyIndicator(true);
}