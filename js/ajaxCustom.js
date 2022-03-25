function uploadHeader(orderNumber, addReturn = false){

    clearError();
    var xmlhttp=new XMLHttpRequest();
    xmlhttp.onreadystatechange=function() {
        if(xmlhttp.responseText == 'timedOut'){
            window.location.href = "/manager";
        }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {

            // alert(xmlhttp.responseText);



            var myArr = JSON.parse(xmlhttp.responseText);
            document.getElementById('customHeader').innerHTML = myArr[0];
            document.getElementById('customMain').innerHTML = myArr[1];
            // document.getElementById('customRAside').innerHTML = "";
            // document.getElementById('customRAside').innerHTML = myArr[3];

            if(addReturn) {
                buildNewItemMenu(orderNumber);
            }
            // toast(myArr[9]);

            // document.getElementById('customMain').innerHTML = xmlhttp.responseText;

            busyIndicator(false);
            window.scrollTo(0, 0);
            
        }
    };
    
    xmlhttp.open("GET","/custom/index.php?action=uploadHeader&orderNumber="+orderNumber);
    xmlhttp.send();
    
    busyIndicator(true);
}

function returnToPending(orderNumber) {
    clearError();
    var xmlhttp=new XMLHttpRequest();
    xmlhttp.onreadystatechange=function() {
        if(xmlhttp.responseText == 'timedOut'){
        }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {
            setOrder(orderNumber, 2);
            busyIndicator(false);
        }
    };
    
    xmlhttp.open("GET","/custom/index.php?action=returnToPending&orderNumber="+orderNumber);
    xmlhttp.send();
    
    busyIndicator(true);
}

function setOrder(orderNumber, status){
    clearError();
    var xmlhttp=new XMLHttpRequest();
    xmlhttp.onreadystatechange=function() {
        if(xmlhttp.responseText == 'timedOut'){
            window.location.href = "/manager";
        }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {
            var myArr = JSON.parse(xmlhttp.responseText);
            
            if(myArr == 0){
                // document.getElementById('errorMessage').innerHTML = '<h2>All items require an assigned lot.</h2>';
                toast('All items require an assigned lot.', 'error');
            }else{
                document.getElementById('customLAside').innerHTML = myArr[0];
                toast('Status of Order#: ' + orderNumber + " is set to:  " + myArr[3], 'success');
            }
            
            if(status == 1){
                //load selected order
                document.getElementById('customHeader').innerHTML = myArr[1];
                document.getElementById('customMain').innerHTML = myArr[2];
                document.getElementById('customRAside').innerHTML = "";
            }else{
                //Clear menu
                document.getElementById('customHeader').innerHTML = '<h2>Order Header</h2>';
                document.getElementById('customMain').innerHTML = '<h2>Order Items</h2>';
                document.getElementById('customRAside').innerHTML = '<h2>Lots</h2>';
            }
            
            busyIndicator(false);
            window.scrollTo(0, 0);
        }
    };
    
    xmlhttp.open("GET","/custom/index.php?action=setOrder&orderNumber="+orderNumber+"&status="+status);
    xmlhttp.send();
    
    busyIndicator(true);
}

function reloadOrders(status){  

      var shipmentGroupID = document.getElementById('customerID').value;
//    alert("CustomerID : " + customerID + " Status : " + status);
    

    clearError();
    var xmlhttp=new XMLHttpRequest();
    xmlhttp.onreadystatechange=function() {
        if(xmlhttp.responseText == 'timedOut'){
            window.location.href = "/manager";
        }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {

            document.getElementById('customView').innerHTML = xmlhttp.responseText;
            
            busyIndicator(false);
            window.scrollTo(0, 0);
        }
    };

    xmlhttp.open("GET","/custom/index.php?action=reloadOrders&status="+status+"&shipmentGroupID="+shipmentGroupID);
    xmlhttp.send();
    
    busyIndicator(true);
}


function uploadBatch(pickPackID, itemNumber, orderNumber, orderQty, bal = 0){
    clearError();
    
    var xmlhttp=new XMLHttpRequest();
    xmlhttp.onreadystatechange=function() {
        if(xmlhttp.responseText == 'timedOut'){
        }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {

            // alert(xmlhttp.responseText);
            
            var myArr = JSON.parse(xmlhttp.responseText);
            document.getElementById('customRAside').innerHTML = myArr[0];
            
            busyIndicator(false);
        }
    };
    
    xmlhttp.open("GET","/custom/index.php?action=uploadBatch&itemNumber="+itemNumber+"&orderNumber="+orderNumber+"&pickPackID="+pickPackID+"&orderQty="+orderQty+"&bal="+bal)
    xmlhttp.send();
    
    busyIndicator(true);
}


function addBatch(batch, qty, orderNumber, pickPackID, orderQty, bal){
    clearError();
    var xmlhttp=new XMLHttpRequest();
    xmlhttp.onreadystatechange=function() {
        if(xmlhttp.responseText == 'timedOut'){
            window.location.href = "/manager";
        }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {
            // alert(xmlhttp.responseText);
            var myArr = JSON.parse(xmlhttp.responseText);

            document.getElementById('customMain').innerHTML = myArr[0];
            document.getElementById('customRAside').innerHTML = myArr[1];
            
            busyIndicator(false);
        }
    };

    xmlhttp.open("GET","/custom/index.php?action=addBatch&batch="+batch+"&qty="+qty+"&orderNumber="+orderNumber+"&pickPackID="+pickPackID+"&orderQty="+orderQty+"&bal="+bal);
    xmlhttp.send();
    
    busyIndicator(true);
}


function deleteBatch(lotAssignID, orderNumber, pickPackID, itemNumber, orderQty, bal, assignedQty){  
    clearError();
    
    var xmlhttp=new XMLHttpRequest();
    xmlhttp.onreadystatechange=function() {
        if(xmlhttp.responseText == 'timedOut'){
            window.location.href = "/manager";
        }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {
            
            var myArr = JSON.parse(xmlhttp.responseText);
            document.getElementById('customMain').innerHTML = myArr[0];
            document.getElementById('customRAside').innerHTML = myArr[1];
            
            busyIndicator(false);
        }
    };
    
    xmlhttp.open("GET","/custom/index.php?action=deleteBatch&lotAssignID="+lotAssignID+"&orderNumber="+orderNumber+"&itemNumber="+itemNumber+"&pickPackID="+pickPackID+"&orderQty="+orderQty+"&bal="+bal+"&assignedQty="+assignedQty);
    xmlhttp.send();
    
    busyIndicator(true);
}

function confirmDelete(orderNumber){

    x = document.getElementById('deleteOrder');

    x.innerHTML = 
    `Are you sure you want to delete this order? 
    <a id="deleteOrder" class="viewportRedBtn" style="color: black;" href="javascript:;" onclick="deleteOrder(\`` +orderNumber+ `\`)">YES</a>       
    <a id="deleteOrder" style="color: black;" href="javascript:;" onclick="cancelDeletion(` + orderNumber + `)">CANCEL</a>`;
}

function cancelDeletion(orderNumber) {  
    
    clearError();
    var xmlhttp=new XMLHttpRequest();
    xmlhttp.onreadystatechange=function() {
        if(xmlhttp.responseText == 'timedOut'){
            window.location.href = "/manager";
        }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {

            document.getElementById('customHeader').innerHTML = xmlhttp.responseText;
            
            busyIndicator(false);
            window.scrollTo(0, 0);
        }
    };
  //  alert(shipmentGroupID);
    xmlhttp.open("GET","/custom/index.php?action=cancelDeletion&orderNumber="+orderNumber);
    xmlhttp.send();
    
    busyIndicator(true);
}

function deleteOrder(orderNumber){  

    var shipmentGroupID = document.getElementById('customerID').value;
    
    clearError();
    var xmlhttp=new XMLHttpRequest();
    xmlhttp.onreadystatechange=function() {
        if(xmlhttp.responseText == 'timedOut'){
            window.location.href = "/manager";
        }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {

            var myArr = JSON.parse(xmlhttp.responseText);

            if(myArr[0] == false) {
                document.getElementById('customView').innerHTML = myArr[1];
            }else if(myArr[0] == true) {
                toast('Order Deletion Failed');
            }else {
                alert(myArr[0]);
            }

            
            busyIndicator(false);
            window.scrollTo(0, 0);
        }
    };
  //  alert(shipmentGroupID);
    xmlhttp.open("GET","/custom/index.php?action=deleteOrder&orderNumber="+orderNumber+"&shipmentGroupID="+shipmentGroupID);
    xmlhttp.send();
    
    busyIndicator(true);
    
}

function editAssignment(lotAssignID, pickPackID, bal){  
    clearError();
    
    var xmlhttp=new XMLHttpRequest();
    xmlhttp.onreadystatechange=function() {
        if(xmlhttp.responseText == 'timedOut'){
            window.location.href = "/manager";
        }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {
            var myArr = JSON.parse(xmlhttp.responseText);
            document.getElementById('overlayPanel').innerHTML = myArr[0];
            
            busyIndicator(false);
        }
    };
    
    xmlhttp.open("GET","/custom/index.php?action=editAssignment&lotAssignID="+lotAssignID+"&pickPackID="+pickPackID+"&bal="+bal);
    xmlhttp.send();
    
    busyIndicator(true);
}


function updateAssignment(pickpacklotassignID){
    clearError();
    
    var orderNumber = document.getElementById('customerSONumber').innerHTML;
    var newQty = document.getElementById('assignedQty').value;
    if(newQty >= 0){

        var xmlhttp=new XMLHttpRequest();
        xmlhttp.onreadystatechange=function() {
            if(xmlhttp.responseText == 'timedOut'){
                window.location.href = "/manager";
            }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {

                var myArr = JSON.parse(xmlhttp.responseText);
                
                document.getElementById('overlayPanel').innerHTML = null;
                document.getElementById('customMain').innerHTML = myArr[0];
                document.getElementById('customRAside').innerHTML = myArr[1];

                busyIndicator(false);
            }
        };

        xmlhttp.open("GET","/custom/index.php?action=updateAssignment&pickpacklotassignID="+pickpacklotassignID+"&newQty="+newQty+"&orderNumber="+orderNumber);
        xmlhttp.send();

        busyIndicator(true);
    }else{
        document.getElementById('errorMessage').innerHTML = "Invalid Qty";
    }
}

function updateLineItem(pickPackItemID){
    clearError();
    var orderQty = document.getElementById('orderQty').value;
    var backOrder = document.getElementById('backOrder').value;
    if(orderQty >= 0 && backOrder >= 0){

        var xmlhttp=new XMLHttpRequest();
        xmlhttp.onreadystatechange=function() {
            if(xmlhttp.responseText == 'timedOut'){
                window.location.href = "/manager";
            }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {

                // alert(xmlhttp.responseText);

                var myArr = JSON.parse(xmlhttp.responseText);
                
                document.getElementById('overlayPanel').innerHTML = null;
                document.getElementById('customMain').innerHTML = myArr[0];
                document.getElementById('customRAside').innerHTML = myArr[1];

                busyIndicator(false);
            }
        };

        xmlhttp.open("GET","/custom/index.php?action=updateLineItem&orderQty="+orderQty+"&backOrder="+backOrder+"&pickPackItemID="+pickPackItemID);
        xmlhttp.send();

        busyIndicator(true);
    }else{
        document.getElementById('errorMessage').innerHTML = "Invalid Qty";
    }
}

function editLineItem(pickPackID, orderQty, bal){
    clearError();
    
    var xmlhttp=new XMLHttpRequest();
    xmlhttp.onreadystatechange=function() {
        if(xmlhttp.responseText == 'timedOut'){
            window.location.href = "/manager";
        }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {
            // alert(xmlhttp.responseText);
            var myArr = JSON.parse(xmlhttp.responseText);
            document.getElementById('overlayPanel').innerHTML = myArr[0];
            busyIndicator(false);
        }
    };
    
    xmlhttp.open("GET","/custom/index.php?action=editLineItem&pickPackID="+pickPackID+"&orderQty="+orderQty+"&bal="+bal);
    xmlhttp.send();
    
    busyIndicator(true);
}

function viewCurrentUsage(lotcode){
    
    var xmlhttp=new XMLHttpRequest();
    
    xmlhttp.onreadystatechange=function() {
        if(xmlhttp.responseText == 'timedOut'){
            window.location.href = "/manager";
        }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {
            
            var myArr = JSON.parse(xmlhttp.responseText);
            document.getElementById('overlayPanel').innerHTML = myArr[0];
            busyIndicator(false);
        }
    };
    
    xmlhttp.open("GET","/custom/index.php?action=viewCurrentUsage&lotcode="+lotcode);
    xmlhttp.send();
    
    busyIndicator(true);
    
   
}

function selectOrder(){
    orderNumber = document.getElementById("selectOrder").value;
    clearError();
    var xmlhttp=new XMLHttpRequest();
    xmlhttp.onreadystatechange=function() {
        if(xmlhttp.responseText == 'timedOut'){
            window.location.href = "/manager";
        }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {
            
            var myArr = JSON.parse(xmlhttp.responseText);
            
            document.getElementById('customHeader').innerHTML = myArr[0];
            document.getElementById('customMain').innerHTML = myArr[1];
            document.getElementById('customRAside').innerHTML = ""
            busyIndicator(false);
            window.scrollTo(0, 0);
        }
    };
    
    xmlhttp.open("GET","/custom/index.php?action=selectOrder&orderNumber="+orderNumber);
    xmlhttp.send();
    
    busyIndicator(true);
   
}


function newOrder(){
    
//    alert("new ORDER fired");
    clearError();
    
    var xmlhttp=new XMLHttpRequest();
    xmlhttp.onreadystatechange=function() {
        if(xmlhttp.responseText == 'timedOut'){
            window.location.href = "/manager";
        }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {
          //  alert(xmlhttp.responseText);
            // document.getElementById('overlay').innerHTML = xmlhttp.responseText;

            buildOverlay(xmlhttp.responseText, '50vw');
            
            busyIndicator(false);
        }
    };
    
    xmlhttp.open("GET",'/custom/index.php?action=newOrder');
    xmlhttp.send();
    
    busyIndicator(true);
}

function changeOrder(soNumber) {

    // soNumber = document.getElementById('soNumber').value;
    orderDate = document.getElementById('orderDate').value;
    shipDate = document.getElementById('shipDate').value;

    poNumber = document.getElementById('poNumber').value;
    custNumber = document.getElementById('custNumber').value;
    shipVia = document.getElementById('shipVia').value;

    // custName = document.getElementById('custName').value;
    // addr1 = document.getElementById('addr1').value;
    // addr2 = document.getElementById('addr2').value;

    // alert(soNumber);
    // alert(orderDate);
    // alert(shipDate);
    // alert(poNumber);
    // alert(custNumber);
    // alert(shipVia);
    // alert(custName);
    // alert(addr1);
    // alert(addr2);

    var xmlhttp=new XMLHttpRequest();
    xmlhttp.onreadystatechange=function() {
        if(xmlhttp.responseText == 'timedOut'){
            window.location.href = "/manager";
        }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {

            uploadHeader(soNumber);

            toast('Order Updated.', 'success');

            busyIndicator(false);

        }
    };
    
    xmlhttp.open("GET",'/custom/index.php?action=changeOrder&soNumber='+soNumber+'&orderDate='+orderDate+'&shipDate='+shipDate+'&poNumber='+poNumber+'&custNumber='+custNumber+'&shipVia='
    +shipVia+'&custName='+custName+'&addr1='+addr1+'&addr2='+addr2);
    xmlhttp.send();
    
    busyIndicator(true);

}

function editItemNotes(partNumber, notes, lineNumber) {

    var pickPackItemsID = document.getElementById('ppiID_'+lineNumber).value;

    // alert('ppiID_'+lineNumber);
    // alert(pickPackItemsID);

    document.getElementById('descLi_' + lineNumber).innerHTML = 
    'Notes: <input style="width: 25% !important" value="' + notes 
    + '" id="notesInput"><a href="#" onclick="updateItemNotes(' + partNumber + ', ' + lineNumber + ', ' + pickPackItemsID + ', \'' + notes + '\')" title="Update Notes"><span> v </span></a>\
    <a href="#" onclick="cancelEditNotes(' + partNumber + ', ' + lineNumber + ', \'' + notes + '\', ' + pickPackItemsID + ')" title="Cancel"><span> x </span></a>';

}

function editOrderItem(part, sku, pickPackItemsID, customerSONumber, notes, qty, lineNumber) {

    var editInProgress = document.getElementById('editInProgress') ? document.getElementById('editInProgress').value : false;

    if(editInProgress) {
        toast('Another Item is still in progress.');
    }else {
        var line = document.getElementById('lineItem_'+lineNumber);

        deleteTableRow('attachLi_'+lineNumber);
    
        line.innerHTML = `
        <td>part#: ` + part + `</td>
        <td>sku#: ` + sku + `</td>
        <td colspan="2">
            <input class="hide" id="editInProgress" value="1">
            <div class="editOrderItem">

                <div>
                    <p>Qty: </p>
                    <input id="orderQty" placeholder="Enter Qty..." type="number" name="ordered" value="` + qty + `" tabindex="1"> 
                </div>
                <div>
                    <p>Notes: </p>
                    <input placeholder="Enter notes..." id="orderNotes" name="notes" value="` + notes + `" tabindex="2">
                </div>

                <a href="#" onclick="uploadHeader('` + customerSONumber + `')" title="Cancel"><span> x </span></a>
                <a href="#" onclick="updateOrderItem(` + pickPackItemsID + `, '` + customerSONumber + `')" title="Update" tabindex="3"><span> v </span></a>

            </div>
        </td>
        `;
    }
}

function updateOrderItem(pickPackItemID, customerSONumber) {

    var qty = document.getElementById('orderQty').value;
    var notes = document.getElementById('orderNotes').value;

    var xmlhttp=new XMLHttpRequest();
    xmlhttp.onreadystatechange=function() {
        if(xmlhttp.responseText == 'timedOut'){
            window.location.href = "/manager";
        }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {

            if(xmlhttp.responseText) {
                toast('Order Updated Successfully.', 'success');
                uploadHeader(customerSONumber);
            }else {
                toast('Order Updated Failed.', 'bad');
            }

            busyIndicator(false);

        }
    };
    
    xmlhttp.open("GET",'/custom/index.php?action=updateOrderItem&pickPackItemsID='+pickPackItemID+'&notes='+notes+'&qty='+qty);
    xmlhttp.send();
    
    busyIndicator(true);

}


function updateItemNotes(partNumber, lineNumber, pickPackItemsID, oldNotes) {

    notes = document.getElementById('notesInput').value;

    if(oldNotes == notes) {
        // if(notes == '' || notes  == null) {
        //     notes = "No notes.";
        // }

        html = 'Notes: ' + notes + 
        '<input id="ppiID_' + lineNumber + '" class="hide" value="' + pickPackItemsID + '"><a href="#" onclick="editItemNotes(' + partNumber + ', \'';
        
        if(notes == "") {
            html += '';
        }else {
            html += notes;
        }
        
        html += '\', ' + lineNumber + ')" title="Edit Notes"><span> p </span></a>';

        document.getElementById('line_' + lineNumber).innerHTML = html;

        toast('Notes Updated.', 'success');
    }else {
        clearError();

        // alert(pickPackItemsID);
        
        var xmlhttp=new XMLHttpRequest();
        xmlhttp.onreadystatechange=function() {
            if(xmlhttp.responseText == 'timedOut'){
                window.location.href = "/manager";
            }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {
                // alert(xmlhttp.responseText);
    
                var myArr = JSON.parse(xmlhttp.responseText);
            
                if(myArr[0] == 1) {
                    // if(notes == '' || notes  == null) {
                    //     notes = "No notes.";
                    // }
        
                    html = 'Notes: ' + notes + 
                    '<input id="ppiID_' + lineNumber + '" class="hide" value="' + pickPackItemsID + '"><a href="#" onclick="editItemNotes(' + partNumber + ', \'';
                    
                    if(notes == "") {
                        html += '';
                    }else {
                        html += notes;
                    }
                    
                    html += '\', ' + lineNumber + ')" title="Edit Notes"><span> p </span></a>';
        
                    document.getElementById('line_' + lineNumber).innerHTML = html;
    
                    toast('Notes Updated.', 'success');
                }else {
                    toast('Update Failed.');
                }
                
                busyIndicator(false);
            }
        };
        
        xmlhttp.open("GET",'/custom/index.php?action=updateItemNotes&pickPackItemsID='+pickPackItemsID+'&notes='+notes);
        xmlhttp.send();
        
        busyIndicator(true);
    }

   

}

function cancelEditNotes(partNumber, lineNumber, notes, pickPackItemsID) {

    // if(notes == '' || notes  == null) {
    //     notes = "No notes.";
    // }

    html = '<input id="ppiID_' + lineNumber + '" class="hide" value="' + pickPackItemsID + '">Notes: ' + notes + 
    '<a href="#" onclick="editItemNotes(' + partNumber + ', \'';
    
    if(notes == "") {
        html += '';
    }else {
        html += notes;
    }
    
    html += '\', ' + lineNumber + ')" title="Edit Notes"><span> p </span></a>';

    document.getElementById('line_' + lineNumber).innerHTML = html;

}


function editOrder() {

    soNumber = document.getElementById('soNumber').value;
    orderDate = document.getElementById('orderDate').value;
    shipDate = document.getElementById('shipDate').value;

    poNumber = document.getElementById('poNumber').value;
    custNumber = document.getElementById('custNumber').value;
    shipVia = document.getElementById('shipVia').value;

    custName = document.getElementById('custName').value;
    addr1 = document.getElementById('addr1').value;
    addr2 = document.getElementById('addr2').value;

    var xmlhttp=new XMLHttpRequest();
    xmlhttp.onreadystatechange=function() {
        if(xmlhttp.responseText == 'timedOut'){
            window.location.href = "/manager";
        }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {

            document.getElementById('customHeader').innerHTML = xmlhttp.responseText;

            busyIndicator(false);

        }
    };
    
    xmlhttp.open("GET",'/custom/index.php?action=editOrder&soNumber='+soNumber+'&orderDate='+orderDate+'&shipDate='+shipDate+'&poNumber='+poNumber+'&custNumber='+custNumber+'&shipVia='
    +shipVia+'&custName='+custName+'&addr1='+addr1+'&addr2='+addr2);
    xmlhttp.send();
    
    busyIndicator(true);

}

function addOrder(){
    clearError();
    
    if (document.getElementById('newOrderCustomerIDBox').value === "null" ) {
        alert("Error : Customer Name is Empty");
    }else if (document.getElementById('shipTo').value === "null" ) {
        alert("Error : Ship To is Empty");   
    }else if(document.getElementById('customerPO').value === "" ) {
        alert("Error : Customer PO is Empty");           
    }else {
        shipToCustomerID = document.getElementById('newOrderCustomerIDBox').value
        shipToID = document.getElementById('shipTo').value
        orderDate = document.getElementById('orderDate').value;
        shipDate = document.getElementById('shipDate').value;
        customerPO = document.getElementById('customerPO').value;
        shipVia = document.getElementById('shipVia').value;
        customerComment = document.getElementById('customerComment').value;
        shipmentGroupID = document.getElementById('orderCustomerID').value;
        //alert(shipToCustomerID + shipmentGroupID);

        // alert(shipToID);

        var xmlhttp=new XMLHttpRequest();
        xmlhttp.onreadystatechange=function() {
            if(xmlhttp.responseText == 'timedOut'){
                window.location.href = "/manager";
            }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {
                closeOverlay();
                
                    // alert(xmlhttp.responseText);

                    // document.getElementById('viewport').innerHTML = xmlhttp.responseText;

                    var myArr = JSON.parse(xmlhttp.responseText);
                
                    document.getElementById('customHeader').innerHTML = myArr[0];
                    document.getElementById('customMain').innerHTML = myArr[1];
                    document.getElementById('customRAside').innerHTML = ""

                    searchOrders(1);
                    closeOverlay();

                busyIndicator(false);
            }
        };
        
        xmlhttp.open("GET",'/custom/index.php?action=addOrder' +
        '&orderDate=' + orderDate +
        '&shipDate=' + shipDate +
        '&customerPO=' + customerPO +
        '&shipVia=' + shipVia +
        '&customerComment=' + customerComment +
        '&shipToCustomerID=' + customerID +
        '&shipToID=' + shipToID + 
        '&shipmentGroupID=' + shipmentGroupID);
        xmlhttp.send();
        
        busyIndicator(true);
    }

}

function toggleNewItemSkuPart() {

    x = document.getElementById('newItemSkuPart');

    if($_SESSION['newItemSkuPart'] == null) {
        $_SESSION['newItemSkuPart'] = 0;
    }

    if($_SESSION['newItemSkuPart'] == 0) {
        x.innerHTML = 'Sku #: <input id="partNo" type="number" name="item">';
    }else if($_SESSION['newItemSkuPart'] == 1) {
        x.innerHTML = 'Part #: <input id="partNo" type="number" name="item">';
    }else {
        toast('Something went wrong...');
    }

}

/* <input id="itemLookupBtn" onclick="lookupItem(true)" type="submit" value="Lookup"> */

function buildNewItemMenu(SONo){
    var x = 
    `<li>
        <label id="inputBtnBox">
            <input id="btnInput" autofocus type="text" placeholder="Enter Part#..." tabindex="1">
            <input id="inputBtn" onclick="newItemSkuPartToggle(true)" type="submit" value="Sku#" tabindex="4">
        </label>
        <input id="qty" placeholder="Enter Qty..." type="number" name="ordered" tabindex="2"> 
        <input style="width: 17vw" placeholder="Enter notes..." id="notes" name="notes" tabindex="5">
        <a href="#" onclick="cancelBuildNewItemMenu()" title="Cancel" tabindex="6"><span> x </span></a>
        <a href="#" onclick="newOrderItem('` + SONo + `')" title="Add Order Item" tabindex="3"><span> v </span></a></li>`;
    
    document.getElementById('newItem').innerHTML = x;
}

function cancelBuildNewItemMenu(){
    var x = '<div class="selectionPanel" id="newItem"><li>Add Item <a href="#" onclick="buildNewItemMenu()" title="newOrderItem"><span> + </span></a></li></div>';

    document.getElementById('newItem').innerHTML = x;
}

function newOrderItem(SONo){
    
    type = document.getElementById('inputBtn').value;
    number = document.getElementById('btnInput').value; 
    
    // alert('type: ' + type + ' | number: ' + number); 

    qty = document.getElementById('qty').value;
    notes = document.getElementById('notes').value;

    customerID = document.getElementById('customerID').value;

    skuPart = number + ',' + type;
    
    // alert('so#: ' + SONo + '| number: ' + number + '| type: ' + type + '| qty: ' + qty + '| customerID: ' + customerID + ' | notes: ' + notes);
    // alert('/custom/index.php?action=newOrderItem&SONo=' + SONo + '&customerID=' + customerID + '&number=' + number + '&type=' + type + '&notes=' + 'notes' + '&qty=' + 'qty');

    clearError();
    
    var xmlhttp=new XMLHttpRequest();
    xmlhttp.onreadystatechange=function() {

        if(xmlhttp.responseText == 'timedOut'){
            window.location.href = "/manager";
        }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {

                // alert(xmlhttp.responseText);

                var myArr = JSON.parse(xmlhttp.responseText);
    
                if(myArr[0] == true){

                    toast('Item Added Successfully', 'success');
                    
                    // document.getElementById('customMain').innerHTML = myArr[1];

                    uploadHeader(SONo, true);

                    // document.getElementById('newItem').innerHTML = document.getElementById('newItem').innerHTML + " <h2>" + myArr[1] + "</h2>";
                    // document.getElementById('customRAside').innerHTML = "Lots";
                    // busyIndicator(false);
                    // window.scrollTo(0, 0);
                }else{
                    toast(myArr[1]);
                    // document.getElementById('customRAside').innerHTML = "";
                    busyIndicator(false);
                    window.scrollTo(0, 0);
                }    
            
            busyIndicator(false);
        }
    };

    xmlhttp.open("GET",'/custom/index.php?action=newOrderItem&qty=' + qty + '&customerID=' + customerID + '&SONo=' +SONo + '&notes=' + notes + '&skuPart=' + skuPart);
    xmlhttp.send();
    
    busyIndicator(true);
}

function newItemSkuPartToggle(type) {

    y = document.getElementById('inputBtnBox');

    if(type == true) {
        // x.value = "sku#";
        y.innerHTML = 
        `<input id="btnInput" autofocus type="text" placeholder="Enter Sku#..." tabindex="1">
        <input id="inputBtn" style="background-color: orange" onclick="newItemSkuPartToggle(false)" type="submit" value="Part#" tabindex="5">`;
        // toast('sku');
    }else if(type == false) {
        // x.value = "part#";
        y.innerHTML = 
        `<input id="btnInput" autofocus type="text" placeholder="Enter Part#..." tabindex="1">
        <input id="inputBtn" onclick="newItemSkuPartToggle(true)" type="submit" value="Sku#" tabindex="5">`;
        // toast('part');
    }else {
        toast('Something went wrong...');
    }

}

function clearError(){
    document.getElementById('errorMessage').innerHTML = null;
}

function closeOverlay(){
    document.getElementById('overlayPanel').innerHTML = null;
    document.getElementById('overlay').innerHTML = null;
    document.getElementById('globalOverlay').innerHTML = null;
}



function customerChange(value){  
    
    // toast(value);
    
    clearError();
    var xmlhttp=new XMLHttpRequest();
    xmlhttp.onreadystatechange=function() {
        if(xmlhttp.responseText == 'timedOut'){
            window.location.href = "/manager";
        }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {

            // alert(xmlhttp.responseText);

            document.getElementById('customView').innerHTML = xmlhttp.responseText;
            
            busyIndicator(false);
            window.scrollTo(0, 0);
        }
    };

    xmlhttp.open("GET","/custom/index.php?action=reloadOrders&shipmentGroupID="+value+"&status=1");
    xmlhttp.send();
    
    busyIndicator(true);
}

function customerSelectChange(value){  
    
    customerID = value;
    
//    alert(customerID);
    
    clearError();
    var xmlhttp=new XMLHttpRequest();
    xmlhttp.onreadystatechange=function() {
        if(xmlhttp.responseText == 'timedOut'){
            window.location.href = "/manager";
        }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {
//            alert(xmlhttp.responseText);
            
            
            document.getElementById('shipTo').outerHTML = xmlhttp.responseText;
            
            busyIndicator(false);
            window.scrollTo(0, 0);
        }
    };

    xmlhttp.open("GET","/custom/index.php?action=getShipTos&customerID="+customerID);
    xmlhttp.send();
    
    busyIndicator(true);

}

function shipToChange(value, customerID) {
    shipToNo = value;
//    alert('Value : ' + shipToNo + ' CustomerID : ' + customerID);
    
    clearError();
    var xmlhttp=new XMLHttpRequest();
    xmlhttp.onreadystatechange=function() {
        if(xmlhttp.responseText == 'timedOut'){
            window.location.href = "/manager";
        }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {
//            alert(xmlhttp.responseText);
            
            document.getElementById('shipToInfo').innerHTML = xmlhttp.responseText;
            
            busyIndicator(false);
            window.scrollTo(0, 0);
        }
    };

    xmlhttp.open("GET","/custom/index.php?action=reloadShipTo&shipToNo="+shipToNo+"&customerID="+customerID);
    xmlhttp.send();
    
    busyIndicator(true);

}


//Press Enter To Search Inventory

// $("#itemSearch").keypress(function(e){
//     if ( e.which === 13 ) {
//         console.log("Search");
//         e.preventDefault();
//         searchInventory();
//     }
// });

 


// function deleteOrderItemConfirm(pickPackItemID){

//     document.getElementById('deleteOrderItem').innerHTML = '<a id="deleteOrderItem" href="javascript:;" onclick="deleteOrderItemConfirm(' + pickPackItemID + ')"><span>  8</span></a>';
//     // alert(pickPackItemID);
// }



function deleteOrderItemConfirm(pickPackItemID, orderNumber){  

    var message = 'Are you sure you want to delete ' + pickPackItemID + '?';
    
    if(confirm(message)) {
        clearError();
        var xmlhttp=new XMLHttpRequest();
        xmlhttp.onreadystatechange=function() {
            if(xmlhttp.responseText == 'timedOut'){
                window.location.href = "/manager";
            }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {
                
                // alert(xmlhttp.responseText);
                var myArr = JSON.parse(xmlhttp.responseText);
                
                toast((myArr[4] ? 'Item Deleted' : 'Something went wrong...'), ( myArr[4] ? 'success' : 'error'));
                
                document.getElementById('customHeader').innerHTML = myArr[0];
                document.getElementById('customMain').innerHTML = myArr[1];
                document.getElementById('customRAside').innerHTML = "";
                
                busyIndicator(false);
                window.scrollTo(0, 0);
            }
        };
      //  alert(shipmentGroupID);
        xmlhttp.open("GET","/custom/index.php?action=deleteOrderItem&pickPackItemID="+pickPackItemID+"&orderNumber="+orderNumber);
        xmlhttp.send();
        
        busyIndicator(true);
    }
    
}

function lookupItem() {
        clearError();
        
        var xmlhttp=new XMLHttpRequest();
        xmlhttp.onreadystatechange=function() {
            if(xmlhttp.responseText == 'timedOut'){
                window.location.href = "/manager";
            }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {

                // alert(xmlhttp.responseText);

                document.getElementById('overlayPanel').innerHTML = xmlhttp.responseText;
                
                busyIndicator(false);
            }
        };
        
        xmlhttp.open("GET","/custom/index.php?action=lookupItem");
        xmlhttp.send();
        
        busyIndicator(true);
}

function searchOrders(status) {

        var search = document.getElementById('searchbox').value;
        var start = document.getElementById('startdate').value;
        var end = document.getElementById('enddate').value;

        if(start == '' && end != '' || start != '' && end == '') {
            toast('Both Dates are required for range search');
            return
        }else {
            dismissToastNow();
        }

        // alert(start);
        // alert(end);

        clearError();
        var xmlhttp=new XMLHttpRequest();
        xmlhttp.onreadystatechange=function() {
            if(xmlhttp.responseText == 'timedOut'){
                window.location.href = "/manager";
            }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {

                // alert(xmlhttp.responseText);

                document.getElementById('orderListBox').innerHTML = xmlhttp.responseText;
                
                busyIndicator(false);
            }
        };
        
        xmlhttp.open("GET","/custom/index.php?action=searchOrders&status="+status+'&search='+search+'&start='+start+'&end='+end);
        xmlhttp.send();
        
        busyIndicator(true);
}

function clearOrderSearch(status) {
    document.getElementById('searchbox').value = null;
    document.getElementById('startdate').value = null;
    document.getElementById('enddate').value = null;

    searchOrders(status);
}

function reassignOrder(pickPackHeaderID) {
    clearError();
    var xmlhttp=new XMLHttpRequest();
    xmlhttp.onreadystatechange=function() {
        if(xmlhttp.responseText == 'timedOut'){
            window.location.href = "/manager";
        }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {
            overlay(xmlhttp.responseText);
            busyIndicator(false);
        }
    };
    
    xmlhttp.open("GET","/custom/index.php?action=reassignOrder&pickPackHeaderID="+pickPackHeaderID);
    xmlhttp.send();
    
    busyIndicator(true);
}

function assignCustomer(pickPackHeaderID) {

    var shipmentGroupID = document.getElementById('reassignCustomerID').value;

    clearError();
    var xmlhttp=new XMLHttpRequest();
    xmlhttp.onreadystatechange=function() {
        if(xmlhttp.responseText == 'timedOut'){
            window.location.href = "/manager";
        }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {

            toast(( xmlhttp.responseText ? 'Order Assigned' : 'Order Failed to be Assigned' ), ( xmlhttp.responseText ? 'success' : 'error' ));
            reloadOrders(shipmentGroupID);
            closeOverlayGlobal();
            busyIndicator(false);
            
        }
    };
    
    xmlhttp.open("GET","/custom/index.php?action=assignCustomer&orderCustomerID="+shipmentGroupID+'&pickPackHeaderID='+pickPackHeaderID);
    xmlhttp.send();
    
    busyIndicator(true);
}