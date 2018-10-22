<?php
/**
 * Copyright © MageKey. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageKey\MigrationSql\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

use MageKey\MigrationSql\Model\Migration\Filesystem;

class MigrationCreateCommand extends Command
{
    /**
     * Arguments
     */
    const INPUT_ARGUMENT_NAME = 'name';

    const INPUT_OPTION_MODULE = 'module';

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @param Filesystem $filesystem
     */
    public function __construct(
        Filesystem $filesystem
    ) {
        $this->filesystem = $filesystem;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('migration:create')
            ->setDescription('Create migration script.')
            ->setDefinition([
                new InputArgument(
                    self::INPUT_ARGUMENT_NAME,
                    InputArgument::OPTIONAL,
                    'Migration name'
                ),
                new InputOption(
                    self::INPUT_OPTION_MODULE,
                    'm',
                    InputOption::VALUE_OPTIONAL,
                    'Module Name [Vendor_Module]'
                )
            ]);

        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $file = $this->filesystem->getMigrationFile(
            $input->getArgument(self::INPUT_ARGUMENT_NAME),
            $input->getOption(self::INPUT_OPTION_MODULE),
            true
        );

        $output->writeln('<info>Migration created:</info> <comment>' . $file . '</comment>');
    }
}
