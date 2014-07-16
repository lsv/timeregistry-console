<?php
namespace Timeregistry\Time;

use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Timeregistry\Command;

class WorkHourCommand extends Command
{

    protected function configure()
    {
        $this
            ->setName('time:workhour')
            ->setAliases(array('workhour'))
            ->setDescription('See your working hours')
            ->addOption(
                'from',
                'f',
                InputOption::VALUE_OPTIONAL,
                'From which date do your want? (including) ' . $this->dateCompatible,
                'now'
            )
            ->addOption(
                'to',
                't',
                InputOption::VALUE_OPTIONAL,
                'To which date do your want? (including) ' . $this->dateCompatible,
                'now'
            )
            ->addOption(
                'dateformat',
                null,
                InputOption::VALUE_OPTIONAL,
                'Format the date output',
                'D d F Y'
            )
            ->addOption(
                'tableformat',
                null,
                InputOption::VALUE_OPTIONAL,
                'Table format output, 0 = Default, 1 = Borderless, 2 = Compact',
                0
            )
            ->addOption(
                'skipemptydays',
                's',
                InputOption::VALUE_OPTIONAL,
                'Skip day(s) without any working time',
                false
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->setApiKey($output);
        $this->checkDateCompatible($output, $input->getOption('from'));
        $this->checkDateCompatible($output, $input->getOption('to'));

        $data = $this->getRequest($output, 'workhour', array(
            'from' => $input->getOption('from'),
            'to' => $input->getOption('to')
        ));
               
        if (isset($data['days'])) {
            foreach ($data['days'] as $day => $d) {
                if (isset($d['tasks']) && $d['tasks']) {
                    /** @var \Symfony\Component\Console\Helper\TableHelper $table */
                    $output->writeln(array(
                        '',
                        '<info>Working hours for: ' . date_create($day)->format($input->getOption('dateformat')) . '</info>',
                        '',
                    ));
                    $table = $this->getHelperSet()->get('table');
                    $table
                        ->setLayout((int)$input->getOption('tableformat'))
                        ->setHeaders(array('Project', 'Workingtime', 'Minutes', 'Comment', 'Task'))
                        ->setRows($d['tasks'])
                        ->addRows(array(
                            new TableSeparator(),
                            array('<comment>Total</comment>', '<comment>' . $d['totaltime']['minutes'] . '</comment>', '<comment>' . $d['totaltime']['min'] . '</comment>')
                        ))
                        ->render($output)
                    ;
                } else {
                    if (! (bool)$input->getOption('skipemptydays')) {
                        $output->writeln(array(
                            '',
                            '<error>No working hours on ' . date_create($day)->format($input->getOption('dateformat')) . '</error>'
                        ));
                    }
                }
            }
            
            if (count($data['days']) > 1) {
                $output->writeln(array(
                    '',
                    sprintf('<info>Total workingtime for all days: %s (%d minutes)</info>', $data['totaltime']['minutes'], $data['totaltime']['min'])
                ));
            }
            
        }
    }

} 