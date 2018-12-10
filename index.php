<?php
require "vendor/autoload.php";
$categories = new \Apps\Models\Category;
$categories->select('name, slug');
dump($categories);
