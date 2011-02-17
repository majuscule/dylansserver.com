<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">

<html>
<head>
  <meta name="generator" content=
  "HTML Tidy for Linux (vers 25 March 2009), see www.w3.org">
  <meta http-equiv="Content-type" content="text/html;charset=UTF-8">

  <title>dylanstestserver</title>
  <link href="includes/style.css" rel="stylesheet" type="text/css">
  <link rel="icon" href="favicon.ico" type="image/png">
  <script type="text/javascript" src="includes/all.js">
</script>
</head>

<body>
  <div id="structure">
    <div id="banner">
      <a href=
      "http://validator.w3.org/unicorn/check?ucn_uri=dylanstestserver.com&amp;ucn_task=conformance#">
      <img src="images/dylanstestserver.png" alt="dylanstestserver"
      border="0"></a>
    </div>

    <div id="content">
      <div id="exhibit">
	    <?php
		  $config = parse_ini_file('/etc/dylanstestserver.ini');
          mysql_connect($config['domain'], $config['user'], $config['password']) or die(mysql_error());
          mysql_select_db($config['database']) or die(mysql_error());
		  if (isset($_GET['project'])) {
		    $page_type = 'project';
			$project = mysql_real_escape_string($_GET['project']);
		    $sql = "SELECT text FROM projects WHERE title='$project'";
		  } else {
		    $sql = "SELECT text FROM projects";
		  }
          $result = mysql_query($sql) or die (mysql_error());
          while($project = mysql_fetch_array($result)) {
			$text = $project['text'];
			if (isset($page_type)) {
			  $text = str_replace("<div class=\"exhibit\"", "<div class=\"exhibit\" style=\"display:block;\"", $text);
			}
			echo $text;
          }
	    ?>
	  </div>

      <ul id="portfolio" style="text-align:right">
        <li>
          <h3>my projects:</h3>
        </li>

        <li><a class="tab" href="repthis">repthis.info</a></li>

        <li><a class="tab" href=
        "youtube_backup">youtube_backup</a></li>

        <li><a class="tab" href=
        "i_like_pandora">i_like_pandora</a></li>

        <li><a class="tab" href=
        "peepshow">foxy-addons/peepshow</a></li>

        <li><a class="tab" href="drawcss">drawcss</a></li>

        <li><a class="tab" href="readoo">readoo</a></li>

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
        "git">git://dylanstestserver.com</a></li>

        <li>
          <h3>some notes:</h3>
        </li>

        <li><a href=
        "/dylanstestserver/notes">here</a></li>

        <li>
          <h1 id="contact_me"><a href=
          "mailto:dylan@psu.edu">dylan</a></h1><a href=
          "mailto:dylan@psu.edu">@psu.edu</a>
        </li>
      </ul>
    </div><br>
    <br>
  </div>
</body>
</html>
