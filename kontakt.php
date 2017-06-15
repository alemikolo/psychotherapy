<?php
error_reporting(0);
require_once 'config/config.php';
require_once 'classes/Mailer.Class.php';

if(filter_input(INPUT_POST, 'iform', FILTER_VALIDATE_INT)){
    if(filter_input(INPUT_POST, 'robot', FILTER_VALIDATE_INT)){
        Header("Location:kontakt.php");
        exit();
    }
    else {
        $oMailer = new Mailer($aValidateParams, $aMessage, $sPrivateKey);
        $aMonits = $oMailer->SendEmail();
    }
}
include_once 'kontakt.html';