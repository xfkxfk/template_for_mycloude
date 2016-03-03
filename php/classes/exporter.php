<?php
class Exporter
{
    private $supported = array ('txt' => 1, 'csv' => 1);
    public function run($type, $data)
    {
        if ($this->supported[$type]) {
          return $this->$type($data);
        } else {
          die ("Unsupported format");
        }
    }

    public function txt($array)
    {
        header('Content-Disposition: attachment; filename="export.txt"');

        foreach ($array as $arr) {
            echo implode(' ', $arr), "\n";
        }
    }

    public function cleanData($str)
    {
        if (is_array($str)) {
            return implode(' ', $str);
        }

        if (strstr($str, '"')) {
            $str = '"'.str_replace('"', '""', $str).'"';
        }

        return $str;
    }

    public function csv($array)
    {
        header('Content-Disposition: attachment; filename="export.csv"');

        $out = fopen('php://output', 'w');
        $keys = null;
        foreach ($array as $row) {
            if (!$keys) {
                fputcsv($out, ($keys = array_keys($row)), ',', '"');
            }

            $values = array();
            foreach ($keys as $key) {
              if (strcmp ($key, 'timestamp') === 0) {
                $row[$key] = strftime ('%Y-%m-%d %H:%M:%S', $row[$key] / 1000);
              }
                $values[] = $this->cleanData($row[$key]);
            }
            fputcsv($out, $values, ',', '"');
        }

        fclose($out);
    }
};
?>
