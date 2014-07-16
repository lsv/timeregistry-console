<?php

class versionNumber extends Task
{

    public function main()
    {
        $versionFile = __DIR__ . '/../version.txt';
        $version = file_get_contents($versionFile);
        list($major, $minor, $release) = explode('.', $version);
        $newversion = sprintf('%s.%s.%s', $major, $minor, ++$release);
        file_put_contents($versionFile, $newversion);
        $this->getProject()->setProperty('version', $newversion);
    }
}