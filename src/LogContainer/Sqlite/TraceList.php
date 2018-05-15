<?php

namespace dbeurive\Trace\LogContainer\Sqlite;
use dbeurive\Trace\LogContainer\TracerData;
use dbeurive\Trace\LogContainer\WriterData;
use dbeurive\Trace\LogContainer\TraceData;
use dbeurive\Trace\LogContainer\InterfaceList;
use dbeurive\Trace\LogContainer\Sqlite;

class TraceList implements InterfaceList
{
    /** @var \PDOStatement */
    private $__pdoStatement;

    /**
     * TraceList constructor.
     * @param array $inInit This array contains the required data used to initialise the list.
     *        This array must contain the key below:
     *        \dbeurive\Trace\LogContainer\Sqlite::CONF_KEY_PDO_HANDLER
     * @see \dbeurive\Trace\LogContainer\Sqlite::CONF_KEY_PDO_HANDLER
     */
    public function __construct(array $inInit) {
        $this->__pdoStatement = $inInit[Sqlite::CONF_KEY_PDO_HANDLER];
    }

    /**
     * Return the next element of the list, or false is the list has no next element.
     * @return false|TraceData
     */
    public function next() {
        $row = $this->__pdoStatement->fetch(\PDO::FETCH_ASSOC);
        if (false === $row) { return false; }

        $tracerId = $row['tracer_id'];
        return new TraceData($row['trace_id'],
                             new TracerData($tracerId),
                             new WriterData($row['writer_id'], new TracerData($tracerId), $row['indent_level']),
                             $row['timestamp'],
                             $row['type'],
                             $row['message'],
                             1 == $row['is_first'],
                             1 == $row['is_last']);
    }
}