/*
 *
 */ 
          
function openInventoryDetails() {
  skuNumber = document.getElementById("skuNumber").value;

  var xmlhttp = new XMLHttpRequest();
  xmlhttp.onreadystatechange = function () {
    if (xmlhttp.responseText == "timedOut") {
      window.location.href = "/manager";
    } else if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
      document.getElementById("overlay").innerHTML =
        '<div class="overlay">' +
        '<a href="#" onclick="closeOverlay()" id="exit"><span>x</span></a>' +
        xmlhttp.responseText +
        "</div>";

      busyIndicator(false);
    }
  };

  xmlhttp.open(
    "GET",
    "/inventory/index.php?action=viewInventoryDetails&skuNumber=" + skuNumber
  );
  xmlhttp.send();

  busyIndicator(true);
}

function updateItem() {
  var skuNumber = document.getElementById("skuNumber").value;
  var partNumber = document.getElementById("partNumber").value;
  var desc1 = document.getElementById("description1").value;
  var desc2 = document.getElementById("description2").value;
  var desc3 = document.getElementById("description3").value;
  var desc4 = document.getElementById("description4").value;
  var desc5 = document.getElementById("description5").value;
  var pricingUnit = document.getElementById("PUT").value;
  var pricingFactor = document.getElementById("PFCTR").value;

  var timeStudyH = document.getElementById("timestudyH").value;
  var timeStudyM = document.getElementById("timestudyM").value;
  var timeStudyS = document.getElementById("timestudyS").value;
  var hourtomin = timeStudyH * 60;
  var totalminutes = parseInt(hourtomin) + parseInt(timeStudyM);
  var timeStudy = parseInt((totalminutes * 60)) + parseInt(timeStudyS);

  var labelColor = document.getElementById("paperColor").value;
  var qtyPerPallet = document.getElementById("palletQty").value;
  var customerRefNumber = document.getElementById("customerRefNumber").value;
  var stockingUnit = document.getElementById("stockingUnit").value;
  var stockingRatio = document.getElementById("stockingRatio").value;
  var organic = document.getElementById("organic").value;
  var kosher = document.getElementById("kosher").value;
  var alergen = document.getElementById("alergen").value;
  var consumable = document.getElementById("consumable").value;
  var kit = document.getElementById("kit").value;
  var comp = document.getElementById("component").value;
  var poReq = document.getElementById("poReq").value;
  // billable = document.getElementById("billablePallet").value;
  var batchReq = document.getElementById("batchReq").value;
  var notStackable = document.getElementById("notStackable").value;
  var pltNoReq = document.getElementById("palletNumberRequired").value;

  var charge = document.getElementById("rackCharge").value;
  var caseQty = document.getElementById("caseQty").value;
  var palletPosition = document.getElementById("palletPos").value;

  var cats = document.getElementById("cats").value;

  var inDetails = document.getElementById('currentPage').value == 'details' ? 1 : 0;

  var xmlhttp = new XMLHttpRequest();
  xmlhttp.onreadystatechange = function () {
    if (xmlhttp.responseText == "timedOut") {
      window.location.href = "/manager";
    } else if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {

      // alert(xmlhttp.responseText);

      var myArr = JSON.parse(xmlhttp.responseText);

      if(myArr[0] == 0) {
        toast('No Changes Made', 'info');
        closeOverlay();
      }else {
        toast('Update Successful', 'success');
        closeOverlay();
      }

      busyIndicator(false);

      if(inDetails) {
        document.getElementById('searchMenu').innerHTML = myArr[1];
      }

      // document.getElementById('billOfMaterials').innerHTML = xmlhttp.responseText;

    }
  };

  xmlhttp.open("GET", "/inventory/index.php?action=updateItem&skuNumber=" + skuNumber +
  "&partNumber=" + partNumber +
  "&desc1=" + desc1 +
  "&desc2=" + desc2 +
  "&desc3=" + desc3 +
  "&desc4=" + desc4 +
  "&desc5=" + desc5 +
  "&pricingUnit=" + pricingUnit +
  "&pricingFactor=" + pricingFactor +
  "&timeStudy=" + timeStudy +
  "&labelColor=" + labelColor +
  "&qtyPerPallet=" + qtyPerPallet + 
  "&customerRefNumber=" + customerRefNumber +
  "&stockingUnit=" + stockingUnit +
  "&stockingRatio=" + stockingRatio +
  "&organic=" + organic +
  "&kosher=" + kosher +
  "&alergen=" + alergen +
  // "&billable=" + billable +
  "&charge=" + charge +
  "&kit=" + kit +
  "&comp=" + comp +
  "&poReq=" + poReq +
  "&batchReq=" + batchReq +
  "&pltNoReq=" + pltNoReq +
  "&cats=" + cats + 
  "&notStackable=" + notStackable +
  "&consumable=" + consumable +
  "&palletPosition=" + palletPosition +
  "&caseQty=" + caseQty +
  "&inDetails=" + inDetails);
  xmlhttp.send();

  busyIndicator(true);
}

function openEditMenu(skuNumber) {
  var xmlhttp = new XMLHttpRequest();

  xmlhttp.onreadystatechange = function () {
    if (xmlhttp.responseText == "timedOut") {
      window.location.href = "/manager";
    } else if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
      busyIndicator(false);

      document.getElementById("overlay").innerHTML =
        '<div class="overlay" style="max-height: 90vh; top: 5vh; overflow-y: scroll;">' +
        '<a href="#" onclick="closeOverlay()" id="exit"><span>x</span></a>' +
        xmlhttp.responseText +
        "</div>";
    }
  };

  xmlhttp.open(
    "GET",
    "/inventory/index.php?action=editIteS&skuNumber=" + skuNumber
  );
  xmlhttp.send();

  busyIndicator(true);
}

function openNotesMenu(skuNumber) {
  var xmlhttp = new XMLHttpRequest();

  xmlhttp.onreadystatechange = function () {
    if (xmlhttp.responseText == "timedOut") {
      window.location.href = "/manager";
    } else if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
      busyIndicator(false);

      // alert(xmlhttp.responseText);

      buildOverlay(xmlhttp.responseText, '35em');

    }
  };

  xmlhttp.open(
    "GET",
    "/inventory/index.php?action=openNotesMenu&skuNumber=" + skuNumber
  );
  xmlhttp.send();

  busyIndicator(true);
}

function unlockItemNotes() {
  var skuNumber = document.getElementById('skuNumber').value;
  document.getElementById('itemNotes').disabled = false;
  document.getElementById('editNotesBtnContainer').innerHTML = 
  '<div class="container"><a href="#" onclick="saveItemNotes(' + skuNumber + ')" title="Save"><span class="viewportGreenBtn">v</span></a></div>';
}

function saveItemNotes(skuNumber) {
  var xmlhttp = new XMLHttpRequest();

  var notes = document.getElementById('itemNotes').value;

  // alert(notes);

  xmlhttp.onreadystatechange = function () {
    if (xmlhttp.responseText == "timedOut") {
      window.location.href = "/manager";
    } else if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
      busyIndicator(false);

      // alert(xmlhttp.responseText);

      if(xmlhttp.responseText == 1) {
        toast('Update Successful', 'success');
      }else {
        toast('Update Failed');
      }

      // openNotesMenu(skuNumber);

    }
  };

  // xmlhttp.open("GET", "/inventory/index.php?action=saveItemNotes&notes=" + notes + "&skuNumber=" + skuNumber);
  // xmlhttp.send();
  xmlhttp.open(
    "GET",
    "/inventory/index.php?action=saveItemNotes&skuNumber=" + skuNumber + '&notes=' + notes
  );
  xmlhttp.send();

  busyIndicator(true);
}



//function addItem() {
//  var xmlhttp = new XMLHttpRequest();
//
//  xmlhttp.onreadystatechange = function() {
//    if (xmlhttp.responseText == "timedOut") {
//      window.location.href = "/manager";
//    } else if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
//      //runs second
//      // alert("fired2");
//      busyIndicator(false);
//
//      document.getElementById("overlay").innerHTML =
//        '<div class="overlay">' +
//        '<a href="" onclick="closeOverlay()" id="exit"><span>x</span></a>' +
//        xmlhttp.responseText +
//        "</div>";
//    }
//  };
//  xmlhttp.open("GET", "/inventory/index.php?action=addItem");
//  xmlhttp.send();
//
//  busyIndicator(true);
//}

function addItem() {
  var xmlhttp = new XMLHttpRequest();

  xmlhttp.onreadystatechange = function () {
    if (xmlhttp.responseText == "timedOut") {
      window.location.href = "/manager";
    } else if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {

      busyIndicator(false);

      // buildOverlay(xmlhttp.responseText);

      document.getElementById("overlay").innerHTML =
        '<div class="overlay" style="max-height: 90vh; top: 5vh; overflow-y: scroll;">' +
        '<a href="#" onclick="closeOverlay()" id="exit"><span>x</span></a>' +
        xmlhttp.responseText +
        "</div>";
    }
  };
  xmlhttp.open("GET", "/inventory/index.php?action=addItem");
  xmlhttp.send();

  busyIndicator(true);
}

// function buildOverlay(html) {

//     var output = '<div class="blur"></div><a href="#" onclick="closeOverlay4()" id="exit"><span>x</span></a><div class="overlay4 anim-scale-up">' + html + '</div>';

//     document.getElementById('overlay').innerHTML = output;

// }

function closeOverlay4() {

  overlay = document.getElementById('overlay');
  overlay.classList.remove("anim-scale-up");
  overlay.classList.add("anim-scale-out");

  setTimeout(function(){
    overlay.innerHTML = '';
  }, 500);
}

function closeOverlay() {
  document.getElementById("overlay").innerHTML = "";
}

function addNewItem() {

  var customerID = document.getElementById("customerID").value;
  var partNumber = document.getElementById("partNumber").value;
  var description1 = document.getElementById("description1").value;
  var description2 = document.getElementById("description2").value;
  var description3 = document.getElementById("description3").value;
  var description4 = document.getElementById("description4").value;
  var description5 = document.getElementById("description5").value;
  var PUT = document.getElementById("PUT").value;
  var PFCTR = document.getElementById("PFCTR").value;

  var timeStudyH = document.getElementById("timestudyH").value;
  var timeStudyM = document.getElementById("timestudyM").value;
  var timeStudyS = document.getElementById("timestudyS").value;
  var hourtomin = timeStudyH * 60;
  var totalminutes = parseInt(hourtomin) + parseInt(timeStudyM);
  var timeStudy = parseInt((totalminutes * 60)) + parseInt(timeStudyS);

  var paperColor = document.getElementById("paperColor").value;
  var palletQty = document.getElementById("palletQty").value;
  var customerRefNumber = document.getElementById("customerRefNumber").value;
  var stockingUnit = document.getElementById("stockingUnit").value;
  var stockingRatio = document.getElementById("stockingRatio").value;
  var organic = document.getElementById("organic").value;
  var kosher = document.getElementById("kosher").value;
  var alergen = document.getElementById("alergen").value;
  // var billablePallet = document.getElementById("billablePallet").value;
  var rackCharge = document.getElementById("rackCharge").value;
  var kit = document.getElementById("kit").value;
  var component = document.getElementById("component").value;
  var poRequired = document.getElementById("poReq").value;
  var batchRequired = document.getElementById("batchReq").value;
  var palletNumberRequired = document.getElementById("palletNumberRequired").value;
  var cats = document.getElementById("cats").value;
  var notStackable = document.getElementById("notStackable").value;
  var consumable = document.getElementById("consumable").value;
  var caseQty = document.getElementById("caseQty").value;
  var palletPos = document.getElementById("palletPos").value;

  var forceBillable = document.getElementById('forceBillable').value;

  if(partNumber == '') {
    toast('Part Number Required');
    return;
  }

  var xmlhttp = new XMLHttpRequest();
  xmlhttp.onreadystatechange = function () {
    if (xmlhttp.responseText == "timedOut") {
      window.location.href = "/manager";
    }
    
    if(xmlhttp.responseText == '0') {
      toast('Failed to Add Item.');
      busyIndicator(false);
    }else if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
      var myArr = JSON.parse(xmlhttp.responseText);
      document.getElementById("searchMenu").innerHTML = myArr[1];
      busyIndicator(false);
      loadingBtn(false);
      if(forceBillable) {
        billablePallet(myArr[0]);
      }
      closeOverlay();
    }
  };

  xmlhttp.open("GET",
    "/inventory/index.php?action=addNewItem&customerID=" + customerID +
    "&partNumber=" + partNumber +
    "&description1=" + description1 +
    "&description2=" + description2 +
    "&description3=" + description3 +
    "&description4=" + description4 +
    "&description5=" + description5 +
    "&PUT=" + PUT +
    "&PFCTR=" + PFCTR +
    "&timeStudy=" + timeStudy +
    "&paperColor=" + paperColor +
    "&palletQty=" + palletQty +
    "&customerRefNumber=" + customerRefNumber +
    "&stockingUnit=" + stockingUnit +
    "&stockingRatio=" + stockingRatio +
    "&organic=" + organic +
    "&kosher=" + kosher +
    "&alergen=" + alergen +
    "&rackCharge=" + rackCharge +
    "&kit=" + kit +
    "&component=" + component +
    "&poRequired=" + poRequired +
    "&batchRequired=" + batchRequired +
    "&palletNumberRequired=" + palletNumberRequired +
    "&cats=" + cats +
    "&notStackable=" + notStackable +
    "&consumable=" + consumable +
    "&caseQty=" + caseQty +
    "&palletPos=" + palletPos);
  xmlhttp.send();

  busyIndicator(true);
  loadingBtn(true);
}

function searchInventory(search) {
  if(search) {
    searchTerm = search;
  }else {
    try {
      searchTerm = document.getElementById("itemSearch").value;
    }
    catch {
      searchTerm = '';
    }
  }
  var xmlhttp = new XMLHttpRequest();
  xmlhttp.onreadystatechange = function () {
    if (xmlhttp.responseText == "timedOut") {
      window.location.href = "/manager";
    } else if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {

      busyIndicator(false);

      document.getElementById("searchMenu").innerHTML = xmlhttp.responseText

    }
  };
  xmlhttp.open("GET", "/inventory/index.php?action=searchItems&itemSearch="+searchTerm+'&foresite=0');
  xmlhttp.send();

  busyIndicator(true);
}

function devButton(sku) {

    viewItem(sku);

}

function filterForesite(value) {
  var checked = value.checked ? 1 : 0;
  var searchTerm = document.getElementById('itemSearch').value;
  
  var xmlhttp = new XMLHttpRequest();
  xmlhttp.onreadystatechange = function () {
    if (xmlhttp.responseText == "timedOut") {
      window.location.href = "/manager";
    } else if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
      busyIndicator(false);
      document.getElementById("searchMenu").innerHTML = xmlhttp.responseText
    }
  };
  xmlhttp.open("GET", "/inventory/index.php?action=searchItems&itemSearch="+searchTerm+'&foresite='+checked);
  xmlhttp.send();
  busyIndicator(true);
}

function searchResultSkus(itemSearch) {

  // alert(itemSearch);

  var xmlhttp = new XMLHttpRequest();

  xmlhttp.onreadystatechange = function () {
    if (xmlhttp.responseText == "timedOut") {
      window.location.href = "/manager";
    } else if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {

      busyIndicator(false);

      buildOverlay(xmlhttp.responseText, '20em');

    }
  };
  xmlhttp.open("GET", "/inventory/index.php?action=searchResultSkus&itemSearch="+itemSearch);
  xmlhttp.send();

  busyIndicator(true);

}

function viewItem(skuNumber) {

  var itemSearch = document.getElementById('itemSearch').value;

  // alert(itemSearch);

  var xmlhttp = new XMLHttpRequest();

  xmlhttp.onreadystatechange = function () {
    if (xmlhttp.responseText == "timedOut") {
      window.location.href = "/manager";
    } else if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {

      busyIndicator(false);

      document.getElementById("searchMenu").innerHTML = xmlhttp.responseText

    }
  };
  xmlhttp.open("GET", "/inventory/index.php?action=viewItem&skuNumber="+skuNumber+'&itemSearch='+itemSearch);
  xmlhttp.send();

  busyIndicator(true);
}

function newCustomer(skuNumber) {


  var xmlhttp = new XMLHttpRequest();
  xmlhttp.onreadystatechange = function () {
    if (xmlhttp.responseText == "timedOut") {
      window.location.href = "/manager";
    } else if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {

      document.getElementById("overlay").innerHTML = "<div class='overlay'>" +
        xmlhttp.responseText + "</div>";

      busyIndicator(false);
    }
  };
  xmlhttp.open(
    "GET",
    "/inventory/index.php?action=newCustomer&skuNumber=" + skuNumber
  );
  xmlhttp.send();

  busyIndicator(true);
}

function addCustomer(skuNumber) {

  customerID = document.getElementById("customerID").value;

  var xmlhttp = new XMLHttpRequest();
  xmlhttp.onreadystatechange = function () {
    if (xmlhttp.responseText == "timedOut") {
      window.location.href = "/manager";
    } else if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {

      closeOverlay();

      if (xmlhttp.responseText == 1) {
        document.getElementById("message").innerHTML = "Failed to Add Associated Customer";
      } else {
        document.getElementById("searchMenu").innerHTML = xmlhttp.responseText;
      }

      busyIndicator(false);
    }
  };

  xmlhttp.open(
    "GET",
    "/inventory/index.php?action=submitNewCustomer&skuNumber=" + skuNumber + "&customerID=" + customerID
  );
  xmlhttp.send();

  busyIndicator(true);
}

function newVendor(skuNumber) {

  var xmlhttp = new XMLHttpRequest();
  xmlhttp.onreadystatechange = function () {
    if (xmlhttp.responseText == "timedOut") {
      window.location.href = "/manager";
    } else if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
      document.getElementById("overlay").innerHTML =
        '<div class="overlay">' +
        xmlhttp.responseText +
        '</div>';

      busyIndicator(false);
    }
  };

  xmlhttp.open(
    "GET",
    "/inventory/index.php?action=newVendor&skuNumber=" + skuNumber
  );
  xmlhttp.send();

  busyIndicator(true);
}

function unknown() {
  //alert("OrderEntryFired");
  var xmlhttp = new XMLHttpRequest();

  xmlhttp.onreadystatechange = function () {
    if (xmlhttp.responseText == "timedOut") {
      window.location.href = "/manager";
    } else if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
      // alert("PHP Ran");
      busyIndicator(false);
      /*
      document.getElementById("overlay").innerHTML =
        '<div class="overlay">' +
        '<a href="#" onclick="closeOverlay()" id="exit"><span>x</span></a>' +
        xmlhttp.responseText +
        "</div>";
        */
    }
  };
}


function buildNewPOItemMenu(orderNumber) {

  var x = '<table id="orderItems"><tr>\n\
<td>Enter Item #: <input tabindex="1" class="text" id="orderSearch" placeholder="Enter Item Number..."></td></td>' +
    '<td>Qty:<input tabindex="2" id="orderItemQty" class="text" placeholder="Enter Quantity..."></div>' +
    '<td>Lot#:<input tabindex="3" id="lotCode" class="text" placeholder="Enter Lot Code..."></div>' +
    '<td>Plt#:<input tabindex="4" id="palletNumber" class="text" placeholder="Enter Pallet#..."></div>' +
    '<td>Hold:<input tabindex="5" id="hold" class="text" type="checkbox"></div>' +
    '<td><div id="loading"><a href="#" tabindex="7" onclick="cancelBuildNewPOItemMenu()" title="Cancel"><span class="itemBtn viewportRedBtn">x</span></a>' +
    '<a href="#" tabindex="6" onclick="addPOItem(' + orderNumber + ')" title="Submit"><span class="itemBtn viewportGreenBtn">v</span></a></div></td></tr></table>';
  document.getElementById('newPOItemMenu').innerHTML = x;

}

function cancelBuildNewPOItemMenu(orderNumber) {
  var x = '<div class="selectionPanel" id="newPOItem"><ul><li>Add Item <a href="#" onclick="buildNewPOItemMenu(' + orderNumber + ')" title="newPOrderItem"><span> + </span></a></li></ul></div>';

  document.getElementById('newPOItemMenu').innerHTML = x;
}

function addPOItem(poNo) {
  orderItemNo = document.getElementById('orderSearch').value;
  orderItemQty = document.getElementById('orderItemQty').value;
  lotCode = document.getElementById('lotCode').value;
  palletNumber = document.getElementById('palletNumber').value;
  hold = document.getElementById('hold').checked;

  hold = hold ? 1 : 0;

  // alert(orderItemNo);
  // alert(orderItemQty);
  // alert(lotCode);
  // alert(palletNumber);
  // alert(hold);

  var xmlhttp = new XMLHttpRequest();
  xmlhttp.onreadystatechange = function () {
    if (xmlhttp.responseText == 'timedOut') {
      window.location.href = "/manager";
    } else if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {

      document.getElementById('poItems').innerHTML = xmlhttp.responseText;

      busyIndicator(false);
    }
  };
  
  xmlhttp.open("GET", '/inventory/index.php?action=addPOItem' +
  '&orderItemNo=' + orderItemNo +
  '&poNo=' + poNo +
  '&orderItemQty=' + orderItemQty +
  '&lotCode=' + lotCode +
  '&palletNumber=' + palletNumber +
  '&hold=' + hold);
  xmlhttp.send();
  
  // busyIndicator(true);
  loadingBtn(true);
}

function flagItem(poiId, checked, poNum, skuNumber, complete) {

  if(complete) {
    toast('This Order is Completed');
  }else {

    checked = checked.checked ? 1 : 0;

    // alert(checked);
    // alert(poiId);
    // alert(orderNumber);
  
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function () {
      if (xmlhttp.responseText == 'timedOut') {
        window.location.href = "/manager";
      } else if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
  
        // alert(xmlhttp.responseText);
  
        var myArr = JSON.parse(xmlhttp.responseText);
  
        if(myArr[0]) {
          toast('Successfully Flagged ' + skuNumber, 'success');
        }else {
          toast('Failed to flag ' + skuNumber);
        }
  
        document.getElementById('poItems').innerHTML = myArr[1];
        busyIndicator(false);
      }
    };
  
    xmlhttp.open("GET", '/inventory/index.php?action=flagItem' +
      '&poiId=' + poiId +
      '&checked=' + checked +
      '&poNum=' + poNum);
    xmlhttp.send();
  
    busyIndicator(true);

  }
}

function addPOrder() {
  customerAddress = document.getElementById('customerAddress').value;
  ShipToAddress = document.getElementById('shipToAddress').value;
  orderDate = document.getElementById('orderDate').value;
  requiredDate = document.getElementById('requiredDate').value;
  shipVia = document.getElementById('shipVia').value;
  notes = document.getElementById('notes').value;
  ref = document.getElementById('refNum').value;

  // alert(customerAddress);
  // alert(ShipToAddress);
  // alert(orderDate);
  // alert(requiredDate);
  // alert(shipVia);
  // alert(ref);
  // alert(notes);

  if(customerAddress == null || ShipToAddress == null || orderDate == null || requiredDate == null || shipVia == null) {

    alert('Make sure to fill out all required info.');

    end();
  }



  var xmlhttp = new XMLHttpRequest();
  xmlhttp.onreadystatechange = function () {
    if (xmlhttp.responseText == 'timedOut') {
      window.location.href = "/manager";
    } else if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
      var myArr = JSON.parse(xmlhttp.responseText);


      // alert(xmlhttp.responseText);

      document.getElementById('POOrderList').innerHTML = myArr[0];
      document.getElementById('poInfo').innerHTML = myArr[1];
      document.getElementById('poDetails').innerHTML = myArr[2];
      document.getElementById('poItems').innerHTML = myArr[3];

      loadingBtn(false);
      busyIndicator(false);
    }
  };

  xmlhttp.open("GET", '/inventory/index.php?action=addPOrder' +
    '&customerAddress=' + customerAddress +
    '&ShipToAddress=' + ShipToAddress +
    '&orderDate=' + orderDate +
    '&requiredDate=' + requiredDate +
    '&shipVia=' + shipVia +
    '&notes=' + notes +
    '&ref=' + ref);
  xmlhttp.send();

  busyIndicator(true);
  loadingBtn(true);
}

function confirmDeletePO(poNo) {

    r = confirm('Are you sure you want to delete PO: ' + poNo + '?');

  if(r) {
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function () {
      if (xmlhttp.responseText == 'timedOut') {
        window.location.href = "/manager";
      } else if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {

        if(xmlhttp.responseText == false) {
          alert('Make sure all items are deleted before deleting the purchase order.');
        }else {
          window.location.href = '/inventory/index.php?action=purchaseOrderMenu';
        }
        busyIndicator(false);
      }
    };

    xmlhttp.open("GET", '/inventory/index.php?action=deletePO&poNo=' + poNo);
    xmlhttp.send();

    busyIndicator(true);
  }

  
}

function openPO(poNumber) {

  var xmlhttp = new XMLHttpRequest();
  xmlhttp.onreadystatechange = function () {
    if (xmlhttp.responseText == 'timedOut') {
      window.location.href = "/manager";
    } else if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {

      // alert(xmlhttp.responseText);

      var myArr = JSON.parse(xmlhttp.responseText);

      // document.getElementById('POOrderList').innerHTML = myArr[0];
      document.getElementById('poInfo').innerHTML = myArr[1];
      document.getElementById('poDetails').innerHTML = myArr[2];
      document.getElementById('poItems').innerHTML = myArr[3];

      busyIndicator(false);
    }
  };

  xmlhttp.open("GET", '/inventory/index.php?action=openPO&poNumber=' + poNumber);
  xmlhttp.send();

  busyIndicator(true);
}

function editPO(poNo) {
  
  // Enable Input Boxes
  document.getElementById('customerAddress').disabled = false;
  document.getElementById('shipToAddress').disabled = false;
  document.getElementById('orderDate').disabled = false;
  document.getElementById('requiredDate').disabled = false;
  document.getElementById('refNum').disabled = false;
  document.getElementById('shipVia').disabled = false;
  document.getElementById('notes').disabled = false;

  // Get Before Values
  var customerAddress = document.getElementById('customerAddress').value;
  var shipToAddress = document.getElementById('shipToAddress').value;
  var orderDate = document.getElementById('orderDate').value;
  var requiredDate = document.getElementById('requiredDate').value;
  var shipVia = document.getElementById('shipVia').value;
  var notes = document.getElementById('notes').value;
  var ref = document.getElementById('refNum').value;

  var before = [ "'" + customerAddress + "'", "'" + shipToAddress + "'", "'" + orderDate + "'", "'" + requiredDate + "'", "'" + shipVia + "'", "'" + notes + "'", "'" + ref + "'" ];
  
  document.getElementById('addPOBtn').innerHTML = '<a href="#" onclick="saveChangesPO(' + poNo + ', ' + before + ')" title="Save Changes"><span class="viewportGreenBtn">v</span></a>';

}

function saveChangesPO(poNo, ...args) {
  
  var customerAddress = document.getElementById('customerAddress').value;
  var shipToAddress = document.getElementById('shipToAddress').value;
  var orderDate = document.getElementById('orderDate').value;
  var requiredDate = document.getElementById('requiredDate').value;
  var shipVia = document.getElementById('shipVia').value;
  var notes = document.getElementById('notes').value;
  var ref = document.getElementById('refNum').value;
  
  var after = [ customerAddress, shipToAddress, orderDate, requiredDate, shipVia , notes, ref ];
  
  // Check for any changes made
  var trigger = false;
  for (i = 0; i < args.length; i++) {
    if(args[i] != after[i]) {
      trigger = true;
    }
  }

  // alert(trigger);

  var xmlhttp = new XMLHttpRequest();
  xmlhttp.onreadystatechange = function () {
    if (xmlhttp.responseText == 'timedOut') {
      window.location.href = "/manager";
    } else if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
      // alert(xmlhttp.responseText);
      if(xmlhttp.responseText == 0) {
        if(trigger) {
          toast('Update Failed.');
        }
        resetNewPOMenu(poNo);
        busyIndicator(false);
      }else {
        openPO(poNo);
      }
    }
  };
  xmlhttp.open("GET", '/inventory/index.php?action=saveChangesPO&customerAddress='+customerAddress+'&ShipToAddress='+shipToAddress+'&orderDate='+orderDate+'&requiredDate='+requiredDate+'&shipVia='+shipVia+'&notes='+notes+'&ref='+ref+'&poNo='+poNo);
  xmlhttp.send();
  busyIndicator(true);
}
  
function resetNewPOMenu(poNumber) {
  // alert(poNumber);
  // Disable Input Boxes
  document.getElementById('customerAddress').disabled = true;
  document.getElementById('shipToAddress').disabled = true;
  document.getElementById('orderDate').disabled = true;
  document.getElementById('requiredDate').disabled = true;
  document.getElementById('shipVia').disabled = true;
  document.getElementById('notes').disabled = true;
  document.getElementById('refNum').disabled = true;

  document.getElementById('addPOBtn').innerHTML = '<a href="#" id="addPOBtn" onclick="editPO(' + poNumber + ')" title=""><span class="viewportBlueBtn">p</span></a>';
}

function confirmDeletePOItem(orderNumber, orderItems) {
    document.getElementById('deleteItem').innerHTML = '<a id="deleteItem" href="javascript:;" onclick="deletePOItem(' + orderItems[$i][5] + ', ' + orderNumber + ')"><span class="viewportRedBtn"> X</span></a>';
}

function deletePOItem(purchaseOrderItemsID, poNo, complete) {

  if(complete) {
    toast('This Order is Completed');
  }else {

    // alert(purchaseOrderItemsID);
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function () {
      if (xmlhttp.responseText == 'timedOut') {
        window.location.href = "/manager";
      } else if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {

        document.getElementById('poItems').innerHTML = xmlhttp.responseText;
        // alert(purchaseOrderItemsID);
        busyIndicator(false);
        window.scrollTo(0, 0);
      }
    };

    xmlhttp.open("GET", "/inventory/index.php?action=deletePOItem&purchaseOrderItemsID=" + purchaseOrderItemsID +
      '&poNo=' + poNo);
    xmlhttp.send();

    busyIndicator(true);

  }
}

function latestPOToast(po){
  toast('<a href="#"><span class="closeBtn viewportBlueBtn" onclick="openPO(\'' + po + '\')">&lt;</span></a>Go To Recent PO: ' + po);
}

function bomScroll() {
  window.location.hash = '#bomTitleTemp';
}

function skuScroll() {
  //alert("hi");
  document.body.scrollTop = document.documentElement.scrollTop = 0;
}

function addBOM(parentSku) {

  skuNumber = document.getElementById('skuSearch').value;
  qtyPer = document.getElementById('qtyPer').value;

  var str = document.getElementById('skuExists').value;
  var check = str.includes(skuNumber);

  if(check) {
    toast('SKU already exists', 'error');
  }else {
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function () {
    if (xmlhttp.responseText == 'timedOut') {
      window.location.href = "/manager";
    } else if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {

      var x = xmlhttp.responseText;

      if (x == -1) {
        // document.getElementById('errorMessage').innerHTML = "Error";
        toast('Failed to add sku#: ' + skuNumber, 'error');
      } else if (x == -2) {
        // document.getElementById('errorMessage').innerHTML = "Error";
        toast('Invalid sku#: ' + skuNumber, 'error');
      } else {
        toast('Successfully Added sku#: ' + skuNumber, 'success');
        document.getElementById('billOfMaterials').innerHTML = xmlhttp.responseText;
        str += parentSku + ' ';
      }

      busyIndicator(false);
      if(x == -1 || x == -2) {
        document.getElementById('newBOM').innerHTML = '';
      }else {
        loadingBtn(false);
      }
    }
    };

    xmlhttp.open("GET", '/inventory/index.php?action=addBOM' +
    '&parentSku=' + parentSku +
    '&skuNumber=' + skuNumber +
    '&qtyPer=' + qtyPer);
    xmlhttp.send();
    busyIndicator(true);
    loadingBtn(true);
  }
}

function buildNewBomMenu(skuNumber) {
  // var x = '<table id="orderItems"><tr><td class="unit one-fifth">Enter Sku #: <input class="text" id="skuSearch" placeholder="Enter Sku Number..."></td></td><td class="unit one-quarter">Desc:<input id="desc" class="text" placeholder="Enter Desc..."></div><td class="unit one-fifth">Enter Sku #: <input class="text" id="skuSearch" placeholder="Enter Sku Number..."></td><td><a href="#" onclick="cancelBuildNewPOItemMenu()" title="Cancel"><span class="itemBtn viewportRedBtn">x</span></a><a href="#" onclick="addPOItem(' + orderNumber + ')" title="Submit"><span class="itemBtn viewportGreenBtn">v</span></a></td></tr></table>';  
  var x = `
  <table id="orderItems">
    <tr>
      <td>Enter Sku#:<input style="width: 10vw !important;" type="text" id="skuSearch" placeholder="Enter Sku Number..."></td>
      <td>Qty Per:<input type="text" style="width: 10vw !important;" id="qtyPer" placeholder="Enter Qty Per Number..."></td>
      <td>
        <div id="loading">
        <a href="#" onclick="addBOM(` + skuNumber + `)" title="Submit"><span class="itemBtn viewportGreenBtn">v</span></a>
        <a href="#" onclick="cancelBuildNewBomMenu()" title="Cancel"><span class="itemBtn viewportRedBtn">x</span></a>
        </div>
      </td>
    </tr>
  </table>`;
  document.getElementById('newBOM').innerHTML = x;
}

function cancelBuildNewBomMenu() {
  x = '';
  document.getElementById('newBOM').innerHTML = x;
}

function deleteBomItem(parentSku, childSku) {

  var xmlhttp = new XMLHttpRequest();
  xmlhttp.onreadystatechange = function () {
    if (xmlhttp.responseText == 'timedOut') {
      window.location.href = "/manager";
    } else if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {

      document.getElementById('billOfMaterials').innerHTML = xmlhttp.responseText;
      busyIndicator(false);
    }
  };

  xmlhttp.open("GET", "/inventory/index.php?action=deleteBom&parentSku=" + parentSku +
    '&childSku=' + childSku);
  xmlhttp.send();

  busyIndicator(true);
}

function enterSubmitSearch(e) {
  var key = e.keyCode || e.which;
  if (key === 13) {
    searchInventory();
  }
}

function enterSubmitPOSearch(e) {
  var key = e.keyCode || e.which;
  if (key === 13) {
    poSearch()
  }
}

function customerToAddressChange(value) {

  customerID = value;

  // alert(customerID);

  //    clearError();
  var xmlhttp = new XMLHttpRequest();
  xmlhttp.onreadystatechange = function () {
    if (xmlhttp.responseText == 'timedOut') {
      window.location.href = "/manager";
    } else if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
      // alert(xmlhttp.responseText);


      document.getElementById('shipToAddressBox').innerHTML = xmlhttp.responseText;

      busyIndicator(false);
      window.scrollTo(0, 0);
    }
  };

  xmlhttp.open("GET", "/inventory/index.php?action=getShipTos&customerID=" + customerID);
  xmlhttp.send();

  busyIndicator(true);

}

function shipToChange() {

}


// function submitGoToPOSearch() {

//   search = document.getElementById('searchPOBox').value;

//   openPO(search);

// };

function poSearch(clear = false) {
  
  var customer = document.getElementById('customerSelectInput').value;
  var search = document.getElementById('searchPOBox').value;
  var startdate = document.getElementById('startdate').value;
  var enddate = document.getElementById('enddate').value;
  var enddate = document.getElementById('enddate').value;
  var complete = document.getElementById('closeOpenStatus').value;

  if(startdate == '' && enddate != '' || startdate != '' && enddate == '') {
    toast('Both Dates are required for range search');
    return
  }else {
      dismissToastNow();
  }

  // alert(search + ', ' + customer + ', ' + startdate + ', ' + enddate);

  var xmlhttp = new XMLHttpRequest();
  xmlhttp.onreadystatechange = function () {
    if (xmlhttp.responseText == 'timedOut') {

    } else if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {

      var myArr = JSON.parse(xmlhttp.responseText);

      if(clear) {
        document.getElementById('poList').innerHTML = myArr[1];
        busyIndicator(false);
      }else {
        if(!isNaN(myArr[1])) {
          openPO(myArr[1]);
          toast('Found ' + myArr[1], 'success');
          clearPOSearch(false);
        }else if(myArr[1] == 'error') {
          document.getElementById('poList').innerHTML = 'No Active Orders Found...';
          busyIndicator(false);
        }else {
          document.getElementById('poList').innerHTML = myArr[1];
          busyIndicator(false);
        }

        if(myArr[0] > 1) {
          document.getElementById('poListCnt').innerHTML = myArr[0] + ' Items';
        }
      }

      window.scrollTo(0, 0);
    }
  };

  xmlhttp.open("GET", "/inventory/index.php?action=poSearch&search="+search+'&customer='+customer+'&startdate='+startdate+'&enddate='+enddate+'&complete='+complete);
  xmlhttp.send();

  busyIndicator(true);

}

function clearPOSearch(clear = true) {

  document.getElementById('searchPOBox').value = '';
  document.getElementById('customerSelectInput').value = null;
  document.getElementById('startdate').value = null;
  document.getElementById('enddate').value = null;

  if(clear) {
    poSearch(true);
  }

}

function changeSelection(value) {

  menu = value;

  skuNumber = document.getElementById('skuNumber').value;

  start = document.getElementById('start').value;
  end = document.getElementById('end').value;

  kit = document.getElementById('kit').checked;
  component = document.getElementById('component').checked;

  output = kit + ' ' + component;

  // alert(output);

  if (output == 'true true') {
    type = '0';
  }else if(output == 'false true') {
    type = '1';
  }else if(output == 'true false') {
    type = '2';
  }

  // alert(type)

  // alert(type);

  //    clearError();
  var xmlhttp = new XMLHttpRequest();
  xmlhttp.onreadystatechange = function () {
    if (xmlhttp.responseText == 'timedOut') {

    } else if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
      // alert(xmlhttp.responseText);

      document.getElementById('binLocations').innerHTML = xmlhttp.responseText;

      busyIndicator(false);
      window.scrollTo(0, 0);
    }
  };

  xmlhttp.open("GET", "/inventory/index.php?action=changeSelection&menu=" + menu + '&skuNumber=' + skuNumber + '&type=' + type + '&start=' + start + '&end=' + end);
  xmlhttp.send();

  busyIndicator(true);

}

function openWODetailsComp() {
skuNumber = document.getElementById("skuNumber").value;

  var xmlhttp = new XMLHttpRequest();
  xmlhttp.onreadystatechange = function () {
    if (xmlhttp.responseText == "timedOut") {
      window.location.href = "/manager";
    } else if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
      document.getElementById("overlay").innerHTML =
        '<div class="overlay">' +
        '<a href="#" onclick="closeOverlay()" id="exit"><span>x</span></a>' +
        xmlhttp.responseText +
        "</div>";

      busyIndicator(false);
    }
  };

  xmlhttp.open(
    "GET",
    "/inventory/index.php?action=viewWODetailsComp&skuNumber=" + skuNumber
  );
  xmlhttp.send();

  busyIndicator(true);
}

function openWODetailsKit() {
  skuNumber = document.getElementById("skuNumber").value;
  
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function () {
      if (xmlhttp.responseText == "timedOut") {
        window.location.href = "/manager";
      } else if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
        document.getElementById("overlay").innerHTML =
          '<div class="overlay">' +
          '<a href="#" onclick="closeOverlay()" id="exit"><span>x</span></a>' +
          xmlhttp.responseText +
          "</div>";
  
        busyIndicator(false);
      }
    };
  
    xmlhttp.open(
      "GET",
      "/inventory/index.php?action=viewWODetailsKit&skuNumber=" + skuNumber
    );
    xmlhttp.send();
  
    busyIndicator(true);
  }

function openPODetails() {
  skuNumber = document.getElementById("skuNumber").value;
  
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function () {
      if (xmlhttp.responseText == "timedOut") {
        window.location.href = "/manager";
      } else if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
        document.getElementById("overlay").innerHTML =
          '<div class="overlay anim-scale-up">' +
          '<a href="#" onclick="closeOverlay()" id="exit"><span>x</span></a>' +
          xmlhttp.responseText +
          "</div>";
  
        busyIndicator(false);
      }
    };
  
    xmlhttp.open(
      "GET",
      "/inventory/index.php?action=viewPODetails&skuNumber=" + skuNumber
    );
    xmlhttp.send();
  
    busyIndicator(true);
  }

function checkPOScroll() {

  var jumpBtn = document.getElementById('jumpBtn');

  var y = document.getElementById('poList').scrollTop;

  // document.getElementById("notes").value = y;

  if(y != 0) {
    jumpBtn.className = 'show';
  }else {
    jumpBtn.className = 'hide';
  }

}

function skuHistory(skuNumber) {
  // skuNumber = document.getElementById("skuNumber").value;

  var xmlhttp = new XMLHttpRequest();
  xmlhttp.onreadystatechange = function () {
    if (xmlhttp.responseText == "timedOut") {
      window.location.href = "/manager";
    } else if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
      document.getElementById("overlay").innerHTML =
        '<div class="overlay anim-scale-up">' +
        '<a href="#" onclick="closeOverlay()" id="exit"><span>x</span></a>' +
        xmlhttp.responseText +
        "</div>";

      busyIndicator(false);
    }
  };

  xmlhttp.open(
    "GET",
    "/inventory/index.php?action=skuHistory&skuNumber=" + skuNumber
  );
  xmlhttp.send();

  busyIndicator(true);
}

function updateApproval(workOrderNumber, approval = false) {

  if(!approval) {
    var approval = document.getElementById('approval').checked ? 1 : 0;
  }

  // toast(approval);

  var xmlhttp = new XMLHttpRequest();
  xmlhttp.onreadystatechange = function () {
    if (xmlhttp.responseText == "timedOut") {
      window.location.href = "/manager";
    } else if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
      
      // alert(xmlhttp.responseText);

      if(xmlhttp.responseText == 1) {
        changeSelection(1); // success > refresh table
        toast('Update Successful', 'success');
      }else {
        toast('Failed to update approval');
        document.getElementById('approval').checked = approval == 1 ? 1 : 0;
      }

      busyIndicator(false);
    }
  };

  xmlhttp.open(
    "GET",
    "/inventory/index.php?action=updateApproval&woNo=" + workOrderNumber + '&approval='+approval
  );
  xmlhttp.send();

  busyIndicator(true);

}

function editPOItem(lineItem, orderNumber, purchaseOrderItemsID) {

  var editInProgress = document.getElementById('editInProgress') ? document.getElementById('editInProgress').value : false;

  if(editInProgress) {
      toast('Another Item is still in progress.');
  }else {
    var orderedQty = document.getElementById('orderedQty_'+lineItem);
    var lotNum = document.getElementById('lotNum_'+lineItem);
    var pltNum = document.getElementById('pltNum_'+lineItem);
    var hold = document.getElementById('hold_'+lineItem);
    var buttons = document.getElementById('liBtns_'+lineItem);

    orderedQty.innerHTML = '<input tabindex="1" class="hide" id="editInProgress" value="1"><input id="orderedQty" value="' + orderedQty.innerHTML + '" placeholder="Enter orderedQty...">';
    lotNum.innerHTML = '<input tabindex="2" id="lotNum" value="' + lotNum.innerHTML + '" placeholder="Enter lot#...">';
    pltNum.innerHTML = '<input tabindex="3" id="pltNum" value="' + pltNum.innerHTML + '" placeholder="Enter plt#...">';
    hold.innerHTML = '<input tabindex="4" id="hold" type="checkbox" ' + ((hold.getElementsByTagName('input')[0].checked) ? 'checked' : '') + '>';
    buttons.innerHTML = `
    <a href="#" tabindex="6" onclick="openPO(` + orderNumber + `)" title="Cancel"><span> x </span></a>
    <a id="deleteItem" tabindex="5" href="#" onclick="updatePOItem(` + orderNumber + `, ` + purchaseOrderItemsID + `)" title="Update"><span class="viewportGreenBtn" > v </span></a>
    `;
  }
}

function updatePOItem(orderNumber, purchaseOrderItemsID) {
  var orderedQty = document.getElementById('orderedQty').value;
  var lotNum = document.getElementById('lotNum').value;
  var pltNum = document.getElementById('pltNum').value;
  var hold = document.getElementById('hold').checked;

  hold = hold ? 1 : 0;

  // alert(purchaseOrderItemsID + ', ' + orderedQty + ', ' + lotNum + ', ' + pltNum + ', ' + hold);

  var xmlhttp = new XMLHttpRequest();
  xmlhttp.onreadystatechange = function () {
    if (xmlhttp.responseText == "timedOut") {
      window.location.href = "/manager";
    } else if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
      // alert(xmlhttp.responseText);
      toast(xmlhttp.responseText ? 'Update successful' : 'Update Failed', xmlhttp.responseText ? 'success' : 'bad');
      if(xmlhttp.responseText) {
        openPO(orderNumber);
      }
      busyIndicator(false);
    }
  };

  xmlhttp.open("GET", "/inventory/index.php?action=updatePOItem&purchaseOrderItemsID="+purchaseOrderItemsID+'&orderedQty='+orderedQty+'&lotNum='+lotNum+'&pltNum='+pltNum+'&hold='+hold);
  xmlhttp.send();
  busyIndicator(true);
}

function openCPO() {

  var customer = document.getElementById('customerSelectInput').value;
  var search = document.getElementById('searchPOBox').value;
  var startdate = document.getElementById('startdate').value;
  var enddate = document.getElementById('enddate').value;

  if(startdate == '' && enddate != '' || startdate != '' && enddate == '') {
    toast('Both Dates are required for range search');
    return
  }else {
      dismissToastNow();
  }

  var xmlhttp=new XMLHttpRequest();
  xmlhttp.onreadystatechange=function() {
    if(xmlhttp.responseText == 'timedOut'){
        window.location.href = "/manager";
    }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {

        x = document.getElementById('completedPOBox')

        y = document.getElementById('poList');
        
        x.innerHTML = '<a href="#"><span class="closeCO" onclick="closeCPO()">`</span></a><hr>' + xmlhttp.responseText == 'error' ? 'No Completed Orders...' : xmlhttp.responseText;

        x.classList.add('poExtend');
        x.classList.remove('poExtendRetract');

        y.classList.add('poRetract');
        y.classList.remove('poRetractExtend');

        busyIndicator(false);

        window.scrollTo(0, 0);

    }
  };

  xmlhttp.open("GET","/inventory/index.php?action=completedPOrders&customer="+customer+"&search="+search+'&startdate='+startdate+'&enddate='+enddate);
  xmlhttp.send();

  busyIndicator(true);
}

function closeCPO() {
    
  x = document.getElementById('completedPOBox')

  y = document.getElementById('poList');
  
  x.innerHTML = '<a href="#"><span onclick="openCPO()">!</span></a><p>Completed</p><hr>';

  x.classList.remove('poExtend');
  x.classList.add('poExtendRetract');

  y.classList.remove('poRetract');
  y.classList.add('poRetractExtend');

}

function togglePOCloseOpen() {
  var state = document.getElementById('closeOpenStatus');
  var x = document.getElementById('poCloseOpenIcon');

  if(state.value == 1) {
    x.innerHTML = 'Open Orders<a href="#" title="Closed Orders" onclick="togglePOCloseOpen()"><i class="bi bi-circle"></i></a>';
    state.value = 0;
  }else {
    x.innerHTML = 'Closed Orders<a href="#" title="Open Orders" onclick="togglePOCloseOpen()"><i class="bi bi-circle-fill"></i></a>';
    state.value = 1;
  }

  poSearch();
}

function billablePallet(skuNumber) {
  var html = '';
  var xmlhttp=new XMLHttpRequest();
  xmlhttp.onreadystatechange=function() {
    if(xmlhttp.responseText == 'timedOut'){
        window.location.href = "/manager";
    }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {

        html = xmlhttp.responseText;
        overlay(html);

        busyIndicator(false);
    }
  };

  xmlhttp.open("GET","/inventory/index.php?action=billablePallet&skuNumber="+skuNumber);
  xmlhttp.send();

  busyIndicator(true);
}

function pricingChange(skuNumber) {
  var pricingListID = document.getElementById('pricingSelect').value;
  var xmlhttp=new XMLHttpRequest();
  xmlhttp.onreadystatechange=function() {
    if(xmlhttp.responseText == 'timedOut'){
        window.location.href = "/manager";
    }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {

        document.getElementById('pricingDescPH').innerHTML = 
        pricingListID == 1 ? '<input id="pricingDesc" onchange="" class="pricingDescInput" maxlength="50" placeholder="Enter Pricing Desc...">' : '';

        document.getElementById('pricingDataPH').innerHTML = xmlhttp.responseText;
        document.getElementById('pricingDataSubmitBtn').classList.remove("hide");
        document.getElementById('originalPricingListID').value = pricingListID;
        busyIndicator(false);
    }
  };
  xmlhttp.open("GET","/inventory/index.php?action=getPricing&pricingListID="+pricingListID+'&skuNumber='+skuNumber);
  xmlhttp.send();
  busyIndicator(true);
}

function updatePricingData(skuNumber) {
  var btn = document.getElementById('pricingDataSubmitBtn');
  var pricingListID = document.getElementById('pricingSelect').value;
  var originalPricingListID = document.getElementById('originalPricingListID').value;
  var originalPricingDesc = document.getElementById('originalPricingDesc').value;
  var pricingDesc = pricingListID == 1 ? document.getElementById('pricingDesc').value : -1;
  
  var originalOne = document.getElementById('originalPriority1').value;
  var originalTwo = document.getElementById('originalPriority2').value;
  var originalThree = document.getElementById('originalPriority3').value;
  
  var typeIDOne = document.getElementById('pInput1').value;
  var typeIDTwo = document.getElementById('pInput2').value;
  var typeIDThree = document.getElementById('pInput3').value;
  
  var priceOne = document.getElementById('priceInput1').value;
  var priceTwo = document.getElementById('priceInput2').value;
  var priceThree = document.getElementById('priceInput3').value;
  
  var pricingTypeID1 = document.getElementById('pInput1').value;
  var pricingTypeID2 = document.getElementById('pInput2').value;
  var pricingTypeID3 = document.getElementById('pInput3').value;
  
  // Check for duplicates
  var trigger = false;
  var array = [];
  array.push(pricingTypeID1);
  array.push(pricingTypeID2);
  array.push(pricingTypeID3);
  trigger = hasDuplicates(array);
  
  var trigger2 = pricingDesc == null && pricingListID == 1 ? 1 : 0;

  var pricingDesc = pricingDesc == null ? '' : pricingDesc;
  
  if(trigger) {
    toast('Cannot have duplicate types');
  }else if(trigger2) {
    toast('Pricing Description Required.');
  }else {

    if(pricingListID == 1) {
      document.getElementById('pricingDesc').disabled = true;
    }

    document.getElementById('pricingSelect').disabled = true;
    document.getElementById('pInput1').disabled = true;
    document.getElementById('pInput2').disabled = true;
    document.getElementById('pInput3').disabled = true;
    document.getElementById('priceInput1').disabled = true;
    document.getElementById('priceInput2').disabled = true;
    document.getElementById('priceInput3').disabled = true;
    document.getElementById('clearPriorityPH2').innerHTML = '';
    document.getElementById('clearPriorityPH3').innerHTML = '';
    document.getElementById('pricingDataSubmitBtn').onclick = '';
    

    // Init Loaders
    var loader = '<p class="rotate"><i class="bi bi-arrow-repeat"></i></p>';
    document.getElementById('loadingPH1').innerHTML = loader;
    document.getElementById('loadingPH2').innerHTML = loader;
    document.getElementById('loadingPH3').innerHTML = loader
    btn.innerHTML = '<p class="rotate"><i class="bi bi-arrow-repeat"></i></p>';
    btn.classList.add('loadingPDSB');

    let promise = new Promise(function(resolve, reject) {
      var xmlhttp=new XMLHttpRequest();
      xmlhttp.onreadystatechange=function() {
        if(xmlhttp.responseText == 'timedOut') {
        }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {
          resolve();
          // console.log('Pricing reset');
        }
      }; 
      xmlhttp.open("GET","/inventory/index.php?action=resetPricing&skuNumber="+skuNumber);
      xmlhttp.send();
    }).then(
      () => {
        if(pricingListID == 2) {
          priceOne = priceTwo = priceThree = null;
        }
        updatePrice(skuNumber, priceOne, pricingListID, typeIDOne, originalOne, 1, function() {
          updatePrice(skuNumber, priceTwo, pricingListID, typeIDTwo, originalTwo, 2, function() {
            updatePrice(skuNumber, priceThree, pricingListID, typeIDThree, originalThree, 3, function() {
              // Update billable + pricingListID for the item
              if(originalPricingListID == pricingListID || pricingDesc == originalPricingDesc ) {
                updateItemPricing(skuNumber, pricingListID, pricingDesc, finishPricing);
                // console.log('match');
              }else {
                finishPricing();
                // console.log('no match');
              }
            })
          })
        })
      }
    )
    return 'this is data';
  }
}

function updateItemPricing(skuNumber, pricingListID, pricingDesc, myCallback) {
  console.log('Updating Items');
  var xmlhttp=new XMLHttpRequest();
  xmlhttp.onreadystatechange=function() {
  if(xmlhttp.responseText == 'timedOut') {
  }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {
    console.log('Updated Item');
    myCallback();
  }
  }; 
  xmlhttp.open("GET","/inventory/index.php?action=updateItemPricing&skuNumber="+skuNumber+'&pricingListID='+pricingListID+'&pricingDesc='+pricingDesc);
  xmlhttp.send();
}

function finishPricing() {
  var btn = document.getElementById('pricingDataSubmitBtn');

  // End Button Loader
  btn.innerHTML = '<p><i style="color:white;" class="bi bi-check-circle-fill"></i></p>';
  btn.classList.add("pulse");
  btn.classList.remove('loadingPDSB');

  // Close Overlay & Set billable to true
  setTimeout(function(){
  closeOverlayGlobal();
  document.getElementById('billablePallettf').classList.add("tfActive");
  }, 2000);

  // Alert User
  toast('Prices Updated Successfully', 'success');
}

function resetPricing(skuNumber) {
  var xmlhttp=new XMLHttpRequest();
  xmlhttp.onreadystatechange=function() {
    if(xmlhttp.responseText == 'timedOut') {
    }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {
      if(xmlhttp.responseText == 0) {
        // toast('Prices failed to be reset', 'bad');
        result = 0;
      }else {
        result = 1;
      }
      console.log('Pricing is reset' + result);
      return Promise.resolve(1);
    }
  }; 
  xmlhttp.open("GET","/inventory/index.php?action=resetPricing&skuNumber="+skuNumber);
  xmlhttp.send();
}

function updatePrice(skuNumber, price, pricingListID, pricingTypeID, original, num, callback) {
  if(original == pricingTypeID || pricingListID == 1 || pricingTypeID == '') {
    pricingTypeID = pricingTypeID == '' ? -1 : pricingTypeID;
    var xmlhttp=new XMLHttpRequest();
    xmlhttp.onreadystatechange=function() {
      if(xmlhttp.responseText == 'timedOut') {
      }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {
        // alert(xmlhttp.responseText);
        // console.log('Updated Price ' + num);
        if(xmlhttp.responseText == 1) {
          document.getElementById('loadingPH'+num).innerHTML = '<div class="pulse br-50"><i style="color:#2bd126;" class="bi bi-check-circle-fill"></i></div>';
        }else {
          document.getElementById('loadingPH'+num).innerHTML = '<div class="pulse br-50"><i style="color:#c70300;" class="bi bi-x-circle-fill"></i></div>';
        }
        callback();
      }
    }; 
    if(num == 1) {
      var priority = 'One';
    }else if(num == 2) {
      var priority = 'Two';
    }else if(num == 3) {
      var priority = 'Three';
    }
    xmlhttp.open("GET","/inventory/index.php?action=updatePrice&skuNumber="+skuNumber+'&price='+price+'&pricingListID='+pricingListID+'&pricingTypeID='+pricingTypeID+'&priority='+priority);
    xmlhttp.send();
  }else {
    document.getElementById('loadingPH'+num).innerHTML = '<div class="pulse br-50"><i style="color:#2bd126;" class="bi bi-check-circle-fill"></i></div>';
    callback();
  }
}

function updatePriority(priority, skuNumber) {
  var pricingListID = document.getElementById('pricingSelect').value;
  var pricingTypeID = document.getElementById('pInput'+priority).value;

  if(priority != 1) {
    document.getElementById('clearPriorityPH'+priority).innerHTML = '<a href="#" onclick="clearPriority(`' + priority + '`)"><i class="bi bi-x-lg"></i></a>';
  }

  if(pricingListID == 1 || pricingListID == 2) {
    
    // Do nothing if customer default or custom. Except this:
    priceChange(priority, pricingTypeID);
    
  }else {
    
    document.getElementById('pInput1').disabled = true;
    document.getElementById('pInput2').disabled = true;
    document.getElementById('pInput3').disabled = true;
    document.getElementById('pricingDataSubmitBtn').classList.add("hide");
    
    var priceInput = document.getElementById('priceInput'+priority);
    var xmlhttp=new XMLHttpRequest();
    xmlhttp.onreadystatechange=function() {
      if(xmlhttp.responseText == 'timedOut') {
      }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {
        // alert(xmlhttp.responseText);
        if(xmlhttp.responseText == -1) {
          // Error
          toast('Something went wrong...', 'bad');
        }else if(xmlhttp.responseText == -2) {
          // Custom
          priceInput.disabled = false;
        }else {
          // Display price
          priceInput.value = xmlhttp.responseText;
        }
        priceChange(priority, pricingTypeID);

        document.getElementById('pInput1').disabled = false;
        document.getElementById('pInput2').disabled = false;
        document.getElementById('pInput3').disabled = false;
        document.getElementById('pricingDataSubmitBtn').classList.remove("hide");
        
        busyIndicator(false);
      }
    }; 
    xmlhttp.open("GET","/inventory/index.php?action=updatePriority&pricingTypeID="+pricingTypeID+'&pricingListID='+pricingListID);
    xmlhttp.send();
    busyIndicator(true);
  }
}

function priceChange(priority, pricingTypeID) {
  document.getElementById('pricingDataSubmitBtn').classList.remove("hide");
  document.getElementById('originalPriority'+priority).value = pricingTypeID;
}

function clearPriority(priority) {
  document.getElementById('pInput'+priority).value = null;
  document.getElementById('priceInput'+priority).value = null;
  document.getElementById('clearPriorityPH'+priority).innerHTML = '';
}

function pricingDescUpdate() {
  document.getElementById('pricingDataSubmitBtn').classList.remove("hide");
  document.getElementById('originalPricingDesc').value = document.getElementById('pricingDesc').value;
}

function openHistorical(serial) {
  var xmlhttp=new XMLHttpRequest();
  xmlhttp.onreadystatechange=function() {
    if(xmlhttp.responseText == 'timedOut'){
        window.location.href = "/manager";
    }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {
      overlay(xmlhttp.responseText);
      busyIndicator(false);
    }
  };
  xmlhttp.open("GET","/inventory/index.php?action=openHistorical&serial="+serial);
  xmlhttp.send();
  busyIndicator(true);
}