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
    function countDown(Czas){
        setTimeout(function(){            
            $('#timer').html(Czas);
            Czas--;
            if(Czas>0){
                countDown(Czas);}
            else{
                window.location.href = "http://www.psychoterapia.fret.com.pl";}
            }, 1000);        
        }
    countDown(10);
});