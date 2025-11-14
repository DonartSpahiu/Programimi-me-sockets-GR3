<?php

// Konfigurimi i Serverit 
$ip = '0.0.0.0';
$port = 12345;
$max_clients = 4;
$client_timeout_seconds = 60;
$admin_password = 'admin123';
$server_files_dir = 'server_files';
$stats_file = 'server_stats.txt';
$log_file = 'server_log.txt';

// Krijo folderin e serverit nëse nuk ekziston
if (!file_exists($server_files_dir)) {
    mkdir($server_files_dir, 0755, true);
    file_put_contents("$server_files_dir/shembull.txt", "Ky është një skedar shembull.");
}

// Krijimi i socket-it UDP
$socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
if (!$socket) die("Gabim në socket_create(): " . socket_strerror(socket_last_error()) . "\n");
if (!socket_bind($socket, $ip, $port)) die("Gabim në socket_bind(): " . socket_strerror(socket_last_error($socket)) . "\n");

echo "Serveri UDP po dëgjon në $ip:$port...\n";

socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, ["sec" => 5, "usec" => 0]);
$clients = [];

?>
