<?php

require_once("model/model.php");

abstract class cms {

  public function __construct() {
    $this->model = new model();
    ob_start();
  }

  public static function init() {
    if (isset($_GET['page']) && is_numeric($_GET['page'])) {
      require_once("model/page.php");
      $page = new page();
      $page->display();
    } else if (isset($_GET['year'])) {
      require_once("model/archive.php");
      $archive = new archive();
      $archive->display();
    } else if (isset($_GET['note'])) {
      require_once("model/note.php");
      $note = new note();
      $note->display();
    } else if ($_SERVER['REQUEST_URI'] == '/') {
      require_once("model/index.php");
      $index = new index();
      $index->display();
    } else if (isset($_GET['project'])) {
      require_once("model/project.php");
      $project = new project();
      $project->display();
	} else if (isset($_GET['rss'])) {
      require_once("model/rss.php");
      $rss = new rss();
      $rss->display();
	} else if (isset($_GET['challenge'])) {
      require_once("model/captcha.php");
      $captcha = new captcha();
      $captcha->display();
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


?>
