<?php

require_once("model/index.php");

class project extends index {

  public function display_exhibits() {
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

?>
