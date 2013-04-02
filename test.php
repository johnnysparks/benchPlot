<?php

require_once('./benchPlot.php');

// test
$bp = new BenchPlot();
$bp->setYLabel("some ints");

$i = 10000;
while($i--) {
    $val = sin( $i / (2 * pi() * 10)) * 10;
    $bp->nextPoint( $val );
    if($i % 1000 == 0) $bp->show();
}
