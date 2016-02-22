<?php

namespace Hnk\MigrationsBundle\Exceptions;

class SqlFileAlreadyExistsException extends \Exception
{
    /**
     * @var string
     */
    private $sqlFilePath;

    /**
     * SqlFileAlreadyExistsException constructor.
     * @param string $sqlFilePath
     * @param int $message
     * @param int $code
     * @param \Exception $previous
     */
    public function __construct($sqlFilePath, $message, $code = 0, \Exception $previous = null)
    {
        $this->sqlFilePath = $sqlFilePath;
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return string
     */
    public function getSqlFilePath()
    {
        return $this->sqlFilePath;
    }

}