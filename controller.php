<?php

require_once("model/model.php");

abstract class cms {

  public function __construct() {
    $this->model = new model();
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
        require_once("model/index.php");
        $index = new index();
        $index->display();
        break;
      case 'project':
        require_once("model/project.php");
        $project = new project();
        $project->display();
        break;
      case 'note':
        require_once("model/note.php");
        $note = new note();
        $note->display();
        break;
      case 'page':
        require_once("model/page.php");
        $page = new page();
        $page->display();
        break;
      case "rss":
        require_once("model/rss.php");
        $rss = new rss();
        $rss->display();
        break;
      case 'archive':
        require_once("model/archive.php");
        $archive = new archive();
        $archive->display();
        break;
      case "captcha":
        require_once("model/captcha.php");
        $captcha = new captcha();
        $captcha->display();
        break;
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
