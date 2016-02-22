<?php

namespace Hnk\MigrationsBundle;

class Configuration
{
    /**
     * @var string
     */
    private $migrationsDirectory;

    /**
     * @var string
     */
    private $sqlDirectoryName;

    /**
     * @var string
     */
    private $templateDirectoryName;

    /**
     * @var string
     */
    private $defaultMode;

    /**
     * @var array
     */
    private $placeHolders;

    /**
     * @var string
     */
    private $sqlMigrationClass;

    /**
     * @var string
     */
    private $preparedMigrationClass;

    public function __construct(
        $migrationsDirectory,
        $sqlDirectoryName,
        $templateDirectoryName,
        $defaultMode,
        $placeHolders,
        $sqlMigrationClass,
        $preparedMigrationClass
    ) {
        $this->migrationsDirectory = $migrationsDirectory;
        $this->sqlDirectoryName = $sqlDirectoryName;
        $this->templateDirectoryName = $templateDirectoryName;
        $this->defaultMode = $defaultMode;
        $this->placeHolders = $placeHolders;
        $this->sqlMigrationClass = $sqlMigrationClass;
        $this->preparedMigrationClass = $preparedMigrationClass;
    }

    /**
     * @return string
     */
    public function getMigrationsDirectory()
    {
        return $this->migrationsDirectory;
    }

    /**
     * @return string
     */
    public function getSqlDirectoryName()
    {
        return $this->sqlDirectoryName;
    }

    /**
     * @return string
     */
    public function getTemplateDirectoryName()
    {
        return $this->templateDirectoryName;
    }

    /**
     * @return string
     */
    public function getDefaultMode()
    {
        return $this->defaultMode;
    }

    /**
     * @return array
     */
    public function getPlaceHolders()
    {
        return $this->placeHolders;
    }

    /**
     * @return string
     */
    public function getSqlMigrationClass()
    {
        return $this->sqlMigrationClass;
    }

    /**
     * @return string
     */
    public function getPreparedMigrationClass()
    {
        return $this->preparedMigrationClass;
    }
}

