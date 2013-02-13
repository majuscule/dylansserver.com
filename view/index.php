<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">

<html>
<head>
  <title><?php echo $this->title ?></title>
  <link rel="icon" href="/favicon.ico" type="image/png">
  <link href='/includes/style.css' rel='stylesheet' type='text/css'>
  <script type='text/javascript' src='/includes/index.js'></script>
  <meta http-equiv="Content-Type" content="text/html;charset=utf-8">
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
          <h3>things i've worked on:</h3>
        </li>
 
        <li><a href=
        "https://duckduckgo.com">duckduckgo.com</a></li>
 
        <li><a href=
        "http://tempositions.com">tempositions.com</a></li>
 
        <li>
          <h3>things i've made for others:</h3>
        </li>
 
        <li><a href=
        "http://activehamptons.com">activehamptons.com</a></li>
 
        <li><a href=
        "http://transfishing.com">transfishing.com</a></li>
 
        <li>
          <h3>my repositories:</h3>
        </li>
 
        <li><a href=
        "/git/">git://dylansserver.com</a></li>
 
        <li><a href=
        "https://github.com/nospampleasemam">git://github.com/nospampleasemam</a></li>
 
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
          <div id="contact_me">
            <h1><a href= "mailto:dylan@dylansserver.com">dylan</a></h1>
            <a href= "mailto:dylan@dylansserver.com">@dylansserver.com</a>
           <br>
           [<a href="http://pgp.mit.edu:11371/pks/lookup?op=vindex&amp;search=0xBE93C5C18CD4C40F">pgp key</a>]
          </div>
        </li>
      </ul>
    </div>
    <br>
    <br>
    <br>
    <br>
    <br>
    &nbsp;
  </div>
</body>
</html>
