<?php

class index extends model {

  public $exhibits = array();
  public $projects = array();

  public function __construct() {
      parent::__construct();
      $this->fetch_exhibits();
      $this->fetch_projects();
  }

  public function display() {
      require_once("view/index.php");
  }

  private function fetch_exhibits() {
    $sql = "SELECT text FROM projects ORDER BY rank";
    $result = $this->db->query($sql);
    while ($entry = $result->fetch_object()) {
      $this->exhibits[] = $entry->text;
    }
  }

  private function fetch_projects() {
    $sql = "SELECT title FROM projects ORDER BY rank";
    $result = $this->db->query($sql);
    while ($entry = $result->fetch_object()) {
        $this->projects[] = $entry->title;
    }
  }

}

?>
