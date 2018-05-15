# introduction

It is a common practice to insert "traces" within the code, during the development process.
This package takes care of the rendering of traces.

Traces are stored within a _LOG container_. Then they are dumped using a _dumper_. The _dumper_ takes care of the rendering.
One of the main functions of the _dumper_ is to calculate the indentation from each trace. 

> Right now, there is only one dumper : this dumper generates a simple textual representation of the flow of traces.
> However, it is possible to create dumpers for any format (HTML, Markdown...).
> A dumper is just a class that implements a given interface.

# Vocabulary

## Trace

A _trace_ is a message.

## Batch

A _batch_ ()of traces) is a set of traces that must be written using the same indentation.
Typically, all traces from a given function call are part of the same _batch_.
All traces included within a _batch_ are written by a single _writer_.

Let's consider the 4 following batches of traces ("`A`", "`B`", "`C`" and "`D`"): 

    --->root
        --->aaaaaaa
        aaaaaaaaaaa
            --->bbbbbbb
            bbbbbbbbbbb
            bbbbbbbbbbb
            --->ccccccc
            ccccccccccc
            ccccccccccc
        aaaaaaaaaaa
        --->ddddddd
        ddddddddddd
        ddddddddddd
    root

We say that:

* Batches "`A`" and "`D`" are "siblings". They are children of the "`root`" batch (which is never represented). 
* Batches "`B`" and "`C`" are "siblings". Batches "`B`" and "`C`" are children of batch "`A`". Or, which is equivalent,
  batch "`A`" is the parent of batches "`B`" and "`C`".

## LOG container

A LOG container provides data storage for a given underlying storage.

> Please note that, in theory, it is possible to implement LOG containers using any kind of data storage.
> However, due to the nature of the operations that need to be done, the data storage should provide database functionalities.

# Synopsis

Consider the following example:

    use dbeurive\Trace\LogContainer\Sqlite;
    use dbeurive\Trace\Tracer;
    
    // Create a LOG container. It is used to store data into the underlying storage.
    $dbPath = __DIR__ . DIRECTORY_SEPARATOR . 'log.sqlite';
    $logContainer = new Sqlite(array(Sqlite::CONF_KEY_DB_PATH => $dbPath, Sqlite::CONF_KEY_DB_OVERRIDE => true));
    
    // Create a tracer that uses the LOG container to store the traces.
    $TRACER = new Tracer($logContainer);

    // Write traces from a function.
    function fib($n) {
        global $TRACER;
        // Get a writer that will write traces for this function.
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

    $x = 6;
    // Get a write that will write traces for the main entry point.
    $w = $TRACER->getWriter("Start the script", false);
    $r = fib($x);
    $w->write("fib($x)=$r");
    $w->write("End of the script");

This code above will create a Sqlite database (the LOG container) that contains the traces.
Let's say that the container's file name is `log-container.sqlite`. 

Then dump the traces from the Sqlite database, using a dumper.
For example, if you use the dumper that generates a simple textual representation of the flow of traces, you get:

Command:

    php trace-dump.php sqlite:text-simple --tab "    " log-container.sqlite

Result:

    Start the script
    Call fib(6)
    Calculate fib(6-1) + fib(6-2)
        Call fib(5)
        Calculate fib(5-1) + fib(5-2)
            Call fib(4)
            Calculate fib(4-1) + fib(4-2)
                Call fib(3)
                Calculate fib(3-1) + fib(3-2)
                    Call fib(2)
                    $n = (1|2) => return 1
                    Call fib(1)
                    $n = (1|2) => return 1
                fib(3-1) + fib(3-2) = 2
                Call fib(2)
                $n = (1|2) => return 1
            fib(4-1) + fib(4-2) = 3
            Call fib(3)
            Calculate fib(3-1) + fib(3-2)
                Call fib(2)
                $n = (1|2) => return 1
                Call fib(1)
                $n = (1|2) => return 1
            fib(3-1) + fib(3-2) = 2
        fib(5-1) + fib(5-2) = 5
        Call fib(4)
        Calculate fib(4-1) + fib(4-2)
            Call fib(3)
            Calculate fib(3-1) + fib(3-2)
                Call fib(2)
                $n = (1|2) => return 1
                Call fib(1)
                $n = (1|2) => return 1
            fib(3-1) + fib(3-2) = 2
            Call fib(2)
            $n = (1|2) => return 1
        fib(4-1) + fib(4-2) = 3
    fib(6-1) + fib(6-2) = 8
    fib(6)=8
    End of the script

# API

## LOG container

Right now, there is only one LOG container available : the Sqlite LOG container.
However, it is possible to create other LOG containers. A LOG container implements the interface
`\dbeurive\Trace\LogContainer\InterfaceLogContainer`.

### Sqlite LOG container

#### Constructor

The method below creates a new Sqlite LOG container.

    public \dbeurive\Trace\LogContainer\Sqlite \dbeurive\Trace\LogContainer\Sqlite::__construct(array $inInitData)
    
The parameter `$inInitData` is an associative array that contains the following keys:

* `\dbeurive\Trace\LogContainer\Sqlite::CONF_KEY_DB_PATH`: the path to the Sqlite database.
* `\dbeurive\Trace\LogContainer\Sqlite::CONF_KEY_DB_OVERRIDE`: this flag tells the container whether it should delete the
  database or not. 

Example:

    $dbPath = __DIR__ . DIRECTORY_SEPARATOR . 'log.sqlite';
    $logContainer = new Sqlite(array(Sqlite::CONF_KEY_DB_PATH => $dbPath, Sqlite::CONF_KEY_DB_OVERRIDE => true));

## Tracer

A tracer provides writers used to write traces.
A tracer uses one (and only one) LOG container.
In a mono thread application, only one tracer should be used.
However, you can create as many tracers as you want. This may be helpful in order to organise things.

### Constructor

The method below creates a new tracer.

    public \dbeurive\trace\Tracer \dbeurive\trace\Tracer::__construct(InterfaceLogContainer $inLogContainer)
    
The parameter `$inLogContainer` contains the LOG container used to store the traces.

### Get a new writer

The method below creates a new writer and returns it.

    public \dbeurive\trace\Writer \dbeurive\trace\Tracer::getWriter($inOptMessage=null)

The parameter `$inOptMessage` represents an optional message that will be inserted into the LOG container.

The method returns a writer.

## Writer

A writer writes traces to the LOG container.
All traces written by a given writer have the same indentation level.
Typically, we create a writer per function.

### Write a trace into the LOG container

The method below writes a trace into the LOG container.

    public \dbeurive\trace\Writer::write(string|array $inMessage, string $inOptType=InterfaceLogContainer::MESSAGE_TYPE_TEXT)

The parameter `$inMessage` represents the message to write.
 
The optional `$inOptType` parameter indicates the type of the message. The type can be:
 
* \dbeurive\Trace\LogContainer\InterfaceLogContainer::MESSAGE_TYPE_TEXT: this is a simple text.
* \dbeurive\Trace\LogContainer\InterfaceLogContainer::MESSAGE_TYPE_DATA: this is a text the represents data.
  The text may be formatted in a certain way that expresses the type of the data.
* \dbeurive\Trace\LogContainer\InterfaceLogContainer::MESSAGE_TYPE_LIST: this is an associative array that represents a
  list of couples (key, value).

### Write the content of a variable 

The method below writes the equivalent of a call to the function `var_dump()`.

    public dbeurive\trace\Writer::varDump($inVar, $inOptVarName=null)

The parameter `$inVar` represents the value of the variable to dump.

The optional parameter `$inOptVarName` represents the name of the variable.
 
### Report the call to a function

The method can be used to signal the call to a given function.

    public dbeurive\trace\Writer::call($inFunctionName)

The parameter `$inFunctionName` represents the name of a function.

> Please note that this function is a shortcut to the call to:
> `\dbeurive\trace\Writer::write("Call " . $inMessage)`

### Write a list of couples key -> value

The method below writes a list of couples "key -> value":

    public dbeurive\trace\Writer::call(array $inList)

The parameter `$inList` is an associative array that contains the couples "`key -> value`" to write. 

# Analysis

The indentation level that applies to a given batch (of traces) depends on the one that applies to its parent.
If the indentation level that applies to the parent is "`N`", then the one that applies to the children is "`N+1`".

> Please note that this definition implies that the indentation level that applies to the root batch (which is never
  represented) is -1.

How to find the parent "`P`" of a given batch "`B`" ?

* Let "`First/X`" be the first trace of the batch "`X`".
* Let "`Last/X`" be the last trace of the batch "`X`".

We consider the set "`S`" of batches that start before "`B`" (relatively to the flow of batches).
All batches "`X`" within "`S`" have a first trace "`First/X`" that appears before "`First/B`" (relatively to the flow of traces).
The parent of "`B`" ("`P`") is the (only) batch included in "`S`" which last line "`Last/P`" appears after "`First/B`"
(relatively to the flow of traces).

# Implementation

## Log container

* The volume of traces may be very important. Therefore, traces should be stored on a disk.
* Finding the parent of a given batch involves a lot of comparisons between various parameters (such as the absolute
  position of a trace, relatively to the global flow of traces, or the relative position of a trace relatively to its batch).

For these reasons, the best container for storing traces is a relational database.

Various relational databases may be used. However, for most cases, a Sqlite database should provide all the necessary
functionalities, since this library should be used in development environment only.

### Sqlite implementation

The text below describes the Sqlite implementation of the LOG container.

A tracer produces batches of traces. Batches are written by writers.

Note about multithreaded scripts:

> You should create a tracer per thread. Each tracer should use its own LOG container.

    CREATE TABLE tracer (
        id INTEGER PRIMARY KEY
    );

A writer writes one batch of traces.
The same indentation level applies to all traces from a batch of traces.
Thus, the indentation level is a batch property.
    
    CREATE TABLE writer (
        id           INTEGER PRIMARY KEY,
        fk_tracer_id INT NOT NULL,
        indent       INT DEFAULT 0,
        FOREIGN KEY(fk_tracer_id) REFERENCES tracer(id)
    );
    
    CREATE INDEX fk_tracer_id_index ON writer (fk_tracer_id);
    
Traces are written one after the other.

* The table field "`id`" indicates the position of the trace within the flow of traces.
  The trace for which `id=0` is the first trace.
* The field "`is_first`" indicates whether the trace is the first of its batch or not.
* The field "`is_last`" indicates whether the trace is the last of its batch or not.
* The field "`fk_writer_id`" identifies the batch which the trace belongs to. Please keep in mind that a writer writes one batch.

    
    CREATE TABLE trace (
        id           INTEGER PRIMARY KEY,
        timestamp    DATETIME DEFAULT CURRENT_TIMESTAMP,
        fk_writer_id INT NOT NULL,
        message      TEXT NOT NULL,
        type         TEXT,
        is_first     INT DEFAULT 0,
        is_last      INT DEFAULT 0,
        FOREIGN KEY(fk_writer_id) REFERENCES writer(id)
    );
    
    CREATE INDEX fk_writer_id_index ON trace (fk_writer_id);

Identifying the parent of a given batch, identified by its id:

    SELECT   trace2.fk_writer_id AS id,
             writer.indent AS indent,
             trace2.id AS traceId
    FROM     trace AS trace1,
             trace AS trace2,
             trace AS trace3,
             writer
    WHERE    trace1.fk_writer_id = :id
      AND    trace2.fk_writer_id = trace3.fk_writer_id
      AND    trace1.is_first = 1
      AND    trace2.is_first = 1
      AND    trace3.is_last = 1
      AND    trace2.id < trace1.id
      AND    trace3.id > trace1.id
      AND    writer.id = trace2.fk_writer_id
    ORDER BY traceId DESC
    LIMIT    1

If the previous request returns no record, then it means that the parent batch is the "root" batch.

## General consideration

This code relies on the mechanism implemented within PHP 5 (and 7) to free resources.

http://php.net/manual/en/language.oop5.decon.php

> PHP 5 introduces a destructor concept similar to that of other object-oriented languages, such as C++.
> The destructor method will be called as soon as there are no other references to a particular object, or in any order
> during the shutdown sequence.



