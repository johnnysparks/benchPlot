<?php

require_once('./CLITimePlot.php');

// test
$ctp = new CLITimePlot();
$ctp->setYLabel("some ints");

$i = 1000;
while($i--) { usleep(1000); $ctp->nextPoint( 1000 ); }
$ctp->show();

$i = 1000;
while($i--) { usleep(1000); $ctp->nextPoint( -1000 ); }
$ctp->show();

$i = 1000;
while($i--) { usleep(1000); $ctp->nextPoint( 1000 ); }
$ctp->show();

$i = 1000;
while($i--) { usleep(1000); $ctp->nextPoint( -1000 ); }
$ctp->show();
