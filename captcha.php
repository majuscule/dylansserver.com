<?php

require_once('index.php');

$page = new blank_page();

require_once('includes/recaptchalib.php');
$resp = recaptcha_check_answer ($page->recaptcha_privatekey,
								$_SERVER["REMOTE_ADDR"],
								$_POST["recaptcha_challenge_field"],
								$_POST["recaptcha_response_field"]);
if (!$resp->is_valid) {
  die ("The reCAPTCHA wasn't entered correctly. Go back and try it again." .
  "(reCAPTCHA said: " . $resp->error . ")");
} else {
  echo "success!";
}

?>
