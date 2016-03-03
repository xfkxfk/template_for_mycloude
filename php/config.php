<?php
  session_start ();
  date_default_timezone_set('Asia/Shanghai');
  $config = array (
    'index' => 'http://127.0.0.1:9200/sniffer/logs',
  );

  function loader ($class) {
    include 'classes/' . strtolower($class) . '.php';
  }

  spl_autoload_register('loader');

  if (! isset ($_SESSION['admin']))
  {
    die (json_encode (array ('status' => -10000, 'descr' => 'login required')));
  }
?>
