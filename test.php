<?php

require_once('./CLITimePlot.php');

// test
$ctp = new CLITimePlot();
$ctp->setYLabel("some ints");

for($i = 1000;$i;$i--){ usleep(1000); $ctp->nextPoint( $i ); }
$ctp->show();

for($i = 0;$i<1000;$i++){ usleep(1000); $ctp->nextPoint( $i ); }
$ctp->show();

for($i = 10000;$i;$i-= 5){ usleep(1000); $ctp->nextPoint( $i ); }
$ctp->show();

for($i = 0;$i< 10000;$i+=5) {usleep(1000); $ctp->nextPoint( $i ); }
$ctp->show();
