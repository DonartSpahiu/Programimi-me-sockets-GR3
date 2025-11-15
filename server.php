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
if (!$socket)
    die("Gabim në socket_create(): " . socket_strerror(socket_last_error()) . "\n");
if (!socket_bind($socket, $ip, $port))
    die("Gabim në socket_bind(): " . socket_strerror(socket_last_error($socket)) . "\n");

echo "Serveri UDP po dëgjon në $ip:$port...\n";

socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, ["sec" => 5, "usec" => 0]);
$clients = [];

// Funksionet 
function send_reply($socket, $client_key, $reply)
{
    global $clients;
    $client = $clients[$client_key];
    if (!$client['is_admin'])
        sleep(1); // vonese per kliente (jo-admin)
    socket_sendto($socket, $reply, strlen($reply), 0, $client['ip'], $client['port']);
    $clients[$client_key]['bytes_sent'] += strlen($reply);
}

function check_client_timeouts()
{
    global $clients, $client_timeout_seconds;
    $now = time();
    foreach ($clients as $key => $client) {
        if ($now - $client['last_seen'] > $client_timeout_seconds) {
            echo "Klienti $key u shkëput (timeout).\n";
            unset($clients[$key]);
        }
    }
}

function update_stats()
{
    global $clients, $stats_file;
    $total_recv = 0;
    $total_sent = 0;
    foreach ($clients as $c) {
        $total_recv += $c['bytes_recv'];
        $total_sent += $c['bytes_sent'];
    }
    $data = "----- STATISTIKA (" . date('H:i:s') . ") -----\n";
    $data .= "Klientë aktivë: " . count($clients) . "\n";
    foreach ($clients as $key => $c) {
        $admin = $c['is_admin'] ? "PO" : "JO";
        $data .= "$key | Admin: $admin | Msg: {$c['msg_count']} | Recv: {$c['bytes_recv']}B | Sent: {$c['bytes_sent']}B\n";
    }
    $data .= "Total Recv: $total_recv B, Sent: $total_sent B\n\n";
    file_put_contents($stats_file, $data);
    return $data;
}

// Funksionet për komandat
function handle_list($dir)
{
    return "Skedarët:\n" . implode("\n", array_diff(scandir($dir), ['.', '..']));
}

function handle_read($dir, $file)
{
    $path = "$dir/" . basename($file);
    return file_exists($path) ? file_get_contents($path) : "Skedari nuk ekziston.";
}

function handle_delete($dir, $file)
{
    $path = "$dir/" . basename($file);
    return (file_exists($path) && unlink($path)) ? "Skedari '$file' u fshi." : "Gabim në fshirje.";
}

function handle_search($dir, $key)
{
    $files = array_diff(scandir($dir), ['.', '..']);
    $matches = array_filter($files, fn($f) => str_contains($f, $key));
    return empty($matches) ? "Nuk u gjet asgjë për '$key'." : implode("\n", $matches);
}

//Cikli kryesor i serverit (per pranim te mesazheve)
while (true) {
    $buf = null;
    $ip = null;
    $portc = null;
    $bytes = @socket_recvfrom($socket, $buf, 4096, 0, $ip, $portc);
    if ($bytes === false) {
        check_client_timeouts();
        update_stats();
        continue;
    }

    $msg = trim($buf);
    $key = "$ip:$portc";

    echo "[$key] tha: $msg\n"; // me shtypje në server

    if (!isset($clients[$key])) {
        if (count($clients) >= $max_clients) {
            socket_sendto($socket, "Serveri është plot.", 64, 0, $ip, $portc);
            continue;
        }
        $clients[$key] = ['ip' => $ip, 'port' => $portc, 'last_seen' => time(), 'is_admin' => false, 'msg_count' => 0, 'bytes_recv' => 0, 'bytes_sent' => 0];
        echo "Klient i ri: $key\n";
        $reply = "Mirë se erdhët! Ju jeni përdorues standard.";
    } else
        $reply = "OK.";

    $clients[$key]['last_seen'] = time();
    $clients[$key]['msg_count']++;
    $clients[$key]['bytes_recv'] += $bytes;
    file_put_contents($log_file, date('H:i:s') . " [$key]: $msg\n", FILE_APPEND);

} ?>