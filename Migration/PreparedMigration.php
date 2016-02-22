<?php

namespace Hnk\MigrationsBundle\Migration;

use Hnk\MigrationsBundle\Exceptions\SqlFileAlreadyExistsException;
use Hnk\MigrationsBundle\Service\PreparedMigrationHandler;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class PreparedMigration extends AbstractFileMigration implements ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    protected function loadFile($path)
    {
        $fileName = $this->getFileName($path);

        try {
            $sqlFilePath = $this->getPreparedMigrationHandler()->prepareFile($fileName);
        } catch (SqlFileAlreadyExistsException $exception) {
            $sqlFilePath = $exception->getSqlFilePath();
        } catch (\Exception $exception) {
            throw $exception;
        }

        $this->handleFile($sqlFilePath);
    }

    protected function getTemplateDirectoryName()
    {
        return $this->container->getParameter('hnk_migrations.generate_command.template_directory_name');
    }

    /**
     * @return PreparedMigrationHandler
     */
    protected function getPreparedMigrationHandler()
    {
        return $this->container->get('hnk_migrations.prepared_migration_handler');
    }

    protected function getFileName($path)
    {
        return basename($path);
    }
}