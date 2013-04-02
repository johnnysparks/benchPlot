<?php

class BenchPlot {
    private $rows;
    private $cols;

    private $plotChars = array(
        'vert' => '|',
        'hori' => '_',
        'bar'  => "▍",
        'xy'   => 'x',
        'label'=> 'L'
    );

    private $plotColors = array(
        'vert'  => 'black',
        'hori'  => 'black',
        'mark'  => 'red',
        'bar'   => 'green',
        'xy'    => 'green',
        'label' => 'blue',
    );

    private $xLabel;
    private $yLabel;

    private $_minX = PHP_INT_MAX;
    private $_minY = PHP_INT_MAX;

    private $_maxX;
    private $_maxY;

    private $types = array('realTime');
    private $type  = 'realTime';

    private $graph;

    private $startTime;
    private $renderTime = 0;

    private $data    = array();
    private $norm    = array();

    private $isPrinted = false;

    private $colors = array(
        'black'         => '30',
        'blue'          => '34',
        'green'         => '32',
        'cyan'          => '36',
        'red'           => '31',
        'purple'        => '35',
        'brown'         => '33',
        'yellow'        => '33',
        'white'         => '37'
    );

    public function __construct( $rows = 30, $cols = 100, $type = 'realTime'){
        $this->rows = ($rows >= 10 && $rows <= 100)  ? $rows : 30;
        $this->cols = ($cols >= 20 && $cols <= 1000) ? $cols : 100;

        $this->maxX(-1 * $this->_minX);
        $this->maxY(-1 * $this->_minY);

        $this->type = in_array($type, $this->types) ? $type : 'realTime';
        $this->setXLabel("Time (seconds)");
    }

    public function setYLabel( $label ){
        if( strlen($label) > $this->rows ){
            $this->yLabel = substr($label, 0, $this->rows);
        } else {
            $this->yLabel = str_pad($label, $this->rows, ' ', STR_PAD_BOTH);
        }
    }

    public function setXLabel( $label ){
        if( strlen($label) > $this->cols ){
            $this->xLabel = substr($label, 0, $this->cols);
        } else {
            $this->xLabel = str_pad($label, $this->cols, ' ', STR_PAD_BOTH);
        }
    }

    public function c( $s, $color ){
        return "\033[" . $this->colors[ $color ] . "m" . $s . "\033[0m";
    }


    public function nextPoint( $y ){
        if(!isset($this->startTime)) $this->startTime = microtime(1);
        $x = microtime(1) - $this->startTime - $this->renderTime;
        $this->addPoint( $x, $y );
    }

    public function addPoint( $x, $y ){
        if($x > $this->maxX()) $this->maxX($x);
        if($y > $this->maxY()) $this->maxY($y);
        if($x < $this->minX()) $this->minX($x);
        if($y < $this->minY()) $this->minY($y);

        $this->data[] = array($x, $y);
    }

    public function printData(){
        echo $this->c(" Y   X \n", 'red');
        foreach( $this->data as $null=>$v){
            echo $this->c( $v[1].', '.$v[0]."\n", 'blue' );
        }
    }

    public function maxX( $set = false ){
        if($set !== false)   $this -> _maxX = $set;
        if($this->_maxX <  0) return $this->_maxX * 0.9;
        if($this->_maxX >= 0) return $this->_maxX * 1.1;
    }

    public function maxY( $set = false ){
        if($set !== false)   $this -> _maxY = $set;
        if($this->_maxY <  0) return $this->_maxY * 0.9;
        if($this->_maxY >= 0) return $this->_maxY * 1.1;
    }

    public function minX( $set = false ){
        if($set !== false)   $this -> _minX = $set;
        if($this->_minX >  0) return $this->_minX * 0.9;
        if($this->_minX <= 0) return $this->_minX * 1.1;
    }

    public function minY( $set = false ){
        if($set !== false)   $this -> _minY = $set;
        if($this->_minY >  0) return $this->_minY * 0.9;
        if($this->_minY <= 0) return $this->_minY * 1.1;
    }

    public function xRange(){
        return $this->maxX() - $this->minX();
    }


    public function yRange(){
        return $this->maxY() - $this->minY();
    }

    public function x2bin($x){
        return (($x - $this->minX()) / $this->xRange()) * $this->width;
    }

    public function bin2x($bin){
        return (($bin / $this->width) * $this->xRange()) + $this->minX();
    }

    public function y2norm($y){
        return (($y - $this->minY()) / $this->yRange()) * $this->height;
    }

    public function norm2y($norm){
        return (($norm / $this->height) * $this->yRange()) + $this->minY();
    }

    public function normalize(){
        $datawidth    = count($this->data);
        $this->height = $this->rows - 2;
        $this->width  = $this->cols - 2;

        if( $datawidth < $this->width ){
            $this->width = $datawidth + 1;
        }

        $bins  = array();

        foreach( $this->data as $point => $xy){
            $x = $xy[0];
            $bin = round( $this->x2bin( $x ) );
            if($bin == $this->width) $bin = $bin - 1; // fill last bin with maxX value
            $bins[$bin] = isset($bins[$bin]) ? $bins[$bin] : array();
            $bins[$bin][] = $xy;
        }

        // fill up empty bins
        $binLen = max(array_keys($bins));
        while($binLen--){
            if(!isset($bins[$binLen])){
                $bins[$binLen] = array(array($this->bin2x( $binLen ), $this->minY() ));
            }
        }

        // reset the normalized data array
        $this->norm = array();
        foreach( $bins as $bindex => $bin ){
            $xs = array();
            $ys = array();
            foreach($bin as $xy){
                $xs[] = $xy[0];
                $ys[] = $xy[1];
            }
            $x = array_sum( $xs ) / count( $xs );
            $y = array_sum( $ys ) / count( $ys );

            $this->norm[] = array($x, $y);
        }

        // now normalize the Y values
        foreach( $this->norm as $point => $xy){
            $ny = $this->y2norm( $xy[1] );
            $this->norm[$point] = array($xy[0], $ny);
        }
    }

    public function buildGraph(){
        $this->resetGraph();
        $col   = 0;
        $row   = 0;
        $xyc   = $this->c($this->plotChars['bar'], $this->plotColors['bar']);

        // add the Y label and Y axis
        $this->fillDown(  $this->rows - 1, 0, $this->colorEach( $this->yLabel, $this->plotColors['label'] ));
        $this->fillRight( 0,               0, $this->colorEach( $this->xLabel, $this->plotColors['label'] ));

        $this->fillDown(  $this->rows - 1, 1, $this->getYAxis());
        $this->fillRight( 1,               1, $this->getXAxis());

        for($col=0; $col < $this->width; $col++){
            if(isset($this->norm[$col][0])){
                $x = $this->norm[$col][0];
                $y = $this->norm[$col][1];
                if((int)$y > 0){
                    $this->fillUp(2, $col+2, array_fill(0, $y, $xyc));
                }
            }
        }
    }

    public function resetGraph(){
        // prefill the plot
        $this->graph = array();
        $this->graph = array_fill(0, $this->cols, array());
        foreach( $this->graph as $row => $v ){
            $this->graph[$row] = array_fill(0, $this->rows, ' ');
        }
    }

    public function colorEach( $arr, $color ){
        if(is_string( $arr )) $arr = str_split( $arr );
        $out = array();
        foreach($arr as $char){
            $out[] = $this->c( $char, $color );
        }
        return $out;
    }

    public function fillRight($rowStart, $colStart, $charArr){
        for($offset = 0; $offset < count($charArr); $offset++){
            $this->graph[$colStart+$offset][$rowStart] = $charArr[$offset];
        }
    }

    public function fillDown($rowStart, $colStart, $charArr){
        for($offset = 0; $offset < count($charArr); $offset++){
            $this->graph[$colStart][$rowStart-$offset] = $charArr[$offset];
        }
    }

    public function fillUp($rowStart, $colStart, $charArr){
        for($offset = 0; $offset < count($charArr); $offset++){
            $this->graph[$colStart][$rowStart+$offset] = $charArr[$offset];
        }
    }

    public function getXAxis(){
        $fill = $this->c($this->plotChars['hori'], $this->plotColors['hori']);
        $markSize = 8;
        $col      = 0;
        $marks    = array();

        while($col < $this->cols - 2){
            if(isset($this->norm[$col])){
                $val    = $this->metricify( $this->norm[$col][0] );
                $mark   = $this->colorEach($val, $this->plotColors['mark']);
                $formed = array_merge( $mark, array_fill(count($mark), $markSize - count($mark), $fill));
                $marks  = array_merge( $marks, $formed);
            }
            $col += $markSize;
        }
        return array_slice($marks, 0, $this->width);
    }

    public function getYAxis(){
        $fill     = $this->c($this->plotChars['vert'], $this->plotColors['vert']);
        $markSize = 5;
        $row      = $this->height;
        $marks    = array();

        while($row > 0){
            $realY  = $this->norm2y( $row );
            $val    = $this->metricify( $realY );
            $mark   = $this->colorEach($val, $this->plotColors['mark']);
            $formed = array_merge( $mark, array_fill(count($mark), $markSize - count($mark), $fill));
            $marks  = array_merge( $marks, $formed);
            $row   -= $markSize;
        }
        return array_slice($marks, 0, $this->height);
    }

    public function metricify( $n ){
        $out = '';
        if($n < 0){
            $out = '-';
            $n = -1 * $n;
        }
        if($n >= 1000000000){
            $n = round($n / 1000000000);
            $out   .= $n."G";
        } elseif($n >= 1000000){
            $n = round($n / 1000000);
            $out   .= $n."M";
        } elseif($n >= 1000){
            $n = round($n / 1000);
            $out   .= $n."K";
        } elseif($n <= .000001){
            $n = round($n * 1000000000);
            $out   .= $n."n";
        } elseif($n <= .001){
            $n = round($n * 1000000);
            $out   .= $n."u";
        } elseif($n <= .1){
            $n = round($n * 1000);
            $out   .= $n."m";
        } else {
            $out .= round($n);
        }

        return (string)$out;
    }

    public function show(){
        $lastRenderTimeStart = microtime(1);

        $this->normalize();
        $this->buildGraph();
        if($this->isPrinted) $this->resetOutput();

        $row = $this -> rows;
        while($row--){
            for($col = 0; $col < $this->cols; $col++){
                echo $this->graph[$col][$row];
            }
            echo "\n";
        }
        $this->isPrinted = true;
        $this->renderTime += microtime(1) - $lastRenderTimeStart;
    }

    public function resetOutput(){
        echo chr(27) ."[0G"; // first col
        echo chr(27) ."[".$this->rows."A"; // first row
    }
}
