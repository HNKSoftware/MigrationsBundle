<?php


namespace Hnk\MigrationsBundle\Service;


use Hnk\MigrationsBundle\Exceptions\SqlFileAlreadyExistsException;

class PreparedMigrationHandler
{
    /**
     * @var string
     */
    private $sqlDirectoryName;

    /**
     * @var string
     */
    private $templateDirectoryName;
    /**
     * @var PlaceHolderTranslator
     */
    private $placeHolderTranslator;

    /**
     * @var string
     */
    private $migrationsDirectory;

    /**
     * PreparedMigrationHandler constructor.
     * @param string $migrationsDirectory
     * @param string $sqlDirectoryName
     * @param string $templateDirectoryName
     * @param PlaceHolderTranslator $placeHolderTranslator
     */
    public function __construct($migrationsDirectory, $sqlDirectoryName, $templateDirectoryName, PlaceHolderTranslator $placeHolderTranslator)
    {
        $this->sqlDirectoryName = $sqlDirectoryName;
        $this->templateDirectoryName = $templateDirectoryName;
        $this->placeHolderTranslator = $placeHolderTranslator;
        $this->migrationsDirectory = $migrationsDirectory;
    }

    /**
     * @param string $fileName
     * @param bool $override
     * @return string
     * @throws SqlFileAlreadyExistsException
     * @throws \Exception
     */
    public function prepareFile($fileName, $override = false)
    {
        $templateFilePath = $this->getTemplateDirectoryPath() . '/' . $fileName;

        if (!file_exists($templateFilePath)) {
            throw new \Exception(sprintf('Template file %s does not exist', $templateFilePath));
        }

        $sqlFilePath = $this->getSqlDirectoryPath() . '/' . $fileName;

        if (file_exists($sqlFilePath) && false === $override) {
            throw new SqlFileAlreadyExistsException(
                $sqlFilePath,
                sprintf('Sql file %s already exists', $sqlFilePath)
            );
        }

        $template = file_get_contents($templateFilePath);

        $code = $this->placeHolderTranslator->replacePlaceholders($template);

        file_put_contents($sqlFilePath, $code);

        return $sqlFilePath;
    }

    protected function getTemplateDirectoryPath()
    {
        return $this->migrationsDirectory . '/' . $this->templateDirectoryName;
    }

    protected function getSqlDirectoryPath()
    {
        return $this->migrationsDirectory . '/' . $this->sqlDirectoryName;
    }
}