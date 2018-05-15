<?php

namespace dbeurive\Trace;
use dbeurive\trace\LogContainer\InterfaceLogContainer;
use dbeurive\trace\LogContainer\TracerData;
use dbeurive\trace\LogContainer\WriterData;
use dbeurive\Trace\LogContainer\InterfaceList;

/**
 * Class Reader
 *
 * This class implements the LOG container reader.
 * It is used by the tool that generates the LOG file (the "logger") from the traces organised within the LOG container.
 * The "logger" should perform the actions below:
 *
 * 1. Instantiate a LOG container object that will be used as an interface to the underlying storage used to store traces.
 * 2. Instantiate a reader object that will exploit the previously instanced LOG container.
 * 3. Eventually, get the list of tracers (using the method "getTracers()").
 * 4. Eventually, get the list of writers (using the method "getWriters()").
 * 5. Get the list of traces for a given tracer (using the method "getTraces(<tracer>)").
 *    Traces come with their indentation levels.
 *
 * @package dbeurive\Trace
 */

class Reader
{
    /** @var InterfaceLogContainer */
    private $__logContainer;

    /**
     * Tracer constructor.
     * @param InterfaceLogContainer $inLogContainer Log container to use.
     * @throws TracerException
     */
    public function __construct(InterfaceLogContainer $inLogContainer) {
        $this->__logContainer = $inLogContainer;
        $this->__calculateIndentation();
    }

    /**
     * Get the list of created tracers.
     * @return InterfaceList The method returns the list of created tracers.
     *         Please note that type of the returned object depends on the container.
     */
    public function getTracers() {
        return $this->__logContainer->getTracers();
    }

    /**
     * Get the list of writers associated to a given tracer.
     * @param TracerData $inTracerData The tracer for which we want to get the list of writers.
     * @return InterfaceList The method returns the list of created writers.
     *         Please note that type of the returned object depends on the container.
     * @throws TracerException
     */
    public function getWriters(TracerData $inTracerData) {
        return $this->__logContainer->getWriters($inTracerData);
    }

    /**
     * Get the list of traces for a given writer.
     * @param TracerData $inTracerData The writer for which we want to get the list of traces.
     * @return InterfaceList The method returns the list of traces.
     *         Please note that type of the returned object depends on the container.
     */
    public function getTraces(TracerData $inTracerData) {
        return $this->__logContainer->getTraces($inTracerData);
    }

    /**
     * Calculate the indentation level for all writers within the LOG container.
     * @throws TracerException
     */
    private function __calculateIndentation() {
        $tracersList = $this->__logContainer->getTracers();
        /** @var TracerData $trace */
        while ($tracerData = $tracersList->next()) {
            $writersList = $this->__logContainer->getWriters($tracerData);
            $writerIndent = array();
            /** @var WriterData $writerData */
            while ($writerData = $writersList->next()) {
                $parent = $this->__logContainer->getParentWriter($writerData);
                if (is_null($parent)) {
                    $writerIndent[$writerData->getId()] = 0;
                    continue;
                }
                if (!array_key_exists($parent->getId(), $writerIndent)) {
                    throw new TracerException(sprintf('An unexpected error occurred. The writer which ID is %d cannot be found.', $parent->getId()));
                }
                $indent = $writerIndent[$parent->getId()] + 1;
                $this->__logContainer->setIndent($writerData, $indent);
                $writerIndent[$writerData->getId()] = $indent;
            }
        }
    }
}