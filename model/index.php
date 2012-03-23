<?php

class index extends model {

  public function display() {
      require_once("view/index.php");
  }

  public function display_exhibits() {
    echo "<div id='exhibit'>";
    $sql = "SELECT text FROM projects ORDER BY rank";
    $result = $this->db->query($sql);
    while ($entry = $result->fetch_object()) {
      echo $entry->text;
    }
    echo "</div>";
  }

  public function list_projects() {
    $sql = "SELECT title FROM projects ORDER BY rank";
    $result = $this->db->query($sql);
    while ($entry = $result->fetch_object()) {
      echo "<li><a class='tab' href='$entry->title'>$entry->title</a></li>";
    }
  }

}

?>
