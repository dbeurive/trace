<?php

namespace dbeurive\trace;
use dbeurive\trace\LogContainer\InterfaceLogContainer;
use dbeurive\trace\LogContainer\WriterData;

/**
 * Class Writer
 *
 * This class defines a writer.
 * A writer writes traces to the LOG container.
 *
 * @package dbeurive\trace
 */

class Writer
{
    /** @var WriterData */
    private $__writerData;
    /** @var InterfaceLogContainer */
    private $__logContainer;

    /**
     * Writer constructor.
     * Please note that writers are created using the LOG container.
     * @param InterfaceLogContainer $inLogContainer The LOG container to use.
     * @param WriterData $inWriterData The data that describes the writer to create.
     */
    public function __construct(InterfaceLogContainer $inLogContainer,  WriterData $inWriterData) {
        $this->__logContainer = $inLogContainer;
        $this->__writerData = $inWriterData;
    }

    /**
     * Write a message.
     * Please note that this method has many helpers:
     * - call
     * - varDump
     * - writeList
     * @param string|array $inMessage The message to write.
     * @param string $inOptType Type of message. This value may be:
     *        - \dbeurive\trace\LogContainer\InterfaceLogContainer::MESSAGE_TYPE_TEXT
     *        - \dbeurive\trace\LogContainer\InterfaceLogContainer::MESSAGE_TYPE_DATA
     *        - \dbeurive\trace\LogContainer\InterfaceLogContainer::MESSAGE_TYPE_LIST
     * @see InterfaceLogContainer::MESSAGE_TYPE_TEXT
     * @see InterfaceLogContainer::MESSAGE_TYPE_DATA
     * @see InterfaceLogContainer::MESSAGE_TYPE_LIST
     * @see Writer::call()
     * @see Writer::varDump() You should used this helper whenever you need to LOG the result of "var_dump".
     * @see Writer::writeList() You should used this helper whenever you need to LOG a list of couples (key, value).
     * @throws TracerException
     */
    public function write($inMessage, $inOptType=InterfaceLogContainer::MESSAGE_TYPE_TEXT) {
        $this->__logContainer->write($inMessage, $this->__writerData->getId(), $inOptType);
    }

    /**
     * Write a message that express a call to a function or method.
     * @param string $inFunctionName The message to write.
     * @throws TracerException
     */
    public function call($inFunctionName) {
        $this->__logContainer->write("Call ${inFunctionName}", $this->__writerData->getId());
    }

    /**
     * Write a message that represents a PHP variable.
     * @param mixed $inVar The PHP variable to dump.
     * @param string $inOptVarName Name of the variable.
     * @throws TracerException
     */
    public function varDump($inVar, $inOptVarName=null) {
        $m = print_r($inVar, true);
        $m = preg_replace('/\r?\n$/', '', $m);
        $m = is_null($inOptVarName) ? $m : "${inOptVarName} = ${m}";
        $this->__logContainer->write($m, $this->__writerData->getId(), InterfaceLogContainer::MESSAGE_TYPE_DATA);
    }

    /**
     * Write a list of couples (key, value).
     * @param array $inList Associative array that represents the list of couples (key, value).
     * @throws TracerException
     */
    public function writeList(array $inList) {
        $this->__logContainer->write($inList, $this->__writerData->getId(), InterfaceLogContainer::MESSAGE_TYPE_LIST);
    }

    /**
     * Writer destructor.
     */
    public function __destruct() {
        $this->__logContainer->destroy($this->__writerData);
    }
}