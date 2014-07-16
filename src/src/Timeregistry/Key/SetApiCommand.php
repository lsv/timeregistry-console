<?php
namespace Timeregistry\Key;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Timeregistry\Command;

class SetApiCommand extends Command
{

    protected function configure()
    {
        $this
            ->setName('api:setkey')
            ->setDescription('Add your email and api key to the registry')
            ->addArgument(
                'apikey',
                InputArgument::REQUIRED,
                'Your Api Key'
            )
            ->addArgument(
                'user',
                InputArgument::REQUIRED,
                'Your email'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->saveApiKey($input, $output);
        $output->writeln('<info>Key is set</info>');
    }

} 