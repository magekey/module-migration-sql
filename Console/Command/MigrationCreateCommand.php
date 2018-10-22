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

use MageKey\MigrationSql\Model\Migration\Filesystem;

class MigrationCreateCommand extends Command
{
    /**
     * Arguments
     */
    const INPUT_KEY_NAME = 'name';

    const INPUT_KEY_MODULE = 'module';

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
                    self::INPUT_KEY_NAME,
                    InputArgument::OPTIONAL,
                    'Migration name'
                ),
                new InputArgument(
                    self::INPUT_KEY_MODULE,
                    InputArgument::OPTIONAL,
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
            $input->getArgument(self::INPUT_KEY_NAME),
            $input->getArgument(self::INPUT_KEY_MODULE),
            true
        );

        $output->writeln('<info>Migration created:</info> <comment>' . $file . '</comment>');
    }
}
