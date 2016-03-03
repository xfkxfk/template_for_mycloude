<?php
   include 'config.php';
   header ('Content-Type: application/json');

   $si = new SystemInfo();
   $data = $si->linux();
   //echo json_encode($data);
   $a = '{"version":"1.0.0","rulesversion":201509260930,"disk_percent":37,"cpu_percent":2,"mem_percent":92,"uptime":117,"sql_count":71,"db_count":1,"server_count":2,"recent_events":[{"client":"cm9vdAo=.xfkxfk.com","timestamp":1443324895417,"db":"101.200.73.168","user":"root","server":"cm9vdAo=.xfkxfk.com","duration":0,"phrase":"SELECT * FROM user #","tags":["SQLi"]},{"client":"cm9vdAo=.xfkxfk.com","timestamp":1443324895372,"db":"101.200.73.168","user":"root","server":"cm9vdAo=.xfkxfk.com","duration":0,"phrase":"SELECT * FROM user WHERE id = 1 UNION ALL SELECT CONCAT(0x716b716a71,IFNULL(CAST(table_name AS CHAR),0x20),0x7176716a71),NULL FROM INFORMATION_SCHEMA.TABLES WHERE table_schema IN (0x74657374)#","tags":["SQLi"]},{"client":"cm9vdAo=.xfkxfk.com","timestamp":1443324891269,"db":"101.200.73.168","user":"root","server":"cm9vdAo=.xfkxfk.com","duration":0,"phrase":"SELECT * FROM user WHERE id = 1 UNION ALL SELECT CONCAT(0x716b716a71,IFNULL(CAST(table_schema AS CHAR),0x20),0x776c6e616a64,IFNULL(CAST(table_name AS CHAR),0x20),0x7176716a71),NULL FROM INFORMATION_SCHEMA.TABLES WHERE table_schema IN (0x666f72756d,0x696e666f726d6174696f6e5f736368656d61,0x6d7973716c,0x74657374,0x787878)#","tags":["SQLi"]},{"client":"cm9vdAo=.xfkxfk.com","timestamp":1443324891196,"db":"101.200.73.168","user":"root","server":"cm9vdAo=.xfkxfk.com","duration":0.001,"phrase":"SELECT * FROM user WHERE id = 1 UNION ALL SELECT CONCAT(0x716b716a71,IFNULL(CAST(schema_name AS CHAR),0x20),0x7176716a71),NULL FROM INFORMATION_SCHEMA.SCHEMATA#","tags":["SQLi"]},{"client":"cm9vdAo=.xfkxfk.com","timestamp":1443324841751,"db":"101.200.73.168","user":"root","server":"cm9vdAo=.xfkxfk.com","duration":4.958,"phrase":"SELECT * FROM user WHERE id = 1 AND (SELECT * FROM (SELECT(SLEEP(5)))vjpR)","tags":["SQLi","Slow"]},{"client":"cm9vdAo=.xfkxfk.com","timestamp":1443324838004,"db":"101.200.73.168","user":"root","server":"cm9vdAo=.xfkxfk.com","duration":3.699,"phrase":"SELECT * FROM user WHERE id = 1 AND (SELECT * FROM (SELECT(SLEEP(5)))vjpR)","tags":["SQLi","Slow"]},{"client":"cm9vdAo=.xfkxfk.com","timestamp":1443324837976,"db":"101.200.73.168","user":"root","server":"cm9vdAo=.xfkxfk.com","duration":0,"phrase":"SELECT * FROM user WHERE id = 1; SELECT BENCHMARK(5000000,MD5(0x78794677))-- ","tags":["SQLi"]},{"client":"cm9vdAo=.xfkxfk.com","timestamp":1443324837925,"db":"101.200.73.168","user":"root","server":"cm9vdAo=.xfkxfk.com","duration":0,"phrase":"SELECT * FROM user WHERE id = 1; SELECT SLEEP(5)-- ","tags":["SQLi"]},{"client":"cm9vdAo=.xfkxfk.com","timestamp":1443324836834,"db":"101.200.73.168","user":"root","server":"cm9vdAo=.xfkxfk.com","duration":0,"phrase":"SELECT * FROM user WHERE id = 1 AND (SELECT a)","tags":["SQLi"]},{"server":"172.16.177.130","client":"172.16.177.1","phrase":"SELECT id","timestamp":1443189390000,"duration":0.022,"db":"101.200.73.168","user":"root","tags":["SQLi"]}]}';
   echo $a;
   exit();

   // 可优化，暂时不做
   $es = new ES($config['index']);
   $count = $es->count('*');
   $data['sql_count'] = isset ($count['count']) ? $count['count'] : 0;
   if ($data['sql_count'] > 1000) {
     $data['sql_count'] = sprintf ("%.1fK", $data['sql_count'] / 1000);
   }

   $db_count = $es->top('db', true);
   $data['db_count'] = $db_count;

   $server_count = $es->top('server', true);
   $data['server_count'] = $server_count;

   $recent_events = $es->_lookup('(tags:"SQLi" OR tags:"Slow")', 0, 10, 0, time() * 1000);

  //  $recent_events = $es->_lookup('*', 0, 10, (time() - 24 * 7 * 3600) * 1000, time() * 1000);
   $json = json_decode ($recent_events, true);
   $source = array();
   if (isset ($json['hits']['hits'])) {
     foreach ($json['hits']['hits'] as $hit) {
         array_push($source, $hit['_source']);
     }
   }
   $data['recent_events'] = $source;

   echo json_encode ($data);
