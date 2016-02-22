<?php


namespace Hnk\MigrationsBundle\Service;

use Hnk\MigrationsBundle\Configuration as BundleConfiguration;
use Hnk\MigrationsBundle\Exceptions\SqlFileAlreadyExistsException;

class PreparedMigrationHandler
{
    /**
     * @var PlaceHolderTranslator
     */
    private $placeHolderTranslator;
    /**
     * @var BundleConfiguration
     */
    private $bundleConfiguration;

    /**
     * PreparedMigrationHandler constructor.
     * @param BundleConfiguration $bundleConfiguration
     * @param PlaceHolderTranslator $placeHolderTranslator
     */
    public function __construct(BundleConfiguration $bundleConfiguration, PlaceHolderTranslator $placeHolderTranslator)
    {
        $this->bundleConfiguration = $bundleConfiguration;
        $this->placeHolderTranslator = $placeHolderTranslator;
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
        return $this->bundleConfiguration->getMigrationsDirectory() . '/' . $this->bundleConfiguration->getTemplateDirectoryName();
    }

    protected function getSqlDirectoryPath()
    {
        return $this->bundleConfiguration->getMigrationsDirectory() . '/' . $this->bundleConfiguration->getSqlDirectoryName();
    }
}