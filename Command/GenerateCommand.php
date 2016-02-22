<?php

namespace Hnk\MigrationsBundle\Command;

use Doctrine\Bundle\MigrationsBundle\Command\MigrationsGenerateDoctrineCommand;
use Hnk\MigrationsBundle\Migration\FileMigration;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Doctrine\DBAL\Migrations\Configuration\Configuration;
use Doctrine\DBAL\Migrations\Tools\Console\Helper\MigrationDirectoryHelper;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateCommand extends MigrationsGenerateDoctrineCommand
{
    const MODE_SQL = 'sql';
    const MODE_TEMPLATE = 'template';

    private static $_template =
        '<?php

namespace <namespace>;

use Doctrine\DBAL\Schema\Schema;
use Hnk\MigrationsBundle\Migration\<migrationClass>;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
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
    private $sqlDirName;

    /**
     * @var string
     */
    private $templateDirName;

    /**
     * @var string
     */
    private $defaultMode;

    /**
     * @var string
     */
    private $mode;

    /**
     * GenerateCommand constructor.
     * @param string $sqlDirectoryName
     * @param string $templateDirectoryName
     * @param string $defaultMode
     * @internal param array $configuration
     */
    public function __construct($sqlDirectoryName, $templateDirectoryName, $defaultMode)
    {
        $this->sqlDirName = $sqlDirectoryName;
        $this->templateDirName = $templateDirectoryName;
        $this->defaultMode = $defaultMode;

        parent::__construct();
    }

    protected function configure()
    {
        parent::configure();

        $this
            ->setName('hnk:migrations:generate')
            ->addOption('mode', null, InputOption::VALUE_REQUIRED, 'Mode sql will generate sql files, mode template will generate files that will require preparation', self::MODE_SQL)
            ->addOption('db', null, InputOption::VALUE_REQUIRED, 'The database connection to use for this command.')
            ->addOption('em', null, InputOption::VALUE_REQUIRED, 'The entity manager to use for this command.')
            ->addOption('shard', null, InputOption::VALUE_REQUIRED, 'The shard connection to use for this command.');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;
        $this->mode = $input->getOption('mode');

        parent::execute($input, $output);
    }

    protected function generateMigration(Configuration $configuration, InputInterface $input, $version, $up = null, $down = null)
    {
        $migrationDirectoryHelper = new MigrationDirectoryHelper($configuration);
        $dir = $migrationDirectoryHelper->getMigrationDirectory();
        $filesDir = $this->getMigrationFilesDirectory($dir);

        $fileUp = $this->createSqlFile($filesDir, $version, 'up');
        $this->output->writeln(sprintf('Generated new migration file to "<info>%s</info>"', $fileUp));
        $fileDown = $this->createSqlFile($filesDir, $version, 'down');
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


    protected function createSqlFile($dir, $version, $direction)
    {
        $fileName = sprintf('version_%s_%s.sql', $version, $direction);
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
            FileMigration::QUERY_SEPARATOR
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

        $dir .= ($this->mode === self::MODE_TEMPLATE) ? $this->templateDirName : $this->sqlDirName;

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
                $class = 'FileMigration';
                break;
            case self::MODE_TEMPLATE:
                $class = 'PreparedMigration';
                break;
            default:
                throw new \Exception('Invalid mode');
        }

        return $class;
    }
}