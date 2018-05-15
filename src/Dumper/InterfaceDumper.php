<?php

namespace dbeurive\Trace\Dumper;
use dbeurive\Trace\LogContainer\InterfaceLogContainer;

/**
 * Interface InterfaceDumper
 *
 * This interface describes a dumper API.
 * Please note that since the dumper's configuration heavily depends on the generated output, the configuration is done
 * through the use of mutators.
 *
 * @package dbeurive\Trace\Dumper
 */
interface InterfaceDumper
{
    /**
     * InterfaceDumper constructor.
     * @param InterfaceLogContainer $inLogContainer The LOG container used to store data to the underlying data storage.
     */
    public function __construct(InterfaceLogContainer $inLogContainer);

    /**
     * Extract data from the LOG container and format the traces.
     */
    function dump();
}