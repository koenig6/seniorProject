function updateTempToggle(c) {
    if(!c.checked) {
        state = 1;
        window.location.href = "/reports/index.php?action=report8temps";
    }else {
        state = 0;
        window.location.href = "/reports/index.php?action=report8";
    }

    // alert(state);
}

function invReportType() {
    var html = 
    '<div>' +
    '<h1>Inventory Report</h1>' +
    '<a onclick="invDetailed()" class="bs-btn btn-blue mx-1">Detailed</a>' + 
    '<a onclick="invSummary()" class="bs-btn btn-blue mx-1">Summary</a>' +
    '<a onclick="invDiscrepancy()" class="bs-btn btn-blue mx-1">Discrepancy</a>' +
    // '<a onclick="invDiscrepancy()" class="bs-btn btn-blue mx-1">Discrepancy</a>' +
    '</div>';
    overlay(html);
}

function invDetailed() {
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function () {
        if (xmlhttp.responseText == "timedOut") {
        } else if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
            var html = 
            '<div>' +
            '<h1 class="w-100">Detailed Inventory Report</h1>' +
            '<div class="invReportSearchInputs">' +
            xmlhttp.responseText + 
            '<input id="skuNumberInput" placeholder="Search by sku#...">' +
            '</div>' +
            '<div class="c-white">' +
            '<div class="toggleFilter" id="holdtf" onclick="toggleFilter(`hold`)"><span>v</span><p>Hold</p><input class="hide" id="hold" value="0"></div>' +
            '<div class="toggleFilter" id="quarantinetf" onclick="toggleFilter(`quarantine`)"><span>v</span><p>Quarantined</p><input class="hide" id="quarantine" value="0"></div>' +
            '<a onclick="invReportSearch(1)" class="bs-btn btn-blue mx-1">Submit</a>' +
            '</div>' +
            '</div>';
            overlay(html);
            busyIndicator(false);
        }
    };
    xmlhttp.open(
        "GET",
        "/reports/index.php?action=getCustomerList"
    );
    xmlhttp.send();
    busyIndicator(true);
}

function invDiscrepancy() {
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function () {
        if (xmlhttp.responseText == "timedOut") {
        } else if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
            var html = 
            '<div>' +
            '<h1 class="w-100">Inventory Discrepancy Report</h1>' +
            '<div class="invReportSearchInputs">' +
            xmlhttp.responseText + 
            '<input id="skuNumberInput" placeholder="Search by sku#...">' +
            '</div>' +
            '<div class="c-white">' +
            '<div class="toggleFilter" id="holdtf" onclick="toggleFilter(`hold`)"><span>v</span><p>Hold</p><input class="hide" id="hold" value="0"></div>' +
            '<div class="toggleFilter" id="quarantinetf" onclick="toggleFilter(`quarantine`)"><span>v</span><p>Quarantined</p><input class="hide" id="quarantine" value="0"></div>' +
            '<a onclick="invReportSearch(2)" class="bs-btn btn-blue mx-1">Submit</a>' +
            '</div>' +
            '</div>';
            overlay(html);
            busyIndicator(false);
        }
    };
    xmlhttp.open(
        "GET",
        "/reports/index.php?action=getCustomerList"
    );
    xmlhttp.send();
    busyIndicator(true);
}

function invSummary() {
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function () {
        if (xmlhttp.responseText == "timedOut") {
        } else if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
            var html = 
            '<div>' +
            '<h1 class="w-100">Inventory Summary Report</h1>' +
            '<div class="invReportSearchInputs">' +
            xmlhttp.responseText + 
            '<input id="skuNumberInput" placeholder="Search by sku#...">' +
            '</div>' +
            '<div class="c-white">' +
            '<div class="toggleFilter" id="holdtf" onclick="toggleFilter(`hold`)"><span>v</span><p>Hold</p><input class="hide" id="hold" value="0"></div>' +
            '<div class="toggleFilter" id="quarantinetf" onclick="toggleFilter(`quarantine`)"><span>v</span><p>Quarantined</p><input class="hide" id="quarantine" value="0"></div>' +
            '<a onclick="invReportSearch(0)" class="bs-btn btn-blue mx-1">Submit</a>' +
            '</div>' +
            '</div>';
            overlay(html);
            busyIndicator(false);
        }
    };
    xmlhttp.open(
        "GET",
        "/reports/index.php?action=getCustomerList"
    );
    xmlhttp.send();
    busyIndicator(true);
}

function invReportSearch(reportType) {
    var hold = document.getElementById('hold').value;
    var quarantine = document.getElementById('quarantine').value;
    var customerID = document.getElementById('customerID').value;
    var skuNumber = document.getElementById('skuNumberInput').value;
    var productCode = document.getElementById('catSelection').value;
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function () {
        if (xmlhttp.responseText == "timedOut") {
        } else if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
            overlay(xmlhttp.responseText, true);
            busyIndicator(false);
        }
    };
    xmlhttp.open(
        "GET",
        "/reports/index.php?action=generateInv&customerID="+customerID+'&reportType='+reportType+'&hold='+hold+'&quarantine='+quarantine+'&skuNumber='+skuNumber+'&productCode='+productCode
    );
    xmlhttp.send();
    busyIndicator(true);
}

function report(reportNum) {

var xmlhttp = new XMLHttpRequest();
xmlhttp.onreadystatechange = function () {
    if (xmlhttp.responseText == "timedOut") {
    // window.location.href = '/logIn/index.php?action=logOut';
    // alert('Timed Out');
    } else if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {

    // alert(xmlhttp.responseText);

    var myArr = JSON.parse(xmlhttp.responseText);

    if(!(myArr[0])) {
        buildOverlay(myArr[1]);
    }else {
        buildOverlay(myArr[1], myArr[0]);
    }
    
    busyIndicator(false);

    }
};

xmlhttp.open(
    "GET",
    "/reports/index.php?action=report" + reportNum
);
xmlhttp.send();

busyIndicator(true);
}