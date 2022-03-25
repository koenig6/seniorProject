/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


function uncheckAll(){ 

    var w = document.getElementsByTagName('input'); 
    var checked = w[0].checked;

    if(checked){
        for(var i = 0; i < w.length; i++){ 
            if(w[i].type=='checkbox'){
              w[i].checked = false;
            }
        }
    }else{
        for(var i = 0; i < w.length; i++){ 
            if(w[i].type=='checkbox'){ 
              w[i].checked = true;
            }
        }        
    }
    
}