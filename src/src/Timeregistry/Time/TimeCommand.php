<?php
namespace Timeregistry\Time;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Timeregistry\Command;

class TimeCommand extends Command
{

    protected function configure()
    {
        $this
            ->setName('time:timelog')
            ->setAliases(array('timelog'))
            ->setDescription('Add time to your timelog')
            ->addArgument(
                'time',
                InputArgument::REQUIRED,
                <<<'EOF'
Your minutes will be translated from the following
<comment>3:20</comment> (will be translated to 3 hours and 20 min)
<comment>1t 20m</comment> (will be translated to 1 hour and 20 min)
<comment>1h 20m</comment> (will be translated to 1 hour and 20 min)
<comment>1t</comment> (will be translated to 1 hour)
<comment>1h</comment> (will be translated to 1 hour)
<comment>20</comment> (will be translated to 20 minutes)
<comment>20m</comment> (will be translated to 20 minutes)
EOF
            )
            ->addArgument(
                'project',
                InputArgument::OPTIONAL,
                'Write which project you want to add the time - if not chosen you will get a list of projects to choose from'
            )
            ->addOption(
                'comment',
                'c',
                InputOption::VALUE_OPTIONAL,
                'Add a comment to the time'
            )
            ->addOption(
                'date',
                'd',
                InputOption::VALUE_OPTIONAL,
                'Set the date ' . $this->dateCompatible,
                'now'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->setApiKey($output);
        $this->checkDateCompatible($output, $input->getOption('date'));
        
        $project = $this->findProject($input, $output, $input->getArgument('project'));
        $time = $input->getArgument('time');
        
        $this->postRequest($output, 'project/addtime/' . $project . '/' . urlencode($time), array(
            'comment' => $input->getOption('comment'),
            'date' => $input->getOption('date')
        ));

    }

} 