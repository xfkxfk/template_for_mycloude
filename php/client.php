<?php
  include 'config.php';
  $_REQUEST = json_decode (file_get_contents ('php://input'), true);

  $keys = array (
    'monitor_db',
    'monitor_user',
    'log_normal',
    'log_intrusion',
    'log_slowquery',
    'log_alluser',
    'log_alldb',
    'sqli'
  );
  foreach ($keys as $key)
  {
    if (! isset ($_REQUEST[$key]))
    {
      $data[$key] = '';
    }
    $data[$key] = $_REQUEST[$key];
  }

  $ret = array ('status' => 0, 'descr' => '保存成功');
  if (! @file_put_contents ('../data/config.json', json_encode ($data)))
  {
     $ret['status'] = -1;
     $ret['descr']  = '无法保存配置，请检查 data/config.json 是否可以写';
  }
  echo json_encode ($ret);
?>
