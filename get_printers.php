<?php
header('Content-Type: application/json');

function getWindowsPrinters() {
    $printers = [];
    
    // Windows için yazıcıları listele
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        if (function_exists('printer_list')) {
            $printers = printer_list(PRINTER_ENUM_LOCAL);
        } else {
            // Alternatif: WMI ile yazıcıları listele
            exec('wmic printer get name', $output);
            foreach ($output as $line) {
                $line = trim($line);
                if ($line && $line !== 'Name') {
                    $printers[] = $line;
                }
            }
        }
    }
    // Linux için yazıcıları listele
    else {
        exec('lpstat -a | cut -d " " -f1', $output);
        $printers = array_filter($output);
    }
    
    return array_values(array_unique($printers));
}

try {
    $printers = getWindowsPrinters();
    echo json_encode($printers);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
