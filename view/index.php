<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">

<html>
<head>
  <title><?php echo $this->title ?></title>
  <link rel="icon" href="/favicon.ico" type="image/png">
  <link href='/includes/style.css' rel='stylesheet' type='text/css'>
  <script type='text/javascript' src='/includes/index.js'></script>
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
</head>

<body onload="return typeof highlight == 'function' ? highlight() : true">
  <div id="structure">
    <div id="banner">
      <a href="<?php echo $this->home_link ?>">
      <img src="/images/dylansserver.png" alt="dylansserver"
      border="0"></a>
    </div>
    <div id="content">
      <div id='exhibit'>
        <?php
          foreach ($this->exhibits as $exhibit) {
            echo $exhibit;
          }
        ?>
      </div>
      <ul id="portfolio">
        <li>
           <h3>my projects:</h3>
        </li>
        <?php
          foreach ($this->projects as $project => $title) {
              echo "<li><a class='tab' href='$title'>$title</a></li>";
          }
        ?>
        <li>
          <h3>things i've done for others:</h3>
        </li>
 
        <li><a href=
        "http://activehamptons.com">activehamptons.com</a></li>
 
        <li><a href=
        "http://transfishing.com">transfishing.com</a></li>
 
        <li>
          <h3>something i've worked on:</h3>
        </li>
 
        <li><a href=
        "http://tempositions.com">tempositions.com</a></li>
 
        <li>
          <h3>my repositories:</h3>
        </li>
 
        <li><a href=
        "/git/">git://dylansserver.com</a></li>
 
        <li>
          <h3>some notes:</h3>
        </li>
 
        <li><a href=
        "/notes/">here</a> [<a href="/notes/rss">rss</a>]</li>
 
        <li>
          <h3>my resume:</h3>
        </li>
 
        <li>[<a href=
        "/resume">pdf</a>]</li>
 
        <li>
        </li>

        <li>
          <div id="contact_me"><h1><a href=
          "mailto:dylan@psu.edu">dylan</a></h1><a href=
          "mailto:dylan@psu.edu">@psu.edu</a>
          </div>
        </li>
      </ul>
    </div>
    <br>
    <br>
  </div>
</body>
</html>
</html>
