<?php
namespace Timeregistry;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class SelfupdateCommand extends Command
{

    const PHARMODE = false;

    protected function configure()
    {
        $this
            ->setName('selfupdate')
            ->setDescription('Update it self')
        ;
    }

    public function isEnabled()
    {
        return self::PHARMODE;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $localFilename = realpath($_SERVER['argv'][0]) ?: $_SERVER['argv'][0];
        $tempFilename = dirname($localFilename) . '/' . basename($localFilename, '.phar').'-temp.phar';

        // check for permissions in local filesystem before start connection process
        if (!is_writable($tempDirectory = dirname($tempFilename))) {
            $output->write('Selfupdater update failed: the "' . $tempDirectory . '" directory is not writable');
            exit;
        }

        if (!is_writable($localFilename)) {
            $output->write('Selfupdater update failed: the "' . $localFilename . '" file could not be written');
        }

        $fs = new Filesystem();
        $remoteFilename = 'https://raw.githubusercontent.com/lsv/timeregistry-console/master/build/timeregistry.phar';
        $latest = 'https://raw.githubusercontent.com/lsv/timeregistry-console/master/version.txt';

        $latestVersion = file_get_contents($latest);

        switch (version_compare($this->getApplication()->getVersion(), $latestVersion)) {
            case -1:
                $output->writeln('<info>You are using the a newer version of "Timelog" version. SWEEET</info>');
                break;
            case 0:
                $output->writeln('<info>You are using the latest "Timelog" version.</info>');
                break;
            case 1:
                $output->writeln(sprintf("Updating to version <info>%s</info>.", $latestVersion));

                $fs->copy($remoteFilename, $tempFilename, true);
                if ($fs->exists($remoteFilename)) {
                    $output->writeln('<error>The download of the new "Timelog" version failed for an unexpected reason');
                    exit;
                }

                try {
                    \error_reporting(E_ALL); // supress notices
                    $fs->chmod($tempFilename, 777, 0777);
                    $phar = new \Phar($tempFilename);
                    unset($phar);
                    $fs->rename($tempFilename, $localFilename);
                    $output->writeln('<info>Successfully updated "Timelog"</info>');
                } catch (\Exception $e) {
                    @unlink($tempFilename);
                    if (!$e instanceof \UnexpectedValueException && !$e instanceof \PharException) {
                        throw $e;
                    }
                    $output->writeln('<error>The download is corrupted ('.$e->getMessage().').</error>');
                    $output->writeln('<error>Please re-run the selfupdate command to try again.</error>');
                }
                break;
        }
    }

} 