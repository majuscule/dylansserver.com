<?php

abstract class cms {
  private $config_file = '/etc/dylanstestserver.ini';
  protected $db;
  protected $recaptcha_publickey;
  protected $recaptcha_privatekey;

  public function __construct() {
    $config = parse_ini_file($this->config_file, true);
    $this->db = new mysqli(
	  $config[database]['domain'],
	  $config[database]['user'],
	  $config[database]['password'],
      $config[database]['database']);
	if (mysqli_connect_errno()) {
	  echo "Problem connecting to database: ";
	  echo mysqli_connect_error();
	  exit();
	}
	$this->recaptcha_publickey = $config[recaptcha]['publickey'];
	$this->recaptcha_privatekey = $config[recaptcha]['privatekey'];
	ob_start();
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
	if (cms::determine_type() == "index") { $scripts = "<script type=\"text/javascript\" src=\"/includes/all.js\">";
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

  public function display_contact() {
    echo <<<END_OF_CONTACT
      <div id="contact_me"><h1><a href=
      "mailto:dylan@psu.edu">dylan</a></h1><a href=
      "mailto:dylan@psu.edu">@psu.edu</a>
	  </div>
END_OF_CONTACT;
  }

  public function display_close($show_contact = true) {
    if ($show_contact) {
	  $this->display_contact();
	}
    echo <<<END_OF_CLOSE
    </div>
	<br>
    <br>
  </div>
</body>
</html>
END_OF_CLOSE;
  ob_flush();
  }

}

class blank_page extends cms {

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
OTHER_PROJECTS;
        // Because of the CSS necessary for the animations,
		// the contact link needs to be in #portfolio to clear
		// the floats.
	    $this->display_contact();
      echo "</ul>";
		$this->display_close($show_contact = false);
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
	    throw new notFound();
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
	  throw new notFound();
	}
	if ($this->page > $this->number_of_pages) {
	  throw new notFound();
	}
	if ($this->page < 1) {
	  throw new notFound();
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

  private $id;
  private $comments_enabled = false;
  private $url;

  public function __construct($comments_enabled = false) {
    parent::__construct();
	$this->check_exists();
	$this->comments_enabled = $comments_enabled;
    $url = htmlspecialchars($_SERVER['REQUEST_URI']);
	if (isset($_GET['verify'])) {
      $url = substr($url, 0, (strlen($url)-7));
	}
	$this->url = $url;
  }

  private function check_exists() {
    $sql = "SELECT COUNT(*) FROM notes
			  WHERE url = ?";
	$results = $this->query($sql, "s", $_GET['note']);
	if ($results[0]["COUNT(*)"] != 1) {
	  throw new notFound();
	}
  }

  public function display() {
	$this->display_head();
	$this->display_note();
	if (isset($_GET['verify'])) {
	  $this->verify();
	}
	if ($this->comments_enabled) {
	  $this->display_comments();  // but where are they?
	  $this->display_comment_form();
	}
	$this->write_navigation();
    $this->display_close();
  }

  private function verify() {
    require_once('includes/recaptchalib.php');
    $resp = recaptcha_check_answer ($this->recaptcha_privatekey,
								    $_SERVER["REMOTE_ADDR"],
								    $_POST["recaptcha_challenge_field"],
								    $_POST["recaptcha_response_field"]);
    if (!$resp->is_valid) {
      echo "The reCAPTCHA wasn't entered correctly. Go back and try it again." . "(reCAPTCHA said: " . $resp->error . ")";
    } else {
	  $sql = ("INSERT INTO comments (date_posted, author,
	  			email, text, note
				VALUES(NOW(), ?, ?, ?, ?, ?");
	  echo htmlspecialchars($_POST['author']);
	  echo htmlspecialchars($_POST['email']);
	  echo htmlspecialchars($_POST['text']);
	}
  }

  private function display_note() {
    $sql = "SELECT title, date_posted, text, id
			  FROM notes WHERE url = ?";
	$result = $this->query($sql, "s",
							  $_GET['note']);
	$entry = $result[0];
	$this->id = $entry["id"]; // This is needed for display_comments()
	$title = $entry["title"];
	$date_posted =  explode("-", $entry["date_posted"]);
	$year_posted = $date_posted[0];
	$month_posted = $date_posted[1];
	$datetime_posted = explode(' ', $date_posted[2]);
	$day_posted = $datetime_posted[0];
	echo "<div id=\"note\">";
    echo "<h2><span style=\"color:grey;\">$year_posted/$month_posted/$day_posted/</span>$title</h2>";
	if (!$this->comments_enabled) {
	  $this->display_comment_link();
	}
	echo $entry['text'];
  }

  private function write_navigation() {
    echo <<<END_OF_NAVIGATION
	<br>
    <div id=\"navigation\">
    <h2>
    <a href=\"/notes/\">notes</a>/
    </h2>
    </div>
END_OF_NAVIGATION;
  }

  private function display_comment_link() {
    $url = $this->url . 'comments/';
    echo "<a id=\"comment_link\" href=\"$url\">comments</a>";
  }

  private function display_comments() {
    echo "<div id=\"comments\">";
	$sql= "SELECT date_posted, author, email, text
	         FROM comments WHERE note = ?";
    $result = $this->query($sql, "d", $this->id);
	foreach ($result as $row => $entry) {
	  $date_posted = $entry['date_posted'];
	  $author = $entry['author'];
	  $email = $entry['email'];
	  $text = htmlspecialchars($entry['text']);
	  echo <<<END_OF_COMMENT
	  <h3><a href="mailto:$email">$author</a></h3>
	  $text
	  <br>
	  <br>
END_OF_COMMENT;
	}
	echo "</div>";
  }

  private function display_comment_form() {
    echo <<<END_CAPTCHA_STYLE
<script type="text/javascript">
var RecaptchaOptions = {
   theme : 'custom',
   custom_theme_widget: 'recaptcha_widget'
   };
</script>
END_CAPTCHA_STYLE;
    require_once('includes/recaptchalib.php');
	// Trailing slash is necessary for reloads to work
    $url = $this->url . "verify/";
	echo "<form method=\"post\" action=\"$url\">";
	echo  <<<FORM
	<div id="comment">

<h3>comment:</h3><br>
<textarea rows="10" cols="30" name=text></textarea><br>
<h3>name:</h3><br>
<input type=text name=author><br>
<h3>email:</h3><br>
<input type=text name=email><br>
<nowiki>

<div id="recaptcha_widget"> 
  <div id="recaptcha_image"></div>
  <div class="recaptcha_only_if_incorrect_sol" style="color:red">Incorrect please try again</div>
  <span class="recaptcha_only_if_image"><b>enter the words above</b>:</span>
  <span class="recaptcha_only_if_audio"><b>enter the numbers you hear</b>:</span>
  <br>
  <input type="text" id="recaptcha_response_field" name="recaptcha_response_field" />
  <div><a href="javascript:Recaptcha.reload()">get another CAPTCHA</a></div>
  <div class="recaptcha_only_if_image"><a href="javascript:Recaptcha.switch_type('audio')">get an audio CAPTCHA</a></div>
  <div class="recaptcha_only_if_audio"><a href="javascript:Recaptcha.switch_type('image')">Get an image CAPTCHA</a></div>
  <div><a href="javascript:Recaptcha.showhelp()">help?</a></div>
  <br><br>
</div>
FORM;
	echo recaptcha_get_html($this->recaptcha_publickey);
	echo  <<<END_OF_FORM
	<input class="submit" type="submit" value="comment">
	</form>
	</div>
END_OF_FORM;
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
			  	FROM notes WHERE YEAR(date_posted) = ?
				ORDER BY date_posted DESC";
		$result = $this->query($sql, "d",
							  	$_GET['year']);
	    break;
	  case (isset($_GET['year']) && isset($_GET['month'])
	 		  && !isset($_GET['day'])):
    	$sql = "SELECT title, url, date_posted, text
			  	FROM notes WHERE YEAR(date_posted) = ?
				AND MONTH(date_posted) = ?
				ORDER BY date_posted DESC";
		$result = $this->query($sql, "dd",
							  	$_GET['year'], $_GET['month']);
	    break;
	  case (isset($_GET['year']) && isset($_GET['month'])
	          && isset($_GET['day'])):
    	$sql = "SELECT title, url, date_posted, text
			  	FROM notes WHERE YEAR(date_posted) = ?
				AND MONTH(date_posted) = ?
				AND DAY(date_posted) = ?
				ORDER BY date_posted DESC";
		$result = $this->query($sql, "ddd",
							  	$_GET['year'], $_GET['month'],
								$_GET['day']);
	    break;
	}
	if (count($result) >= 1) {
	  echo "<div id=\"notes\">";
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
	  echo "<h2 style=\"font-family:sans-serif;\">sorry, nothing here</h2>";
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


class notFound extends Exception {
	public function __construct() {
      header("HTTP/1.0 404 Not Found");
	  ob_end_clean();
	  include("404.php");
	  exit();
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
    if (isset($_GET['comments'])) {
      $note = new note($comments_enabled = true);
	} else {
      $note = new note;
	}
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
