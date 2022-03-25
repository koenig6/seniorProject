

function scan2(serialNumber, orderedQty, pickedQty){

    var manual = document.getElementById('manualMode').checked ? 1 : 0;
    var master = document.getElementById('masterTagMode').checked ? 1 : 0;
    var picked = document.getElementById('pickedSkuList').value.includes(serialNumber);      
    var shippingID = document.getElementById('shippingID').value;    
    
    clearError();
    var xmlhttp=new XMLHttpRequest();
    xmlhttp.onreadystatechange=function() {
        if(xmlhttp.responseText == 'timedOut'){
            window.location.href = "/forklift";
        }else if(xmlhttp.readyState==4 && xmlhttp.status==200) {
            
            alert(xmlhttp.responseText);

            var a = JSON.parse(xmlhttp.responseText);
            var real = a[0];
            var qty = a[1];

            if(!real) {
                toast('Serial# Not Found');
            }else {
                if(picked) {
                    confirm('Are you sure you want to un-pick ' + serialNumber) && unPickPallet();
                }else {
                    if(manual) {
                        manualPick(serialNumber);
                    }else {
                        var addedQty = parseInt(qty) + parseInt(pickedQty);
                        if(addedQty > orderedQty) {
                            var newQty = orderedQty - pickedQty; // New Pallet Qty
                            var reminaingQty = qty - newQty; // Old Pallet Qty
                            ask(serialNumber, newQty, reminaingQty, shippingID);
                            console.log(`Surplus: ${addedQty} is greater than ${orderedQty}`);
                        }else {
                            pickPallet(serialNumber);
                            console.log(`Pick: ${addedQty} is less than ${orderedQty}`);
                        }
                    }
                }
                if(master) {
                    addMaster(serialNumber);
                }
            }
            busyIndicator(false);

        }
    };
    xmlhttp.open("GET","/forklift/index2.php?action=serialCheck&serialNumber="+serialNumber);
    xmlhttp.send();
    
    busyIndicator(true);
}

function split(serialNumber, newQty, remainingQty, shippingID) {
    clearError();
    var xmlhttp=new XMLHttpRequest();
    xmlhttp.onreadystatechange=function() {
        if(xmlhttp.responseText == 'timedOut'){
            window.location.href = "/forklift";
        }else if(xmlhttp.readyState==4 && xmlhttp.status==200) {
            alert(xmlhttp.responseText);
        }
        busyIndicator(false);
    };
    xmlhttp.open("GET","/forklift/index2.php?action=split&serialNumber="+serialNumber+"&newQty="+newQty+"&remainingQty="+remainingQty+"&shippingID="+shippingID);
    xmlhttp.send();
    busyIndicator(true);
}

function pickPallet(serialNumber) {

}

function unPickPallet(serialNumber) {

}

function manualPick(serialNumber) {

}

function addMaster(serialNumber) {

}

function ask(serialNumber, newQty, remainingQty, shippingID) {
    alert(`${serialNumber}, ${newQty}, ${remainingQty}, ${shippingID}`);

    $html = `
    <a id="buttonB" onclick="split(${serialNumber}, ${newQty}, ${remainingQty}, ${shippingID})">Auto</a>
    <a id="buttonB" onclick="manualPick(${serialNumber})">Manual</a>
    <a id="buttonB" onclick="cancel()">Cancel</a>
    `;
    document.getElementById('overlayPanel').innerHTML = $html;
}

function cancel() {
    document.getElementById('overlayPanel').innerHTML = '';
    document.getElementById('scanPalletInput').value = '';
}
