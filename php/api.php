<?php
   include 'config.php';
   echo '{"descr":"ok","status":0,"data":{"data":[{"client":"127.0.0.1","timestamp":1443324895417,"db":"test","user":"root","server":"127.0.0.1","duration":0,"phrase":"SELECT * FROM user WHERE id = 1 UNION ALL SELECT CONCAT(0x716b716a71,IFNULL(CAST(column_name AS CHAR),0x20),0x776c6e616a64,IFNULL(CAST(column_type AS CHAR),0x20),0x7176716a71),NULL FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name=0x75736572 AND table_schema=0x74657374#","tags":["SQLi"]},{"client":"127.0.0.1","timestamp":1443324895372,"db":"test","user":"root","server":"127.0.0.1","duration":0,"phrase":"SELECT * FROM user WHERE id = 1 UNION ALL SELECT CONCAT(0x716b716a71,IFNULL(CAST(table_name AS CHAR),0x20),0x7176716a71),NULL FROM INFORMATION_SCHEMA.TABLES WHERE table_schema IN (0x74657374)#","tags":["SQLi"]},{"client":"127.0.0.1","timestamp":1443324891269,"db":"test","user":"root","server":"127.0.0.1","duration":0,"phrase":"SELECT * FROM user WHERE id = 1 UNION ALL SELECT CONCAT(0x716b716a71,IFNULL(CAST(table_schema AS CHAR),0x20),0x776c6e616a64,IFNULL(CAST(table_name AS CHAR),0x20),0x7176716a71),NULL FROM INFORMATION_SCHEMA.TABLES WHERE table_schema IN (0x666f72756d,0x696e666f726d6174696f6e5f736368656d61,0x6d7973716c,0x74657374,0x787878)#","tags":["SQLi"]},{"client":"127.0.0.1","timestamp":1443324891196,"db":"test","user":"root","server":"127.0.0.1","duration":0.001,"phrase":"SELECT * FROM user WHERE id = 1 UNION ALL SELECT CONCAT(0x716b716a71,IFNULL(CAST(schema_name AS CHAR),0x20),0x7176716a71),NULL FROM INFORMATION_SCHEMA.SCHEMATA#","tags":["SQLi"]},{"client":"127.0.0.1","timestamp":1443324841751,"db":"test","user":"root","server":"127.0.0.1","duration":4.958,"phrase":"SELECT * FROM user WHERE id = 1 AND (SELECT * FROM (SELECT(SLEEP(5)))vjpR)","tags":["SQLi","Slow"]},{"client":"127.0.0.1","timestamp":1443324838004,"db":"test","user":"root","server":"127.0.0.1","duration":3.699,"phrase":"SELECT * FROM user WHERE id = 1 AND (SELECT * FROM (SELECT(SLEEP(5)))vjpR)","tags":["SQLi","Slow"]},{"client":"127.0.0.1","timestamp":1443324837976,"db":"test","user":"root","server":"127.0.0.1","duration":0,"phrase":"SELECT * FROM user WHERE id = 1; SELECT BENCHMARK(5000000,MD5(0x78794677))-- ","tags":["SQLi"]},{"client":"127.0.0.1","timestamp":1443324837925,"db":"test","user":"root","server":"127.0.0.1","duration":0,"phrase":"SELECT * FROM user WHERE id = 1; SELECT SLEEP(5)-- ","tags":["SQLi"]},{"client":"127.0.0.1","timestamp":1443324836834,"db":"test","user":"root","server":"127.0.0.1","duration":0,"phrase":"SELECT * FROM user WHERE id = 1 AND (SELECT 4049 FROM(SELECT COUNT(*),CONCAT(0x716b716a71,(SELECT (CASE WHEN (4049=4049) THEN 1 ELSE 0 END)),0x7176716a71,FLOOR(RAND(0)*2))x FROM INFORMATION_SCHEMA.CHARACTER_SETS GROUP BY x)a)","tags":["SQLi"]}],"total":9}}';
   exit();

   if ($_SERVER['REQUEST_METHOD'] === 'POST') {
       $_REQUEST = json_decode(file_get_contents('php://input'), true);
   }

   $es = new ES($config['index']);
   $json = $es->lookup($_REQUEST);

   $source = array();
   if (isset ($json['hits']['hits'])) {
     foreach ($json['hits']['hits'] as $hit) {
         array_push($source, $hit['_source']);
     }
   }

   $ret = array(
      'descr' => 'ok',
      'status' => 0,
      'data' => array(
         'data' => $source,
         'total' => $json['hits']['total'],
      ),
   );

   if (isset($_REQUEST['format'])) {
       header('Content-Type: application/octstream');
       header('Content-Transfer-Encoding: binary');
       header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
       $exporter = new Exporter;
       $exporter->run (strtolower($_REQUEST['format']), $source);

   } else {
       header('Content-Type: application/json');
       echo json_encode($ret);
   }
