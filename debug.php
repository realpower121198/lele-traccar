<?php
// Funktion zum Schreiben von Debug-Informationen in eine Log-Datei
function lele_debug_log($message) {
    $debug_option = get_option('lele_enable_debug'); // Überprüfen, ob Debugging aktiviert ist
    if ($debug_option == 'yes') {
        $log_file = plugin_dir_path(__FILE__) . 'debug.log';
        $current_time = date('Y-m-d H:i:s');
        file_put_contents($log_file, "[$current_time] $message\n", FILE_APPEND);
    }
}
