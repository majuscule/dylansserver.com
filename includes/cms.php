<?php

abstract class cms {
  private $config_file = '/etc/dylanstestserver.ini';
  protected $db;

  public function __construct() {
    $config = parse_ini_file($this->config_file);
    $this->db = new mysqli(
	  $config['domain'],
	  $config['user'],
	  $config['password'],
      $config['database']);
	if (mysqli_connect_errno()) {
	  echo "Problem connecting to database: ";
	  echo mysqli_connect_error();
	  exit();
	}
  }

  public static function determine_type() {
    if (isset($_GET['page']) && is_numeric($_GET['page'])) {
	  return 'page';
	} else if (isset($_GET['year'])) {
	  return 'archive';
	} else if (isset($_GET['note'])) {
	  return 'note';
	} else if ($_SERVER['REQUEST_URI'] == '/') {
      return 'index';
	} else if (isset($_GET['project'])) {
      return 'project';
	}
  }

  protected function not_found() {
    header("HTTP/1.0 404 Not Found");
	include("404.php");
	exit();
  }

  public function query() {
    $args = func_get_args();
	$statement = $this->db->prepare($args[0]);
	$args = array_slice($args, 1);
	call_user_func_array(array($statement, 'bind_param'), &$args);
	$statement->execute();
	$return = array();
	$statement->store_result();
	$row = array();
	$data = $statement->result_metadata();
	$fields = array();
	$fields[0] = &$statement;
	while($field = $data->fetch_field()) {
	  $fields[] = &$row[$field->name];
	}
	call_user_func_array("mysqli_stmt_bind_result", $fields);
	$i = 0;
	while ($statement->fetch()) {
  	  foreach ($row as $key1=>$value1) $return[$i][$key1] = $value1;
	  $i++;
	}
	$statement->free_result();
	return $return;
  	}

  public function display_head($title = "dylanstestserver",
  								$home_link = "/") {
    $scripts = "";
	$stylesheets = "<link href=\"/includes/style.css\" rel=\"stylesheet\" type=\"text/css\">";
	if (cms::determine_type() == "index") {
	  $scripts = "<script type=\"text/javascript\" src=\"/includes/all.js\">";
	  $home_link = "http://validator.w3.org/unicorn/check?ucn_uri=dylanstestserver.com&amp;ucn_task=conformance#";
	}
  echo <<<END_OF_HEAD
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">

<html>
<head>
  <meta name="generator" content=
  "HTML Tidy for Linux (vers 25 March 2009), see www.w3.org">
  <meta http-equiv="Content-type" content="text/html;charset=UTF-8">

  <title>$title</title>
  <link rel="icon" href="favicon.ico" type="image/png">
  $stylesheets
  $scripts
</script>
</head>

<body>
  <div id="structure">
    <div id="banner">
      <a href="$home_link">
      <img src="/images/dylanstestserver.png" alt="dylanstestserver"
      border="0"></a>
    </div>

    <div id="content">
END_OF_HEAD;
  }

  public function display_close(){
    echo <<<END_OF_CLOSE
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
END_OF_CLOSE;
  }

}

class index extends cms {
	public function display() {
		$this->display_head();
		$this->display_exhibits();
		echo "<ul id=\"portfolio\" style=\"text-align:right\">";
		$this->list_projects();
		echo <<<OTHER_PROJECTS
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
        "/notes/">here</a></li>

        <li>
        </li>
      </ul>
OTHER_PROJECTS;
		$this->display_close();
	}

	protected function display_exhibits() {
	  echo "<div id=\"exhibit\">";
      $sql = "SELECT text FROM projects";
	  $result = $this->db->query($sql);
	  while ($entry = $result->fetch_object()) {
	    echo $entry->text;
	  }
	  echo "</div>";
	}

	private function list_projects() {
	  echo "<div id=\"exhibit\">";
	  echo <<<HEREDOC
        <li>
          <h3>my projects:</h3>
        </li>
HEREDOC;
      $sql = "SELECT title FROM projects";
	  $result = $this->db->query($sql);
	  while ($entry = $result->fetch_object()) {
	    echo "<li><a class=\"tab\" href=\"$entry->title\">$entry->title</a></li>";
	  }
	}
}

class project extends index {
	protected function display_exhibits() {
	  echo "<div id=\"exhibit\">";
	  $sql = "SELECT text FROM projects
	  		    WHERE title = ?";
	  $result = $this->query($sql, "s", $_GET['project']);
	  if ($result = $result[0]['text']) {
	    $text =  str_replace("class=\"exhibit\"", "class=\"exhibit\" style=\"display:block;\"", $result);
	    echo $text;
	    echo "</div>";
	  } else {
	    $this->not_found();
	  }
	}
}

class page extends cms {
  private $page = 1;
  private $offset = 0;
  private $notes_per_page = 4;
  private $number_of_pages = 1;

  public function __construct() {
    parent::__construct();
	$this->page_offset();
  }

  private function page_offset() {
	$sql = "SELECT COUNT(*) FROM notes";
	$result = $this->db->query($sql);
	$result = $result->fetch_array();
	$this->number_of_pages = ceil($result[0] / $this->notes_per_page);
	if (isset($_GET['page']) && is_numeric($_GET['page'])) {
	  $this->page = (int) $_GET['page'];
	} else {
	  $this->not_found();
	}
	if ($this->page > $this->number_of_pages) {
	  $this->not_found();
	}
	if ($this->page < 1) {
	  $this->not_found();
	}
	$this->offset = ($this->page - 1) * $this->notes_per_page;
  }

  public function display() {
    $this->display_head();
	echo "<div id=\"notes\">";
    $sql = "SELECT date_posted, title, url, text
              FROM notes ORDER BY date_posted DESC
			  LIMIT ?, ?";
	$result = $this->query($sql, "ii",
	                          $this->offset,
							  $this->notes_per_page);
	foreach ($result as $row => $entry) {
	  $title = $entry['title'];
	  $url = '/note/' . $entry['url'];
	  $date_posted =  explode("-", $entry['date_posted']);
	  $year_posted = $date_posted[0];
	  $month_posted = $date_posted[1];
	  $datetime_posted = explode(' ', $date_posted[2]);
	  $day_posted = $datetime_posted[0];
	  echo "<div class=\"note\">";
      echo "<h2><span style=\"color:grey;\">$year_posted/$month_posted/$day_posted/</span><a href=\"$url\">$title</a></h2>";
	  echo $entry['text'];
	  echo "</div>";
	}
	echo "</div>";
	$this->write_navigation();
    $this->display_close();
  }

  private function write_navigation() {
    echo "<div id=\"navigation\">";
    echo "<h2>";
    if($this->page > 1){
      $previous_page = $this->page - 1;
      echo "<a href=\"/notes/page/$previous_page\">prev</a>";
    }
    if($this->page < $this->number_of_pages) {
      $forward_page = $this->page + 1;
      echo " <a href=\"/notes/page/$forward_page\">next</a>";
    }
    echo "</h2>";
    echo "</div>";
  }

}

class note extends cms {

  public function __construct() {
    parent::__construct();
	$this->check_exists();
  }

  private function check_exists() {
    $sql = "SELECT COUNT(*) FROM notes
			  WHERE url = ?";
	$results = $this->query($sql, "s", $_GET['note']);
	if ($results[0]["COUNT(*)"] != 1) {
	  $this->not_found();
	}
  }

  public function display() {
	$this->display_head();
    $sql = "SELECT title, date_posted, text
			  FROM notes WHERE url = ?";
	$result = $this->query($sql, "s",
							  $_GET['note']);
	$entry = $result[0];
	$title = $entry["title"];
	$date_posted =  explode("-", $entry["date_posted"]);
	$year_posted = $date_posted[0];
	$month_posted = $date_posted[1];
	$datetime_posted = explode(' ', $date_posted[2]);
	$day_posted = $datetime_posted[0];
	echo "<div id=\"note\">";
    echo "<h2><span style=\"color:grey;\">$year_posted/$month_posted/$day_posted/</span>$title</h2>";
	echo $entry['text'];
	$this->write_navigation();
    $this->display_close();
  }

  private function write_navigation() {
	echo "<br>";
    echo "<div id=\"navigation\">";
    echo "<h2>";
    echo "<a href=\"/notes/\">notes</a>/";
    echo "</h2>";
    echo "</div>";
  }
}

class archive extends cms {

  public function __construct() {
    parent::__construct();
  }

  private function check_exists() {
    $sql = "SELECT COUNT(*) FROM notes
			  WHERE url = ?";
	$results = $this->query($sql, "s", $_GET['note']);
	if ($results[0]["COUNT(*)"] != 1) {
	  $this->not_found();
	}
  }

  public function display() {
    // this really needs its own pagination...
	// there should be a class for that.
	$this->display_head();
	switch (true) {
	  case (isset($_GET['year']) && !isset($_GET['month'])
	  		  && !isset($_GET['day'])):
    	$sql = "SELECT title, url, date_posted, text
			  	FROM notes WHERE YEAR(date_posted) = ?";
		$result = $this->query($sql, "d",
							  	$_GET['year']);
	    break;
	  case (isset($_GET['year']) && isset($_GET['month'])
	 		  && !isset($_GET['day'])):
    	$sql = "SELECT title, url, date_posted, text
			  	FROM notes WHERE YEAR(date_posted) = ?
				AND MONTH(date_posted) = ?";
		$result = $this->query($sql, "dd",
							  	$_GET['year'], $_GET['month']);
	    break;
	  case (isset($_GET['year']) && isset($_GET['month'])
	          && isset($_GET['day'])):
    	$sql = "SELECT title, url, date_posted, text
			  	FROM notes WHERE YEAR(date_posted) = ?
				AND MONTH(date_posted) = ?
				AND DAY(date_posted) = ?";
		$result = $this->query($sql, "ddd",
							  	$_GET['year'], $_GET['month'],
								$_GET['day']);
	    break;
	}
	if (count($result) >= 1) {
	  foreach ($result as $row => $entry) {
	    $title = $entry['title'];
	    $url = '/note/' . $entry['url'];
	    $date_posted =  explode("-", $entry['date_posted']);
	    $year_posted = $date_posted[0];
	    $month_posted = $date_posted[1];
	    $datetime_posted = explode(' ', $date_posted[2]);
	    $day_posted = $datetime_posted[0];
	    echo "<div class=\"note\">";
        echo "<h2><span style=\"color:grey;\">$year_posted/$month_posted/$day_posted/</span><a href=\"$url\">$title</a></h2>";
	    echo $entry['text'];
	    echo "</div>";
	  }
	  echo "</div>";
	  $this->write_navigation();
	} else {
	  echo "<br>";
	  echo "<h2 style=\"font-family:sans-serif;\">sorry, can't find that</h2>";
	  echo "<pre>Empty set (0.00 sec)</pre>";
	}
    $this->display_close();
  }

  private function write_navigation() {
	echo "<br>";
    echo "<div id=\"navigation\">";
    echo "<h2>";
	// fill me in!
    echo "</h2>";
    echo "</div>";
  }
}

## now actually do something:
switch (cms::determine_type()) {
  case "index":
    $index = new index();
	$index->display();
	break;
  case "project":
    $project = new project();
	$project->display();
	break;
  case "note":
    $note = new note;
	$note->display();
	break;
  case "page":
    $page = new page;
	$page->display();
	break;
  case "archive":
    $archive = new archive;
	$archive->display();
	break;
}

?>
