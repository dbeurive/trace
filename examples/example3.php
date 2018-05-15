<?php

/**
 *   The result will be:
 *  
 *  
 *    Call {closure}
 *    message
 *    * a: 1
 *    * b: 2
 *    $v = Array
 *    (
 *        [a] => 1
 *        [b] => 2
 *    )
 *    	Call f2
 *    * k1 : 4
 *    * k20: 9
 *    	END
 *    	f3
 *    		Call f2
 *    * k1 : 7
 *    * k20: 1
 *    		END
 *    		Call f1
 *    		This is a multiline
 *    		that spans over 2 lines
 *    		Array
 *            (
 *                [0] => 1
 *    		    [1] => 2
 *    		    [2] => 3
 *    		    [3] => 4
 *    		    [4] => 5
 *    		)
 *    
 *    		* 0: 1
 *    * 1: 2
 *    * 2: 3
 *    * 3: 4
 *    * 4: 5
 *    			{closure}
 *    			This is an anonymous function call
 *    			END
 *    		End of f1
 *    		END
 *    	End of f3
 *    	END
 *    The end
 *    END
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
use dbeurive\Trace\LogContainer\InterfaceLogContainer;



$dbPath = __DIR__ . DIRECTORY_SEPARATOR . 'log.sqlite';

try {
    $logContainer = new Sqlite(array(Sqlite::CONF_KEY_DB_PATH => $dbPath, Sqlite::CONF_KEY_DB_OVERRIDE => true));
    $TRACER = new Tracer($logContainer);

    function f1(callable $inFunction) {
        global $TRACER;
        $w = $TRACER->getWriter();
        $a = array(1,2,3,4,5);
        $w->write('Call ' . __FUNCTION__);
        $w->write("This is a multiline\nthat spans over 2 lines");
        $w->write(print_r($a, true), InterfaceLogContainer::MESSAGE_TYPE_DATA);
        $w->writeList($a);
        call_user_func($inFunction);
        $w->write("End of f1");
    }

    function f2() {
        global $TRACER;
        $w = $TRACER->getWriter();
        $w->call(__FUNCTION__);
        $w->write(array('k1' => rand(0, 10), 'k20' => rand(0, 10)));
    }

    function f3() {
        $f = function() {
            global $TRACER;
            $w = $TRACER->getWriter();
            $w->write(__FUNCTION__);
            $w->write("This is an anonymous function call");
        };

        global $TRACER;
        $w = $TRACER->getWriter();
        $w->write(__FUNCTION__);
        f2();
        f1($f);
        $w->write("End of f3");
    }

    // We use an anonymous function to make sure that all writers get destroyed before we calculate the indentation.

    (function() {
        global $TRACER;
        $w = $TRACER->getWriter();
        $v = array('a' => 1, 'b' => 2);
        $w->call(__FUNCTION__);
        $w->write("message", InterfaceLogContainer::MESSAGE_TYPE_DATA);
        $w->write($v);
        $w->varDump($v, '$v');
        f2();
        f3();
        $w->write("The end");
    })();

} catch (\Exception $e) {
    print $e->getMessage() . "\n";
}


$dumper = new TextSimple($logContainer);
$dumper->setIndentationStep("\t");
$dumper->dump();
