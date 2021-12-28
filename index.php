<?php
require 'vendor/autoload.php';
use App\MegaRegression;
use App\MegaRegression2;
use App\MegaRegression3;


echo "MegaRegression 1\n";
$mega = new MegaRegression();
$mega->run(true, false);
print_r($mega->comparePredictions());

echo "\n\nMegaRegression 2\n";
$mega2 = new MegaRegression2();
$mega2->run(true, false);
print_r($mega2->comparePredictions());


echo "\n\nMegaRegression 3\n";
$mega3 = new MegaRegression3();
$mega3->run(true, false);
print_r($mega3->comparePredictions());

