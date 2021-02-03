<?php
//function logger($fileName) {
//    $fileHandle = fopen($fileName, 'a');
//    var_dump('1111');
//    while (true) {
//        fwrite($fileHandle, yield . "\n");
//    }
//}
//$logger = logger(__DIR__ . '/log');
//var_dump($logger);
//
//$logger->send('Foo');
//$logger->send('Bar');


function gen()
{
    var_dump('123');
    $ret = yield  'yield1';
    var_dump("var_dump1: $ret");
    $ret = yield 'yield2';
    var_dump("var_dump2: $ret");
}

$gen = gen();
var_dump($gen->send('12312'));
//var_dump($gen->valid());
//var_dump($gen->send('333'));
//var_dump($gen->valid());

//var_dump("cur1:" . $gen->current());
//$gen->next();
//var_dump("cur1:" . $gen->current());
//
//$gen->next();

//$cur1 = $gen->next();
//$cur2 = $gen->current();
//var_dump("cur2:$cur2");
//$cur3 = $gen->current();
//var_dump("cur3:$cur3");


//var_dump($gen->current());    // string(6) "yield1"
//$send1 = $gen->send('ret1');
//var_dump($send1); // string(4) "ret1"   (the first var_dump in gen)
// string(6) "yield2" (the var_dump of the ->send() return value)
//var_dump($gen->send('ret2')); // string(4) "ret2"   (again from within gen)
// NULL               (the return value of ->send())
