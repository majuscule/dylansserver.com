<!DOCTYPE html>

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
  <script type='text/javascript' src='https://www.google.com/recaptcha/api/js/recaptcha_ajax.js'></script>
  <script type='text/javascript' src='/includes/comment.js'></script>
  <meta http-equiv="Content-Type" content="text/html;charset=utf-8">
</head>

<body onload="return typeof highlight == 'function' ? highlight() : true">
  <div id="structure">
    <div id="banner">
      <a href="<?php echo $this->home_link ?>">
      <img src="/images/dylansserver.png" alt="dylansserver"></a>
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
      "mailto:dylan@dylansserver.com">dylan</a></h1><a href=
      "mailto:dylan@dylansserver.com">@dylansserver.com</a>
      </div>
    </div>
    <br>
    <br>
  </div>
<? include 'analytics.php' ?>
</body>
</html>
