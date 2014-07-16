<?php
/**
 * Created by PhpStorm.
 * User: lsv
 * Date: 5/20/14
 * Time: 3:22 AM
 */

namespace Timeregistry;

use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;

class Application extends BaseApplication
{

    public function __construct($name = 'UNKNOWN', $version = 'UNKNOWN')
    {
        parent::__construct($name, $version);
    }
    
    protected function getDefaultInputDefinition()
    {
        return new InputDefinition(array(
            new InputArgument('command', InputArgument::REQUIRED, 'The command to execute'),
            new InputOption('--help',           '-h', InputOption::VALUE_NONE, 'Display this help message.'),
        ));
    }
    
} 