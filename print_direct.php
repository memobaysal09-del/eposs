<?php
// print_direct.php (ENTEGRE EDİLMİŞ)
// Basit ESC/POS oluşturan ve 3 bağlantı tipini destekleyen yardımcı

function escpos_build($order, $columns = 32){
    $ESC = chr(27); $GS = chr(29);
    $buf = '';
    $buf .= $ESC.'@'; // init
    $buf .= $ESC.'!'.chr(8);
    $buf .= ($order['title'] ?? 'Fiş')."\n";
    $buf .= $ESC.'!'.chr(0);
    $buf .= str_repeat('-', $columns)."\n";
    foreach (($order['lines'] ?? []) as $l){
        $name = mb_strimwidth($l[0],0,$columns-12,'','UTF-8');
        $qty  = $l[1]; $price = number_format($l[2],2,'.','');
        $line = sprintf("%-{w}s %2dx %6s\n", $name, $qty, $price);
        $line = str_replace('{w}', $columns-12, $line);
        $buf .= $line;
    }
    $buf .= str_repeat('-', $columns)."\n";
    $buf .= sprintf("%-{w}s %10.2f\n", 'TOPLAM', ($order['total'] ?? 0));
    $buf = str_replace('{w}', $columns-10, $buf);
    if (!empty($order['footer'])) $buf .= "\n".$order['footer'].'\n';
    $buf .= "\n\n".$GS.'V'.chr(66).chr(0); // cut
    return $buf;
}

function printToEpson($order, $opts){
    $columns = $opts['columns'] ?? 32;
    $payload = escpos_build($order, $columns);

    $type = $opts['type'] ?? 'network';
    if ($type === 'network'){
        $ip = $opts['ip'] ?? '127.0.0.1';
        $port = intval($opts['port'] ?? 9100);
        $errno=0; $errstr='';
        $fp = @fsockopen($ip, $port, $errno, $errstr, 3.0);
        if (!$fp) return false;
        fwrite($fp, $payload);
        fclose($fp);
        return true;
    } elseif ($type === 'bluetooth' || $type === 'usb'){
        $dev = $opts['device'] ?? null; // COM6: veya /dev/usb/lp0
        if (!$dev) return false;
        $h = @fopen($dev, 'w');
        if (!$h) return false;
        fwrite($h, $payload);
        fclose($h);
        return true;
    }
    return false;
}
