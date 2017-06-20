<?php

namespace DTL\Filesystem\Adapter\Git;

use DTL\Filesystem\Domain\Filesystem;
use DTL\Filesystem\Domain\FileList;
use DTL\Filesystem\Domain\FileLocation;
use DTL\Filesystem\Domain\AbsoluteExistingPath;
use DTL\Filesystem\Adapter\Git\GitFileIterator;

class GitFilesystem implements Filesystem
{
    public function fileList(): FileList
    {
        $gitFiles = $this->exec('ls-files');
        $files = [];

        foreach ($gitFiles as $gitFile) {
            $files[] = FileLocation::fromString($gitFile);
        }

        return FileList::fromIterator(new \ArrayIterator($files));
    }

    public function remove(FileLocation $location)
    {
        $this->exec(sprintf('rm -f %s', $location->__toString()));
    }

    public function move(FileLocation $srcPath, FileLocation $destPath)
    {
        $this->exec(sprintf(
            'mv %s %s',
            $srcPath->__toString(),
            $destPath->__toString()
        ));
    }

    private function exec($command)
    {
        exec(sprintf('git %s 2>&1', $command), $output, $return);

        if ($return !== 0) {
            throw new \InvalidArgumentException(sprintf(
                'Could not execute git command "git %s", exit code "%s", output "%s"',
                $command, $return, implode(PHP_EOL, $output)
            ));
        }

        return $output;
    }
}
