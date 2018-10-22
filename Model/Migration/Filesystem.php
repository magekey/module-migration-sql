<?php
/**
 * Copyright © MageKey. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageKey\MigrationSql\Model\Migration;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Module\Dir as ModuleDir;
use Magento\Framework\Module\ModuleListInterface;
//use Magento\Framework\Filesystem\Directory\WriteFactory as DirectoryWriteFactory;
use Magento\Framework\Config\FileIterator;
use Magento\Framework\Config\FileIteratorFactory;

class Filesystem
{
    /**
     * Migration directory name
     */
    const MIGRATION_DIR = 'migration_sql';

    /**
     * Migration extension
     */
    const MIGRATION_EXT = '.sql';

    /**
     * Migration name prefix
     */
    const MIGRATION_NAME_PREFIX = 'migration_';

    /**
     * Migration extension
     */
    const MIGRATION_MODULE_FILES_PATTERN = self::MIGRATION_DIR . '.sql';

    /**
     * Migration combined prefix
     */
    const MIGRATION_COMBINED_PREFIX = 'migration_combined_';

    /**
     * @var \Magento\Framework\Filesystem\Directory\Write
     */
    protected $directory;

    /**
     * @var ModuleDir
     */
    protected $moduleDir;

    /**
     * @var ModuleListInterface
     */
    protected $modulesList;

    /**
     * @var FileIteratorFactory
     */
    protected $fileIteratorFactory;

    /**
     * @param \Magento\Framework\Filesystem $filesystem
     * @param ModuleDir $moduleDir
     * @param ModuleListInterface $modulesList
     * @param FileIteratorFactory $fileIteratorFactory
     */
    public function __construct(
        \Magento\Framework\Filesystem $filesystem,
        ModuleDir $moduleDir,
        ModuleListInterface $modulesList,
        FileIteratorFactory $fileIteratorFactory
    ) {
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);
        $this->moduleDir = $moduleDir;
        $this->modulesList = $modulesList;
        $this->fileIteratorFactory = $fileIteratorFactory;
    }

    /**
     * Get directory write object
     *
     * @return \Magento\Framework\Filesystem\Directory\Write
     */
    public function getDirectory()
    {
        return $this->directory;
    }

    /**
     * Get migration file
     *
     * @param string|null $name
     * @param string|null $module
     * @return string
     * @throws \InvalidArgumentException
     */
    public function getMigrationFile($name = null, $module = null, $forceCreate = false)
    {
        $file = $this->getMigrationPath($module)
            . DIRECTORY_SEPARATOR
            . $this->getMigrationName($name)
            . self::MIGRATION_EXT;

        if ($forceCreate && !is_file($file)) {
            $this->directory->writeFile($file, "");
        }

        return $file;
    }

    /**
     * Get migration path
     *
     * @param string|null $module
     * @param bool $relative
     * @return string
     * @throws \InvalidArgumentException
     */
    public function getMigrationPath($module = null, $relative = false)
    {
        if ($module) {
            $path = $this->moduleDir->getDir($module);
        } else {
            $path = $this->directory->getAbsolutePath(DirectoryList::VAR_DIR);
        }
        $path .=  DIRECTORY_SEPARATOR . self::MIGRATION_DIR;

        return $relative ? $this->directory->getRelativePath($path) : $path;
    }

    /**
     * Get migration name
     *
     * @param string|null $name
     * @return string
     */
    public function getMigrationName($name = null)
    {
        if (!$name) {
            $name = time();
        }

        return self::MIGRATION_NAME_PREFIX . $name;
    }

    /**
     * Get migration setup paths
     *
     * @return array
     */
    public function getMigrationSetupPaths()
    {
        $paths = [];

        foreach ($this->modulesList->getNames() as $moduleName) {
            $paths[] = $this->getMigrationPath($moduleName, true);
        }
        $paths[] = $this->getMigrationPath(null, true);

        return $paths;
    }

    /**
     * Get migration setup files
     *
     * @return FileIterator
     */
    public function getMigrationSetupFiles()
    {
        $files = [];
        $searchPattern = '/' . self::MIGRATION_NAME_PREFIX . '*' . self::MIGRATION_EXT;
        $paths = $this->getMigrationSetupPaths();

        foreach ($paths as $path) {
            $foundFiles = $this->directory->search($path . $searchPattern);
            foreach ($foundFiles as $file) {
                $files[] = $this->directory->getAbsolutePath($file);
            }
        }

        return $this->fileIteratorFactory->create($files);
    }

    /**
     * Crate migration combined file
     *
     * @param string $content
     * @return string
     */
    public function createMigrationCombinedFile($content)
    {
        $tmpFile = tempnam(sys_get_temp_dir(), self::MIGRATION_COMBINED_PREFIX);
        @file_put_contents($tmpFile, $content);

        return $tmpFile;
    }
}
