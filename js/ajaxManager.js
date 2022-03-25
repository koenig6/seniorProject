function addBinLocItem() {
    var binName = document.getElementById('binNameAddInput').value;
    var binCatID = document.getElementById('binCatAddInput').value;

    // alert(binName + ', ' + binCatID);

    if(binName && binCatID != 'null') {
        var xmlhttp = new XMLHttpRequest();
        xmlhttp.onreadystatechange = function () {
          if (xmlhttp.responseText == "timedOut") {
            window.location.href = "/manager";
          } else if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
    
            var success = 'Successfully Added Bin Location';
            var failure = 'Failed to Add Bin Location';
            xmlhttp.responseText == 1 ? toast(success, 'success') : toast(failure, 'error');
            searchBinLocation(false, binName);
    
            document.getElementById('binNameAddInput').value = null;
            document.getElementById('binCatAddInput').value = null;
    
            busyIndicator(false);
          }
        };
        xmlhttp.open("GET", "/manager/index.php?action=addBinLocItem&binName="+binName+'&binCatID='+binCatID);
        xmlhttp.send();
        busyIndicator(true);
    }else {
        toast('All fields are required.', 'bad');
    }
}

function editBinLocItem(binID, binName, binCatID) {
    var replace = document.getElementById('binLocItem_'+binID);

    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function () {
      if (xmlhttp.responseText == "timedOut") {
        window.location.href = "/manager";
      } else if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {

        replace.innerHTML = 
        '<input id="binNameEditInput" value="' + binName + '">' +
        xmlhttp.responseText +
        '<div class="edit">' +
        '<a title="Confirm" style="font-size: 1.8em" onclick="updateBinLocItem(' + binID + ', `' + binName + '`, ' + binCatID + ')"><i class="bi bi-check"></i></a>' +
        '<a title="Cancel" style="font-size: 1.8em" onclick="searchBinLocation(false, ' + binID + ');"><i class="bi bi-x redIcon"></i></a>' +
        '</div>';

        busyIndicator(false);
      }
    };
    xmlhttp.open("GET", "/manager/index.php?action=editBinLocItem&binCatID="+binCatID+'&binName='+binName+'&binCatID='+binCatID);
    xmlhttp.send();
    busyIndicator(true);
}


function setBinCategoryID(binID, binCatID) {
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function () {
    if (xmlhttp.responseText == "timedOut") {
        window.location.href = "/manager";
    } else if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
        var success = 'Successfully Deactivated Bin Location';
        var failure = 'Failed to Deactivate Bin Location';
        if(xmlhttp.responseText == 1) {
            toast(success, 'success');
            searchBinLocation(null, binID);
        }else {
            toast(failure, 'error');
        }     
        busyIndicator(false);
        document.getElementById('').classList.innerHTML = '<p class="status binStatusBlue">Deactivated</p>';
    }
    };
    xmlhttp.open("GET", "/manager/index.php?action=setBinCategoryID&binID=" + binID + '&binCatID=' + binCatID);
    xmlhttp.send();
    busyIndicator(true);
}

function updateBinLocItem(binId, originalBinName, originalBinCatID) {
    var newbinName = document.getElementById('binNameEditInput').value;
    var newbinCatId = document.getElementById('binCatEditInput').value;
    // var search = document.getElementById('binLocSearchInput') ? document.getElementById('binLocSearchInput').value : binId;

    if(newbinName == originalBinName && originalBinCatID == newbinCatId) {
        // Nothing Changed
        searchBinLocation(false, binId);
    }else {
        var xmlhttp = new XMLHttpRequest();
        xmlhttp.onreadystatechange = function () {
        if (xmlhttp.responseText == "timedOut") {
            window.location.href = "/manager";
        } else if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
            var success = 'Successfully Updated Bin Location';
            var failure = 'Failed to Update Bin Location';
            if(xmlhttp.responseText == 1) {
                toast(success, 'success')
                searchBinLocation(false, binId);
            }else {
                toast(failure, 'error');
            }
            busyIndicator(false);
        }
        };
        xmlhttp.open("GET", "/manager/index.php?action=updateBinLocItem&binID="+binId+'&binName='+newbinName+'&binCatID='+newbinCatId);
        xmlhttp.send();
        busyIndicator(true);
    }

 
}

function searchBinLocation(cat = false, search = false) {
    var search = !search ? document.getElementById('binLocSearchInput').value : search;
    var cat = document.getElementById('binCatSearchInput').value;
    // alert(search);
    // alert(cat);

    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function () {
    if (xmlhttp.responseText == "timedOut") {
        // window.location.href = "/manager";
    } else if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
        var list = document.getElementById('binLocList');
        list.innerHTML = xmlhttp.responseText != '' ? xmlhttp.responseText : 'No Bin Locations...';
        list.scrollTop = 0;
        busyIndicator(false);
    }
    };
    xmlhttp.open("GET", "/manager/index.php?action=searchBinLocation&search="+search+'&cat='+cat);
    xmlhttp.send();
    busyIndicator(true);
}