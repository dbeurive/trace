<?php

namespace dbeurive\Trace\LogContainer;
use dbeurive\trace\TracerException;

/**
 * Interface InterfaceLogger
 *
 * This interface defines the API of a LOG container.
 * A LOG container provides data storage for a given underlying storage.
 * The underlying storage may be a Sqlite database, for example.
 *
 * @package dbeurive\Trace\LogContainer;
 */
interface InterfaceLogContainer
{
    const MESSAGE_TYPE_TEXT  = 'TEXT';
    const MESSAGE_TYPE_DATA  = 'DATA';
    const MESSAGE_TYPE_LIST  = 'LIST';

    /**
     * InterfaceLogger constructor.
     * @param array $inInitData This array contains whatever parameter needed to create the container.
     * @throws TracerException
     */
    public function __construct(array $inInitData);

    /**
     * Create a new tracer.
     * @return TracerData The method returns the new tracer.
     * @throws TracerException
     */
    public function declareNewTracer();

    /**
     * Create a new writer for a given tracer.
     * @param TracerData $inTracerData The tracer for which we want to create a new writer.
     * @return WriterData The method returns the new writer.
     * @throws TracerException
     */
    public function declareNewWriter(TracerData $inTracerData);

    /**
     * Get the list of created tracers.
     * @return InterfaceList The method returns the list of created tracers.
     *         Please note that the type of the returned object depends on the type of the LOG container.
     * @throws TracerException
     */
    public function getTracers();

    /**
     * Get the list of writers associated to a given tracer.
     * @param TracerData $inTracerData The tracer for which we want to get the list of writers.
     * @return InterfaceList The method returns the list of created writers.
     *         Please note that the type of the returned object depends on the type of the LOG container.
     * @throws TracerException
     */
    public function getWriters(TracerData $inTracerData);


    /**
     * Get the list of traces associated to a given writer.
     * @param TracerData $inTracerDate The tracer for which you want to get the traces.
     * @return InterfaceList The method returns the list of traces.
     *         Please note that the type of the returned object depends on the type of the LOG container.
     */
    public function getTraces(TracerData $inTracerDate);

    /**
     * Write a message.
     * @param string|array $inMessage The message to write.
     * @param mixed $inWriterId ID of the writer that writes the message.
     * @param string $inOptType Type of message. This value may be:
     *        - InterfaceLogger::MESSAGE_TYPE_TEXT
     *        - InterfaceLogger::MESSAGE_TYPE_DATA
     *        - InterfaceLogger::MESSAGE_TYPE_LIST
     * @throws TracerException
     */
    public function write($inMessage, $inWriterId, $inOptType=InterfaceLogContainer::MESSAGE_TYPE_TEXT);

    /**
     * Destroy a writer.
     * @param WriterData $inWriterData Data that describes the writer that is being destroyed.
     * @throws TracerException
     */
    public function destroy(WriterData $inWriterData);

    /**
     * Set the indentation level for a given writer.
     * @param WriterData $inWriterData Data that describes the writer.
     * @param int $inIndent The indentation level.
     * @throws TracerException
     */
    public function setIndent(WriterData $inWriterData, $inIndent);

    /**
     * Find the parent of a given writer.
     * @param WriterData $inWriterData Data that describes the writer for which we want to get the parent.
     * @return WriterData|null The method returns data about the parent writer.
     *         The special value null means that the parent writer is the root writer.
     * @throws TracerException
     */
    public function getParentWriter(WriterData $inWriterData);
}