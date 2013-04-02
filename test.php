<?php

require_once('./CLITimePlot.php');

// test
$ctp = new CLITimePlot();
$ctp->setYLabel("some ints");

$i = 10000;
while($i--) {
    $val = sin( $i / (2 * pi() * 10)) * 10;
    $ctp->nextPoint( $val );
    if($i % 1000 == 0) $ctp->show();
}
