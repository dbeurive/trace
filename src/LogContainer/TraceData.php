<?php

namespace dbeurive\Trace\LogContainer;

/**
 * Class TraceData
 *
 * This class defines the data that represents a trace.
 *
 * @package dbeurive\Trace\LogContainer
 */

class TraceData
{
    private $__id;
    private $__tracerData;
    private $__writerData;
    private $__timestamp;
    private $__type;
    private $__message;
    private $__isFirst;
    private $__isLast;

    /**
     * TraceData constructor.
     * @param mixed $inId The ID of the trace. Please note that each trace has a unique ID.
     * @param TracerData $inTracerData The data that applies to the tracer that generated the trace.
     * @param WriterData $inWriterData The data that applies to the writer that generated the trace.
     * @param mixed $inTimeStamp The timestamp associated to the trace.
     * @param string $inType The type of the trace.
     * @param string $inMessage The message.
     * @param bool $inIsFirst This flag indicates whether the trace is the first of its batch.
     * @param bool $inIsLast This flag indicates whether the trace is the last of its batch.
     * @see InterfaceLogContainer::MESSAGE_TYPE_DATA
     * @see InterfaceLogContainer::MESSAGE_TYPE_TEXT
     * @see InterfaceLogContainer::MESSAGE_TYPE_LIST
     */
    public function __construct($inId, TracerData $inTracerData, WriterData $inWriterData, $inTimeStamp, $inType, $inMessage, $inIsFirst, $inIsLast) {
        $this->__id = $inId;
        $this->__tracerData = $inTracerData;
        $this->__writerData = $inWriterData;
        $this->__timestamp = $inTimeStamp;
        $this->__type = $inType;
        $this->__message = $inMessage;
        $this->__isFirst = $inIsFirst;
        $this->__isLast = $inIsLast;
    }

    /**
     * Return the ID of the trace.
     * @return mixed The ID of the trace.
     */
    public function getId() {
        return $this->__id;
    }

    /**
     * Return the data that describes the tracer that wrote the trace.
     * @return TracerData The data that describes the tracer that wrote the trace.
     */
    public function getTracerData() {
        return $this->__tracerData;
    }

    /**
     * Return the data that describes the writer that wrote the trace.
     * @return WriterData The data that describes the writer that wrote the trace.
     */
    public function getWriterData() {
        return $this->__writerData;
    }

    /**
     * Return the timestamp associated with the trace.
     * @return mixed The timestamp associated with the trace.
     */
    public function getTimeStamp() {
        return $this->__timestamp;
    }

    /**
     * Return the type of the trace.
     * @return string The trace of the trace.
     * @see InterfaceLogContainer::MESSAGE_TYPE_DATA
     * @see InterfaceLogContainer::MESSAGE_TYPE_TEXT
     * @see InterfaceLogContainer::MESSAGE_TYPE_LIST
     */
    public function getType() {
        return $this->__type;
    }

    /**
     * Return the message associated with the trace.
     * @return string The message associated with the trace.
     */
    public function getMessage() {
        return $this->__message;
    }

    /**
     * Test if the trace is the first of its batch.
     * @return bool If the trace is the first of its batch, then the method returns the value true.
     *         Otherwise, it returns the value false.
     */
    public function isFirst() {
        return $this->__isFirst;
    }

    /**
     * Test if the trace is the last of its batch.
     * @return bool If the trace is the last of its batch, then the method returns the value true.
     *         Otherwise, it returns the value false.
     */
    public function isLast() {
        return $this->__isLast;
    }
}