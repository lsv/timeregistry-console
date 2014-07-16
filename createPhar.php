<?php
require 'vendor/autoload.php';

function getStub() {
    return $stub = <<<'EOF'
#!/usr/bin/env php
<?php
Phar::mapPhar('timeregistry.phar');
require 'phar://timeregistry.phar/src/index.php';
__HALT_COMPILER();
EOF;
}

$pharname = 'timeregistry.phar';
$srcRoot = "./src";
$buildRoot = "./build";

unlink($buildRoot . '/' . $pharname);

$phar = new Phar($buildRoot . "/" . $pharname, 0, $pharname);
$phar->setSignatureAlgorithm(\Phar::SHA1);
$phar->startBuffering();

$finder = new \Symfony\Component\Finder\Finder();
$finder->files()
    ->ignoreVCS(true)
    ->name('*.php')
    ->in($srcRoot)
;

foreach($finder as $file) {
    $thisdir = __DIR__;
    $path = str_replace($thisdir . '/', '', $file->getRealPath());
    $content = file_get_contents($file->getRealPath());
    $phar->addFromString($path, $content);
}

$phar->setStub(getStub());
$phar->stopBuffering();
unset($phar);