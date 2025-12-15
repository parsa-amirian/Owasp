<?php
$config = json_decode(file_get_contents(__DIR__ . '/config.json'), true);
$Ip = $config['ip'] ?? "192.168.1.103";