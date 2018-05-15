<?php

namespace dbeurive\Trace\Dumper;

use dbeurive\Trace\LogContainer\InterfaceLogContainer;
use dbeurive\Trace\Reader;
use dbeurive\Trace\LogContainer\TracerData;
use dbeurive\Trace\LogContainer\TraceData;
use dbeurive\Trace\TracerException;

/**
 * Class TextSimple
 *
 * This class implements the dumper that generates a simple textual output using the data extracted from the LOG container.
 *
 * @package dbeurive\Trace\Dumper
 */

class TextSimple implements InterfaceDumper
{
    /** @var InterfaceLogContainer */
    private $__logContainer;
    /** @var string|false */
    private $__outputFilePath=false;
    /** @var bool */
    private $__dumpTimestamp=false;
    /** @var string */
    private $__indentationStep=' ';

    /**
     * TextSimple constructor.
     * @param InterfaceLogContainer $inLogContainer The LOG container used to store data to the underlying data storage.
     */
    public function __construct(InterfaceLogContainer $inLogContainer) {
        $this->__logContainer = $inLogContainer;
    }

    /**
     * Set the path to the output file used to store the generated textual representation.
     * If no path is configured, then the dumper dumps the generated textual representation to the standard output.
     * @param string $inPath Path to the output file used to store the generated textual representation.
     */
    public function setOutputFilePath($inPath) {
        $this->__outputFilePath = $inPath;
    }

    /**
     * Tells the dumper whether it must include timestamps within the generated textual representation.
     * @param bool $inFlag Flag that indicates whether the timestamp should be included within the generated textual
     *        representation.
     *        - is the given value is true, then the timestamp is included.
     *        - is the given value is true, then the timestamp is not included.
     *        Please note that, by default, the timestamp is not included.
     */
    public function setDumpTimestamp($inFlag) {
        $this->__dumpTimestamp = $inFlag;
    }

    /**
     * Set the string that must be used as "base pattern" to generate the margin which width depends on the indentation level.
     * @param string $inStep The "base pattern" to generate the margin which width depends on the indentation level.
     *        The default value is the simple space (" ").
     */
    public function setIndentationStep($inStep) {
        $this->__indentationStep = $inStep;
    }

    /**
     * Generate the textual representation of the trace using the data extracted from the LOG container.
     * Then dump this representation to the standard output or to a file, depending on the dumper's configuration.
     */
    function dump() {
        $reader = new Reader($this->__logContainer);
        $tracersData = $reader->getTracers();
        /** @var TracerData $traceData */
        while ($traceData = $tracersData->next()) {
            $tracesData = $reader->getTraces($traceData);
            /** @var TraceData $traceData */
            while ($traceData = $tracesData->next()) {

                if ($traceData->isFirst()) {
                    continue;
                }

                $timestamp = $this->__dumpTimestamp ? '[' . $traceData->getTimeStamp() . '] ' : '';
                $margin = $this->__margin($traceData->getWriterData()->getIndent());

                $message = 'END';
                if (! $traceData->isLast()) {

                    $message = $this->__processMessage($traceData->getMessage(), $traceData->getType(), $margin);
                }

                if (! $this->__outputFilePath) {
                    printf("%s%s%s\n", $timestamp, $margin, $message);
                }
            }
        }

    }

    /**
     * Return the margin that represents a given level of indentation.
     * @param int $inIndentationLevel The indentation level.
     * @return string The method returns a string that represents the margin.
     */
    private function __margin($inIndentationLevel) {
        $margin = '';
        for ($i=0; $i<$inIndentationLevel; $i++) {
            $margin .= $this->__indentationStep;
        }
        return $margin;
    }

    /**
     * Generate a string that represents a given message, extracted from a trace.
     * @param string $inMessage Message to print.
     * @param string $inType Type of the message. The type can be:
     *        - \dbeurive\Trace\LogContainer\InterfaceLogContainer::MESSAGE_TYPE_DATA
     *        - \dbeurive\Trace\LogContainer\InterfaceLogContainer::MESSAGE_TYPE_TEXT
     *        - \dbeurive\Trace\LogContainer\InterfaceLogContainer::MESSAGE_TYPE_LIST
     * @param string $inMargin The margin used to indent the message.
     * @return string
     * @see InterfaceLogContainer::MESSAGE_TYPE_DATA
     * @see InterfaceLogContainer::MESSAGE_TYPE_TEXT
     * @see InterfaceLogContainer::MESSAGE_TYPE_LIST
     * @throws TracerException
     */
    private function __processMessage($inMessage, $inType, $inMargin) {
        if (InterfaceLogContainer::MESSAGE_TYPE_DATA == $inType || InterfaceLogContainer::MESSAGE_TYPE_TEXT == $inType) {
            return $this->__processMultiLine($inMessage, $inMargin);
        }
        return $this->__processList($inMessage, $inMargin);
    }

    /**
     * Process a message that spans over several lines.
     * This method splits the message into several lines and add the required margin where it should be added.
     * @param string $inMessage Message to process.
     * @param string $inMargin Margin to apply.
     * @return string The method returns the processed message, that is ready to be printed.
     */
    private function __processMultiLine($inMessage, $inMargin) {
        $lines = preg_split('/\n/', $inMessage);
        if (1 == count($lines)) {
            return $inMessage;
        }
        $lines = array_map(function($inLine) use($inMargin) {
            return "${inMargin}${inLine}";
        }, $lines);
        return substr(implode("\n", $lines), strlen($inMargin));
    }

    /**
     * Process a message that represents a list of couples (key, value).
     * @param string $inMessage The JSON encoding of the array that represents the list of couples (key, value).
     * @param string $inMargin Margin to apply.
     * @return string The method returns the processed message, that is ready to be printed.
     * @throws TracerException
     */
    private function __processList($inMessage, $inMargin) {

        $list = json_decode($inMessage, true);
        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new TracerException(sprintf('An error occurred while processing the list "%s": %s', $inMessage, json_last_error_msg()));
        }

        $keys = array_keys($list);
        $max = $this->__getMaxLength($keys);
        sort($keys);
        $lines = array_map(function($inKey) use($list, $max, $inMargin) {
            return sprintf("%s* %-${max}s: %s", $inMargin, $inKey, $list[$inKey]);
        }, $keys);
        return substr(implode("\n", $lines), strlen($inMargin));
    }

    /**
     * Given a list of string, the method returns the length of the longest string.
     * @param array $inStrings The list of strings.
     * @return int The method returns the length of the longest string.
     */
    private function __getMaxLength(array $inStrings) {
        $max = 0;
        foreach ($inStrings as $_string) {
            $max = strlen($_string) > $max ? strlen($_string) : $max;
        }
        return $max;
    }
}