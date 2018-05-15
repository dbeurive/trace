<?php

namespace dbeurive\Trace;
use dbeurive\Trace\LogContainer\InterfaceLogContainer;
use dbeurive\Trace\LogContainer\TracerData;

/**
 * Class Tracer
 *
 * The class defines a tracer.
 * A tracer provides writers used to write traces.
 * Please note that a tracer requires an instance of a LOG container in order to be used.
 * The instance of a LOG container acts as an interface between the tracer and the specific underlying storage used to
 * record the traces.
 *
 * @package dbeurive\trace
 */

class Tracer {

    /** @var InterfaceLogContainer */
    private $__logContainer;
    /** @var TracerData */
    private $__tracerData;

    /**
     * Tracer constructor.
     * @param InterfaceLogContainer $inLogContainer Log container to use.
     * @throws TracerException
     */
    public function __construct(InterfaceLogContainer $inLogContainer) {
        $this->__logContainer = $inLogContainer;
        $this->__tracerData = $this->__logContainer->declareNewTracer();
    }

    /**
     * Get a new writer.
     * @param string $inOptMessage Message to print.
     * @param bool $inOptCall This flag indicates whether the trace's message starts with the string "Call " or not.
     *        If the given value is true, then the message is suffixed with the string "Call ".
     *        Otherwise, no string is suffixed to the message.
     *        By default, the message will start with "Call ", since a writer is usually created at the beginning of each
     *        function.
     * @return Writer The method returns the newly instanced writer.
     * @throws TracerException
     */
    public function getWriter($inOptMessage=null, $inOptCall=true) {
        $writerData = $this->__logContainer->declareNewWriter($this->__tracerData);
        $w = new Writer($this->__logContainer, $writerData);
        if (! is_null($inOptMessage)) {
            $suffix = $inOptCall ? 'Call ' : '';
            $w->write("${suffix}${inOptMessage}");
        }
        return $w;
    }
}