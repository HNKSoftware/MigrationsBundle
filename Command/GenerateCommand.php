<?php

namespace Hnk\MigrationsBundle\Command;

use Doctrine\Bundle\MigrationsBundle\Command\MigrationsGenerateDoctrineCommand;
use Doctrine\DBAL\Migrations\Configuration\Configuration;
use Doctrine\DBAL\Migrations\Tools\Console\Helper\MigrationDirectoryHelper;
use Hnk\MigrationsBundle\Configuration as BundleConfiguration;
use Hnk\MigrationsBundle\Migration\AbstractFileMigration;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateCommand extends MigrationsGenerateDoctrineCommand
{
    const MODE_SQL = 'sql';
    const MODE_TEMPLATE = 'template';

    private static $_template =
        '<?php

namespace <namespace>;

use Doctrine\DBAL\Schema\Schema;

class Version<version> extends <migrationClass>
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->loadFile(\'<fileUp>\');
<up>
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->loadFile(\'<fileDown>\');
<down>
    }
}
';

    private static $_sqlTemplate = '<separator>';

    /**
     * @var InputInterface
     */
    private $input;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var string
     */
    private $mode;

    /**
     * @var BundleConfiguration
     */
    private $bundleConfiguration;

    /**
     * GenerateCommand constructor.
     * @param BundleConfiguration $bundleConfiguration
     */
    public function __construct(BundleConfiguration $bundleConfiguration)
    {
        $this->bundleConfiguration = $bundleConfiguration;
        parent::__construct();
    }

    protected function configure()
    {
        parent::configure();

        $this
            ->setName('hnk:migrations:generate')
            ->addOption(
                'mode',
                null,
                InputOption::VALUE_REQUIRED,
                'Mode sql will generate sql files, mode template will generate files that will require preparation',
                $this->bundleConfiguration->getDefaultMode()
            )
            ->addOption(
                'name',
                null,
                InputOption::VALUE_OPTIONAL,
                'Additional name for migration'
            )
            ->addOption('db', null, InputOption::VALUE_REQUIRED, 'The database connection to use for this command.')
            ->addOption('em', null, InputOption::VALUE_REQUIRED, 'The entity manager to use for this command.')
            ->addOption('shard', null, InputOption::VALUE_REQUIRED, 'The shard connection to use for this command.');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;
        $this->mode = $input->getOption('mode'); // todo handle unknown

        parent::execute($input, $output);
    }

    protected function generateMigration(Configuration $configuration, InputInterface $input, $version, $up = null, $down = null)
    {
        if ($input->hasOption('name')) {
            $name = trim(preg_replace('/[^a-zA-Z0-9]+/', '_', $input->getOption('name')), '_');
        }

        $migrationDirectoryHelper = new MigrationDirectoryHelper($configuration);
        $dir = $migrationDirectoryHelper->getMigrationDirectory();
        $filesDir = $this->getMigrationFilesDirectory($dir);

        $fileUp = $this->createSqlFile($filesDir, $version, 'up', $name);
        $this->output->writeln(sprintf('Generated new migration file to "<info>%s</info>"', $fileUp));
        $fileDown = $this->createSqlFile($filesDir, $version, 'down', $name);
        $this->output->writeln(sprintf('Generated new migration file to "<info>%s</info>"', $fileDown));

        $placeHolders = [
            '<namespace>',
            '<version>',
            '<up>',
            '<down>',
            '<fileUp>',
            '<fileDown>',
            '<migrationClass>',
        ];
        $replacements = [
            $configuration->getMigrationsNamespace(),
            $version,
            $up ? "        " . implode("\n        ", explode("\n", $up)) : null,
            $down ? "        " . implode("\n        ", explode("\n", $down)) : null,
            str_replace($dir, '', $fileUp),
            str_replace($dir, '', $fileDown),
            $this->getMigrationClass()
        ];
        $code = str_replace($placeHolders, $replacements, $this->getTemplate());
        $code = preg_replace('/^ +$/m', '', $code);

        $path = $dir . '/Version' . $version . '.php';

        file_put_contents($path, $code);

        return $path;
    }


    protected function createSqlFile($dir, $version, $direction, $name = null)
    {
        $namePart = (null !== $name) ? '_' . $name : '';
        $fileName = sprintf('version_%s_%s%s.sql', $version, $direction, $namePart);
        $filePath = $dir . '/' . $fileName;

        if (file_exists($filePath)) {
            throw new \Exception(sprintf('File %s already exists!', $filePath));
        }

        $placeHolders = [
            '<version>',
            '<direction>',
            '<separator>',
        ];
        $replacements = [
            $version,
            $direction,
            AbstractFileMigration::QUERY_SEPARATOR
        ];
        $template = $this->getSqlTemplate();
        $code = str_replace($placeHolders, $replacements, $template);

        file_put_contents($filePath, $code);

        return $filePath;
    }

    protected function getMigrationFilesDirectory($migrationsDir)
    {
        $dir = rtrim($migrationsDir, '/') . '/';

        if (!file_exists($dir)) {
            throw new \InvalidArgumentException(sprintf('Migrations directory "%s" does not exist.', $dir));
        }

        $dir .= ($this->mode === self::MODE_TEMPLATE) ?
            $this->bundleConfiguration->getTemplateDirectoryName() :
            $this->bundleConfiguration->getSqlDirectoryName();

        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }

        return $dir;
    }

    protected function getCurrentMigrationConfiguration()
    {
        return $this->getMigrationConfiguration($this->input, $this->output);
    }

    protected function getTemplate()
    {
        return self::$_template;
    }

    protected function getSqlTemplate()
    {
        return self::$_sqlTemplate;
    }

    protected function getMigrationClass()
    {
        switch ($this->mode) {
            case self::MODE_SQL:
                $class = $this->bundleConfiguration->getSqlMigrationClass();
                break;
            case self::MODE_TEMPLATE:
                $class = $this->bundleConfiguration->getPreparedMigrationClass();
                break;
            default:
                throw new \Exception('Invalid mode');
        }

        return $class;
    }
}