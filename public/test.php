<?php

require __DIR__.'/../vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Facade as Facade;
use Illuminate\Support\Facades\DB;

$a = 123;
$b = 346;
$c = $a + $b;
echo $c;
