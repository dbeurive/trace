<?php

namespace dbeurive\Trace\LogContainer;

/**
 * Class TracerData
 *
 * This class defines the data that represents a tracer.
 *
 * @package dbeurive\Trace\LogContainer
 */

class TracerData
{
    /** @var mixed The unique ID that identifies the tracer. */
    private $__id;

    /**
     * TracerData constructor.
     * @param mixed $inId ID of the tracer.
     */
    public function __construct($inId) {
        $this->__id = $inId;
    }

    /**
     * Get the ID of the trace.
     * @return mixed The ID of the tracer.
     */
    public function getId() {
        return $this->__id;
    }
}