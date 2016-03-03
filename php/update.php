<?php
  $ret = array (
    'status' => 0,
    'description' => 'ok'
  );
  $data = file_get_contents ('php://input');
  $json = json_decode ($data, true);
  if ($json != null) {
    if (! @file_put_contents ('../data/rules.json', $data)) {
      $ret = array (
      'status' => -1,
      'description' => '无法写入文件，请检查 data/rules.json 是否可以写入'
      );
    }
  }

  echo json_encode ($ret);
?>
