<?php

class captcha extends model {

    public function display() {
      $challenge = $_GET['challenge'];
      $response = $_GET['response'];
      $remoteip = $_SERVER['REMOTE_ADDR'];
      $curl = curl_init('http://api-verify.recaptcha.net/verify?');
      curl_setopt ($curl, CURLOPT_POST, 4);
      curl_setopt ($curl, CURLOPT_POSTFIELDS, "privatekey=$this->recaptcha_privatekey&remoteip=$remoteip&challenge=$challenge&response=$response");
      $result = curl_exec ($curl);
      curl_close ($curl);
    }

}

?>
