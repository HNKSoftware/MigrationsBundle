<?php

namespace Hnk\MigrationsBundle\Command;

use Hnk\MigrationsBundle\Service\PreparedMigrationHandler;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class PrepareCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('hnk:migrations:prepare')
            ->setDescription('Creates sql migration files from templates')
            ->addOption('ver', null, InputOption::VALUE_OPTIONAL, 'If set, only migration with this version will be prepared')
            ->addOption('override', null, InputOption::VALUE_OPTIONAL, 'If set, command will override existing sql migrations', 'false')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $version = $input->getOption('ver');
        $override = ('true' === $input->getOption('override'));

        $files = [];
        if ($version) {
            $files[] = sprintf('%s/version_%s_up.sql', $this->getTemplateDirectoryPath(), $version);
            $files[] = sprintf('%s/version_%s_down.sql', $this->getTemplateDirectoryPath(), $version);
        } else {
            $finder = new Finder();
            /** @var SplFileInfo $file */
            foreach ($finder->files()->in($this->getTemplateDirectoryPath())->getIterator() as $file) {
                $files[] =  $file->getRealPath();
            }
        }

        foreach ($files as $file) {
            $sqlFile = $this->getPreparedMigrationHandler()->prepareFile(basename($file), $override);
            $output->writeln(sprintf('Prepared file "<info>%s</info>"', $sqlFile));
        }

    }

    protected function getTemplateDirectoryPath()
    {
        return sprintf(
            '%s/%s',
            rtrim($this->getContainer()->getParameter('doctrine_migrations.dir_name'), '/'),
            $this->getContainer()->getParameter('hnk_migrations.template_directory_name')
        );
    }

    /**
     * @return PreparedMigrationHandler
     */
    protected function getPreparedMigrationHandler()
    {
        return $this->getContainer()->get('hnk_migrations.prepared_migration_handler');
    }
}