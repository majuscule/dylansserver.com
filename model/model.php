<?php

class model {

  private $config_file = '/etc/dylansserver.ini';
  protected $model;
  protected $recaptcha_publickey;
  protected $recaptcha_privatekey;
  public $title;
  public $home_link;

  public function __construct() {
    $config = parse_ini_file($this->config_file, true);
    $this->db = new mysqli(
      $config['database']['domain'],
      $config['database']['user'],
      $config['database']['password'],
      $config['database']['database']);
    if (mysqli_connect_errno()) {
      echo "Problem connecting to database: ";
      echo mysqli_connect_error();
      exit();
    }
    $this->recaptcha_publickey = $config['recaptcha']['publickey'];
    $this->recaptcha_privatekey = $config['recaptcha']['privatekey'];
    $this->title = $config['site']['default_title'];
    $this->home_link = $config['site']['home_link'];
  }

  public function query() {
    $args = func_get_args();
    $statement = $this->db->prepare($args[0]);
    $args = array_slice($args, 1);
    call_user_func_array(array($statement, 'bind_param'), $args);
    $statement->execute();
    $return = array();
    $statement->store_result();
    $row = array();
    $data = $statement->result_metadata();
    $fields = array();
    $fields[0] = &$statement;
    while($field = $data->fetch_field()) {
      $fields[] = &$row[$field->name];
    }
    call_user_func_array("mysqli_stmt_bind_result", $fields);
    $i = 0;
    while ($statement->fetch()) {
        foreach ($row as $key=>$value) $return[$i][$key] = $value;
      $i++;
    }
    $statement->free_result();
    return $return;
  }

}

?>
