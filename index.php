<?php
require "vendor/autoload.php";
$db = new \System\DB\Mysqli(config('db_host'),config('db_name'))
