function phyInvNav(action){   
    var xmlhttp=new XMLHttpRequest();

    closeOverlay();
    
    xmlhttp.onreadystatechange=function() {
        if(xmlhttp.responseText == 'timedOut'){
            window.location.href = "/manager";
        }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {
            var myArr = JSON.parse(xmlhttp.responseText);
            
            refreshInv(myArr);
            
            busyIndicator(false);
        }
    };
    
    xmlhttp.open("GET","/physicalInventory/index.php?action="+action);
    xmlhttp.send();
    
    busyIndicator(true);
}


function updateStatus(action, status, skuNumber){   
    var xmlhttp=new XMLHttpRequest();
    var el = document.getElementById(skuNumber);
    xmlhttp.onreadystatechange=function() {
        if(xmlhttp.responseText == 'timedOut'){
            window.location.href = "/manager";
        }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {
            var myArr = JSON.parse(xmlhttp.responseText);
            if(el) {
                //el.innerHTML = "";
                el.className += el.className ? ' anim-scale-out' : 'anim-scale-out';
                
                setTimeout(function(){el.classList.add("hide")}, 1000);
            }
            //Clear Overlay View if on
            
            document.getElementById('physicalInventoryDetails').innerHTML = "";
            document.getElementById('physicalInventoryHeader').innerHTML = myArr[0];
        }
    };
    
    xmlhttp.open("GET","/physicalInventory/index.php?action=update&view="+action+"&status="+status+"&skuNumber="+skuNumber);
    xmlhttp.send();
}

function refreshInv(myArr){
    document.getElementById('physicalInventoryHeader').innerHTML = myArr[0];
    document.getElementById('physicalInventory').innerHTML = myArr[1];
}

function phyInvDetails(skuNumber)
{
    closeOverlay();

    var xmlhttp=new XMLHttpRequest();
    xmlhttp.onreadystatechange=function() {
        if(xmlhttp.responseText == 'timedOut'){
            window.location.href = "/manager";
        }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {
            
            document.getElementById('physicalInventoryDetails').innerHTML = xmlhttp.responseText;
            document.getElementById('phyInventoryPanel').innerHTML = document.getElementById(skuNumber).innerHTML;
            
            document.body.style.overflow = "hidden";
            busyIndicator(false);
        }
    };
    
    xmlhttp.open("GET","/physicalInventory/index.php?action=details&skuNumber="+skuNumber);
    xmlhttp.send();
    
    busyIndicator(true);
}

function phyInvDetailsClose(){
    document.getElementById('physicalInventoryDetails').innerHTML = "";
    document.body.style.overflow = "auto";
}

function reloadInventory(){
    window.location.href = "/physicalInventory";
}

function phyInvSkuDetails(){
    
    document.getElementById('overlay').className += document.getElementById('overlay').className ? ' anim-flip-horizontal' : 'anim-flip-horizontal';
    document.getElementById('overlay').innerHTML = "<h1>BackSide</h1>";
}

function selectRecount(skuNumber, audit){
    
    var xmlhttp=new XMLHttpRequest();
    xmlhttp.onreadystatechange=function() {
        if(xmlhttp.responseText == 'timedOut'){
            window.location.href = "/manager";
        }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {
            var myArr = JSON.parse(xmlhttp.responseText);

            document.getElementById('physicalInventoryDetails').innerHTML = myArr[1];
            document.getElementById('phyInventoryPanel').innerHTML = document.getElementById(skuNumber).innerHTML;
            
            document.getElementById(skuNumber).innerHTML = myArr[0];
            document.getElementById(skuNumber).className = "";
            busyIndicator(false);
            
            document.body.style.overflow = "hidden";
            
            setFocusToTextBox();
        }
    };
    
    xmlhttp.open("GET","/physicalInventory/index.php?action=selectRecount&skuNumber="+skuNumber+"&audit="+audit);
    xmlhttp.send();
    
    busyIndicator(true);
}



function resumeRecount(skuNumber, audit){
    var xmlhttp=new XMLHttpRequest();
    xmlhttp.onreadystatechange=function() {
        if(xmlhttp.responseText == 'timedOut'){
            window.location.href = "/manager";
        }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {

            var myArr = JSON.parse(xmlhttp.responseText);
            
            document.getElementById('physicalInventoryDetails').innerHTML = myArr[1];
            document.getElementById('phyInventoryPanel').innerHTML = myArr[0];
            
            busyIndicator(false);
            
            document.body.style.overflow = "hidden";
            
            setFocusToTextBox();
        }
    };

    xmlhttp.open("GET","/physicalInventory/index.php?action=resumeRecount&skuNumber="+skuNumber+"&audit="+audit);
    xmlhttp.send();
    
    busyIndicator(true);
}

function releaseRecount(skuNumber){
    
    var xmlhttp=new XMLHttpRequest();
    xmlhttp.onreadystatechange=function() {
        if(xmlhttp.responseText == 'timedOut'){
            window.location.href = "/manager";
        }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {
            var el = document.getElementById(skuNumber);
            var releasedPanel = xmlhttp.responseText;
            
            //Change Panel
            el.innerHTML = releasedPanel;
            el.className = 'phyInventoryPanel anim-scale-up';
            
            busyIndicator(false);
        }
    };
    
    xmlhttp.open("GET","/physicalInventory/index.php?action=releaseRecount&skuNumber="+skuNumber);
    xmlhttp.send();
    
    busyIndicator(true);
}

function reCountAction(serialID, action){
    var xmlhttp=new XMLHttpRequest();
    xmlhttp.onreadystatechange=function() {
        if(xmlhttp.responseText == 'timedOut'){
            window.location.href = "/manager";
        }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {
            var el = document.getElementById(serialID);
            
            //Change Panel
            el.innerHTML = xmlhttp.responseText;
            el.className += ' anim-flip-V360';
            
            busyIndicator(false);
            
        }
    };
    xmlhttp.open("GET","/physicalInventory/index.php?action="+action+"&serialID="+serialID);
    xmlhttp.send();
    
    busyIndicator(true);
}

function recountScan(){
    var serialID = document.getElementById("recountScan").value;
    var xmlhttp=new XMLHttpRequest();
    xmlhttp.onreadystatechange=function() {
        if(xmlhttp.responseText == 'timedOut'){
            window.location.href = "/manager";
        }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {
            if(xmlhttp.responseText == 1){
                document.getElementById(serialID).scrollIntoView();
                document.getElementById(serialID).focus();
                document.getElementById("recountScan").value = '';
                document.getElementById('errorMessage').innerHTML = '';
                
                
            }else{
                document.getElementById('errorMessage').innerHTML = 'SERIAL IS NOT ON RECOUNT LIST';
                document.getElementById("recountScan").value = '';
            }
            busyIndicator(false);
        }
    };
    
    xmlhttp.open("GET","/physicalInventory/index.php?action=recountScan&serialID="+serialID);
    xmlhttp.send();
    
    busyIndicator(true);
}

function setFocusToTextBox(){
    document.getElementById("recountScan").focus();
}


function addRecount(serialID){
    reCountAction(serialID, 'RC-found');
     //Create field IDs
    var qtyField = "newQty"+serialID;
    var recountExtID = "recountExt"+serialID;
    
    //Get Qty From submitted field
    var newQty = document.getElementById(qtyField).value;

    var xmlhttp=new XMLHttpRequest();
    xmlhttp.onreadystatechange=function() {
        if(xmlhttp.responseText == 'timedOut'){
            window.location.href = "/manager";
        }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {
            var myArr = JSON.parse(xmlhttp.responseText);
            
            if(myArr[0] == 2){
                document.getElementById('errorMessage').innerHTML = 'CANNOT RECOUNT A \"NOT FOUND\" STATUS';
                
            }else if(myArr[0] == 3){
                document.getElementById('errorMessage').innerHTML = 'TRANSACTION FAILED';
                
            }else if(myArr[0] == 0){
                document.getElementById(serialID).innerHTML = myArr[1];
                document.getElementById(recountExtID).innerHTML = myArr[2];
                document.getElementById(qtyField).value = "";
                
            }else if(myArr[0] == 1){
                document.getElementById(recountExtID).innerHTML = myArr[1];
                document.getElementById(qtyField).value = "";
            }else{
                document.getElementById('errorMessage').innerHTML = 'TRANSACTION FAILED';
                document.getElementById(qtyField).value = "";
            }
            busyIndicator(false);
        }
    };
    
    xmlhttp.open("GET","/physicalInventory/index.php?action=addRecount&serialID="+serialID+"&newQty="+newQty);
    xmlhttp.send();
    
    busyIndicator(true);
}


function submitRecount(skuNumber){
    var xmlhttp=new XMLHttpRequest();
    xmlhttp.onreadystatechange=function() {
        if(xmlhttp.responseText == 'timedOut'){
            window.location.href = "/manager";
        }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {
            if(xmlhttp.responseText > 0){
                document.getElementById('errorMessage').innerHTML = 'RECOUNT IS NOT COMPLETE';
            }else if(xmlhttp.responseText < 0){
                document.getElementById('errorMessage').innerHTML = 'TRANSACTION FAILED';
            }else{
                window.location.assign("/physicalInventory/index.php?action=recount");
            }
            busyIndicator(false);
        }
    };
    
    xmlhttp.open("GET","/physicalInventory/index.php?action=submitRecount&skuNumber="+skuNumber);
    xmlhttp.send();
    
    busyIndicator(true);
}

function useRecount(serialID, recountQty){
    var xmlhttp=new XMLHttpRequest();
    xmlhttp.onreadystatechange=function() {
        if(xmlhttp.responseText == 'timedOut'){
            window.location.href = "/manager";
        }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {
            var myArr = JSON.parse(xmlhttp.responseText);
            if(myArr[0] == 0){
                document.getElementById('errorMessage').innerHTML = 'NOT RECOUNTED';
            }else{
                document.getElementById(myArr[3]).innerHTML = myArr[0];
                document.getElementById('phyInventoryPanel').innerHTML = myArr[0];
                document.getElementById(serialID).innerHTML = myArr[1];
                document.getElementById('recountExt'+serialID).innerHTML = myArr[2];
            }
             busyIndicator(false);
        }
    };
    
    xmlhttp.open("GET","/physicalInventory/index.php?action=useRecount&serialID="+serialID+"&recountQty="+recountQty);
    xmlhttp.send();
    
    busyIndicator(true);
}


function complete(){
    var xmlhttp=new XMLHttpRequest();
    xmlhttp.onreadystatechange=function() {
        if(xmlhttp.responseText == 'timedOut'){
            window.location.href = "/manager";
        }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {
            // document.getElementById('physicalInventoryDetails').innerHTML = xmlhttp.responseText;
            overlay(xmlhttp.responseText);
            busyIndicator(false);
        }
    };
    
    xmlhttp.open("GET","/physicalInventory/index.php?action=completeStatus");
    xmlhttp.send();
    
    busyIndicator(true);
}

function correction(){
    document.getElementById('submitButton').innerHTML = '<div class="text-center">Please Be Patient</div>';
    
    //Run Corrections which will start completion when finished
    // new Promise(function(fulfill, reject) {
    //     statusCorrection(completionProgress());
    //     fulfill(true);
    // }).then(function(fulfill, reject) {
    //     binCorrection(completionProgress());
    //     fulfill(true);
    // }).then(function(result) {
    //     completionProgress()
    //     completePhysicalInventory();
    // })
    busyIndicator(true);

    let promise = new Promise(function(resolve, reject) {
        statusCorrection();
        resolve();
    });
    
    promise.then(function(result) {
        let promise2 = new Promise(function(resolve, reject) {
            binCorrection();
            resolve();
        });

        promise2.then(function(result) {
            let promise3 = new Promise(function(resolve, reject) {
                qtyCorrection();
                resolve();
            });

            promise3.then(function(result) {
                completePhysicalInventory();
                return
            })
        })
    }); 

    busyIndicator(false);
    
}

function completionProgress() {
    var progressBar = document.getElementById('completionProgress');
    var progressLabel = document.getElementById('completionProgress-label');

    progressBar.value++;
    progressLabel.innerHTML = Math.round((progressBar.value * 33.33)) + '%';
}

function statusCorrection(){
    var xmlhttp=new XMLHttpRequest();
    
    xmlhttp.onreadystatechange=function() {
        if(xmlhttp.responseText == 'timedOut'){
            window.location.href = "/manager";
        }else if (xmlhttp.readyState==4 && xmlhttp.status==200){
            var message = document.getElementById('statusChangeError');
            var res = xmlhttp.responseText;

            // Display Errors
            message.innerHTML += res ? res : 'None';

            // Increment Progress Bar
            completionProgress();
        }
    };
    
    xmlhttp.open("GET","/physicalInventory/index.php?action=statusCorrection");
    xmlhttp.send();
    
}

function binCorrection(){
    var xmlhttp=new XMLHttpRequest();
    xmlhttp.onreadystatechange=function() {
        if(xmlhttp.responseText == 'timedOut'){
            window.location.href = "/manager";
        }else if (xmlhttp.readyState==4 && xmlhttp.status==200){
            var message = document.getElementById('WriteInError');
            var res = xmlhttp.responseText;
            
            // Display Errors
            message.innerHTML += res ? res : 'None';

            // Increment Progress Bar
            completionProgress();
        }
    };
    
    xmlhttp.open("GET","/physicalInventory/index.php?action=binCorrection");
    xmlhttp.send();
    
}

function qtyCorrection(){
    var xmlhttp=new XMLHttpRequest();
    xmlhttp.onreadystatechange=function() {
        if(xmlhttp.responseText == 'timedOut'){
            window.location.href = "/manager";
        }else if (xmlhttp.readyState==4 && xmlhttp.status==200){
            var message = document.getElementById('QtyChangeError');
            var res = xmlhttp.responseText;
            
            // Display Errors
            message.innerHTML += res ? res : 'None';
            cnt.innerHTML = '';

            // Increment Progress Bar
            completionProgress();
        }
    };
    
    xmlhttp.open("GET","/physicalInventory/index.php?action=qtyCorrection");
    xmlhttp.send();


    // var xmlhttp=new XMLHttpRequest();
    // xmlhttp.onreadystatechange=function() {
    //     if(xmlhttp.responseText == 'timedOut'){
    //         window.location.href = "/manager";
    //     }else if (xmlhttp.readyState==4 && xmlhttp.status==200){
    //         var error = JSON.parse(xmlhttp.responseText);
    //         var message = document.getElementById('QtyChangeError').innerHTML;
                
    //             if(error[0] != 'No Errors'){
    //                 for(i = 0; i < error.length - 1; i++){
    //                     message.concat(", " . error[i]) ;
    //                 }
    //             }

    //             document.getElementById('QtyChangeError').innerHTML = message;
                
    //         if(error[1] != 1){
    //             var totalComplete = document.getElementById('QtyChange').innerHTML + ' *';
    //             document.getElementById('QtyChange').innerHTML = totalComplete;
    //             qtyCorrection();
    //         }else{
    //             if (message == 'Errors:'){
    //                 document.getElementById('QtyChangeError').innerHTML = 'Errors: None';
    //             }
    //             document.getElementById('QtyChange').innerHTML = ": Complete";
                
    //             //Set physicalInventory Status to NULL
    //             completePhysicalInventory();
    //         }
    //     }
    // };
    
    // xmlhttp.open("GET","/physicalInventory/index.php?action=qtyCorrection");
    // xmlhttp.send();
    
}


function completePhysicalInventory() {
    var xmlhttp=new XMLHttpRequest();
    var statusChange = document.getElementById('statusChange').innerHTML;
    var writeIn = document.getElementById('WriteIn').innerHTML;
    // var qtyChange = document.getElementById('QtyChange').innerHTML;
    
    xmlhttp.onreadystatechange=function() {
        if(xmlhttp.responseText == 'timedOut'){
            window.location.href = "/manager";
        }else if (xmlhttp.readyState==4 && xmlhttp.status==200){
            //Set Button to Ok
            document.getElementById('submitButton').innerHTML = '<a class="bs-btn btn-green" onclick="reloadInventory()" title="Close">Close</a>';
            completionProgress();
            toast('Inventory Complete.', 'success');
        }
    };
    
    xmlhttp.open("GET","/physicalInventory/index.php?action=completeInventory");
    xmlhttp.send();
    
}


function startInventory(){
    
      var xmlhttp=new XMLHttpRequest();
    
    
    
    xmlhttp.onreadystatechange=function() {
        if(xmlhttp.responseText == 'timedOut'){
            window.location.href = "/manager";
        }else if (xmlhttp.readyState==4 && xmlhttp.status==200){
            
            
            document.getElementById('physicalInventoryDetails').innerHTML  = '<div class="overlay anim-scale-up" id="overlay">' + xmlhttp.responseText + '</div>';
   
            
        }
    };
    
    xmlhttp.open("GET","/physicalInventory/index.php?action=startInventory");
    xmlhttp.send();         
}

function setupInventory(){
    var xmlhttp=new XMLHttpRequest();
    
    
    
    xmlhttp.onreadystatechange=function() {
        if(xmlhttp.responseText == 'timedOut'){
            window.location.href = "/manager";
        }else if (xmlhttp.readyState==4 && xmlhttp.status==200){
            if(xmlhttp.responseText){
                window.location.href = "/physicalInventory/index.php";
            }else{
                document.getElementById('clearInvMessage').innerHTML = 'Reset Failed';
            }
            
        }
    };
    
    xmlhttp.open("GET","/physicalInventory/index.php?action=setUpInventory");
    xmlhttp.send();
    
    
}

function resetInventory() {
    var xmlhttp=new XMLHttpRequest();
    
    xmlhttp.onreadystatechange=function() {
        if(xmlhttp.responseText == 'timedOut'){
            window.location.href = "/manager";
        }else if (xmlhttp.readyState==4 && xmlhttp.status==200){

            location.reload();

        }
    };
    
    xmlhttp.open("GET","/physicalInventory/index.php?action=resetInventory");
    xmlhttp.send();
}

function newBinWriteIn(serialID){
    var xmlhttp=new XMLHttpRequest();
    var binID = document.getElementById('newBin' + serialID).value;
    

    
        xmlhttp.onreadystatechange=function() {
        if(xmlhttp.responseText == 'timedOut'){
            window.location.href = "/manager";
        }else if (xmlhttp.readyState==4 && xmlhttp.status==200){
            var myArr = JSON.parse(xmlhttp.responseText);
            // alert(xmlhttp.responseText);
            if(myArr[0]){
                document.getElementById("wi"+serialID).innerHTML = myArr[1];
                document.getElementById("wi"+serialID).className += " highlight";
                
                idValue = "wi"+serialID;
                className = "writeInTrue";
                document.getElementById("newBin"+serialID).value = null;
                
                setTimeout(function() {
                    refreshSummary(idValue, className);
                },1000);
            }else{
                // alert(myArr[1]);
            }
            
            
        }
    };

    // alert(serialID);
    // alert(binID);

    xmlhttp.open("GET","/physicalInventory/index.php?action=newBinWriteIn&serialID="+serialID+"&binID="+binID);
    xmlhttp.send();
    
}


function refreshSummary(idValue, className){
    document.getElementById(idValue).className = className;
}

function uncheckAll(){ 

    document.getElementById('checkBoxBtn').innerHTML = '<h2>Select Category <a href="#" id="invertChecks" onclick="checkAll();">Check-All</a></h2>';

    var w = document.getElementsByTagName('input'); 

    for(var i = 0; i < w.length; i++){ 
        if(w[i].type=='checkbox'){
          w[i].checked = false;
        }
    }
    
}

function checkAll() {

    document.getElementById('checkBoxBtn').innerHTML = '<h2>Select Category <a href="#" id="invertChecks" onclick="uncheckAll();">Uncheck-All</a></h2>';

    var w = document.getElementsByTagName('input'); 

    for(var i = 0; i < w.length; i++){ 
        if(w[i].type=='checkbox'){
          w[i].checked = true;
        }
    }
}

function reportFilter() {    

    var customerID = document.getElementById('customerFilter').value;

    var xmlhttp=new XMLHttpRequest();

    var cnt = 0;
    var cats;

    var sbox = Array.from(document.getElementsByName( "filter" ) );

    sbox.forEach( function( v ) {
        if (cnt == 0 && v.checked){
            cats = "(itf.CAT = " + "'" + v.value + "'";
            cnt++;
        }else{
            if (v.checked ) {
                cats += " || itf.CAT = " + "'" + v.value + "'";
                cnt++;
            }
        }
        
    });
    if(customerID != "") {
        cats = cats + ') AND c.companyID = \'' + customerID + '\'';
    }else{
        cats = cats + ')';
    }
    
        xmlhttp.onreadystatechange=function() {
        if(xmlhttp.responseText == 'timedOut'){
            window.location.href = "/manager";
        }else if (xmlhttp.readyState==4 && xmlhttp.status==200){
            
            // document.getElementById('message').innerHTML = xmlhttp.responseText;
            closeOverlay();
            window.location.href = 'http://whitney.localhost/physicalInventory/index.php';
        }
    };
    
    
    // alert(cats);
    
    
    xmlhttp.open("GET","/physicalInventory/index.php?action=reportFilter&cats="+cats);
    xmlhttp.send();
    
}

function closeOverlay() {
    document.getElementById("physicalInventoryDetails").innerHTML = "";
    try {
        document.getElementById("overlayPanel").innerHTML = "";
    }
    catch {}
}

function exportInventory(optionSelected = false) {

    optionSelected = optionSelected ? document.getElementById('exports').value : false;
    
    var xmlhttp=new XMLHttpRequest();
    xmlhttp.onreadystatechange=function() {
        if(xmlhttp.responseText == 'timedOut'){
            window.location.href = "/manager";
        }else if (xmlhttp.readyState==4 && xmlhttp.status==200){
            xmlhttp.responseText == 0 ? toast('No Inventory To Export', 'error') : overlay(xmlhttp.responseText);
            optionSelected && closeOverlayGlobal();
            busyIndicator(false);
        }
    };
    busyIndicator(true);
    xmlhttp.open("GET",'/physicalInventory/index.php?action=' + ( optionSelected ? 'exportInventory&option=' + optionSelected : 'exportInvOptions' ));
    xmlhttp.send();
    
}

function showLots(skuNumber) {
    var xmlhttp=new XMLHttpRequest();
    
    xmlhttp.onreadystatechange=function() {
        if(xmlhttp.responseText == 'timedOut'){
            window.location.href = "/manager";
        }else if (xmlhttp.readyState==4 && xmlhttp.status==200){   

            document.getElementById('overlayPanel').innerHTML = xmlhttp.responseText;

            busyIndicator(false);

        }
    };

    busyIndicator(true);
    
    xmlhttp.open("GET","/physicalInventory/index.php?action=showLots&skuNumber="+skuNumber);
    xmlhttp.send();
}