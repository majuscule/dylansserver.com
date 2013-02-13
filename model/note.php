<?php

class note extends model {

  public $id;
  public $comments_enabled = false;
  public $failed_captcha;
  public $url;
  public $title;
  public $year_posted;
  public $month_posted;
  public $day_posted;
  public $text;
  public $number_of_comments;
  public $comments;

  public function __construct() {
    parent::__construct();
    if (isset($_GET['comments'])) {
      $this->comments_enabled = true;
    }
    $url = htmlspecialchars($_SERVER['REQUEST_URI']);
    if (isset($_GET['verify'])) {
      $url = substr($url, 0, (strlen($url)-6));
    }
    $this->url = $url;
    $this->fetch_note();
    $this->fetch_comments();
  }

  public function fetch_note() {
    $sql = "SELECT title, date_posted, text, id
              FROM notes WHERE url = ?";
    $result = $this->query($sql, "s",
                              $_GET['note']);
    if ($result) {
      $entry = $result[0];
      $this->id = $entry["id"];
      $this->title = $entry["title"];
      $date_posted =  explode("-", $entry["date_posted"]);
      $this->year_posted = $date_posted[0];
      $this->month_posted = $date_posted[1];
      $datetime_posted = explode(' ', $date_posted[2]);
      $this->day_posted = $datetime_posted[0];
      $this->text = $entry["text"];
    } else {
      throw new notFound();
    }
  }

  public function fetch_comments() {
    $sql = "SELECT COUNT(*) FROM comments
              WHERE note = $this->id";
    $result = $this->db->query($sql);
    $result = $result->fetch_array();
    $this->number_of_comments = $result[0];
    if (isset($_GET['verify'])) {
      $this->verify();
    }
  }

  public function display() {
      require_once("view/note.php");
  }

  public function display_comment_link() {
    if ($this->number_of_comments > 0) {
      $anchor_text = "comments($this->number_of_comments)/";
    } else {
      $anchor_text = "comment?";
    }
    if (substr($this->url, (strlen($this->url)-1), strlen($this->url)) == '/') {
      $url = $this->url . 'comments/';
    } else {
      $url = $this->url . '/comments/';
    }
    echo "<a id='comment_link' href='$url'>$anchor_text</a>";
  }

  public function display_comments() {
    $sql= "SELECT date_posted, author, text
             FROM comments WHERE note = ?
             ORDER BY date_posted DESC";
    $result = $this->query($sql, 'd', $this->id);
    $i = 0;
    foreach ($result as $row => $entry) {
      $this->comment[$i]['date_posted'] = $entry['date_posted'];
      $this->comment[$i]['author']  = htmlspecialchars($entry['author']);
      $this->comment[$i]['text'] = htmlspecialchars($entry['text']);
      $i++;
    }
    require_once('view/comment.php');
  }

  public function display_comment_form() {
    $publickey = $this->recaptcha_publickey;
    require_once("view/comment-form.php");
  }

  public function verify() {
    if (!isset($_POST['captcha'])) {
      require_once('includes/recaptchalib.php');
      echo "<br>";
      $resp = recaptcha_check_answer ($this->recaptcha_privatekey,
                                      $_SERVER["REMOTE_ADDR"],
                                      $_POST["recaptcha_challenge_field"],
                                      $_POST["recaptcha_response_field"]);
      if (!$resp->is_valid) {
        $this->failed_captcha = true;
      }
    }
    if (isset($_POST['captcha']) || $resp->is_valid) {
      $sql = ("INSERT INTO comments (date_posted, author,
                  text, note)
                VALUES(NOW(), ?, ?, ?)");
      $stmt = $this->db->prepare($sql);
      // Checks are needed here (no blank text,
      // and a default author needs to be set
      // for no-javascript users.
      $stmt->bind_param('sss',
                          $_POST['name'],
                          $_POST['text'],
                        $this->id);
      $stmt->execute();
    }
  }

}

?>
