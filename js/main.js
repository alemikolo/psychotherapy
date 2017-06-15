$(document).ready(function(){    
    function datownik(){
        var oCzas = new Date();
        var iYear = oCzas.getFullYear();
        var iStartYear = 2015;
        if(iYear > iStartYear){
            $('#years').html(iStartYear+'–'+iYear);}
        else{
            $('#years').html(iStartYear);}}
    datownik();
    $('.js_monit, .anchor, #no_js_form_monit').hide(); 
    $('#kontakt form').show();
    $('#formularz p').show();
    $('#menu').slicknav();
});