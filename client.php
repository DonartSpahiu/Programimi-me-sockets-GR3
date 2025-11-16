<?php
$server_ip = '127.0.0.1';
$server_port = 12345;

$socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, ["sec" => 5, "usec" => 0]);

echo "Lidhja me serverin $server_ip:$server_port...\n";
echo "Shkruani 'exit' për dalje.\n";

function send_and_receive($socket, $msg, $ip, $port) {
    socket_sendto($socket, $msg, strlen($msg), 0, $ip, $port);
    $buf = null; $from = null; $fp = null;
    $bytes = @socket_recvfrom($socket, $buf, 8192, 0, $from, $fp);
    if ($bytes === false) echo "Asnjë përgjigje.\n";
    else echo "\n------------ Përgjigje ------------\n$buf\n-----------------------------------\n";
}

echo "Dëshironi të hyni si ADMIN? (po/jo): ";
$pergjigje = strtolower(trim(fgets(STDIN))); 

if ($pergjigje === 'po') {
    echo "Shkruani fjalëkalimin: ";
    $pw = trim(fgets(STDIN));
    send_and_receive($socket, "LOGIN $pw", $server_ip, $server_port);
} else {
    send_and_receive($socket, "PERSHENDETJE", $server_ip, $server_port);
}

while (true) {
    $msg = readline("Jep komanden ose mesazhin: ");
    if ($msg === 'exit') break;

    if (str_starts_with($msg, "/upload ")) {
        [$cmd, $filename] = explode(" ", $msg, 2);
        if (!file_exists($filename)) { echo "Skedari nuk ekziston.\n"; continue; }
        $encoded = base64_encode(file_get_contents($filename));
        $msg = "/upload " . basename($filename) . " " . $encoded;
    }
    elseif (str_starts_with($msg, "/download ")) {
        socket_sendto($socket, $msg, strlen($msg), 0, $server_ip, $server_port);
        $buf = null; $from = null; $fp = null;
        $bytes = @socket_recvfrom($socket, $buf, 65535, 0, $from, $fp);
        if ($bytes === false) { echo "Asnjë përgjigje.\n"; continue; }
        $filename = substr($msg, 10);
        $decoded = base64_decode($buf);
        file_put_contents("downloaded_$filename", $decoded);
        echo "Skedari u shkarkua si 'downloaded_$filename'.\n";
        continue;
    }

    send_and_receive($socket, $msg, $server_ip, $server_port);
}

socket_close($socket);
echo "Lidhja u mbyll.\n";
?>