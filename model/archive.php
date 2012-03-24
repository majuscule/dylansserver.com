<?php

class archive extends model {

  public $notes = array();

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
      $this->fetch_notes();
      require_once("view/archive.php");
  }

  public function fetch_notes() {
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
    foreach ($result as $row => $entry) {
      $entry['url'] = '/note/' . $entry['url'];
      $date_posted = explode("-", $entry['date_posted']);
      $entry['year_posted'] = $date_posted[0];
      $entry['month_posted'] = $date_posted[1];
      $entry['datetime_posted'] = explode(' ', $date_posted[2]);
      $entry['day_posted'] = $entry['date_posted'][0];
      $this->notes[$row] = $entry;
    }
  }

}

?>
