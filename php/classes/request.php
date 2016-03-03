<?php
   class Request
   {
      public function get($url)
      {
         $ch = curl_init();
         curl_setopt($ch, CURLOPT_URL, $url);
         curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
         $data = curl_exec($ch);
         curl_close($ch);

         return $data;
      }

      public function post($url, $data)
      {
         $ch = curl_init();
         curl_setopt($ch, CURLOPT_URL, $url);
         curl_setopt($ch, CURLOPT_POST, true);
         curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
         curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
         $data = curl_exec($ch);
         curl_close($ch);

         return $data;
      }
   };
