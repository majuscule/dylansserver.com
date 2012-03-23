<?php

class archive extends model {

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

?>
