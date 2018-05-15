<?php

namespace dbeurive\Trace\LogContainer;

/**
 * Interface InterfaceList
 *
 * This interface defines the public API of a list of items related to LOG traces.
 * Such items may be:
 * - tracer data.
 * - writer data.
 * - trace data.
 *
 * @package dbeurive\Trace\LogContainer
 */

interface InterfaceList
{
    /**
     * InterfaceList constructor.
     * @param array $inInit This array contains whatever parameter needed to create the list.
     *        The content of the given array depends on the nature of the instanced LOG container.
     *        Typically, for the Sqlite container, the list constructor needs a PDO statement.
     */
    public function __construct(array $inInit);

    /**
     * Return the next item (tracer data, writer data, or trace data) of the list.
     * @return TracerData|WriterData|TraceData|false The method returns the next item in the list, or false if the list
     *         does not contain any additional item.
     *         - If the list contains data about tracers, then the method returns the next tracer data.
     *         - If the list contains data about writers, then the method returns the next writer data.
     *         - If the list contains data about traces, then the method returns the next trace data.
     */
    public function next();
}