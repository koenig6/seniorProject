/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


function checkTimeOut(str){
    if(str.substring(0,15) == "<!DOCTYPE html>"){
        return true;
    }else{
        return false;
    }
}