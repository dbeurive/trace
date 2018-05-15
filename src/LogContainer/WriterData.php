<?php

namespace dbeurive\Trace\LogContainer;

/**
 * Class WriterData
 *
 * This class defines the data that represents a writer.
 *
 * @package dbeurive\Trace\LogContainer
 */

class WriterData
{
    /** @var mixed The unique ID that identifies the writer. */
    private $__id;
    /** @var TracerData The tracer the holds this writer. */
    private $__tracerData;
    /** @var int The indentation level. */
    private $__indent;

    /**
     * Writer constructor.
     * @param mixed $inId ID if the writer.
     * @param TracerData $inTracerData Data that describes the tracer that holds the writer.
     * @param int $inIndent The indentation level that applies to the writer.
     */
    public function __construct($inId, TracerData $inTracerData, $inIndent=0) {
        $this->__id = $inId;
        $this->__tracerData = $inTracerData;
        $this->__indent = $inIndent;
    }

    /**
     * Return the ID of the writer.
     * @return mixed The ID of the writer.
     */
    public function getId() {
        return $this->__id;
    }

    /**
     * Return that tracer that holds the writer.
     * @return TracerData The method returns the writer that holds the tracer.
     */
    public function getTracerData() {
        return $this->__tracerData;
    }

    /**
     * Return the indentation level that applies to the writer.
     * @return int The indentation level that applies to the writer.
     */
    public function getIndent() {
        return $this->__indent;
    }
}