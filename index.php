<?php

abstract class cms {

  private $config_file = '/etc/dylansserver.ini';
  protected $db;
  protected $recaptcha_publickey;
  protected $recaptcha_privatekey;
  protected  $scripts;
  public $title;
  public $home_link;

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
    $this->recaptcha_publickey = $config['recaptcha']['publickey'];
    $this->recaptcha_privatekey = $config['recaptcha']['privatekey'];
    $this->title = $config['site']['default_title'];
    $this->home_link = $config['site']['home_link'];
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
    } else if (isset($_GET['challenge'])) {
      return 'captcha';
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
        foreach ($row as $key=>$value) $return[$i][$key] = $value;
      $i++;
    }
    $statement->free_result();
    return $return;
      }

  public function display_head($title = "dylansserver",
                                  $home_link = "/") {
    $scripts = $this->scripts;
    $stylesheets = "<link href='/includes/style.css' rel='stylesheet' type='text/css'>";
    $home_link = "http://validator.w3.org/unicorn/check?ucn_uri=dylansserver.com&amp;ucn_task=conformance#";
    echo <<<END_OF_HEAD
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">

<html>
<head>
  <meta name="generator" content=
  "HTML Tidy for Linux (vers 25 March 2009), see www.w3.org">
  <meta http-equiv="Content-type" content="text/html;charset=UTF-8">

  <title>$this->title</title>
  <link rel="icon" href="favicon.ico" type="image/png">
  $stylesheets
  $scripts
</head>

<body>
  <div id="structure">
    <div id="banner">
      <a href="$this->home_link">
      <img src="/images/dylansserver.png" alt="dylansserver"
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


class index extends cms {

  public function display() {
    $this->scripts = "<script type='text/javascript' src='/includes/index.js'></script>"; 
    $this->display_head();
    $this->display_exhibits();
    echo "<ul id='portfolio' style='text-align:right'>";
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
        "git">git://dylansserver.com</a></li>

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
    echo "<li>";
    $this->display_contact();
    echo "</li>";
    echo "</ul>";
    $this->display_close($show_contact = false);
  }

  protected function display_exhibits() {
    echo "<div id='exhibit'>";
    $sql = "SELECT text FROM projects";
    $result = $this->db->query($sql);
    while ($entry = $result->fetch_object()) {
      echo $entry->text;
    }
    echo "</div>";
  }

  private function list_projects() {
    echo <<<HEREDOC
        <li>
          <h3>my projects:</h3>
        </li>
HEREDOC;
    $sql = "SELECT title FROM projects";
    $result = $this->db->query($sql);
    while ($entry = $result->fetch_object()) {
      echo "<li><a class='tab' href='$entry->title'>$entry->title</a></li>";
    }
  }

}


class project extends index {

  protected function display_exhibits() {
    echo "<div id='exhibit'>";
    $sql = "SELECT text FROM projects
             WHERE title = ?";
    $result = $this->query($sql, "s", $_GET['project']);
    if ($result = $result[0]['text']) {
      $text =  str_replace("class='exhibit'", "class='exhibit' style='display:block;'", $result);
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
    echo "<div id='notes'>";
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
      echo "<div class='note'>";
      echo "<h2><span style='color:grey;'>$year_posted/$month_posted/$day_posted/</span><a href='$url'>$title</a></h2>";
      echo $entry['text'];
      echo "</div>";
    }
    echo "</div>";
    $this->write_navigation();
    $this->display_close();
  }

  private function write_navigation() {
    echo "<div id='navigation'>";
    echo "<h2>";
    if($this->page > 1){
      $previous_page = $this->page - 1;
      echo "<a href='/notes/page/$previous_page'>prev</a>";
    }
    if($this->page < $this->number_of_pages) {
    $forward_page = $this->page + 1;
    echo " <a href='/notes/page/$forward_page'>next</a>";
    }
    echo "</h2>";
    echo "</div>";
  }

}


class note extends cms {

  private $id;
  private $comments_enabled = false;
  private $failed_captcha;
  public $url;
  public $title;
  public $year_posted;
  public $month_posted;
  public $day_posted;
  public $text;
  public $number_of_comments;

  public function __construct() {
    if (isset($_GET['comments'])) {
      $this->scripts = "
        <script type='text/javascript' src='http://www.google.com/recaptcha/api/js/recaptcha_ajax.js'></script>
        <script type='text/javascript' src='/includes/comment.js'></script>";
    }
    parent::__construct();
    if (isset($_GET['comments'])) {
      $this->comments_enabled = true;
    }
    $url = htmlspecialchars($_SERVER['REQUEST_URI']);
    if (isset($_GET['verify'])) {
      $url = substr($url, 0, (strlen($url)-6));
    }
    $this->url = $url;
    $sql = "SELECT title, date_posted, text, id
              FROM notes WHERE url = ?";
    $result = $this->query($sql, "s",
                              $_GET['note']);
    if ($result) {
      $entry = $result[0];
      $this->id = $entry["id"];
      $this->title = $entry["title"];
      $date_posted =  explode("-", $entry["date_posted"]);
      $this->year_posted = $date_posted[0];
      $this->month_posted = $date_posted[1];
      $datetime_posted = explode(' ', $date_posted[2]);
      $this->day_posted = $datetime_posted[0];
      $this->text = $entry["text"];
    } else {
      throw new notFound();
    }
    $sql = "SELECT COUNT(*) FROM comments
              WHERE note = $this->id";
    $result = $this->db->query($sql);
    $result = $result->fetch_array();
    $this->number_of_comments = $result[0];
    if (isset($_GET['verify'])) {
      $this->verify();
    }
  }

  public function display() {
    $this->display_head();
    $this->display_note();
    if ($this->comments_enabled) {
      $this->display_comments();
      $this->display_comment_form();
    }
    $this->write_navigation();
    $this->display_close();
  }

  private function verify() {
    if (!isset($_POST['captcha'])) {
      require_once('includes/recaptchalib.php');
      echo "<br>";
      $resp = recaptcha_check_answer ($this->recaptcha_privatekey,
                                      $_SERVER["REMOTE_ADDR"],
                                      $_POST["recaptcha_challenge_field"],
                                      $_POST["recaptcha_response_field"]);
      if (!$resp->is_valid) {
        $this->failed_captcha = true;
      }
    }
    if (isset($_POST['captcha']) || $resp->is_valid) {
      $sql = ("INSERT INTO comments (date_posted, author,
                  text, note)
                VALUES(NOW(), ?, ?, ?)");
      $stmt = $this->db->prepare($sql);
      // Checks are needed here (no blank text,
      // and a default author needs to be set
      // for no-javascript users.
      $stmt->bind_param('sss',
                          htmlspecialchars($_POST['name']),
                          htmlspecialchars($_POST['text']),
                        $this->id);
      $stmt->execute();
    }
  }

  private function display_note() {
    echo <<<END_OF_NOTE
    <div id='note'>
    <h2><span style='color:grey;'>$this->year_posted/$this->month_posted/$this->day_posted/</span>$this->title</h2>
    $this->text
    </div>
END_OF_NOTE;
  }

  private function write_navigation() {
    echo <<<END_OF_NAVIGATION
    <br>
    <div id='navigation'>
    <h2>
END_OF_NAVIGATION;
    if (!$this->comments_enabled) {
      $this->display_comment_link();
    }
    echo <<<END_OF_NAVIGATION
    <a href="/notes/">back to notes</a>/
    </h2>
    </div>
END_OF_NAVIGATION;
  }

  private function display_comment_link() {
    if ($this->number_of_comments > 0) {
      $anchor_text = "comments($this->number_of_comments)/";
    } else {
      $anchor_text = "comment?";
    }
    if (substr($this->url, (strlen($this->url)-1), strlen($this->url)) == '/') {
      $url = $this->url . 'comments/';
    } else {
      $url = $this->url . '/comments/';
    }
    echo "<a id='comment_link' href='$url'>$anchor_text</a>";
  }

  private function display_comments() {
    echo "<div id='comments'>";
    $sql= "SELECT date_posted, author, text
             FROM comments WHERE note = ?
             ORDER BY date_posted DESC";
    $result = $this->query($sql, 'd', $this->id);
    foreach ($result as $row => $entry) {
      $date_posted = $entry['date_posted'];
      $author = $entry['author'];
      $text = htmlspecialchars($entry['text']);
      $head = "<h3>$author</h3>";
      echo <<<END_OF_COMMENT
      <div class='comment'>
      $head
      $text
      </div>
END_OF_COMMENT;
      }
    echo "</div>";
  }

  private function display_comment_form() {
    $publickey = $this->recaptcha_publickey;
    echo <<<END_CAPTCHA_STYLE
<script type="text/javascript">
Recaptcha.create("$publickey",
   "recaptcha_div", 
   {
     theme : 'custom',
     custom_theme_widget: 'recaptcha_widget',
     callback: Recaptcha.focus_response_field
   });
</script>
END_CAPTCHA_STYLE;
    require_once('includes/recaptchalib.php');
    $url = $this->url . "verify";
    echo "<form id='comment_form'  method='post' action='$url'>";
    echo <<<END_OF_FORM
    <div id="comment">
      <h3>comment:</h3>
      <textarea rows="10" cols="70" name="text" id="comment_text"></textarea>
      <h3>name:</h3>
      <input type=text name="name" id="comment_name">
  
      <nowiki>
      <div id="recaptcha_widget"> 
        <h3 class="recaptcha_only_if_image"><b>what's this say</b>?</h3>
        <h3 class="recaptcha_only_if_audio"><b>enter the numbers you hear</b>:</h3>
        <span style="font-size:80%;">
          ( <a href="javascript:Recaptcha.reload()">another</a> /
          <span class="recaptcha_only_if_image"><a href="javascript:Recaptcha.switch_type('audio')">audio</a></span> /
          <span class="recaptcha_only_if_audio"><a href="javascript:Recaptcha.switch_type('image')">image</a></span><a href="javascript:Recaptcha.showhelp()">help</a> )
        </span>
        <br><br>
        <input type="text" id="recaptcha_response_field" name="recaptcha_response_field" />
        <br><br>
        <div style="float:right;position:relative;width:100px;">
          <div id="recaptcha_image"></div>
        </div>
        <br><br><br><br>
      </div>
END_OF_FORM;
    echo recaptcha_get_html($this->recaptcha_publickey); 
    if ($this->failed_captcha) {
    echo <<<END_OF_ERRORS
        <div id="not_human">
          reCAPTCHA said you're not human, <br>
          try again?
        </div>
        <input id="submit" class="submit" type="submit" value="post comment">
        </form>
      </div>
END_OF_ERRORS;
    } else {
      echo <<<END_OF_ERRORS
        <div id="not_human">
          reCAPTCHA said you're not human, <br>
          try again?
        </div>
        <div id="blank_comment">
          but you didn't write anything! <br>
        </div>
END_OF_ERRORS;
    }
    echo <<<END_OF_FORM
      <input id="submit" class="submit" type="submit" value="post comment">
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
      echo "<div id='notes'>";
      foreach ($result as $row => $entry) {
        $title = $entry['title'];
        $url = '/note/' . $entry['url'];
        $date_posted =  explode("-", $entry['date_posted']);
        $year_posted = $date_posted[0];
        $month_posted = $date_posted[1];
        $datetime_posted = explode(' ', $date_posted[2]);
        $day_posted = $datetime_posted[0];
        echo "<div class='note'>";
        echo "<h2><span style='color:grey;'>";
        echo "$year_posted/$month_posted/$day_posted/";
        echo "</span><a href='$url'>$title</a></h2>";
        echo $entry['text'];
        echo "</div>";
      }
      echo "</div>";
      $this->write_navigation();
    } else {
      echo "<br>";
      echo "<h2 style='font-family:sans-serif;'>sorry, nothing here</h2>";
      echo "<pre>Empty set (0.00 sec)</pre>";
    }
    $this->display_close();
  }

  private function write_navigation() {
    echo "<br>";
    echo "<div id='navigation'>";
    echo "<h2>";
    // fill me in!
    echo "</h2>";
    echo "</div>";
  }

}


class notFound extends Exception {

    public function __construct() {
      header('HTTP/1.0 404 Not Found');
      ob_end_clean();
      include('404.php');
      exit();
    }

}


class captcha extends cms {

    public function display() {
      $challenge = $_GET['challenge'];
      $response = $_GET['response'];
      $remoteip = $_SERVER['REMOTE_ADDR'];
      $curl = curl_init('http://api-verify.recaptcha.net/verify?');
      curl_setopt ($curl, CURLOPT_POST, 4);
      curl_setopt ($curl, CURLOPT_POSTFIELDS, "privatekey=$this->recaptcha_privatekey&remoteip=$remoteip&challenge=$challenge&response=$response");
      $result = curl_exec ($curl);
      curl_close ($curl);
    }

}


## now actually do something:
switch (cms::determine_type()) {
  case 'index':
    $index = new index();
    $index->display();
    break;
  case 'project':
    $project = new project();
    $project->display();
    break;
  case 'note':
    $note = new note;
    $note->display();
    break;
  case 'page':
    $page = new page;
    $page->display();
    break;
  case 'archive':
    $archive = new archive;
    $archive->display();
    break;
  case "captcha":
    $captcha = new captcha;
    $captcha->display();
    break;
}

?>
