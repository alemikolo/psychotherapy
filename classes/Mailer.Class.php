<?php

/*
 * Copyright (C) 2015 Aleksander Fret
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

/* 
 * version 1.0
 * @author Aleksander Fret
 */

/**
 * Opis klasy Mailer:
 * Warunki niezbędne do skorzystania z tej klasy:
 *   1. Utworzenie wielowymiarowej tablicy asocjacyjnej zawierającej wszystkie
 *      użyte pola formularza z ich nazwami deklarowanymi w html-u jako atrybut
 *      "name". Kluczami tej tablicy muszą być nazwy pól formularza a wartościami
 *      tablice z informacjami o rodzaju filtrowania (klasa korzysta z funkcji
 *      filter_input) jego opcjach, oraz komunikatach które zostaną wyświetlone
 *      użytkownikowi informując go o błędach. Tablica taka powinna wyglądać:
 *      $aTabliaDoWalidacji = [
 *          'nazwa_pola_formularza' => [
 *              'filter' => RODZAJ_STOSOWANEGO_FILTRA, //see http://php.net/manual/en/filter.filters.php 
 *              'options' => ['options' => ['option' => 'value']], // see http://php.net/manual/en/function.filter-input.php
                'null' => 'treść komuninaktu jeśli dane pole jest puste',
 *              'error' => 'treść komunikatu jeśli dane pole zawiera błędy']];
 *      Jeśli filter nie wymaga opcji, należy usunąć całą sekcję 'options'.
 *      Jeśli pole może być puste, należy usunąć całą sekcję 'null'.
 * 
 *   2. Utworzenie tablicy asocjacyjnej z ustawieniami wysyłanej wiadomości:
 *      Tablica taka powinna wyglądać:
 *      $aTablicaWiadomosci = [
            'recipient' => "example@op.pl", //odbiorca wiadomości
            'subject' => " (wiadomość ze strony www.example.com)", // ewentualny tekst dołączany do tematu wpisanego w formularzu
            'header' => "Content-type: text/plain; charset=utf-8\r\n",
            'content' => "", //ewentualny tekst dołączany do treści z formularza
            'success' => "Dziękuję za wysłanie wiadomosci",
            'error' => "Wystąpił błąd podczas wysyłania wiadomości. Proszę spróbować ponownie."];
 * 
 *   3. Ewentualne przygotowanie i skorzystanie z wartości Site Key oraz
 *      Secret Key od Googla (ReCaptcha) w celu ochrony przed spamem.
 *      // see https://developers.google.com/recaptcha/intro
 */

class Mailer {
    /* właściwości klasy Mailer */
    private $aValidateParams = [];       // tablica z parametrami Walidacji
    private $aFormData = [];             // tablica z przefiltrowanymi danymi formularza
    private $aMessage = [];              // tablica z ustawieniami wiadomości
    private $aErrors = [];               // tablica z błędami
    private $aFormKeys = [];             // tablica z kluczami wartości przesłanych metodą $_POST 
    private $sPrivateKey;                // decyduje czy ma być wysłana informacja zwrotna do nadawcy
    

    /* tworząc obiekt przekazujemy do konstruktora tablicę z parametrami do
     * walidacji, tablicę z ustawieniami wiadomości, ew. Secret Key od Googla
     * oraz ew. w czwartej zmiennej przekazujemy FALSE jeśli nie chcemy wysyłać
     * potwierdzenia nadawcy. */
    public function __construct(array $aValidateParams, array $aMessage, $sPrivateKey = '') {
        $this->aFormKeys = array_keys(filter_input_array(INPUT_POST, FILTER_UNSAFE_RAW)); // pobiera klucze tablicy $_POST
        $this->aValidateParams = $aValidateParams; // parametry walidacji
        $this->aMessage = $aMessage; // ustawienia wiadomości
        $this->sPrivateKey = $sPrivateKey; // Secret Key ReCaptcha od Googla        
    }    
    /* funkcja wysyła email lub zwraca błąd */
    public function SendEmail() {     
        /* wywołuje funkcję walidującą dane przesłane w formularzu przekazując
         * jej tablicę z kluczami tablicy $_POST (patrz wyżej) */
        $this->ValidateForm($this->aFormKeys);
        /* jeśli nie ma błędów - przechodzi dalej do wysyłania wiadomości */
        //var_dump($this->aErrors);exit();
        if(empty($this->aErrors)){           
            /* wywołuje funkcję, która wysyła wiadomość, wczytuje stronę 
             * z podziękowaniem albo zwraca błędy. */ 
            $this->MailSender();		
        }
        /* zwraca monit o wysłaniu wiadomości lub tablicę z błedami. */
        if(!empty($this->aErrors)){
            $aReturnedValuesAndErrors = [];
            foreach ($this->aFormData as $sKey => $sValue) {
                $aReturnedValuesAndErrors[$sKey]['value'] = $sValue;
            }
            
            foreach($this->aErrors as $sKey => $sValue) {
                $aReturnedValuesAndErrors[$sKey]['error'] = $sValue;
            }
            return $aReturnedValuesAndErrors;
        }
    }
    /* funkcja ta na podstawie przekazanego parametru wysyła wiadomość tylko do
     * odbiorcy albo do odbiorcy i nadawcy. */
    private function MailSender() {
        /* wywołuje fukcję, która przygotowuje treść wiadomości na podstawie 
         * danych z formularza oraz tablicy z ustawieniami wiadomości. */
        $this->PrepareMessage();
        /* wysyła email do odbiorcy i potwierdzenie do nadawcy*/    
        if($this->aFormData['sendemailcopy'] == 1){
            if(!mail($this->aMessage['recipients'], $this->aMessage['subject'], $this->aMessage['content'], $this->aMessage['header'])) {
                $this->aErrors['failed'] = $this->aMessage['error'];
            }
            else{            
                header("Location: dziekuje.html");
                exit();
            }
        }
        /* wysyła email tylko do odbiorcy */
        else{
             if(!mail($this->aMessage['recipient'], $this->aMessage['subject'], $this->aMessage['content'], $this->aMessage['header'])) {
                 $this->aErrors['failed'] = $this->aMessage['error'];
            }
            else{            
                header("Location: dziekuje.html");
                exit();
            }
        }       
        
    }
    /* funkcja na podstawie przefiltrowanych wczesniej wartości z formularza
     * oraz ustawień wiadomości przygotowuje ostateczną treść wiadomości, która
     * zostanie wysłana do adresata. */
    private function PrepareMessage(){
        $this->aMessage['recipients'] = $this->aMessage['recipient'].', '.$this->aFormData['email'];
        $this->aMessage['subject'] = $this->aFormData['subject'].$this->aMessage['subject'];        
        $this->aMessage['content'] = "Wiadomość od ".$this->aFormData['name']." ".$this->aFormData['surname']."\r\n".$this->aFormData['content'];
        $this->aMessage['header'] = "From: ".$this->aFormData['email']."\r\n".$this->aMessage['header'];       
        $this->aMessage['sender'] = $this->aFormData['email'];
    }
    
    /* funkcja dla każdego klucza z przekazanej tablicy (klucze tablicy $_POST)
     * sprawdza czy taki klucz istnieje również w tablicy z parametrami
     * walidacji a jeśli istnieje to wywołuje dla niego funkcję Filtrującą
     * dane - FilterFormData */
    private function ValidateForm($aKeys) {
        foreach ($aKeys as $sKey) {
            if (array_key_exists($sKey, $this->aValidateParams)) {
                $this->FilterFormData($sKey);
            }
        }
    }
    /* funkcja filtruje dane z fomrularza za pomocą funkcji filter_input
     * korzystając z parametrów walidacji zawartych w tablicy przekazanej przy
     * tworzeniu obiektu Mailer (TABLICA TA MUSI ZAWIERAĆ WSZYSTKIE PARAMETRY
     * DLA KAŻDEGO POLA FORMULARZA W INNYM PRZYPADKU DANE WPROWADZONE PRZEZ
     * UŻYTKOWNIKA NIE ZOSTANĄ ZWALIDOWANE!!!).*/
    private function FilterFormData($sKey) {
        /* Jeśli wartość nie jest pusta rozpoczyna walidację */
        if(filter_input(INPUT_POST, $sKey, FILTER_UNSAFE_RAW) != '') {
            /* Jeśli wartość klucza to 'g-recaptcha-response' wówczas wywoływana
             * jest zewnętrzna funkcja walidująca CaptchaValid. W innym razie
             * walidacja przeprowadzana jest tutaj. Jeśli wartość przejdzie
             * walidację zapisywana jest do tablicy $this->aFormData, jeśli
             * nie przejdzie - czyli filter_input zwróci FALSE, w tablicy
             * $this->aErrors zapisywany jest odpowiedni komunikat błędu
             * zdefiniowany wcześniej w tablicy z parametrami walidacji. */
            if($sKey == 'g-recaptcha-response') {
                $this->CaptchaValid($sKey, $this->sPrivateKey);
            }
            else {
                $this->aFormData[$sKey] = strip_tags(filter_input(INPUT_POST, $sKey, $this->aValidateParams[$sKey]['filter'], $this->aValidateParams[$sKey]['options']));
                
            }
            if($sKey != 'g-recaptcha-response' && $this->aFormData[$sKey] === '') {
                $this->aFormData[$sKey] = filter_input(INPUT_POST, $sKey, FILTER_UNSAFE_RAW);
                $this->aErrors[$sKey] = $this->aValidateParams[$sKey]['error'];
            }
        }
        else if(key_exists('null', $this->aValidateParams[$sKey])) {
            /* Jesli wartość jest pusta w tablicy $this->aErrors zapisywany
             * jest odpowiedni komunikat błędu zdefiniowany wcześniej w tablicy
             *  z parametrami walidacji. */
            $this->aErrors[$sKey] = $this->aValidateParams[$sKey]['null'];
        }
    }   
    /* Funkcja ta waliduje tylko parametr 'g-recaptcha-response'. Jeśli
     * walidacja nie powiedzie się (rezultat ostatnie instrukcji warunkowej
     * zwróci TRUE funkcja ustawi odpowiedni błąd. */
    private function CaptchaValid($sKey, $sPrivateKey) {        
        $sUrl = 'https://www.google.com/recaptcha/api/siteverify';
        $aData = [
            'secret' => $sPrivateKey,
            'response' => filter_input(INPUT_POST, $sKey, FILTER_SANITIZE_STRING),
            'remoteip' => filter_input(INPUT_SERVER, 'REMOTE_ADDR', FILTER_VALIDATE_IP)];        
        $aOptions = [
            'http' => [
                'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                'method' => 'POST',
                'content' => http_build_query($aData)]];
        $rContext = stream_context_create($aOptions);
        $rResult = file_get_contents($sUrl, false, $rContext);
        
        if(!json_decode($rResult)->success){
            var_dump(json_decode($rResult)->success);exit();
            $this->aErrors[$sKey] = $this->aValidateParams[$sKey]['error'];
        }        
    }    
    /* usuwane dane z tablicy $_POST */   
    public function __destruct(){
        array_splice($_POST, 0);
    }
}
