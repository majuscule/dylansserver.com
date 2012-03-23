<?php

require_once("model/model.php");

abstract class cms {

  private $config_file = '/etc/dylansserver.ini';
  protected $model;
  protected $recaptcha_publickey;
  protected $recaptcha_privatekey;
  public $title;
  public $home_link;

  public function __construct() {
    $this->model = new model();
    $config = parse_ini_file($this->config_file, true);
    $this->db = new mysqli(
      $config['database']['domain'],
      $config['database']['user'],
      $config['database']['password'],
      $config['database']['database']);
    if (mysqli_connect_errno()) {
      echo "Problem connecting to database: ";
      echo mysqli_connect_error();
      exit();
    }
    $this->recaptcha_publickey = $config['recaptcha']['publickey'];
    $this->recaptcha_privatekey = $config['recaptcha']['privatekey'];
    $this->title = $config['site']['default_title'];
    $this->home_link = $config['site']['home_link'];
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
	} else if (isset($_GET['rss'])) {
	  return 'rss';
	} else if (isset($_GET['challenge'])) {
      return 'captcha';
    }
  }

  public function init() {
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
        require_once("model/note.php");
        $note = new note();
        require_once("view/note.php");
        break;
      case 'page':
        $page = new page;
        $page->display();
        break;
      case "rss":
        $rss = new rss();
        $rss->display();
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
  }

}


class index extends cms {

  public function display() {
      require_once("view/index.php");
  }

  protected function display_exhibits() {
    echo "<div id='exhibit'>";
    $sql = "SELECT text FROM projects ORDER BY rank";
    $result = $this->db->query($sql);
    while ($entry = $result->fetch_object()) {
      echo $entry->text;
    }
    echo "</div>";
  }

  private function list_projects() {
    $sql = "SELECT title FROM projects ORDER BY rank";
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
      require_once("view/page.php");
  }

  public function display_notes() {
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
      $text = $entry['text'];
      echo <<<END_NOTE
      <div class='note'>
      <h1>
        <span class='date'>$year_posted/$month_posted/$day_posted/</span><a rel="canonical" href='$url'>$title</a>
      </h1>
      $text
      </div>
END_NOTE;
    }
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
      require_once("view/archive.php");
  }

  public function display_notes() {
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
        echo "<h1><span class='date'>";
        echo "$year_posted/$month_posted/$day_posted/";
        echo "</span><a href='$url'>$title</a></h1>";
        echo $entry['text'];
        echo "</div>";
      }
      echo "</div>";
    } else {
      echo "<br>";
      echo "<h1>sorry, nothing here</h2>";
      echo "<pre>Empty set (0.00 sec)</pre>";
    }
  }

}


class rss extends cms {

  public function display() {
    require_once("view/rss.php");
  }

  public function display_items() {
    $result = $this->db->query("SELECT date_posted, title, text, url
					  FROM notes ORDER BY date_posted DESC
					  LIMIT 5");
	while ($entry = $result->fetch_object()) {
	  $title = $entry->title;
	  $date_posted = $entry->date_posted;
	  $url = "http://dylansserver.com/note/" . $entry->url;
	  $text = $entry->text;
	  $text = strip_tags($text);
	  $end_of_first_sentence = strpos($text, '.');
	  if ($end_of_first_sentence) {
	    $end_of_second_sentence = strpos($text, '.', ($end_of_first_sentence + 1));
		if ($end_of_second_sentence) {
		  $description = substr($text, '0', ($end_of_second_sentence + 1));
		} else {
		  $description = substr($text, '0', ($end_of_first_sentence + 1));
		}
	  }
      echo "<item>";
      echo "  <title>$title</title>";
      echo "  <link>$url</link>";
      echo "  <guid>$url</guid>";
      echo "  <description>$description</description>";
      echo "</item>";
	}
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

?>
