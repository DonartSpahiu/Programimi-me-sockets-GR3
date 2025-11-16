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
