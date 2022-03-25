function punch(disclaimerResponse = false)
{
    
    var xmlhttp=new XMLHttpRequest();
    var employeeID = document.getElementById("punch").value;
    var timeStamp = getSqlTimeStamp();
    
    var employeeIDStripped = employeeID.substring(1,employeeID.length);

    xmlhttp.onreadystatechange=function() {
        if(xmlhttp.responseText == 'timedOut'){
            window.location.href = "/timeClock";
        }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {
            var myArr = JSON.parse(xmlhttp.responseText);
            if (myArr[0] == 1){
                document.getElementById("punchOverlay").innerHTML=myArr[1];
                document.getElementById("punchBox").innerHTML=
                        '<input type="text" name="punch" id="punch" autocomplete="off" disabled onkeydown="Javascript: if (event.keyCode==13) punch();">';
                
                fade();
                setTimeout(resetPunch, 2000);
                setTimeout(resetFade, 2000);
            }else if(myArr[0] == 2){
                document.getElementById("punch").value = myArr[2];
                document.getElementById("punchOverlay").innerHTML=myArr[1];
                fade();
            }else{
                document.getElementById("punchOverlay").innerHTML='<div></div>';
                resetPunch();
            }
        }
    };
    xmlhttp.open("GET","/timeClock/index.php?action=punch&keyCard="+employeeIDStripped+"&punch="+timeStamp+"&disclaimerResponse="+disclaimerResponse);
    xmlhttp.send();
    document.getElementById("punch").value="";
}

function fade()
{
    document.getElementById("fadeStyle").innerHTML='<style>header{opacity: .3;} main{opacity: .3;}</style>';
}

function resetPunch()
{
    document.getElementById("punchOverlay").innerHTML='<div></div>';
    document.getElementById("punchBox").innerHTML=
            '<input type="text" name="punch" id="punch" autocomplete="off" autofocus onkeydown="Javascript: if (event.keyCode==13) punch();">';
    document.getElementById("punch").focus();
}

function resetFade()
{
    document.getElementById("fadeStyle").innerHTML='<style></style>';
}

function getSqlTimeStamp(){
    date = new Date;
    year = date.getFullYear();
    month = date.getMonth() + 1;
    d = date.getDate();
    day = date.getDay();
    h = date.getHours();
    m = date.getMinutes();
    s = date.getSeconds();

    if(h<10)
    {
            h = "0"+h;
    }

    if(m<10)
    {
            m = "0"+m;
    }

    if(s<10)
    {
            s = "0"+s;
    } 
    
    return year+'-'+month+'-'+d+' '+h+':'+m+':'+s;
    
}