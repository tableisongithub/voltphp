#!/usr/bin/env php
<?php
$args = $argv;
array_shift($args);
switch ($args[0]) {
    case 'down':
        echo "App is entering maintenance mode...\n";
        // write maintenance = true to .env
        $env = parse_ini_file('.env');
        $env['MAINTENANCE'] = 'true';
        $envContent = '';
        foreach ($env as $key => $value) {
            $envContent .= "{$key} = {$value}\n";
        }
        file_put_contents('.env', $envContent);
        break;
    case 'up':
        echo "App is leaving maintenance mode...\n";
        // write maintenance = false to .env
        $env = parse_ini_file('.env');
        $env['MAINTENANCE'] = 'false';
        $envContent = '';
        foreach ($env as $key => $value) {
            $envContent .= "{$key} = {$value}\n";
        }
        file_put_contents('.env', $envContent);
        break;
    default:
        echo "Unknown command: {$args[0]}\n";
        break;
}
