<?php

class SystemInfo
{
    public function linux()
    {
        // uptime
        $uptime = explode ('.', @file_get_contents("/proc/uptime"));
        $uptime = intval($uptime[0] / 86400);

        // disk
        $disk = intval(exec('df -h . | tail -1 | awk \'{print $5}\''));

        // cpu
        $cpu = intval(1 + exec('awk \'/cpu /{printf "%.2f",($2+$4)*100/($2+$4+$5)}\' /proc/stat'));

        // mem
        $mem = intval(exec('free | awk \'{ if (/Mem/) { printf "%d", $3 * 100 / $2 } }\''));

        // rulesversion

        $rules = json_decode (@file_get_contents (dirname (__FILE__) . '/../../data/rules.json'), true);

        return array(
          'version' => '1.0.0',
          'rulesversion' => $rules['version'],
          'disk_percent' => $disk,
          'cpu_percent' => $cpu,
          'mem_percent' => $mem,
          'uptime' => $uptime
        );
    }
};
