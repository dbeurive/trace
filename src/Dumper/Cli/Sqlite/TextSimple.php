<?php

namespace dbeurive\Trace\Dumper\Cli\Sqlite;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use dbeurive\Trace\Dumper\TextSimple as Dumper;
use dbeurive\Trace\LogContainer\Sqlite;

class TextSimple extends Command
{
    const CLA_INPUT     = 'input';
    const CLA_OUTPUT    = 'output';
    const CLO_TAB       = 'tab';
    const CLO_SC_TAB    = 't';

    /**
     * HtmlDumper constructor.
     * @see \Symfony\Component\Console\Command\Command
     */
    final public function __construct() {
        parent::__construct();
        $this->addArgument(self::CLA_INPUT, InputArgument::REQUIRED, 'Path to the input file', null)
             ->addArgument(self::CLA_OUTPUT, InputArgument::OPTIONAL, 'Path to the output file (default: STDOUT)', null)
             ->addOption(self::CLO_TAB, self::CLO_SC_TAB, InputOption::VALUE_REQUIRED, 'String used as base component from the margin (default is the space " ")', ' ');
    }

    /**
     * {@inheritDoc}
     * @see \Symfony\Component\Console\Command\Command
     */
    protected function configure() {
        $this->setName('sqlite:text-simple')
             ->setDescription("Dump the content of a Sqlite LOG container into a simple text file.");
    }

    /**
     * {@inheritDoc}
     * @see \Symfony\Component\Console\Command\Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $inputPath  = $input->getArgument(self::CLA_INPUT);
        $outputPath = $input->getArgument(self::CLA_OUTPUT);
        $delta      = $input->getOption(self::CLO_TAB);

        if (is_null($inputPath)) {
            throw new \Exception("The mandatory argument that represents the path to the LOG container is missing.");
        }

        $logContainer = new Sqlite(array(Sqlite::CONF_KEY_DB_PATH => $inputPath, Sqlite::CONF_KEY_DB_OVERRIDE => false));

        $dumper = new Dumper($logContainer);
        $dumper->setIndentationStep($delta);
        if (! is_null($outputPath)) {
            $dumper->setOutputFilePath($outputPath);
        }

        $dumper->dump();
    }
}