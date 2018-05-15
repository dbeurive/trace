<?php

/**
 * This script illustrates the use of the tracer with a recursive function.
 * The output of the script will be:
 *
 * Start the script
 * Call fib(6)
 * Calculate fib(6-1) + fib(6-2)
 *     Call fib(5)
 *     Calculate fib(5-1) + fib(5-2)
 *          Call fib(4)
 *          Calculate fib(4-1) + fib(4-2)
 *              Call fib(3)
 *              Calculate fib(3-1) + fib(3-2)
 *                  Call fib(2)
 *                  $n = (1|2) => return 1
 *                  END
 *                  Call fib(1)
 *                  $n = (1|2) => return 1
 *                  END
 *              fib(3-1) + fib(3-2) = 2
 *              END
 *              Call fib(2)
 *              $n = (1|2) => return 1
 *              END
 *          fib(4-1) + fib(4-2) = 3
 *          END
 *          Call fib(3)
 *          Calculate fib(3-1) + fib(3-2)
 *              Call fib(2)
 *              $n = (1|2) => return 1
 *              END
 *              Call fib(1)
 *              $n = (1|2) => return 1
 *              END
 *          fib(3-1) + fib(3-2) = 2
 *          END
 *     fib(5-1) + fib(5-2) = 5
 *     END
 *     Call fib(4)
 *     Calculate fib(4-1) + fib(4-2)
 *          Call fib(3)
 *          Calculate fib(3-1) + fib(3-2)
 *              Call fib(2)
 *              $n = (1|2) => return 1
 *              END
 *              Call fib(1)
 *              $n = (1|2) => return 1
 *              END
 *          fib(3-1) + fib(3-2) = 2
 *          END
 *          Call fib(2)
 *          $n = (1|2) => return 1
 *          END
 *     fib(4-1) + fib(4-2) = 3
 *     END
 * fib(6-1) + fib(6-2) = 8
 * END
 * fib(6)=8
 * End of the script
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

    $x = 6;
    $w = $TRACER->getWriter("Start the script", false);
    $r = fib($x);
    $w->write("fib($x)=$r");
    $w->write("End of the script");

} catch (\Exception $e) {
    print $e->getMessage() . "\n";
}

$dumper = new TextSimple($logContainer);
$dumper->setIndentationStep("\t");
$dumper->dump();
