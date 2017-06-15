$(document).ready(function(){
    $('input, textarea, .g-recaptcha').bind('focusout', ValidateForm);
    function ValidateForm(){
        
        function oValidateParams(regexp, nullMonit, errorMonit){
            this.regexp = regexp;
            this.nullMonit = nullMonit;
            this.errorMonit = errorMonit;
        }        
        var oValidateSettings = {            
            "name": new oValidateParams(/^[a-zA-ZąćęłńóśżźĄĆĘŁŃÓŚŻŹ ]{1,30}$/,'Proszę wprowadzić imię.','Proszę wprowadzić imię, korzystając wyłącznie z liter.'),
            "surname": new oValidateParams(/^[a-zA-ZąćęłńóśżźĄĆĘŁŃÓŚŻŹ -\']{1,30}$/, 'Proszę wprowadzić nazwisko.', 'Proszę wprowadzić nazwisko, korzystając wyłącznie z liter, myślnika i apostrofu.'),                                          
            "email": new oValidateParams(/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9-]+\.[a-z]{2,6}$/ ,'Proszę wprowadzić adres e-mail.','Proszę wprowadzić poprawny adres e-mail.'),
            "subject": new oValidateParams(/^[a-zA-ZąćęłńóśżźĄĆĘŁŃÓŚŻŹ0-9,.)-:(!? \']{1,500}$/,'Proszę wprowadzić temat wiadomości.','Proszę wprowadzić temat wiadomości, korzystając wyłącznie z liter, cyfr oraz znaków ,.)-:(!?\'.'),
            "content": new oValidateParams(/^[a-zA-ZąćęłńóśżźĄĆĘŁŃÓŚŻŹ0-9,.)-:(!? \'\n]{1,2000}$/,'Proszę wprowadzić temat wiadomości.','Proszę wprowadzić temat wiadomości, korzystając wyłącznie z liter, cyfr oraz znaków ,.)-:(!?\'.'),
            "sendemailcopy": new oValidateParams(/1/,'Proszę zaznaczyć pole: "Wyślij do mnie kopię tej wiadomości."', 'Niepoprawna wartość pola: "Wyślij do mnie kopię tej wiadomości."'),
            "g-recaptcha-response": new oValidateParams(/1/, 'Proszę kliknąć w polu reCAPTCHA.', 'Walidacja przeciwko robotom nie powiodła się. Proszę spróbuj ponownie.')
        };
        
        var oField = $(this);
        var sFieldName = oField.attr("name");
        if(oField.next().hasClass('alert')){
            oField.next().remove();
        }
        
        function ValidateInput(field){
            var bIsValid = true;
            if(field.val() !== ''){
                var sName = field.attr("name");
                var sParam = oValidateSettings[sName].regexp;
                bIsValid = sParam.test($.trim(field.val()));
            }
            return bIsValid;            
        }
        
        if(oField.hasClass('required')){
            if($.trim(oField.val()) === ''){                
                oField.addClass('error').after('<div class="alert">'+oValidateSettings[sFieldName].nullMonit+'</div>');
                oField.data('valid', false);
                return;
            }
        }
        if(!ValidateInput(oField)){
            oField.addClass('error').after('<div class="alert">'+oValidateSettings[sFieldName].errorMonit+'</div>');
            oField.data('valid', false);
        }
        else{
            oField.removeClass('error');
            oField.data('valid', true);
        }
        
    }
    
    $('#submit').click(function(){       
        var bDataValid = true;
        if(grecaptcha.getResponse().length === 0){
            $('.g-recaptcha').data('valid', false);                
        }
        else{
            $('.g-recaptcha').data('valid', true); 
        }
        
        if(!$("#robot").is(':checked')){            
            $('input, textarea, .g-recaptcha').each(function(){
                var oField = $(this);                
                if(oField.hasClass('required') && oField.data('valid') === undefined && !oField.next().hasClass('alert')){
                    oField.addClass('error').after('<div class="alert">To pole nie może byc puste.</div>');                    
                }
                if(oField.next().hasClass('alert')){
                    bDataValid = false;
                }
            });         
        }
        else{
            bDataValid = false;
            
        }
        return bDataValid;
        });
});
function recaptchaCallback(){
    $('.g-recaptcha').data('valid', true); 
    if($('.g-recaptcha').next().hasClass('alert')){
       $('.g-recaptcha').next().remove();
    }
}