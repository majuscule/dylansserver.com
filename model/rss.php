<?php

class rss extends model {

  public $items = array();

  public function display() {
    $this->fetch_items();
    require_once("view/rss.php");
  }

  public function fetch_items() {
    $result = $this->db->query("SELECT date_posted, title, text, url
					  FROM notes ORDER BY date_posted DESC
					  LIMIT 5");
	while ($entry = $result->fetch_object()) {
	  $entry->url = "http://dylansserver.com/note/" . $entry->url;
	  $entry->text = strip_tags($entry->text);
	  $end_of_first_sentence = strpos($entry->text, '.');
	  if ($end_of_first_sentence) {
	    $end_of_second_sentence = strpos($entry->text, '.', ($end_of_first_sentence + 1));
		if ($end_of_second_sentence) {
		  $entry->description = substr($entry->text, '0', ($end_of_second_sentence + 1));
		} else {
		  $entry->description = substr($entry->text, '0', ($end_of_first_sentence + 1));
		}
        foreach ($entry as $key => $val) {
          $this->items[][$key] = $entry->$key;
        }
	  }
	}
  }
}

?>
