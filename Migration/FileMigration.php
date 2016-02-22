<?php

namespace Hnk\MigrationsBundle\Migration;

abstract class FileMigration extends AbstractFileMigration
{
    protected function loadFile($path)
    {
        $dir = $this->version->getConfiguration()->getMigrationsDirectory();
        $filePath = $dir . $path;

        $this->handleFile($filePath);
    }
}