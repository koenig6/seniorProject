/* 
 * Date_Time
 */

function date_time(id0, id1, id2, id3)
{
        date = new Date;
        year = date.getFullYear();
        month = date.getMonth();
        months = new Array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');
        d = date.getDate();
        day = date.getDay();
        days = new Array('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday');
        h = date.getHours();
        
        //Check to see if am or pm and convert hours
        if(h >= 12)
        {
            ampm = 'PM'
            
            if(h != 12)
            {
                h = h - 12;
            }
        }
        else
        {
            ampm = 'AM'
        }
        
        if(h == 0)
        {
            h = 12;
        }
//        if(h<10)
//        {
//                h = "0"+h;
//        }
        m = date.getMinutes();
        if(m<10)
        {
                m = "0"+m;
        }
        s = date.getSeconds();
        if(s<10)
        {
                s = "0"+s;
        }
        result = ''+days[day]+' '+months[month]+' '+d+', '+year;
        document.getElementById(id0).innerHTML = result;
        document.getElementById(id1).innerHTML = h;
        document.getElementById(id2).innerHTML = m;
        document.getElementById(id3).innerHTML = s;
        
        setTimeout('date_time("'+id0+'","'+id1+'", "'+id2+'", "'+id3+'");','1000');
        
        return true;
}

function refreshSchedule(){
    var xmlhttp=new XMLHttpRequest();
    xmlhttp.onreadystatechange=function() {
        if(xmlhttp.responseText == 'timedOut'){
            window.location.href = "/timeClock";
        }else if (xmlhttp.readyState==4 && xmlhttp.status==200) {
            if (xmlhttp.responseText !== 'false'){
                document.getElementById("schedule").innerHTML=xmlhttp.responseText;
                setTimeout('refreshSchedule();','10000');
            }
        }
    };
    xmlhttp.open("GET","/timeClock/index.php?action=refresh");
    xmlhttp.send();
    
    //setTimeout('refreshSchedule();','5000');

}

