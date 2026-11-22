<?php

if (! defined('BASEPATH')) {
    exit('No direct script access allowed');
}

$config['maintenance_mode'] = false;
$config['maintenance_ips'] = ['0.0.0.0', '1.1.1.1']; // add ip to get ip visit https://www.whatismyip.com/
