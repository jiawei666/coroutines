<?php
function xrange($start, $end, $step = 1) {
    yield 1 + 1;
    yield 2;
    yield 3;
}
//foreach (xrange(1, 10) as $num) {
//    echo $num, "\n";
//}

$xrange = xrange(1, 10);

var_dump($xrange);
var_dump($xrange instanceof  Iterator);

foreach ($xrange as $value) {
    var_dump($value);
}