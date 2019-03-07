<?php

require_once "config.php";
set_include_path(dirname(__FILE__) . PATH_SEPARATOR .
  dirname(__FILE__) . "/include" . PATH_SEPARATOR .
  get_include_path());

require_once "autoload.php";
if (!init_plugins()) return;

require_once('functions.php');
if ($_GET["user"] && $_GET["password"]){
  if (authenticate_user($_GET["user"], $_GET["password"])) {
    header('Content-Type: text/plain');
    $username=$_GET["user"];
    echo("Welcome $username \n\n");
    $args = '--feeds';
    if ($_GET["force"]) {
      if ($_GET["force"] == 1) {
        $args .= ' --force-update --force-rehash';
      }
    }
    echo('excute /usr/bin/php ' . dirname(__FILE__) . '/update.php ' . $args);
    exec('/usr/bin/php ' . dirname(__FILE__) . '/update.php ' . $args, $output, $return_code );
    if($return_code == 0) {
      foreach ($output as $key => $val){
        echo "$val \n";
      }
    }
  } else {
    header($_SERVER['SERVER_PROTOCOL'] .' 404 Not Found');
    exit(1);
  }
} else {
  header($_SERVER['SERVER_PROTOCOL'] .' 404 Not Found');
  exit(1);
}
?>
