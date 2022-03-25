/* 
 * Print Button
 */

function printReport() {
    window.print();
}

function autoPrintHTML(divId = false, content = false, callback = false) {
    // var divId = !divId ? 'printThis' : divId;
    var divId = 'printThis';
    // var printContents = !content ? document.getElementById(divId).innerHTML : content;
    var printContents = document.getElementById(divId).innerHTML;
    qz.websocket.connect().then(function() { 
        return qz.printers.find("GK420d"); // Pass the printer name into the next Promise
    }).then(function(printer) {
        var config = qz.configs.create(printer);
        var data = [{
        type: 'pixel',
        format: 'html',
        flavor: 'data', // or 'plain' if the data is raw HTML
        data: printContents
        }];
        qz.print(config, data).catch(function(e) { console.error(e); });
    }).catch(function(e) { console.error(e); });
}

function printReport2(divId = false, content = false, callback = false) {

    var divId = !divId ? 'printThis' : divId;

    var printContents = !content ? document.getElementById(divId).innerHTML : content;
    // var printContents = !content ? document.getElementsByClassName("print").innerHTML : content;
    var originalContents = document.body.innerHTML;

    document.body.innerHTML = printContents;

    document.body.innerHTML = originalContents;

    if(callback) callback();
}

function exportReport(repNum){

    if (repNum == 4){
        window.location.href = "/reports/index.php?action=exportReport4";

    }else if(repNum == 10){
        window.location.href = "/reports/index.php?action=exportReport10";

    }else if(repNum == 7){
        window.location.href = "/reports/index.php?action=exportReport7";
    }else if(repNum == 9){
        
        window.location.href = "/reports/index.php?action=exportReport9";
    }else if(repNum == 122){
        
        window.location.href = "/reports/index.php?action=exportReport12b";
    }else if(repNum == -1) {
        window.location.href = "/reports/index.php?action=exportInvBakReport";
    }else if(repNum == 0) {
        window.location.href = "/reports/index.php?action=exportInvBakReportSummary";
    }else if(repNum == 1) {
        window.location.href = "/reports/index.php?action=exportInvBakReportDetailed";
    }else if(repNum == 2) {
        window.location.href = "/reports/index.php?action=exportInvBakReportDiscrepancy";
    }
    
}

function printDIV2(rptName = false) {

    var strid = 'printThis';
    var rptName = !rptName ? '0' : rptName;

    var prtContent = document.getElementById(strid);

    // Intial header
    var html = '<html>\n<head>\n';
    html += '<title>' + 'Name of your project : ' + rptName + '</title>';

    // Get value for header for Telerik stylesheet
    if (document.getElementsByTagName != null) {
        var headTags = document.getElementsByTagName("head");
        if (headTags.length > 0) 
            html += headTags[0].innerHTML;
    }
    html += ' <title>' + 'Whitney-' + rptName + ' </title>';

    // End the header and open body
    html += '\n</head>\n<body>\n';

    if (prtContent != null) { // Get all html 
        html += document.getElementById(strid).innerHTML;
    }
    else {
        alert("Could not find the print div");
        return;
    }

    //End the body and html
    html += "\n</body></html>";

    // Opem new wind
    var WinPrint = window.open('', '', 'letf=10,top=10,width="450",height="250",toolbar=1,scrollbars=1,status=0');

    WinPrint.document.write(html);
    WinPrint.document.close();
    WinPrint.focus();
    WinPrint.print();
    return false;
}