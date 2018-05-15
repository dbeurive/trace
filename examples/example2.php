<?php

/**
 * This script illustrates the use of the tracer with a recursive function called within an anonymous function.
 *
 * Please note that all the data recorded by the tracer is stored within the SQLITE database "log.sqlite".
 * To dump the content of the SQLITE log container, you can run the command below:
 *
 * php ../bin/trace-dump.php sqlite:text-simple --tab "    " log.sqlite
 */

require_once __DIR__ . '/../vendor/autoload.php';
use dbeurive\Trace\Tracer;
use dbeurive\Trace\LogContainer\Sqlite;
use dbeurive\Trace\Dumper\TextSimple;

/**
 * @param $n
 * @return int|null
 * @throws \dbeurive\trace\TracerException
 */

function fib($n) {
    global $TRACER;
    $w = $TRACER->getWriter(__FUNCTION__ . "($n)");
    if ($n < 0) {
        $w->write('$n < 0 => return NULL');
        return NULL;
    } elseif ($n === 0) {
        $w->write('$n = 0 => return 0');
        return 0;
    } elseif ($n === 1 || $n === 2) {
        $w->write('$n = (1|2) => return 1');
        return 1;
    } else {
        $w->write("Calculate fib($n-1) + fib($n-2)");
        $r = fib($n-1) + fib($n-2);
        $w->write("fib($n-1) + fib($n-2) = $r");
        return $r;
    }
}

$dbPath = __DIR__ . DIRECTORY_SEPARATOR . 'log.sqlite';

try {
    $logContainer = new Sqlite(array(Sqlite::CONF_KEY_DB_PATH => $dbPath, Sqlite::CONF_KEY_DB_OVERRIDE => true));
    $TRACER = new Tracer($logContainer);

    (function() {
        global $TRACER;
        $x = 6;
        $w = $TRACER->getWriter("Call " . __FUNCTION__);
        $r = fib($x);
        $w->write("fib($x)=$r");
    })();

} catch (\Exception $e) {
    print $e->getMessage() . "\n";
}


$dumper = new TextSimple($logContainer);
$dumper->setIndentationStep("\t");
$dumper->dump();
