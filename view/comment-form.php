<script type="text/javascript">
Recaptcha.create("$publickey",
   "recaptcha_div", 
   {
     theme : 'custom',
     custom_theme_widget: 'recaptcha_widget',
     callback: Recaptcha.focus_response_field
   });
</script>
<?php
require_once('/srv/http/dylansserver.com/includes/recaptchalib.php');
$url = $this->url . "verify";
?>
<form id='comment_form'  method='post' action='$url'>
  <div id="comment">
    <h3>comment:</h3>
    <textarea rows="10" cols="70" name="text" id="comment_text"></textarea>
    <h3>name:</h3>
    <input type=text name="name" id="comment_name">
  
    <nowiki>
    <div id="recaptcha_widget"> 
      <br>
      <h3><b>what's this say</b>?</h3>
      <br>
        <div id="recaptcha_image"></div>
      <br><br><br>
      <span class="recaptcha_only_if_image"><br><br><br></span>
      <input type="text" id="recaptcha_response_field" name="recaptcha_response_field" />
      <br><br>
      <h3 class="recaptcha_only_if_audio"><b>enter the numbers you hear</b>:</h3>
      <span class="recaptcha_help">
        <a href="javascript:Recaptcha.reload()">another?</a> /
        <span class="recaptcha_only_if_image"><a href="javascript:Recaptcha.switch_type('audio')">audio?</a> /</span>
        <span class="recaptcha_only_if_audio"><a href="javascript:Recaptcha.switch_type('image')">image?</a></span><a href="javascript:Recaptcha.showhelp()">help?</a>
      </span>
    </div>
    <?php recaptcha_get_html($this->recaptcha_publickey);
    if ($this->failed_captcha) {
        echo "<div id=\"not_human\">";
        echo "  reCAPTCHA said you're not human, <br>";
        echo "  try again?";
        echo "</div>";
    } else {
        echo "<div id=\"blank_comment\">";
        echo "  but you didn't write anything! <br>";
        echo "</div>";
    } ?>
    <input id="submit" class="submit" type="submit" value="post comment">
  </div>
</form>
<br><br><br><br>
