<?php

namespace dbeurive\Trace\LogContainer\Sqlite;
use dbeurive\Trace\LogContainer\TracerData;
use dbeurive\Trace\LogContainer\WriterData;
use dbeurive\Trace\LogContainer\InterfaceList;
use dbeurive\Trace\LogContainer\Sqlite;


class WriterList implements InterfaceList
{
    /** @var \PDOStatement */
    private $__pdoStatement;

    /**
     * WriterList constructor.
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
     * @return false|WriterData
     */
    public function next() {
        $row = $this->__pdoStatement->fetch(\PDO::FETCH_ASSOC);
        if (false === $row) {
            return false;
        }
        return new WriterData($row['id'], new TracerData($row['fk_tracer_id']), $row['indent']);
    }
}