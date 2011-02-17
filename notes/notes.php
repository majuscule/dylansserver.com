<?php
  $config = parse_ini_file('/etc/dylanstestserver.ini');
  mysql_connect($config['domain'], $config['user'], $config['password']) or die(mysql_error());
  mysql_select_db($config['database']) or die(mysql_error());
  $sql = "SELECT COUNT(*) FROM notes";
  $result = mysql_query($sql);
  $result = mysql_fetch_row($result);
  $number_of_notes = $result[0];
  $notes_per_page = 4;
  $total_number_of_pages = ceil($number_of_notes/$notes_per_page);
  if (isset($_GET['note'])) {
	$note = mysql_real_escape_string($_GET['note']);
    $sql = "SELECT title, date_posted, text
	         FROM notes WHERE url=\"$note\"";
    $result = mysql_query($sql) or die(mysql_error());
    while($note = mysql_fetch_array($result)) {
	  echo "<div class=\"note\">";
	  $title = $note['title'];
	  $date_posted =  explode("-", $note['date_posted']);
	  $year_posted = $date_posted[0];
	  $month_posted = $date_posted[1];
	  $day_posted = $date_posted[2];
      echo "<h2><span style=\"color:grey;\">$year_posted/$month_posted/$day_posted/</span>$title</h2>";
	  echo $note['text'];
	  echo "</div>";
	}
  } else {
    if (isset($_GET['page']) && is_numeric($_GET['page'])) {
      $page = (int) $_GET['page'];
    } else {
      $page = 1;
    }
    if ($page < 1) {
      $page = 1;
    } else if ($page > $total_number_of_pages) {
      $page = $total_number_of_pages;
    }
    $page_offset = ($page - 1) * $notes_per_page;
    $notes = mysql_query("SELECT title, date_posted, text, url
                            FROM notes ORDER BY date_posted DESC
						    LIMIT $page_offset, $notes_per_page");
    while($note = mysql_fetch_array($notes)) {
	  echo "<div class=\"note\">";
	  $title = $note['title'];
	  $date_posted =  explode("-", $note['date_posted']);
	  $year_posted = $date_posted[0];
	  $month_posted = $date_posted[1];
	  $day_posted = $date_posted[2];
	  $url = $note['url'];
      echo "<h2><span style=\"color:grey;\">$year_posted/$month_posted/$day_posted/</span><a href=\"/dylanstestserver/notes/$url\">$title</a></h2>";
	  echo $note['text'];
	  echo "</div>";
    }
    echo "<h2>";
    if($page != 1){
	  if(!$page == 2 && $total_number_of_pages == 2)
      echo "<a href=\"{$_SERVER['PHP_SELF']}/page/1\">first</a> / ";
      $previous_page = $page - 1;
      echo "<a href=\"/dylanstestserver/notes/page/$previous_page\">prev</a>";
    }
    if($page < $total_number_of_pages) {
      $forward_page = $page + 1;
      echo "<a href=\"/dylanstestserver/notes/page/$forward_page\">next</a>";
    }
    if($page != $total_number_of_pages && (!$page == 1 && $total_number_of_pages == 2)){
      echo " / <a href=\"/dylanstestserver/notes/page/$total_number_of_pages\">last</a>";
    }
    echo "</h2>";
  }
?>
