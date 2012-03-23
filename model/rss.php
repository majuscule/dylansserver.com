<?php

class rss extends model {

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

?>
