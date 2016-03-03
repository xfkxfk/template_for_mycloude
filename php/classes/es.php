<?php
   class ES extends Request
   {
      private $index;
      public function __construct($index)
      {
         $this->index = $index;
      }

      // plain query
      public function lookup($request)
      {
         $query = trim($request['query']);
         if (empty ($query)) $query = '*';
         if (isset($request['extra'])) {
            $query .= ' AND '.$request['extra'];
         }
         if (!empty($request['db_name'])) {
            $query .= ' AND db:"'.str_replace('"', '\"', $request['db_name']).'"';
         }
         if (!empty($request['db_user'])) {
            $query .= ' AND user:"'.str_replace('"', '\"', $request['db_user']).'"';
         }
         switch (intval($request['type'])) {
            case 1:
            $query .= ' AND select AND from';
            break;
            case 2:
            $query .= ' AND insert AND into AND values';
            break;
            case 3:
            $query .= ' AND update AND set';
            break;
         }

         $data = $this->_lookup(
            $query,
            $request['from'],
            $request['size'],
            strtotime($request['start_time']) * 1000,
            strtotime($request['end_time']) * 1000
         );

         return json_decode ($data, true);
      }

      public function top($field, $count = false)
      {
         $query = array (
            "size" => 0,
            "aggs" => array (
               "grp" => array (
                  "terms" => array (
                     "field" => $field,
                     "size"  => 0
                  )
               )
            )
         );

         $data = $this->post ($this->index . '/_search', json_encode ($query));
         $data = json_decode ($data, true);

         // 只是计数
         if ($count)
         {
            return count ($data['aggregations']['grp']['buckets']) +
            $data['aggregations']['grp']['sum_other_doc_count'];
         }

         return $data;
      }

      public function count($query)
      {
         $data = $this->_lookup($query, 0, 0, 0, time() * 1000, 'count');
         return json_decode ($data, true);
      }

      public function _lookup($query, $from, $size, $start_time, $end_time, $qtype = 'search')
      {
         $query = sprintf(
            '%s AND timestamp:[%d %d]',
            $query, $start_time, $end_time);
            $esurl = sprintf(
               '%s/_%s?q=%s&from=%d&size=%d&sort=timestamp:desc',
               $this->index,
               $qtype,
               urlencode($query),
               $from, $size
            );

            // echo $esurl;

            return $this->get($esurl);
         }
      }；

   ?>
