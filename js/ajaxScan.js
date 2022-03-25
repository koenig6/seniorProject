/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function scan(type) {
  var toggle = null;
  if (type === undefined) {
    type = 0;
  } else {
    var status = document.getElementById('week3');

    if (status.checked) {
      toggle = 0;
    } else {
      toggle = 1;
    }
  }

  var input = document.getElementById('scan').value;
  var mod = null;
  var audio = new Audio('/audio/Error.wav');

  var str = input;
  if (str.charAt(0) == 'P') {
    shipSummaryTag(input);
    return;
  }
  /*
    if (str.charAt(0) == 'W'){
        window.location.href = "/jobClock/index.php?action=openWOLaborAssigner&workOrderNumber="+input
        return;
    }
    */

  if (str.charAt(0) == 'O') {
    window.location.href =
      '/forklift/index.php?action=shippingMenu&orderNumber=' + input;
    return;
  }

  var xmlhttp = new XMLHttpRequest();
  xmlhttp.onreadystatechange = function () {
    if (xmlhttp.responseText == 'timedOut') {
      window.location.href = '/manager';
    } else if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
      // alert(xmlhttp.responseText);

      if (xmlhttp.responseText == 1) {
        if (input.charAt(0) == '*' || input.charAt(0) == 'S') {
          mod = input.substring(1);
          window.location.href =
            '/forklift/index.php?action=sku&serialID=' + mod;
        } else {
          if (type == 0) {
            window.location.href =
              '/forklift/index.php?action=scan&serialID=' + input;
          } else if (type == 1 && toggle == 0) {
            bulkPick(input);
          } else if (type == 1 && toggle == 1) {
            bulkShip(input);
          }
        }
      } else {
        document.getElementById('scan').value = '';
        audio.play();
      }
    }
  };

  xmlhttp.open(
    'GET',
    '/forklift/index.php?action=scanValidation&scan=' + input
  );
  xmlhttp.send();
}

function expandHours(serialID, binLocationID) {
  var expandID = 'expand' + binLocationID;
  var expandIcon = 'expandIcon' + binLocationID;
  var anchor = (document.getElementById(expandID).innerHTML =
    '<a href="#" onclick="contractHours(' +
    serialID +
    ', ' +
    binLocationID +
    ');"><span>0</span></a>');

  var xmlhttp = new XMLHttpRequest();
  xmlhttp.onreadystatechange = function () {
    if (xmlhttp.responseText == 'timedOut') {
      window.location.href = '/manager';
    } else if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
      document.getElementById(expandID).innerHTML = xmlhttp.responseText;
      document.getElementById(expandIcon).innerHTML = anchor;
    }
  };

  xmlhttp.open(
    'GET',
    '/forklift/index.php?action=expandSummary&serialID=' +
      serialID +
      '&binLocationID=' +
      binLocationID
  );
  xmlhttp.send();
}

function contractHours(serialID, binLocationID) {
  var expandID = 'expand' + binLocationID;
  var expandIcon = 'expandIcon' + binLocationID;
  var anchor =
    '<a href="#" onclick="expandHours(' +
    serialID +
    ', ' +
    binLocationID +
    ');"><span>1</span></a>';

  document.getElementById(expandIcon).innerHTML = anchor;
  document.getElementById(expandID).innerHTML = '<div></div>';
}

function pickAssignValidate(serialID) {
  var xmlhttp = new XMLHttpRequest();
  xmlhttp.onreadystatechange = function () {
    if (xmlhttp.responseText == 'timedOut') {
      window.location.href = '/manager';
    } else if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
      document.getElementById('assignWorkOrderPanel').innerHTML =
        xmlhttp.responseText;
    }
  };

  xmlhttp.open(
    'GET',
    '/forklift/index.php?action=pickAssignValidate&serialID=' + serialID
  );
  xmlhttp.send();
}

function pickAssign(serialID) {
  var workOrderNumber = document.getElementById('workOrder').value;
  var xmlhttp = new XMLHttpRequest();
  xmlhttp.onreadystatechange = function () {
    if (xmlhttp.responseText == 'timedOut') {
      window.location.href = '/manager';
    } else if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
      if (xmlhttp.responseText == 1) {
        closeOverlay();
        pick(serialID);
      } else {
        document.getElementById('errorMessage').innerHTML =
          'Error: Unable to locate work order #';
      }
    }
  };
  xmlhttp.open(
    'GET',
    '/forklift/index.php?action=pickAssign&serialID=' +
      serialID +
      '&workOrderNumber=' +
      workOrderNumber
  );
  xmlhttp.send();
}

function closeOverlay() {
  document.getElementById('assignWorkOrderPanel').innerHTML = '';
}

function pick(serialID) {
  check = document.getElementById('focusOff').innerHTML;
  // status = document.getElementById('binStatus').innerHTML;

  // toast(status, 'info');

  if (check == 'Deactivated') {
    alertDeactivated();
  } else if (check == 'Shipped') {
    ok('This pallet is shipped.');
  } else if (check == 'Not Received') {
    ok('This pallet is Not Received.');
  } else if (check == 'Hold') {
    ok('This pallet is on hold.');
  } else {
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function () {
      if (xmlhttp.responseText == 'timedOut') {
        window.location.href = '/manager';
      } else if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
        if (xmlhttp.responseText == 1) {
          document.getElementById('focus').innerHTML =
            '<div id="focusOn">Picked</div>';
          setTimeout(reDirect, 1500);
        }
      }
    };

    xmlhttp.open(
      'GET',
      '/forklift/index.php?action=pickSerial&serialID=' +
        serialID +
        '&status=' +
        status
    );
    xmlhttp.send();
  }
}

function returnPick(serialID) {
  var xmlhttp = new XMLHttpRequest();
  xmlhttp.onreadystatechange = function () {
    if (xmlhttp.responseText == 'timedOut') {
      window.location.href = '/manager';
    } else if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
      if (xmlhttp.responseText == 1) {
        document.getElementById('focus').innerHTML =
          '<div id="focusOn">In Bin</div>';
        setTimeout(reDirect, 1500);
      }
    }
  };

  xmlhttp.open(
    'GET',
    '/forklift/index.php?action=returnSerial&serialID=' + serialID + '&status=4'
  );
  xmlhttp.send();
}

function reDirect() {
  window.location.href = '/forklift';
}

function validateBin(serialID) {
  var scanValue = document.getElementById('scan').value;

  if (scanValue != '') {
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function () {
      if (xmlhttp.responseText == 'timedOut') {
        window.location.href = '/manager';
      } else if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
        // alert(xmlhttp.responseText);
        if (xmlhttp.responseText == 1) {
          scanBin(serialID, scanValue);
        } else {
          scan();
        }
      }
    };
    xmlhttp.open(
      'GET',
      '/forklift/index.php?action=validateBin&binLocation=' + scanValue
    );
    xmlhttp.send();
  }
}

function scanBin(serialID, scanValue) {
  // alert(`serialID: ${serialID}, scanValue: ${scanValue}`);

  var xmlhttp = new XMLHttpRequest();
  xmlhttp.onreadystatechange = function () {
    if (xmlhttp.responseText == 'timedOut') {
      window.location.href = '/manager';
    } else if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
      var bin = xmlhttp.responseText;

      // alert(bin);

      document.getElementById('bin').innerHTML =
        '<div id="binLocationFocus">' + bin + '</div>';
      setTimeout(reDirect, 1500);
    }
  };

  xmlhttp.open(
    'GET',
    '/forklift/index.php?action=scanBin&serialID=' +
      serialID +
      '&binLocation=' +
      scanValue
  );
  xmlhttp.send();
}

function ship(serialID) {
  check = document.getElementById('focusOff').innerHTML;

  if (check == 'Deactivated') {
    alertDeactivated();
  } else if (check == 'Shipped') {
    ok('This pallet is shipped.');
  } else if (check == 'Not Received') {
    ok('This pallet is Not Received.');
  } else if (check == 'Hold') {
    ok('This pallet is on hold.');
  } else {
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function () {
      if (xmlhttp.responseText == 'timedOut') {
        window.location.href = '/manager';
      } else if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
        if (xmlhttp.responseText == 1) {
          document.getElementById('focus').innerHTML =
            '<div id="focusOn">Shipped</div>';
          setTimeout(reDirect, 1500);
        }
      }
    };

    xmlhttp.open(
      'GET',
      '/forklift/index.php?action=shipSerial&serialID=' + serialID + '&status=3'
    );
    xmlhttp.send();
  }
}

function alertDeactivated() {
  // alert("This Pallet is Deativated.");
  ok('This Pallet is Deativated.');
}

function toggle(sku) {
  var status = document.getElementById('week3');

  if (status.checked) {
    pickedPallets(sku);
  } else {
    shippedPallets(sku);
  }
}

function shippedPallets(sku) {
  var xmlhttp = new XMLHttpRequest();
  xmlhttp.onreadystatechange = function () {
    if (xmlhttp.responseText == 'timedOut') {
      window.location.href = '/manager';
    } else if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
      document.getElementById('table2').innerHTML = xmlhttp.responseText;
      document.getElementById('scan').focus();
    }
  };

  xmlhttp.open(
    'GET',
    '/forklift/index.php?action=shippedPallets&serialID=' + sku
  );
  xmlhttp.send();
}

function pickedPallets(sku) {
  var xmlhttp = new XMLHttpRequest();
  xmlhttp.onreadystatechange = function () {
    if (xmlhttp.responseText == 'timedOut') {
      window.location.href = '/manager';
    } else if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
      document.getElementById('table2').innerHTML = xmlhttp.responseText;
      document.getElementById('scan').value = '';
      document.getElementById('scan').focus();
    }
  };

  xmlhttp.open(
    'GET',
    '/forklift/index.php?action=pickedPallets&serialID=' + sku
  );
  xmlhttp.send();
}

function bulkPick(serial) {
  var xmlhttp = new XMLHttpRequest();
  xmlhttp.onreadystatechange = function () {
    if (xmlhttp.responseText == 'timedOut') {
      window.location.href = '/manager';
    } else if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
      var myArr = JSON.parse(xmlhttp.responseText);
      document.getElementById('table1').innerHTML = myArr[0];
      document.getElementById('table2').innerHTML = myArr[1];
      document.getElementById('scan').value = '';
      document.getElementById('scan').focus();
    }
  };

  xmlhttp.open('GET', '/forklift/index.php?action=bulkPick&serialID=' + serial);
  xmlhttp.send();
}

function bulkShip(serial) {
  var xmlhttp = new XMLHttpRequest();
  xmlhttp.onreadystatechange = function () {
    if (xmlhttp.responseText == 'timedOut') {
      window.location.href = '/manager';
    } else if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
      var myArr = JSON.parse(xmlhttp.responseText);
      document.getElementById('table1').innerHTML = myArr[0];
      document.getElementById('table2').innerHTML = myArr[1];
      document.getElementById('scan').value = '';
      document.getElementById('scan').focus();
    }
  };

  xmlhttp.open('GET', '/forklift/index.php?action=bulkShip&serialID=' + serial);
  xmlhttp.send();
}

function shipSummaryTag(summaryID) {
  var xmlhttp = new XMLHttpRequest();

  var audio = new Audio('/audio/Error.wav');

  xmlhttp.onreadystatechange = function () {
    if (xmlhttp.responseText == 'timedOut') {
      window.location.href = '/manager';
    } else if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
      if (xmlhttp.responseText == 1) {
        document.getElementById('scan').value = '';
        document.getElementById('scan').focus();
        document.getElementById('errorMessage').innerHTML =
          summaryID + ' : Successfully Shipped';
        setTimeout(clearError, 3000);
      } else if (xmlhttp.responseText == -1) {
        document.getElementById('scan').value = '';
        audio.play();
        document.getElementById('errorMessage').innerHTML =
          'Summary Order Does Not Exsist';
        setTimeout(clearError, 3000);
      } else if (xmlhttp.responseText == -2) {
        document.getElementById('scan').value = '';
        audio.play();
        document.getElementById('errorMessage').innerHTML =
          'Summary Order: already been shipped';
        setTimeout(clearError, 3000);
      } else {
        document.getElementById('scan').value = '';
        audio.play();
        document.getElementById('errorMessage').innerHTML = 'Failed to Ship';
        setTimeout(clearError, 3000);
      }
    }
  };

  xmlhttp.open(
    'GET',
    '/forklift/index.php?action=summaryShip&summaryID=' + summaryID
  );
  xmlhttp.send();
}

function clearError() {
  document.getElementById('errorMessage').innerHTML = '';
}

function uploadHeader(orderNumber) {
  clearError();
  closeOverlay();

  var xmlhttp = new XMLHttpRequest();
  xmlhttp.onreadystatechange = function () {
    if (xmlhttp.responseText == 'timedOut') {
      window.location.href = '/manager';
    } else if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
      // alert(xmlhttp.responseText);

      var myArr = JSON.parse(xmlhttp.responseText);
      document.getElementById('orderDetailsBox').innerHTML = myArr[0];
      document.getElementById('orderItemsBox').innerHTML = myArr[1];
      document.getElementById('orderCommandCenter').innerHTML = myArr[2];

      if (myArr[3] != null) {
        document.getElementById('shippedInfo').innerHTML = myArr[3];
        document.getElementById('shippedPallets').innerHTML = myArr[4];
        try {
          document.getElementById('masterTagMenuBtnSpot').innerHTML =
            '<a href="#" id="masterTagMenuBtn" title="Master Tag" onclick="openMasterTag(`' +
            orderNumber +
            '`)"><span>t</span></a>';
        } catch (err) {}
      } else {
        document.getElementById('shippedInfo').innerHTML =
          '<h2 style="margin-left: 0.8em; margin-top: 0.5em;">Available Pallets</h2>';
        document.getElementById('shippedPallets').innerHTML =
          '<h2 style="margin-left: 0.8em; margin-top: 0.5em;" class="align-center">Picked Pallets</h2>';
      }

      busyIndicator(false);
      window.scrollTo(0, 0);
      // document.getElementById('scanPalletInput').autofocus;
    }
  };

  xmlhttp.open(
    'GET',
    '/forklift/index.php?action=uploadHeader&orderNumber=' + orderNumber
  );
  xmlhttp.send();

  busyIndicator(true);
}

function showShippingInfo(
  partNumber,
  pickPackItemsID,
  customerSONumber,
  shippingID,
  skuNumber
) {
  // alert(shippingID);

  clearError();
  var xmlhttp = new XMLHttpRequest();
  xmlhttp.onreadystatechange = function () {
    if (xmlhttp.responseText == 'timedOut') {
      window.location.href = '/manager';
    } else if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
      // alert(xmlhttp.responseText);

      var myArr = JSON.parse(xmlhttp.responseText);
      document.getElementById('shippedInfo').innerHTML = myArr[0];
      document.getElementById('shippedPallets').innerHTML = myArr[1];

      try {
        document.getElementById('masterTagMenuBtnSpot').innerHTML =
          '<a href="#" id="masterTagMenuBtn" title="Master Tag" onclick="openMasterTag(`' +
          customerSONumber +
          '`)"><span>t</span></a>';
        // document.getElementById('masterTagMenuBtnSpot').innerHTML = '<a href="#" id="masterTagMenuBtn" class="disable" title="Master Tag" onclick=""><span>t</span></a>';
      } catch (err) {}

      busyIndicator(false);
      // window.scrollTo(0, 0);
      // document.getElementById('scanPalletInput').focus();
    }
  };

  xmlhttp.open(
    'GET',
    '/forklift/index.php?action=shippedInfo&partNo=' +
      partNumber +
      '&pickPackItemsID=' +
      pickPackItemsID +
      '&customerSONumber=' +
      customerSONumber +
      '&shippingID=' +
      shippingID +
      '&skuNumber=' +
      skuNumber
  );
  xmlhttp.send();

  busyIndicator(true);
}

function masterTagAlert() {
  toast('Choose a part first');
}

function customerChangeForklift(value) {
  clearError();
  var xmlhttp = new XMLHttpRequest();
  xmlhttp.onreadystatechange = function () {
    if (xmlhttp.responseText == 'timedOut') {
      window.location.href = '/manager';
    } else if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
      //            alert (xmlhttp.responseText);
      document.getElementById('ordersList').innerHTML = xmlhttp.responseText;

      busyIndicator(false);
      window.scrollTo(0, 0);
    }
  };

  xmlhttp.open(
    'GET',
    '/forklift/index.php?action=reloadOrders&shipmentGroupID=' +
      value +
      '&status=1'
  );
  xmlhttp.send();

  busyIndicator(true);
}

function pickWholePallet(
  skuNo,
  pickPackItemsID,
  customerSONumber,
  shippingID,
  partNum
) {
  // scanInput = document.getElementById('scanPalletInput').value;
  var masterTagMode = document.getElementById('masterTagMode').checked ? 1 : 0;

  if (masterTagMode == true) {
    try {
      var masterTagID = document.getElementById('selectedMasterPallet').value;
    } catch {
      var masterTagID = 1;
    }
    // toast('Fire Master Tag Mode: ' + masterTagMode + ' | ' + masterTagID, 'info');
  } else {
    var masterTagID = 0;
  }

  closeOverlay();

  clearError();
  var xmlhttp = new XMLHttpRequest();
  xmlhttp.onreadystatechange = function () {
    if (xmlhttp.responseText == 'timedOut') {
      window.location.href = '/manager';
    } else if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
      // alert (xmlhttp.responseText);

      var myArr = JSON.parse(xmlhttp.responseText);

      $message = 'Successfully Picked S#: ' + myArr[4];
      toast($message, 'success');

      document.getElementById('shippedInfo').innerHTML = myArr[1];
      document.getElementById('shippedPallets').innerHTML = myArr[2];
      document.getElementById('orderItemsBox').innerHTML = myArr[3];

      // document.getElementById("scanPalletInput").autofocus;

      busyIndicator(false);
      window.scrollTo(0, 0);

      if (masterTagMode == 1) {
        masterTag(masterTagID, 'false', false);
      }
    }
  };

  xmlhttp.open(
    'GET',
    '/forklift/index.php?action=pickWholePallet&scanInput=' +
      scanInput +
      '&skuNumber=' +
      skuNo +
      '&pickPackItemsID=' +
      pickPackItemsID +
      '&customerSONumber=' +
      customerSONumber +
      '&shippingID=' +
      shippingID +
      '&partNum=' +
      partNum +
      '&masterTagID=' +
      masterTagID
  );
  xmlhttp.send();

  busyIndicator(true);
}

function manualPalletEntry(
  skuNo,
  pickPackItemsID,
  customerSONumber,
  shippingID,
  partNum,
  qty
) {
  // scanInput = document.getElementById('scanPalletInput').value;
  customerID = document.getElementById('customerID').value;
  var masterTagMode = document.getElementById('masterTagMode').value;

  // toast(shippingID);

  // alert(`skuNo: ${skuNo} pickPackItemsID: ${pickPackItemsID} customerSONumber: ${customerSONumber} shippingID: ${shippingID} partNum: ${partNum} qty: ${qty} scanInput: ${scanInput} masterTagMode: ${masterTagMode}`);

  if (masterTagMode == true) {
    try {
      var masterTagID = document.getElementById('selectedMasterPallet').value;
    } catch {
      var masterTagID = 1;
    }
    // toast('Fire Master Tag Mode: ' + masterTagMode + ' | ' + masterTagID, 'info');
  } else {
    var masterTagID = 0;
  }

  closeOverlay();

  clearError();
  var xmlhttp = new XMLHttpRequest();
  xmlhttp.onreadystatechange = function () {
    if (xmlhttp.responseText == 'timedOut') {
      window.location.href = '/manager';
    } else if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
      // alert(xmlhttp.responseText);

      var myArr = JSON.parse(xmlhttp.responseText);

      if (!myArr[0]) {
        $message = 'Successfully Picked ' + qty + ' from S#: ' + myArr[4];
        toast($message, 'success');
      } else {
        $message = 'Failed to picked ' + qty + ' from S#: ' + myArr[4];
        toast($message);
      }

      document.getElementById('shippedInfo').innerHTML = myArr[1];
      document.getElementById('shippedPallets').innerHTML = myArr[2];
      document.getElementById('orderItemsBox').innerHTML = myArr[3];

      // alert(myArr[5]);

      // document.getElementById("scanPalletInput").autofocus;

      if (masterTagMode == 1) {
        masterTag(masterTagID, false);
      }

      busyIndicator(false);
      window.scrollTo(0, 0);
    }
  };

  xmlhttp.open(
    'GET',
    '/forklift/index.php?action=manualPalletEntry&scanInput=' +
      scanInput +
      '&skuNumber=' +
      skuNo +
      '&pickPackItemsID=' +
      pickPackItemsID +
      '&customerSONumber=' +
      customerSONumber +
      '&shippingID=' +
      shippingID +
      '&partNum=' +
      partNum +
      '&qty=' +
      qty +
      '&customerID=' +
      customerID +
      '&masterTagID=' +
      masterTagID
  );
  xmlhttp.send();

  busyIndicator(true);
}

function insertMaster() {
  // serialNumber = document.getElementById('scanPalletInput').value;

  closeOverlay();

  clearError();
  var xmlhttp = new XMLHttpRequest();
  xmlhttp.onreadystatechange = function () {
    if (xmlhttp.responseText == 'timedOut') {
      window.location.href = '/manager';
    } else if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
      // alert(xmlhttp.responseText);
    }
  };

  xmlhttp.open(
    'GET',
    '/forklift/index.php?action=insertMaster&serialNumber=' + serialNumber
  );
  xmlhttp.send();

  busyIndicator(true);
}

function removeMaster() {
  // serialNumber = document.getElementById('scanPalletInput').value;

  closeOverlay();

  clearError();
  var xmlhttp = new XMLHttpRequest();
  xmlhttp.onreadystatechange = function () {
    if (xmlhttp.responseText == 'timedOut') {
      window.location.href = '/manager';
    } else if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
      // alert(xmlhttp.responseText);
    }
  };

  xmlhttp.open(
    'GET',
    '/forklift/index.php?action=removeMaster&serialNumber=' + serialNumber
  );
  xmlhttp.send();

  busyIndicator(true);
}

function toggleManualMode() {
  var manual = document.getElementById('manualMode').checked;

  if (manual) {
    document.getElementById('manualModeBtnSection').innerHTML = `
            <a href="#" id="masterTagMenuBtn" title="Manual Mode" onclick="toggleManualMode()"><span>&</span></a>
            <input id="manualMode" class="hide" type="checkbox">
        `;
  } else {
    document.getElementById('manualModeBtnSection').innerHTML = `
            <a href="#" id="masterTagMenuBtn" title="Manual Mode" onclick="toggleManualMode()"><span style="color: yellow">&</span></a>
            <input id="manualMode" class="hide" type="checkbox" checked>
        `;
  }
}

function getPickedPallets(skuNumber, shippingID) {
  clearError();
  var xmlhttp = new XMLHttpRequest();
  xmlhttp.onreadystatechange = function () {
    if (xmlhttp.responseText == 'timedOut') {
      window.location.href = '/manager';
    } else if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
      // alert(xmlhttp.responseText);

      var myArr = JSON.parse(xmlhttp.responseText);

      // alert(myArr);

      return myArr;
    }
  };
  xmlhttp.open(
    'GET',
    '/forklift/index.php?action=getPickedPalletSerial&skuNumber=' +
      skuNumber +
      '&shippingID=' +
      shippingID
  );
  xmlhttp.send();
}

// Super Secret Scan Pallet Input
function scanPalletS() {
  var serialNumber = prompt('Enter Serial#: ');
  if (serialNumber >= 5) {
    scanPallet(serialNumber);
  }
}

function goToOrderPart(serialNumber) {
  scanPallet(serialNumber, false, false, false, true);
}

function scanPallet(
  serialNumber = false,
  manualQty = false,
  pickWhole = false,
  unpick = false,
  goTo = false
) {
  var customerSONumber = document.getElementById('customerSONumber');
  customerSONumber = customerSONumber ? customerSONumber.value : false;

  var shippingID = document.getElementById('shippingID').value;

  // Check if order is selected
  if (!customerSONumber) {
    toast('You need to select an order.');
  } else {
    // Get Variables Stored In Page
    var manual = document.getElementById('manualMode');
    manual = manual ? manual.checked : false;
    var masterTagMode = document.getElementById('masterTagMode');
    masterTagMode = masterTagMode ? masterTagMode.checked : false;
    var headerStatus = document.getElementById('headerStatus').value;

    // Manual Picking Mode
    if (manual && !manualQty && !pickWhole && !unpick) {
      // Check if the pallet is already picked
      var check = document
        .getElementById('pickedSkuList')
        .value.includes(serialNumber);

      // Open Manual Picking Menu
      if (check) {
        manualPicking(serialNumber, customerSONumber, 'manual');
        return;
      }
    }

    // Master Pallet Mode
    if (masterTagMode) {
      var masterTagID = document.getElementById('selectedMasterPallet');
      masterTagID = masterTagID ? masterTagID.value : 1;
    } else {
      var masterTagID = 0;
    }

    clearError();
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function () {
      if (xmlhttp.responseText == 'timedOut') {
      } else if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
        var res = xmlhttp.responseText;

        // console.log(JSON.parse(res));
        // console.log(res);

        // No Serial Found
        if (res == 'noserial') {
          toast('Serial does not exist.', 'error');
        } else if (res == 'inactive') {
          toast('Serial is not available.', 'error');
        } else if (res == 'noinv') {
          // Something is wrong if this happens...
        } else if (res == 'newInvFailed') {
          toast('Failed to create new tag.', 'error');
        } else if (res == 'notEnoughInv') {
          toast('You cannot pick more than the pallet contains.', 'error');
        } else if (res == 'deactivated') {
          toast('Serial is Deactivated.', 'error');
        } else if (res == 'notreceived') {
          toast('Pallet is Not Received.', 'error');
        } else if (res == 'orderComplete') {
          toast('Order is already completed.', 'error');
        } else if (res == 'pickingIssue') {
          toast('Something went wrong...', 'error');
        } else {
          // Parse JSON
          var arr = JSON.parse(res);

          // Picked Qty exceeds Order Required Quantity
          if (arr[1] == 'reqQtyMet') {
            var partNumber = arr[0];
            manualPicking(serialNumber, customerSONumber, 'overpick');
          } else {
            var code = arr[0];
            var availablePallets = arr[1];
            var pickedPallets = arr[2];
            var orderItems = arr[3];

            console.log(arr);

            // Alert User
            switch (code) {
              case 'qtyMet':
                toast('Ordered Quantity is already met.', 'error');
                break;
              case 'donothing':
                break;
              case 'merge':
                toast('Pallet Merged', 'success');
                break;
              case 'remove':
                toast('Pallet Un-Picked', 'success');
                break;
              case 'surplus':
                toast('Pallet Split', 'success');
                break;
              case 'picked':
                toast('Pallet Picked', 'success');
                break;
            }

            // Rebuild Page
            document.getElementById('shippedInfo').innerHTML = availablePallets;
            document.getElementById('shippedPallets').innerHTML = pickedPallets;
            document.getElementById('orderItemsBox').innerHTML = orderItems;

            // Re-open master slider if applicable
            if (masterTagMode == true) {
              masterTag(masterTagID, false);
            }

            if (code) {
              closeOverlay();
              busyIndicator(false);
            }
          }
        }

        !Array.isArray(res) && busyIndicator(false);
      }
    };
    xmlhttp.open(
      'GET',
      '/forklift/index.php?action=scanPallet&serialNumber=' +
        serialNumber +
        '&masterTagID=' +
        masterTagID +
        '&customerSONumber=' +
        customerSONumber +
        '&headerStatus=' +
        headerStatus +
        '&goTo=' +
        (goTo ? 1 : 0) +
        '&manualQty=' +
        (manualQty ? manualQty : 0) +
        '&pickWhole=' +
        (pickWhole ? 1 : 0) +
        '&shippingID=' +
        shippingID
    );
    xmlhttp.send();

    busyIndicator(true);
  }
}

function scanPallert(
  skuNumber,
  pickPackItemsID,
  customerSONumber,
  shippingID,
  partNumber,
  serialInput = false
) {
  // All I need
  // $serialNumber, $masterTagID, $shippingID, $customerSONumber, $serialInput

  var serialNumber = !serialInput
    ? document.getElementById('scanPalletInput').value
    : serialInput;
  var manual = document.getElementById('manualMode').checked;
  var masterTagMode = document.getElementById('masterTagMode').value;

  // toast(manual, 'info');

  if (manual) {
    // Check if the pallet is already picked
    var str = document.getElementById('pickedSkuList').value; // This sku list needs to be updated to include the skus from the master pallets! --------------------------
    var check = str.includes(serialNumber);

    var pickedPallets = getPickedPallets(skuNumber, shippingID);

    // alert(pickedPallets);

    if (!check) {
      // Open Manual Picking Menu
      manualPicking(skuNumber, pickPackItemsID, customerSONumber, partNumber);

      return;
    }
  }
  // alert('fire');

  if (masterTagMode == true) {
    try {
      var masterTagID = document.getElementById('selectedMasterPallet').value;
    } catch {
      var masterTagID = 1;
    }
    // toast('Fire Master Tag Mode: ' + masterTagMode + ' | ' + masterTagID, 'info');
  } else {
    var masterTagID = 0;
  }

  closeOverlay();

  busyIndicator(true);

  clearError();
  var xmlhttp = new XMLHttpRequest();
  xmlhttp.onreadystatechange = function () {
    if (xmlhttp.responseText == 'timedOut') {
      window.location.href = '/manager';
    } else if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
      alert(xmlhttp.responseText);

      var myArr = JSON.parse(xmlhttp.responseText);

      // 0 : Error Trigger
      // 1 : Available Pallets
      // 2 : Picked Pallets
      // 3 : Order Items
      // 4 : New Qty
      // 5 : Old Inventory ID

      if (myArr[0] != null) {
        if (myArr[0] == 'noPallet') {
          toast('No Serial# Found');
        } else if (myArr[0] == 'wrongSku') {
          toast('Scanned pallet does not match the order');
        } else if (myArr[0] == 'reqQtyMet') {
          overShipWarning(serialNumber, customerSONumber);
        } else if (myArr[0] == 'deactivated') {
          toast('Sku# Deactivated');
        } else if (myArr[0] == 'notReceived') {
          toast('Sku# not Received');
        } else if (myArr[0] == 'pta') {
          toast('Something went wrong... Please try Again');
        }
      } else {
        if (myArr[1] == 'surplus') {
          splitPallet(
            myArr[5],
            myArr[6],
            myArr[7],
            myArr[8],
            skuNumber,
            pickPackItemsID,
            customerSONumber,
            shippingID,
            partNumber
          );

          // if($return == 'surplus') {
          //     $tranArr[5] = $serialNumber;
          //     $tranArr[6] = $orderedQty;
          //     $tranArr[7] = $invQty;
          //     $tranArr[8] = $picked;
          // }
        } else {
          code = myArr[1];

          if (code == 'merge') {
            toast(
              'Successfully merged ' + serialNumber + ' into ' + myArr[6],
              'success'
            );
          } else if (code == 'remove') {
            toast('Successfully un-picked S#: ' + serialNumber, 'success');
          } else if (code == 'picked') {
            toast('Successfully picked S#: ' + serialNumber, 'success');
          }

          if (myArr[2] != null) {
            document.getElementById('shippedInfo').innerHTML = myArr[2];
            document.getElementById('shippedPallets').innerHTML = myArr[3];
            document.getElementById('orderItemsBox').innerHTML = myArr[4];

            if (masterTagMode == true) {
              masterTag(masterTagID, false);
            }
          }

          // if(myArr[15] != null) {
          //     alert('it is set: ' + myArr[15]);
          // }else {
          //     alert('it is null');
          // }

          // document.getElementById('orderItemsBox').innerHTML = myArr[15] + ' |     ' + myArr[16];
        }
      }
      if (myArr[1] != 'surplus') {
        busyIndicator(false);
      }
      window.scrollTo(0, 0);
      closeOverlay;
    }
  };

  xmlhttp.open(
    'GET',
    '/forklift/index.php?action=scanPallet&serialNumber=' +
      serialNumber +
      '&skuNumber=' +
      skuNumber +
      '&pickPackItemsID=' +
      pickPackItemsID +
      '&customerSONumber=' +
      customerSONumber +
      '&shippingID=' +
      shippingID +
      '&partNumber=' +
      partNumber +
      '&masterTagID=' +
      masterTagID +
      '&manual=' +
      manualMode
  );

  xmlhttp.send();
  busyIndicator(true);
}

function splitPallet(
  serialNumber,
  orderedQty,
  invQty,
  picked,
  skuNumber,
  pickPackItemsID,
  customerSONumber,
  shippingID,
  partNumber
) {
  // alert('serial: ' + serialNumber + ', ordredQty: ' + orderedQty + ', invQty: ' + invQty + ', picked: ' + picked + ', customersonumber: ' + customerSONumber + ', serialNum' + serialNumber + ', orderedqty: ' + orderedQty + ', invQty;  ' + invQty + ', picekd: ' + picked + ', customersoNumber: ' + customerSONumber + ', shippingID: ' + shippingID + ', skuNumber: ' + skuNumber + ', part#:  ' + partNumber + ', pickpackITEms: ' + pickPackItemsID);

  var master = document.getElementById('masterTagMode');
  master = master ? master.value : 0;

  if (masterTagMode == true) {
    try {
      var masterTagID = document.getElementById('selectedMasterPallet').value;
    } catch {
      var masterTagID = 1;
    }
    toast(
      'Fire Master Tag Mode: ' + masterTagMode + ' | ' + masterTagID,
      'info'
    );
  } else {
    var masterTagID = 0;
  }

  clearError();
  var xmlhttp = new XMLHttpRequest();
  xmlhttp.onreadystatechange = function () {
    if (xmlhttp.responseText == 'timedOut') {
      window.location.href = '/manager';
    } else if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
      // alert(xmlhttp.responseText);
      var myArr = JSON.parse(xmlhttp.responseText);

      if (myArr[0] == 0) {
        toast('Something went wrong...');
      } else {
        toast(
          'Successfully split ' + myArr[4] + ' from ' + myArr[5],
          'success'
        );
      }

      if (myArr[2] != null) {
        document.getElementById('shippedInfo').innerHTML = myArr[1];
        document.getElementById('shippedPallets').innerHTML = myArr[2];
        document.getElementById('orderItemsBox').innerHTML = myArr[3];

        if (masterTagMode == 1) {
          masterTag(masterTagID, false);
        }
      }

      // document.getElementById('orderItemsBox').innerHTML = myArr[7];

      busyIndicator(false);
      window.scrollTo(0, 0);

      closeOverlay();
    }
  };

  xmlhttp.open(
    'GET',
    '/forklift/index.php?action=splitPallet&serialNumber=' +
      serialNumber +
      '&orderedQty=' +
      orderedQty +
      '&invQty=' +
      invQty +
      '&picked=' +
      picked +
      '&customerSONumber=' +
      customerSONumber +
      '&shippingID=' +
      shippingID +
      '&skuNumber=' +
      skuNumber +
      '&partNumber=' +
      partNumber +
      '&pickPackItemsID=' +
      pickPackItemsID +
      '&masterTagID=' +
      masterTagID
  );
  xmlhttp.send();

  busyIndicator(true);
}

function confirmComplete(orderNumber) {
  document.getElementById('confirmComplete').innerHTML =
    `<a href="#" style="margin-right: 1vw" title="Complete Order" onclick="completeOrder('` +
    orderNumber +
    `')"><span class="viewportGreenBtn">v</span></a><a href="#" title="Cancel" style="margin-left: 1vw" onclick="cancelComplete()"><span class="viewportRedBtn">X</span></a><p style="font-size: 0.55em;
    color: red;">Are you sure you want to complete the order?</p>`;
}

function cancelComplete(orderNumber) {
  document.getElementById('confirmComplete').innerHTML =
    '<a href="#" title="Complete Order" onclick="confirmComplete(\'' +
    orderNumber +
    '\')"><span>v</span></a>';
}

function completeOrder(orderNumber, complete) {
  var complete = document.getElementById('orderCompleted').value == 1 ? 1 : 0;

  if (complete) {
    complete = confirm('Are you sure you want to complete the order?');
  } else {
    complete = confirm(
      'This order is not filled, are you sure you want to complete it?'
    );
  }

  if (complete) {
    customer = document.getElementById('customerID').value;

    clearError();
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function () {
      if (xmlhttp.responseText == 'timedOut') {
        window.location.href = '/manager';
      } else if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
        customerChangeForklift(customer);

        document.getElementById('orderDetailsBox').innerHTML =
          '<h2>Order Header</h2>';
        document.getElementById('orderItemsBox').innerHTML =
          '<h2>Order Items</h2>';
        document.getElementById('shippedInfo').innerHTML =
          '<h2 style="margin-left: 0.8em; margin-top: 0.5em;">Available Pallets</h2>';
        document.getElementById('shippedPallets').innerHTML =
          '<h2 style="margin-left: 0.8em; margin-top: 0.5em;" class="align-center">Picked Pallets</h2>';
        document.getElementById('orderCommandCenter').innerHTML = '';

        toast('Completed SO#: ' + orderNumber, 'success');

        busyIndicator(false);
        window.scrollTo(0, 0);
      }
    };

    xmlhttp.open('GET', '/forklift/index.php?action=completeOrder');
    xmlhttp.send();

    busyIndicator(true);
  }
}

function backOrder() {
  // toast('backInfo', 'info');
  // clearError();
  // var xmlhttp=new XMLHttpRequest();
  // xmlhttp.onreadystatechange=function() {
  //     if(xmlhttp.responseText == 'timedOut'){
  //         window.location.href = "/manager";
  //     }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {
  //         busyIndicator(false);
  //         window.scrollTo(0, 0);
  //     }
  // };
  // xmlhttp.open("GET","/forklift/index.php?action=reloadOrders&shipmentGroupID="+value+"&status=1");
  // xmlhttp.send();
  // busyIndicator(true);
}

function openCO() {
  customerID = document.getElementById('customerID').value;

  // alert(customerID);

  clearError();
  var xmlhttp = new XMLHttpRequest();
  xmlhttp.onreadystatechange = function () {
    if (xmlhttp.responseText == 'timedOut') {
      window.location.href = '/manager';
    } else if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
      x = document.getElementById('completedOrdersBox');

      y = document.getElementById('forkliftOrderListBox');

      x.innerHTML =
        '<a href="#"><span class="closeCO" onclick="closeCO()">`</span></a><hr>' +
        xmlhttp.responseText;

      x.classList.add('extend');
      x.classList.remove('extend-retract');

      y.classList.add('retract');
      y.classList.remove('retract-extend');

      busyIndicator(false);

      window.scrollTo(0, 0);
    }
  };

  xmlhttp.open(
    'GET',
    '/forklift/index.php?action=completedOrders&customerID=' + customerID
  );
  xmlhttp.send();

  busyIndicator(true);
}

function closeCO() {
  x = document.getElementById('completedOrdersBox');

  y = document.getElementById('forkliftOrderListBox');

  x.innerHTML =
    '<a href="#"><span class="openCO" onclick="openCO()">!</span></a><p>Completed</p><hr>';

  x.classList.remove('extend');
  x.classList.add('extend-retract');

  y.classList.remove('retract');
  y.classList.add('retract-extend');
}

function openMasterTag(customerSONumber) {
  // customer = document.getElementById('customerID').value;
  // customerSONumber = document.getElementById('customerSONumber').value;

  clearError();
  var xmlhttp = new XMLHttpRequest();
  xmlhttp.onreadystatechange = function () {
    if (xmlhttp.responseText == 'timedOut') {
      window.location.href = '/manager';
    } else if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
      // alert(xmlhttp.responseText);

      document.getElementById('overlayPanel').innerHTML = xmlhttp.responseText;

      busyIndicator(false);
      window.scrollTo(0, 0);
    }
  };

  xmlhttp.open(
    'GET',
    '/forklift/index.php?action=openMasterTag&customerSONumber=' +
      customerSONumber
  );
  xmlhttp.send();

  busyIndicator(true);
}

function closeOverlay() {
  try {
    document.getElementById('splitOverlay').innerHTML = null;
  } catch (err) {}

  document.getElementById('overlayPanel').innerHTML = null;
  document.getElementsByClassName('overlay').innerHTML = null;
}

function masterTag(
  masterTagID = false,
  masterList,
  overlay = false,
  reopen = true
) {
  var skuInput = document.getElementById('skuNumber');

  var skuNumber = !overlay ? (skuInput ? skuInput.value : null) : null;

  if (!skuNumber && !overlay) {
    toast('A part needs to be selected.');
    return;
  }

  masterTagID = !masterTagID
    ? parseInt(document.getElementById('masterTag').value) + 1
    : masterTagID;
  masterList = masterList ? 1 : 0;

  // alert(masterList);
  // return

  customerSONumber = document.getElementById('customerSONumber').value;
  var shippingID = document.getElementById('shippingID').value;

  clearError();
  var xmlhttp = new XMLHttpRequest();
  xmlhttp.onreadystatechange = function () {
    if (xmlhttp.responseText == 'timedOut') {
      window.location.href = '/manager';
    } else if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
      // alert(xmlhttp.responseText);
      closeOverlay();

      if (overlay) {
        document.getElementById('overlayPanel').innerHTML =
          `
                <div class="overlay grid" style="padding: 1vh">
                    <div class="unit whole">
                        <a href="javascript:;" class="align-left" style="margin-left: 1vw; font-size: 10px;" onclick="openMasterTag('` +
          customerSONumber +
          `')" title="Back"><span><</span></a>
                        <h1 style="margin-left: 1vw">Master Pallet #` +
          masterTagID +
          `</h1>      
                    </div>
                    <div class="unit whole align-center">` +
          xmlhttp.responseText +
          `</div>
                </div>
                `;
      } else {
        if (reopen) {
          openMasterTagSlider(xmlhttp.responseText, masterTagID, masterList);
        }
      }

      busyIndicator(false);
      window.scrollTo(0, 0);
    }
  };

  xmlhttp.open(
    'GET',
    '/forklift/index.php?action=masterTag&masterTagID=' +
      masterTagID +
      '&skuNumber=' +
      skuNumber +
      '&masterList=' +
      masterList +
      '&customerSONumber=' +
      customerSONumber +
      '&overlay=' +
      overlay +
      '&shippingID=' +
      shippingID
  );
  xmlhttp.send();

  busyIndicator(true);
}

function openMasterTagSlider(html, masterTagID = false, masterList = false) {
  x = document.getElementById('masterTagSlideUp');
  y = document.getElementById('pickedPalletSlider');

  if (!masterList) {
    x.innerHTML =
      '<a href="#" onclick="masterTag(null, true);" style="float: left;"><span class="back">&lt;</span></a><a href="#" onclick="closeMasterTagSlider()"><span class="Close Master Pallet">`</span></a><p>Master Pallet #' +
      masterTagID +
      '</p><input id="selectedMasterPallet" value="' +
      masterTagID +
      '" class="hide"><hr>';
  } else {
    x.innerHTML =
      '<a href="#" onclick="closeMasterTagSlider()"><span class="Close Master Pallet">`</span></a>' +
      '<p>Master Pallets <a href="#" onclick="masterTag(' +
      masterTagID +
      ', false, false)" title="New Master Pallet"><span class="viewportGreenBtn">+</span></a></p><hr>';
  }

  x.innerHTML += html;

  x.classList.remove('masterTag-extend-retract');
  y.classList.remove('masterTag-retract-extend');

  x.classList.add('masterTag-extend');
  y.classList.add('masterTag-retract');

  document.getElementById('masterTagMode').checked = true;
}

function closeMasterTagSlider() {
  masterTagID = document.getElementById('masterTag').value;

  // z = document.getElementById('masterTagPallets').innerHTML;

  masterTagID.value = 0;

  x = document.getElementById('masterTagSlideUp');

  masterCnt = document.getElementById('masterCnt').value;

  x.innerHTML =
    `
        <a href="#" onclick="masterTag(null, true)" title="Master Pallets">
        <span class="viewportGreenBtn">!</span>
        </a>

        <p>Master Pallets (` +
    masterCnt +
    `)</p> 
    `;

  x.classList.remove('masterTag-extend');
  y.classList.remove('masterTag-retract');

  x.classList.add('masterTag-extend-retract');
  y.classList.add('masterTag-retract-extend');

  document.getElementById('masterTagMode').checked = false;
}

function splitPalletWarning(
  skuNo,
  pickPackItemsID,
  customerSONumber,
  shippingID,
  partNum,
  serialNumber,
  orderedQty,
  invQty,
  picked
) {
  if (picked == null) {
    picked = 0;
  }

  $html =
    `
        <div id="splitOverlay" class="splitOverlay">
            <div class="grid">
                <div class="unit whole" style="margin-bottom: 3vh;">
                    <h1>Qty Exceeds Ordered Qty</h1>
                </div>
                <div class="unit whole">
                    <div class="unit whole" id="exceedQtyBtns">
                        <a id="buttonB" onclick="splitPallet(` +
    serialNumber +
    ', ' +
    orderedQty +
    ', ' +
    invQty +
    ', ' +
    picked +
    ', ' +
    skuNo +
    ', ' +
    pickPackItemsID +
    ", '" +
    customerSONumber +
    "', " +
    shippingID +
    ', ' +
    partNum +
    `);">Auto Split</a>
                    </div>
                    <div class="unit whole">
                        <a id="buttonB" onclick="changePalletQty(` +
    skuNo +
    ', ' +
    pickPackItemsID +
    ", '" +
    customerSONumber +
    "', " +
    shippingID +
    ', ' +
    partNum +
    `);">Set Split Qty</a>
                    </div>
                    <div class="unit whole">
                        <a id="buttonB" onclick="pickWholePallet(` +
    skuNo +
    ', ' +
    pickPackItemsID +
    ", '" +
    customerSONumber +
    "', " +
    shippingID +
    ', ' +
    partNum +
    `);">Pick Whole Pallet</a>
                    </div>
                    <div class="unit whole">
                        <a id="buttonB" onclick="cancelSplit();">Cancel Split</a>
                    </div>
                </div>
            </div>
        </div>`;

  document.getElementById('overlayPanel').innerHTML = $html;
}

function manualPicking(serialNumber, customerSONumber, purpose = null) {
  busyIndicator(false);
  html = `
        <div id="splitOverlay" class="splitOverlay">
            <div class="grid">
                <div class="unit whole" style="margin-bottom: 3vh;">
                <h1>`;

  switch (purpose) {
    case 'overpick':
      html += 'Are you sure you want to over pick?';
      break;
    case 'manual':
      html += 'Manual Picking';
      break;

    default:
      html += 'Qty Exceeds Ordered Qty';
      break;
  }

  html += `
                </h1>

                </div>
                <div class="unit whole">
                    <div class="unit whole">
                    <a id="buttonB" onclick="scanPallet('${serialNumber}', false, true)">Pick Whole Pallet</a>
                    </div>
                    <div class="unit whole">
                    <a id="buttonB" onclick="changePalletQty('${serialNumber}');">Set Qty</a>
                    </div>
                    <div class="unit whole">
                        <a id="buttonB" onclick="cancelSplit();">Cancel</a>
                    </div>
                </div>
            </div>
        </div>`;

  document.getElementById('overlayPanel').innerHTML = html;
}

function overShipWarning(serialNumber, customerSONumber) {
  $html = `
    <div id="splitOverlay" class="splitOverlay">
        <div class="grid">
            <div class="unit whole" style="margin-bottom: 3vh;">
                <h1>Are you sure you want to over pick?</h1>
            </div>
            <div class="unit whole">
                <div class="unit whole">
                    <a id="buttonB" onclick="scanPallet('${serialNumber}', false, true);">Pick Whole Pallet</a>
                </div>
                <div class="unit whole">
                    <a id="buttonB" onclick="changePalletQty('${serialNumber}');">Set Picked Qty</a>
                </div>
                <div class="unit whole">
                    <a id="buttonB" onclick="cancelSplit();">Cancel</a>
                </div>
            </div>
        </div>
    </div>`;

  document.getElementById('overlayPanel').innerHTML = $html;
}

function changePalletQty(serialNumber) {
  $html = `<div class="splitOverlay">
    <div class="grid">
        <div class="unit whole">
            <a href="javascript:;" onclick="closeOverlay()" id="exit"><span>x</span></a>
            <div class="unit whole">
                <input id="manualQtyInput" style="color: black; margin-bottom: 3vh;" placeholder="Enter Qty...">
            </div>
            <div id="enterQtySubmitBox" class="unit whole">
                <a id="buttonB" onclick="submitQty('${serialNumber}')" >Submit</a>
            </div>
        </div>
        </div>
    </div>`;

  document.getElementById('overlayPanel').innerHTML = $html;
}

function submitQty(serialNumber) {
  qty = document.getElementById('manualQtyInput').value;
  if (qty == null || qty <= 0) {
    toast('Enter a valid qty.');
  } else {
    scanPallet(serialNumber, qty);
    closeOverlay();
  }
}

function cancelSplit() {
  document.getElementById('overlayPanel').innerHTML = '';
  document.getElementsByClassName('overlay').innerHTML = '';
  // document.getElementById('scanPalletInput').value = '';
}

function filterCurrent(skuNumber, filter, component, filterBy) {
  // alert(skuNumber + ', ' + component + ', ' + filter + ', ' + filterBy);
  // alert(filterBy + ': ' + filter);
  clearError();
  var xmlhttp = new XMLHttpRequest();
  xmlhttp.onreadystatechange = function () {
    if (xmlhttp.responseText == 'timedOut') {
      window.location.href = '/manager';
    } else if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
      alert(xmlhttp.responseText);
      document.getElementById('currentTableData').innerHTML =
        xmlhttp.responseText;

      busyIndicator(false);
      // window.scrollTo(0, 0);
    }
  };

  xmlhttp.open(
    'GET',
    '/forklift/index.php?action=filterCurrent&skuNumber=' +
      skuNumber +
      '&component=' +
      component +
      '&filter=' +
      filter +
      '&filterBy=' +
      filterBy
  );
  xmlhttp.send();

  busyIndicator(true);
}
