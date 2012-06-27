<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">

<html>
<head>
  <title><?php echo $this->title; ?></title>
  <link rel="icon" href="/favicon.ico" type="image/png">
  <link href='/includes/style.css' rel='stylesheet' type='text/css'>
  <script type='text/javascript' src='/includes/syntax/scripts/shCore.js'></script>
  <script type='text/javascript' src='/includes/syntax/scripts/shAutoloader.js'></script>
  <link type='text/css' rel='stylesheet' href='/includes/syntax/styles/shCore.css'>
  <link type='text/css' rel='stylesheet' href='/includes/syntax/styles/shThemeDefault.css'>
  <script type='text/javascript'>
    function highlight() {
      SyntaxHighlighter.autoloader(
       'js /includes/syntax/scripts/shBrushJScript.js',
       'bash /includes/syntax/scripts/shBrushBash.js',
       'sql /includes/syntax/scripts/shBrushSql.js',
       'cpp /includes/syntax/scripts/shBrushCpp.js');
      SyntaxHighlighter.defaults['gutter'] = false;
      SyntaxHighlighter.defaults['toolbar'] = false;
      SyntaxHighlighter.all();
    }
  </script>
  <script type='text/javascript' src='http://www.google.com/recaptcha/api/js/recaptcha_ajax.js'></script>
  <script type='text/javascript' src='/includes/comment.js'></script>
</head>

<body onload="return typeof highlight == 'function' ? highlight() : true">
  <div id="structure">
    <div id="banner">
      <a href="<?php echo $this->home_link ?>">
      <img src="/images/dylansserver.png" alt="dylansserver"
      border="0"></a>
    </div>

    <div id="content">
      <div id='notes'>
        <?php
          if (count($this->notes) >= 1) {
            foreach ($this->notes as $note) {
              echo "<div class='note'>";
              echo "<h1><span class='date'>";
              echo $note['year_posted'] . "/";
              echo $note['month_posted'] . "/";
              echo $note['day_posted'] . "/";
              echo "</span><a href='" . $note['url'] . "'>";
              echo $note['title'] . "</a></h1>";
              echo $note['text'];
              echo "</div>";
            }
          } else {
            echo "<br>";
            echo "<h1>sorry, nothing here</h2>";
            echo "<pre>Empty set (0.00 sec)</pre>";
          }
        ?>
      </div>
      <div id="contact_me"><h1><a href=
      "mailto:dylan@psu.edu">dylan</a></h1><a href=
      "mailto:dylan@psu.edu">@psu.edu</a>
      </div>
    </div>
    <br>
    <br>
  </div>
</body>
</html>
