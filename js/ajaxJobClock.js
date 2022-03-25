
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.

function openJobAssigner(workOrderNumber){
    
    var xmlhttp=new XMLHttpRequest();
    
    xmlhttp.onreadystatechange=function() {
        if(xmlhttp.responseText == 'timedOut'){
            window.location.href = "/timeClock";
        }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {

                document.getElementById("overlay").innerHTML=xmlhttp.responseText;
        }
    };
    
    xmlhttp.open("GET","/jobClock/index.php?action=openJobAssigner&workOrderNumber="+workOrderNumber);
    xmlhttp.send();

}

 


