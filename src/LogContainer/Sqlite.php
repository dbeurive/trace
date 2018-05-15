<?php

namespace dbeurive\Trace\LogContainer;
use dbeurive\Trace\LogContainer\Sqlite\TraceList;
use dbeurive\Trace\TracerException;
use dbeurive\Trace\LogContainer\Sqlite\WriterList;
use dbeurive\Trace\LogContainer\Sqlite\TracerList;

/**
 * Class Sqlite
 *
 * This class implements the LOG container that uses the Sqlite database.
 *
 * @package dbeurive\Trace\LogContainer;
 */

class Sqlite implements InterfaceLogContainer
{

    const CONF_KEY_DB_PATH = 'path';
    const CONF_KEY_DB_OVERRIDE = 'override';
    const CONF_KEY_PDO_HANDLER = 'pdo-handler';

    const __MESSAGE_TYPE_FIRST = 'FIRST';
    const __MESSAGE_TYPE_LAST  = 'LAST';


    /** @var string Path to the database. */
    private $__dbPath;
    /** @var \PDO The PDO handler. */
    private $__pdo;

    /**
     * Sqlite constructor.
     * @param array $inInitData Data used to initialise the logger.
     *        This array contains the following keys:
     *        - self::CONF_KEY_DB_PATH (mandatory): path to the SQLITE database.
     *        - self::CONF_KEY_DB_OVERRIDE (optional): flag that indicates whether the database must be deleted or not.
     *          The value true indicates that the database must be deleted.
     *          The default value if false (the database is not deleted).
     * @throws TracerException
     */
    public function __construct(array $inInitData) {
        if (! array_key_exists(self::CONF_KEY_DB_PATH, array(self::CONF_KEY_DB_PATH => 0))) {
            throw new TracerException(sprintf('The provided configuration is not valid. The parameter "%s" is missing.', self::CONF_KEY_DB_PATH));
        }

        $this->__dbPath = $inInitData[self::CONF_KEY_DB_PATH];

        if (array_key_exists(self::CONF_KEY_DB_OVERRIDE, $inInitData) && $inInitData[self::CONF_KEY_DB_OVERRIDE]) {
            if (file_exists($this->__dbPath)) {
                if (! unlink($this->__dbPath)) {
                    throw new TracerException(sprintf('Can not delete the file "%s".', $this->__dbPath));
                }
            }
        }

        $this->__pdo = new \PDO("sqlite:" . $this->__dbPath);

        if (array_key_exists(self::CONF_KEY_DB_OVERRIDE, $inInitData) && $inInitData[self::CONF_KEY_DB_OVERRIDE]) {
            $this->__createDatabase();
        }
    }

    /**
     * Create a new tracer.
     * Please note that you should create one tracer per thread.
     * @return TracerData The method returns the new tracer.
     * @throws TracerException
     */
     public function declareNewTracer() {
        $sql = 'INSERT INTO tracer DEFAULT VALUES;';

        if (false === $statement = $this->__pdo->prepare($sql)) {
            $info = $this->__pdo->errorInfo();
            throw new TracerException(sprintf('An unexpected error occurred. Cannot prepare the following SQL request: %s (%s)', $sql, $info[2]));
        }

        if (! $statement->execute()) {
            $info = $statement->errorInfo();
            throw new TracerException(sprintf('An unexpected error occurred. Cannot declare the tracer. The following SQL request failed: %s (%s)', $sql, $info[2]));
        }

        return new TracerData($this->__pdo->lastInsertId());
    }

    /**
     * Create a new writer for a given tracer.
     * @param TracerData $inTracerData The data that describes the tracer for which we want to create a new writer.
     * @return WriterData The method returns the new writer.
     * @throws TracerException
     */
     public function declareNewWriter(TracerData $inTracerData) {
        $sql = 'INSERT INTO writer (fk_tracer_id) VALUES(:id)';

        if (false === $statement = $this->__pdo->prepare($sql)) {
            $info = $this->__pdo->errorInfo();
            throw new TracerException(sprintf('An unexpected error occurred. Cannot prepare the following SQL request: %s (%s)', $sql, $info[2]));
        }

        if (false === $statement->bindValue(':id', $inTracerData->getId())) {
            $info = $statement->errorInfo();
            throw new TracerException(sprintf('An unexpected error occurred. Can not bind value ":id" to the following SQL request: %s (%s)', $sql, $info[2]));
        }

        if (! $statement->execute()) {
            $info = $statement->errorInfo();
            throw new TracerException(sprintf('Cannot declare the writer. The following SQL request failed: %s (%s)', $sql, $info[2]));
        }

        $writerId = $this->__pdo->lastInsertId();
        $this->write('', $writerId, self::__MESSAGE_TYPE_FIRST);
        return new WriterData($writerId, $inTracerData);
    }

    /**
     * Get the list of created tracers.
     * @return InterfaceList The method returns the list of created tracers.
     *         Please note that type of the returned object depends on the nature of the LOG container.
     * @throws TracerException
     */
     public function getTracers() {
        $sql = 'SELECT id FROM tracer ORDER BY id ASC;';

        if (false === $statement = $this->__pdo->prepare($sql)) {
            $info = $this->__pdo->errorInfo();
            throw new TracerException(sprintf('An unexpected error occurred. Cannot prepare the following SQL request: %s (%s)', $sql, $info[2]));
        }

        if (! $statement->execute()) {
            $info = $statement->errorInfo();
            throw new TracerException(sprintf('An unexpected error occurred. Cannot execute the following SQL request: %s (%s)', $sql, $info[2]));
        }

        return new TracerList(array(Sqlite::CONF_KEY_PDO_HANDLER => $statement));
    }

    /**
     * Get the list of writers associated to a given tracer.
     * @param TracerData $inTracerData The data that describes the tracer for which we want to get the list of writers.
     * @return InterfaceList The method returns the list of created writers.
     *         Please note that type of the returned object depends on the container.
     * @throws TracerException
     */
     public function getWriters(TracerData $inTracerData) {

        $sql = 'SELECT id, fk_tracer_id, indent FROM writer WHERE fk_tracer_id = :id ORDER BY id ASC';

        if (false === $statement = $this->__pdo->prepare($sql)) {
            $info = $this->__pdo->errorInfo();
            throw new TracerException(sprintf('An unexpected error occurred. Cannot prepare the following SQL request: %s (%s)', $sql, $info[2]));
        }

        if (false === $statement->bindValue(':id', $inTracerData->getId())) {
            $info = $statement->errorInfo();
            throw new TracerException(sprintf('An unexpected error occurred. Can not bind value ":id" to the following SQL request: %s (%s)', $sql, $info[2]));
        }

        if (! $statement->execute()) {
            $info = $statement->errorInfo();
            throw new TracerException(sprintf('An unexpected error occurred. Cannot execute the following SQL request: %s (%s)', $sql, $info[2]));
        }

        return new WriterList(array(Sqlite::CONF_KEY_PDO_HANDLER => $statement));
    }

    /**
     * Get the list of traces associated to a given writer.
     * @param TracerData $inTracerDate The data that describes the tracer for which you want to get the traces.
     * @return TraceList The method returns the list of traces.
     *         Please note that type of the returned object depends on the container.
     * @throws TracerException
     */
    public function getTraces(TracerData $inTracerDate) {

         $sql = 'SELECT   writer.id AS writer_id,
                          writer.fk_tracer_id AS tracer_id,
                          writer.indent AS indent_level,
                          trace.id AS trace_id,
                          trace.timestamp AS timestamp,
                          trace.message AS message,
                          trace.type AS type,
                          trace.is_first AS is_first,
                          trace.is_last AS is_last
                 FROM     tracer, writer, trace
                 WHERE    tracer.id=:tracer_id
                   AND    writer.fk_tracer_id=tracer.id
                   AND    trace.fk_writer_id=writer.id
                 ORDER BY trace.id ASC';

        if (false === $statement = $this->__pdo->prepare($sql)) {
            $info = $this->__pdo->errorInfo();
            throw new TracerException(sprintf('An unexpected error occurred. Cannot prepare the following SQL request: %s (%s)', $sql, $info[2]));
        }

        if (false === $statement->bindValue(':tracer_id', $inTracerDate->getId())) {
            $info = $statement->errorInfo();
            throw new TracerException(sprintf('An unexpected error occurred. Can not bind value ":id" to the following SQL request: %s (%s)', $sql, $info[2]));
        }

        if (! $statement->execute()) {
            $info = $statement->errorInfo();
            throw new TracerException(sprintf('An unexpected error occurred. Cannot execute the following SQL request: %s (%s)', $sql, $info[2]));
        }

        return new TraceList(array(Sqlite::CONF_KEY_PDO_HANDLER => $statement));
    }

    /**
     * Write a message.
     * @param string|array $inMessage The message to write.
     * @param mixed $inWriterId ID of the writer that writes the message.
     * @param string $inOptType Type of message. This value may be:
     *        - \dbeurive\Trace\LogContainer\InterfaceLogContainer::MESSAGE_TYPE_TEXT
     *        - \dbeurive\Trace\LogContainer\InterfaceLogContainer::MESSAGE_TYPE_DATA
     *        - \dbeurive\Trace\LogContainer\InterfaceLogContainer::MESSAGE_TYPE_LIST
     *        Please note that the values below can also be provided:
     *        - self::__MESSAGE_TYPE_FIRST
     *        - self::__MESSAGE_TYPE_LAST
     *        These values are used internally.
     * @see InterfaceLogContainer::MESSAGE_TYPE_TEXT
     * @see InterfaceLogContainer::MESSAGE_TYPE_DATA
     * @see InterfaceLogContainer::MESSAGE_TYPE_LIST
     * @throws TracerException
     */
    public function write($inMessage, $inWriterId, $inOptType=InterfaceLogContainer::MESSAGE_TYPE_TEXT) {

        $isFirst = $inOptType == self::__MESSAGE_TYPE_FIRST ? 1 : 0;
        $isLast = $inOptType == self::__MESSAGE_TYPE_LAST ? 1 : 0;
        $type = $isFirst || $isLast ? null : $inOptType;

        if (is_array($inMessage)) {
            $type = self::MESSAGE_TYPE_LIST;
            $inMessage = json_encode($inMessage);
            if (JSON_ERROR_NONE != json_last_error()) {
                throw new TracerException(sprintf('An unexpected error occurred. Cannot JSON encode data: %s', json_last_error_msg()));
            }
        }

        $sql = 'INSERT INTO trace(fk_writer_id, message, type, is_first, is_last) VALUES(:id, :message, :type, :is_first, :is_last)';
        if (false === $statement = $this->__pdo->prepare($sql)) {
            $info = $this->__pdo->errorInfo();
            throw new TracerException(sprintf('An unexpected error occurred. Cannot prepare the following SQL request: %s (%s)', $sql, $info[2]));
        }

        if (false === $statement->bindValue(':id', $inWriterId)) {
            $info = $statement->errorInfo();
            throw new TracerException(sprintf('An unexpected error occurred. Can not bind value ":id" to the following SQL request: %s (%s)', $sql, $info[2]));
        }

        if (false === $statement->bindValue(':message', $inMessage)) {
            $info = $statement->errorInfo();
            throw new TracerException(sprintf('An unexpected error occurred. Can not bind value ":message" to the following SQL request: %s (%s)', $sql, $info[2]));
        }

        if (false === $statement->bindValue(':type', $type)) {
            $info = $statement->errorInfo();
            throw new TracerException(sprintf('An unexpected error occurred. Can not bind value ":type" to the following SQL request: %s (%s)', $sql, $info[2]));
        }

        if (false === $statement->bindValue(':is_first', $isFirst)) {
            $info = $statement->errorInfo();
            throw new TracerException(sprintf('An unexpected error occurred. Can not bind value ":is_first" to the following SQL request: %s (%s)', $sql, $info[2]));
        }

        if (false === $statement->bindValue(':is_last', $isLast)) {
            $info = $statement->errorInfo();
            throw new TracerException(sprintf('An unexpected error occurred. Can not bind value ":is_last" to the following SQL request: %s (%s)', $sql, $info[2]));
        }

        if (! $statement->execute()) {
            $info = $statement->errorInfo();
            throw new TracerException(sprintf('An unexpected error occurred. The following SQL request failed: %s (%s)', $sql, $info[2]));
        }
    }

    /**
     * Set the indentation level for a given writer.
     * @param WriterData $inWriterData The data that describes the writer for which we want to set the indentation level.
     * @param int $inIndent The indentation level to set.
     * @throws TracerException
     */
    public function setIndent(WriterData $inWriterData, $inIndent) {
        $sql = 'UPDATE writer SET indent=:indent WHERE id=:id';

        if (false === $statement = $this->__pdo->prepare($sql)) {
            $info = $this->__pdo->errorInfo();
            throw new TracerException(sprintf('An unexpected error occurred. Cannot prepare the following SQL request: %s (%s)', $sql, $info[2]));
        }

        if (false === $statement->bindValue(':indent', $inIndent)) {
            $info = $statement->errorInfo();
            throw new TracerException(sprintf('An unexpected error occurred. Can not bind value ":trace_id" to the following SQL request: %s (%s)', $sql, $info[2]));
        }

        if (false === $statement->bindValue(':id', $inWriterData->getId())) {
            $info = $statement->errorInfo();
            throw new TracerException(sprintf('An unexpected error occurred. Can not bind value ":trace_id" to the following SQL request: %s (%s)', $sql, $info[2]));
        }

        if (! $statement->execute()) {
            $info = $statement->errorInfo();
            throw new TracerException(sprintf('An unexpected error occurred. Cannot execute the following SQL request: %s (%s)', $sql, $info[2]));
        }
    }

    /**
     * Destroy a writer.
     * @param WriterData $inWriterData The data that describes the writer being destroyed.
     * @throws TracerException
     */
    public function destroy(WriterData $inWriterData) {
        $this->write('', $inWriterData->getId(), self::__MESSAGE_TYPE_LAST);
    }


    /**
     * Find the parent of a given writer.
     * @param WriterData $inWriterData The data that describes the writer for which we want to get the parent.
     * @return WriterData|null The method returns the parent writer.
     *         The special value null means that the parent writer is the root writer.
     * @throws TracerException
     */
     public function getParentWriter(WriterData $inWriterData) {

        $sql = 'SELECT   trace2.fk_writer_id AS id,
                         writer.indent AS indent,
                         trace2.id AS traceId
                FROM     trace AS trace1,
                         trace AS trace2,
                         trace AS trace3,
                         writer
                WHERE    trace1.fk_writer_id = :id
                  AND    trace2.fk_writer_id = trace3.fk_writer_id
                  AND    trace1.is_first = 1
                  AND    trace2.is_first = 1
                  AND    trace3.is_last = 1
                  AND    trace2.id < trace1.id
                  AND    trace3.id > trace1.id
                  AND    writer.id = trace2.fk_writer_id
                ORDER BY traceId DESC
                LIMIT    1';

        if (false === $statement = $this->__pdo->prepare($sql)) {
            $info = $this->__pdo->errorInfo();
            throw new TracerException(sprintf('An unexpected error occurred. Cannot prepare the following SQL request: %s (%s)', $sql, $info[2]));
        }

        if (false === $statement->bindValue(':id', $inWriterData->getId())) {
            $info = $statement->errorInfo();
            throw new TracerException(sprintf('An unexpected error occurred. Can not bind value ":id" to the following SQL request: %s (%s)', $sql, $info[2]));
        }

        if (! $statement->execute()) {
            $info = $statement->errorInfo();
            throw new TracerException(sprintf('An unexpected error occurred. Cannot execute the following SQL request: %s (%s)', $sql, $info[2]));
        }

        $row = $statement->fetch(\PDO::FETCH_ASSOC);

        if (false !== $row) {
            return new WriterData($row['id'], new TracerData($row['traceId']), $row['indent']);
        }
        return null;
    }

    /**
     * Create the SQLITE database.
     */
    public function __createDatabase() {
        $schema = array(
            'CREATE TABLE tracer (
                id INTEGER PRIMARY KEY
            )',
            'CREATE TABLE writer (
                id           INTEGER PRIMARY KEY,
                fk_tracer_id INT NOT NULL,
                indent       INT DEFAULT 0,
                FOREIGN KEY(fk_tracer_id) REFERENCES tracer(id)
            )',
            'CREATE INDEX fk_tracer_id_index ON writer (fk_tracer_id)',
            'CREATE TABLE trace (
                id           INTEGER PRIMARY KEY,
                timestamp    DATETIME DEFAULT CURRENT_TIMESTAMP,
                fk_writer_id INT NOT NULL,
                message      TEXT NOT NULL,
                type         TEXT,
                is_first     INT DEFAULT 0,
                is_last      INT DEFAULT 0,
                FOREIGN KEY(fk_writer_id) REFERENCES writer(id)
            )',
            'CREATE INDEX fk_writer_id_index ON trace (fk_writer_id)'
        );

        /** @var string $_sql */
        foreach ($schema as $_sql) {
            $this->__pdo->exec($_sql);
        }
    }

}