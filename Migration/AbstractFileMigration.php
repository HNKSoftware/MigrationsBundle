<?php


namespace Hnk\MigrationsBundle\Migration;


use Doctrine\DBAL\Migrations\AbstractMigration;

abstract class AbstractFileMigration extends AbstractMigration
{
    const QUERY_SEPARATOR = '--HNKQS--';

    abstract protected function loadFile($path);

    protected function handleFile($path)
    {
        if (!file_exists($path)) {
            throw new \Exception(sprintf('File %s does not exist', $path));
        }

        $migration = file_get_contents($path);

        $queries = explode(self::QUERY_SEPARATOR, $migration);

        if ($queries) {
            foreach ($queries as $query) {
                $query = trim($query);

                if ($query) {
                    $this->addSql($query);
                }
            }
        }
    }
}