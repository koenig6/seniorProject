/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */;

function busyIndicator(busy){
    //Set Busy Variable
    var busyHtml;
    
    if(busy){
    //Build busy HTML
        busyHtml = "<div id=\"circularG\"> \
                    <div id=\"circularG_1\" class=\"circularG\"></div> \
                    <div id=\"circularG_2\" class=\"circularG\"></div> \
                    <div id=\"circularG_3\" class=\"circularG\"></div> \
                    <div id=\"circularG_4\" class=\"circularG\"></div> \
                    <div id=\"circularG_5\" class=\"circularG\"></div> \
                    <div id=\"circularG_6\" class=\"circularG\"></div> \
                    <div id=\"circularG_7\" class=\"circularG\"></div> \
                    <div id=\"circularG_8\" class=\"circularG\"></div> \
                    </div>";
    }else{
        busyHtml = "";
    }
    
    //Assign Busy HTML to current page
    document.getElementById("busy").innerHTML=busyHtml;
}

// function checkSession() {
//     var xmlhttp = new XMLHttpRequest();
//     xmlhttp.onreadystatechange = function () {
//       if (xmlhttp.responseText == "timedOut") {
//         window.location.href = '/logIn/index.php?action=logOut';
//       } else if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
//         setTimeout(function(){ 
//             if(!xmlhttp.responseText) {
//                 console.log('Dead');
//                 window.location.href = '/logIn/index.php?action=logOut';
//             }else {
//                 console.log('Alive');
//                 checkSession();
//             }
//         }, 10000);
//       }
//     };
//     xmlhttp.open("GET", "/login/index.php?action=checkSession");
//     xmlhttp.send();
// }

// checkSession();


// Auto Logout (TimeOut)

// function idleTimer() {
//     toast('go');
//     if($_SESSION['logOut'] == false) {
//         setTimeout(idleTimer, 5000);  // time is in milliseconds (1000 is 1 second)
//     }else {
//         toast('die');
//     }
//  }

//  // Logout the user.
//  function logout() {
//     window.location.href = '/logIn/index.php?action=logOut';
// }


function autoReport(ask = false) {

  if(ask) {
    var reportNum = document.getElementById('report').value
    try { var start = document.getElementById('start').value }catch { var start = false; }
    try { var end = document.getElementById('end').value }catch { var end = false; }
    try { var skuNumber = document.getElementById('skuNumber').value }catch { var skuNumber = false; }
    try { var partNumber = document.getElementById('partNumber').value }catch { var partNumber = false; }

    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function () {
      if (xmlhttp.responseText == "timedOut") {
        // window.location.href = '/logIn/index.php?action=logOut';
        // alert('Timed Out');
      }else if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
  
        // alert(xmlhttp.responseText);
  
        buildAutoReportOverlay(xmlhttp.responseText);
        
        busyIndicator(false);
      }
    };
  
    xmlhttp.open(
      "GET", "/reports/index.php?action=autoReport&report=" + reportNum + "&start=" + start + "&end=" + end + "&skuNumber=" + skuNumber + "&partNumber=" + partNumber
    );
    xmlhttp.send();
  
    busyIndicator(true);
  }else {

    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function () {
      if (xmlhttp.responseText == "timedOut") {
        // window.location.href = '/logIn/index.php?action=logOut';
        // alert('Timed Out');
      }else if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
  
        // alert(xmlhttp.responseText);
  
        buildOverlay(xmlhttp.responseText);
        
        busyIndicator(false);
      }
    };
  
    xmlhttp.open(
      "GET", "/reports/index.php?action=customReportList"
    );
    xmlhttp.send();
  
    busyIndicator(true);
  }
}

function updateAutoReportVars() {
  var id = document.getElementById('report').value
  var vars = document.getElementById('vars');

  var xmlhttp = new XMLHttpRequest();
  xmlhttp.onreadystatechange = function () {
    if (xmlhttp.responseText == "timedOut") {
      // window.location.href = '/logIn/index.php?action=logOut';
      // alert('Timed Out');
    }else if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {

      // alert(xmlhttp.responseText);

      vars.innerHTML = xmlhttp.responseText;
      
      busyIndicator(false);
    }
  };

  xmlhttp.open(
    "GET", "/reports/index.php?action=updateAutoReportVars&id="+id
  );
  xmlhttp.send();

  busyIndicator(true);

  vars.innerHTML = '';
}

function exportAutoReport() {
  window.location.href = "/reports/index.php?action=exportCustomReport";
}

function submitCustomReport() {
    var id = document.getElementById('report').value
    var cnt = document.getElementById('varCnt').value;
    var varNames = document.getElementById('varNames').value;

    var array = [];

    for (var i = 0; i < cnt; i++) {
      var data = document.getElementById('varNum'+i).value;
      if(data == 'on') {
        var data = document.getElementById('varNum'+i).checked;
        data = data ? 1 : 0;
      }
      array.push(data);
    }

    // alert(id);
    // alert(array);

    data = JSON.stringify(array);

    // alert(data);

    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function () {
      if (xmlhttp.responseText == "timedOut") {
        // window.location.href = '/logIn/index.php?action=logOut';
        // alert('Timed Out');
      }else if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
  
        // alert(xmlhttp.responseText);

        if(xmlhttp.responseText == -1) {
          
        }

        buildAutoReportOverlay(xmlhttp.responseText);
        
        // closeOverlayGlobal();
        
        busyIndicator(false);
      }
    };
  
    xmlhttp.open(
      "GET", "/reports/index.php?action=buildCustomReport&id=" + id + "&vars=" + data + "&varNames=" + varNames
    );
    xmlhttp.send();
  
    busyIndicator(true);

}

function loadingBtn(a) {
  if(a) {
    var loading = document.getElementById('loading');
    loading.innerHTML = '<p class="rotate"><i class="bi bi-arrow-repeat"></i></p>';
  }
}

   // Toast Message Handling
  function toast($message, $type = 'error') {
    var audio = new Audio('/audio/BeepEnd.wav');
    audio.play();

    var x = document.getElementById('snackbar');

    if($type == 'danger') {
        x.classList.add('warnToast'); 
    }else if($type == 'bad' || $type == 'error') {
        x.classList.add('badToast');
    }else if($type == 'success') {
        x.classList.add("successToast");
    }else if($type == 'info') {
        x.classList.add('infoToast');
    }

    x.innerHTML = $message;
    
    // Add the "show" class to DIV
    x.classList.add("show");
    
    // After 10 seconds, remove the show class from DIV
    setTimeout(function(){ 
      x.className = x.classList.remove("show"); 
      if($type != false) {
        x.className = x.classList.remove($type + 'Toast'); 
      }
    }, 10000);
  }

  function dismissToast(type) {
    var x = document.getElementById('snackbar');
    setTimeout(function(){ 
      x.className = x.classList.remove("show"); 
      if(type != false) {
        x.className = x.classList.remove(type + 'Toast'); 
      }
    }, 8000);
  }

  function dismissToastNow() {
    var x = document.getElementById('snackbar');
    x.classList.remove("show");
  }


  function goToWorkOrder(input = false, search = false) {
  
    // 138130

    if(!input) {
      overlay(
        '<div>' +
        '<h1>Go To WO</h1><br>' +
        '<div id="errorMessage" style="color: red;"></div>' +
        '<input onkeydown="Javascript: if (event.keyCode==13) goToWorkOrder(true);" id="workOrderNumberInput" placeholder="Enter Work Order Number..." value=""><br>' +
        '<a class="bs-btn btn-blue" onclick="goToWorkOrder(true)">Go</a>', false, true
      )

      document.getElementById('workOrderNumberInput').focus();

    }else {

        var workOrderNumber = !search ? document.getElementById('workOrderNumberInput').value : search;
        
        if(window.location.pathname != '/production/index.php') {
          window.location.replace('/production/index.php?action=productionSchedule');
        }

        var xmlhttp=new XMLHttpRequest();
        xmlhttp.onreadystatechange=function() {
            if(xmlhttp.responseText == 'timedOut'){
                window.location.href = "/production";
            }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {

                // alert(xmlhttp.responseText);

                const response = xmlhttp.responseText;
                
                if(!response) {
                  toast('Invalid Work Order #');
                }else {
                
                  // Close Overlay
                  closeOverlayGlobal();
                  
                  if(response != -1) {
                    prodScheduleSearch(xmlhttp.responseText, false, false, true, scrollIn);
                  }else {
                    scrollIn();
                  }

                  function scrollIn() {
                    const line = document.getElementById('woNum_'+workOrderNumber);

                    line.scrollIntoView({
                      behavior: 'smooth',
                      block: 'center'
                    })
                    
                    setTimeout(function(){ 
                      var line = document.getElementById('woNum_'+workOrderNumber);
                      if(line != null) {
                          line.classList.add('yellowInBG'); 
                          setTimeout(function(){ line.className = line.className.replace("yellowInBG", ""); }, 5000);
                      }
                    }, 1200);
                  }
                }
     
                busyIndicator(false);
            }
        };
    
        xmlhttp.open("GET","/production/index.php?action=workOrder2&workOrderNumber="+workOrderNumber);
        xmlhttp.send();
    
        busyIndicator(true);
    }
}

function prodScheduleSearch(search, oldDate = false, workOrderNumber = false, goToWO = false, callback = null) {
    
  //  alert(search);
  //    alert(woNo);
      
  var xmlhttp=new XMLHttpRequest();
      
  xmlhttp.onreadystatechange=function() {
      if(xmlhttp.responseText == 'timedOut'){
          window.location.href = "/production";
      }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {
          
          if(oldDate != false) {
              toast('WO#: ' + workOrderNumber + ' was scheduled to today from ' + oldDate, 'info'); 
          }

          if(!goToWO) {
            setTimeout(function(){ 
                var line = document.getElementById('woNum_'+workOrderNumber);
                if(line != null) {
                    line.classList.add('blueIn'); 
                    setTimeout(function(){ line.className = line.className.replace("blueIn", ""); }, 5000);
                }
            }, 1500);
          }


          document.getElementById("productionItems").innerHTML = xmlhttp.responseText;

          if(callback) callback();    

          busyIndicator(false);
      }
  };

  xmlhttp.open("GET","/production/index.php?action=prodSearch&search="+search);
  xmlhttp.send();

  busyIndicator(true);

  if(goToWO) {
      return true;
  }

}

function deleteTableRow(rowid) {   
    var row = document.getElementById(rowid);
    row.parentNode.removeChild(row);
}

function hasDuplicates(arr) {
  return arr.some( function(item) {
      return arr.indexOf(item) !== arr.lastIndexOf(item);
  });
}

function printShipping(skuNumber, comp) {

  var xmlhttp = new XMLHttpRequest();
  xmlhttp.onreadystatechange = function () {
    if (xmlhttp.responseText == "timedOut") {
      // Timeout
    }else if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {

      // print(xmlhttp.responseText);
      printReport2(null, xmlhttp.responseText, () => {
        var line = document.getElementById('skuLine_'+skuNumber);
        if(line) {
          line.scrollIntoView({
            behavior: 'smooth',
            block: 'center'
          });
        }
      });

      busyIndicator(false);
    }
  };

  xmlhttp.open(
    "GET", "/forklift/index.php?action=printShipping&skuNumber="+skuNumber+'&comp='+comp
  );
  xmlhttp.send();

  busyIndicator(true);
}

function loadModuleModal(module) {
  var xmlhttp=new XMLHttpRequest();
  xmlhttp.onreadystatechange=function() {
      if(xmlhttp.responseText == 'timedOut'){
          window.location.href = "/production";
      }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {
          // alert(xmlhttp.responseText);
          overlay(xmlhttp.responseText, false, true);
          busyIndicator(false);
      }
  };

  // TODO This action should be relocated to a more global spot at some point

  xmlhttp.open("GET","/custom/index.php?action=moduleModal&module="+module);
  xmlhttp.send();
  busyIndicator(true);
}

function completeWOModal() {
  const woNo = document.getElementById('woNumInput').value;
  var xmlhttp=new XMLHttpRequest();
  xmlhttp.onreadystatechange=function() {
      if(xmlhttp.responseText == 'timedOut'){
          window.location.href = "/production";
      }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {
          // alert(xmlhttp.responseText);
          overlay(xmlhttp.responseText, false, true);
          busyIndicator(false);
      }
  };
  xmlhttp.open("GET","/production/index.php?action=completeWOEx&woNo="+woNo);
  xmlhttp.send();
  busyIndicator(true);
}

function completeProdItem(woNo, compQty, skuNumber, desc) {

  var xmlhttp=new XMLHttpRequest();
      
  xmlhttp.onreadystatechange=function() {
      if(xmlhttp.responseText == 'timedOut'){
          window.location.href = "/production";
      }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {
          
          document.getElementById("myonoffswitch").checked = false;

          overlay(xmlhttp.responseText, false, false);

          busyIndicator(false);
      }
  };

  xmlhttp.open("GET","/production/index.php?action=newCompleteWO&woNo="+woNo+'&compQty='+compQty+'&fromProd=true&skuNumber='+skuNumber+'&desc='+desc);
  xmlhttp.send();

     busyIndicator(true);
}

function autoReschedule() {
  var xmlhttp=new XMLHttpRequest();
  xmlhttp.onreadystatechange=function() {
      if(xmlhttp.responseText == 'timedOut'){
          window.location.href = "/production";
      }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {
          // alert(xmlhttp.responseText);
          // console.log(xmlhttp.responseText);
          location.replace("/production/index.php?action=productionSchedule");
          busyIndicator(false);
      }
  };
  xmlhttp.open("GET","/production/index.php?action=autoReschedule");
  xmlhttp.send();
  busyIndicator(true);
}

function updateWO() {
  var xmlhttp=new XMLHttpRequest();

  var workOrderNumber = document.getElementById('workOrderNumber').value;
  var crewLeader = document.getElementById('crewLeader').value;
  crewLeader = crewLeader == null ? -1 : crewLeader
  var completionDate = document.getElementById('completionDate').value;
  var crewCount = document.getElementById('crewCount').value;
  var quantity = document.getElementById('quantity').value;
  var timeStudyH = document.getElementById('elapsedHrs').value;
  var timeStudyM = document.getElementById('elapsedMins').value;
  var timeStudyS = document.getElementById('elapsedSecs').value;

  var hourtomin = timeStudyH * 60;
  var totalminutes = parseInt(hourtomin) + parseInt(timeStudyM);
  var elapsedSeconds = parseInt((totalminutes * 60)) + parseInt(timeStudyS);

  xmlhttp.onreadystatechange=function() {
      if(xmlhttp.responseText == 'timedOut'){
          window.location.href = "/production";
      }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {
          // alert(xmlhttp.responseText);
          if(parseInt(xmlhttp.responseText) == 0 ) {
            toast('WO Failed to Update', 'bad');
          }else {
            var crew = document.getElementById('crewCnt_'+workOrderNumber);
            if(crew) {
              crew.value = crewCount;
            }  
            toast('WO Successfully Updated', 'success');
          }
          
          var onoff = document.getElementById("myonoffswitch");
          if(onoff) {
            onoff.checked = false;
          }
          closeOverlayGlobal();
          busyIndicator(false);
      }
  };

  xmlhttp.open("GET","/production/index.php?action=updateWO&workOrderNumber="+workOrderNumber+'&crewLeader='+crewLeader+'&completionDate='+completionDate+'&crewCount='+crewCount+'&quantity='+quantity+'&elapsedSeconds='+elapsedSeconds);
  xmlhttp.send();

     busyIndicator(true);
}

function filterTable() {
  var input, filter, table, tr, td, cell, i, j;
  var filterCnt = 0;
  input = document.getElementById("filterInput");
  filter = input.value.toUpperCase();
  table = document.getElementById("filterTable");
  tr = table.getElementsByTagName("tr");
  for (i = 1; i < tr.length; i++) {
    // Hide the row initially.
    tr[i].style.display = "none";
  
    td = tr[i].getElementsByTagName("td");
    for (var j = 0; j < td.length; j++) {
      cell = tr[i].getElementsByTagName("td")[j];
      if (cell) {
        if (cell.innerHTML.toUpperCase().indexOf(filter) > -1) {
          tr[i].style.display = "";
          filterCnt++;
          break;
        } 
      }
    }
    document.getElementById('filterCnt').innerHTML = 'Cnt: ' + filterCnt
  }
}

function sortTable(n, table = false) {
  var tableHeader, table, rows, switching, i, x, y, shouldSwitch, dir, switchcount = 0;
  tableHeader = !table ? 1 : 0;
  table = !table ? document.getElementById("filterTable") : document.getElementById(table);

  switching = true;
  //Set the sorting direction to ascending:
  dir = "desc"; 

  /*Make a loop that will continue until
  no switching has been done:*/
  while (switching) {
    //start by saying: no switching is done:
    switching = false;
    rows = table.rows;

    /*Loop through all table rows (except the
    first, which contains table headers):*/
    for (i = 1; i < (rows.length - 1); i++) {
      //start by saying there should be no switching:
      shouldSwitch = false;
      /*Get the two elements you want to compare,
      one from current row and one from the next:*/
      x = rows[i].getElementsByTagName("TD")[n];
      y = rows[i + 1].getElementsByTagName("TD")[n];
      /*check if the two rows should switch place,
      based on the direction, asc or desc:*/
      if (dir == "asc") {
        if (x.innerHTML.toLowerCase() > y.innerHTML.toLowerCase()) {
          //if so, mark as a switch and break the loop:
          shouldSwitch= true;
          break;
        }
      } else if (dir == "desc") {
        if (x.innerHTML.toLowerCase() < y.innerHTML.toLowerCase()) {
          //if so, mark as a switch and break the loop:
          shouldSwitch = true;
          break;
        }
      }
    }
    if (shouldSwitch) {
      /*If a switch has been marked, make the switch
      and mark that a switch has been done:*/
      rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
      switching = true;
      //Each time a switch is done, increase this count by 1:
      switchcount ++;      
    } else {
      /*If no switching has been done AND the direction is "asc",
      set the direction to "desc" and run the while loop again.*/
      if (switchcount == 0 && dir == "desc") {
        dir = "asc";
        switching = true;
      }
    }
  }

  // Remove All Other Arrows
  headerCnt = table.rows[0].cells.length;
  for (let i = 0; i < headerCnt; i++) {
    document.getElementById('sortDir'+(tableHeader ? table : '')+i).innerHTML = '';
  }

  // Edit Header Based on Selection
  var headerArrow = document.getElementById('sortDir'+(tableHeader ? table : '')+n);
  if(dir == 'asc') {
    headerArrow.innerHTML = '<i class="bi bi-arrow-up"></i>';
  }else {
    headerArrow.innerHTML = '<i class="bi bi-arrow-down"></i>';
  }
}

function formatDate(date) {
    var d = new Date(date),
        month = '' + (d.getMonth() + 1),
        day = '' + d.getDate(),
        year = d.getFullYear();

    if (month.length < 2) 
        month = '0' + month;
    if (day.length < 2) 
        day = '0' + day;

    return [year, month, day].join('-');
}

function printData() {
  qz.websocket.connect().then(function() { 
      return qz.printers.find("zebra"); // Pass the printer name into the next Promise
  }).then(function(printer) {
      var config = qz.configs.create(printer); // Create a default config for the found printer
      var data = ['^XA^FO50,50^ADN,36,20^FDRAW ZPL EXAMPLE^FS^XZ']; // Raw ZPL
      return qz.print(config, data);
  }).catch(function(e) { console.error(e); });
}

function printFile(file) {

  var options = {
    size: {
      width: '4in',
      height: '6in'
    }
  }

  qz.websocket.connect().then(function() { 
    return qz.printers.find("GK420d"); // Pass the printer name into the next Promise
  }).then(function(printer, options) {
    var config = qz.configs.create(printer);
    var data = [{
      type: 'pixel',
      format: 'html',
      flavor: 'file',
      data: '/module/tag.php'
      }];
    qz.print(config, data).catch(function(e) { console.error(e); });
  }).catch(function(e) { console.error(e); });

}

function printHTML() {
  qz.websocket.connect().then(function() { 
      return qz.printers.find("GK420d"); // Pass the printer name into the next Promise
  }).then(function(printer) {
      var config = qz.configs.create(printer);
      var data = [{
      type: 'pixel',
      format: 'html',
      flavor: 'file', // or 'plain' if the data is raw HTML
      data: '/module/tag.php'
      }];
      qz.print(config, data).catch(function(e) { console.error(e); });
  }).catch(function(e) { console.error(e); });
}