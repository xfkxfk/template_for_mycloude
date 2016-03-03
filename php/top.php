<?php
   include 'config.php';
   header ('Content-Type: application/json');
   echo '["127.0.0.1","172.16.177.130"]';
   exit();

   if (! isset ($_GET['field'])) {
     die ('invalid parameters');
   }
   $result = array ();

   $es = new ES($config['index']);
   $res = $es->top($_GET['field']);

   foreach ($res['aggregations']['grp']['buckets'] as $bucket) {
     array_push ($result, $bucket['key']);
   }

   echo json_encode ($result);
