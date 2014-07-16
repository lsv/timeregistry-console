<?php
namespace Timeregistry;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SelfupdateCommand extends Command
{

    protected function configure()
    {
        $this
            ->setName('selfupdate')
            ->setDescription('Update it self')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $localFilename = realpath($_SERVER['argv'][0]) ?: $_SERVER['argv'][0];
        $tempFilename = dirname($localFilename) . '/' . basename($localFilename, '.phar').'-temp.phar';

        var_dump($localFilename, $tempFilename);

        // check for permissions in local filesystem before start connection process
        if (!is_writable($tempDirectory = dirname($tempFilename))) {
            $output->write('Selfupdater update failed: the "' . $tempDirectory . '" directory is not writable');
            exit;
        }

        if (!is_writable($localFilename)) {
            $output->write('Selfupdater update failed: the "' . $localFilename . '" file could not be written');
        }

        $io = new ConsoleIO($input, $output, $this->getHelperSet());
        $rfs = new RemoteFilesystem($io);

        $loadUnstable = $input->getOption('unstable');
        if ($loadUnstable) {
            $versionTxtUrl = 'https://raw.githubusercontent.com/netz98/n98-magerun/develop/version.txt';
            $remoteFilename = 'https://raw.githubusercontent.com/netz98/n98-magerun/develop/n98-magerun.phar';
        } else {
            $versionTxtUrl = 'https://raw.githubusercontent.com/netz98/n98-magerun/master/version.txt';
            $remoteFilename = 'https://raw.githubusercontent.com/netz98/n98-magerun/master/n98-magerun.phar';
        }

        $latest = trim($rfs->getContents('raw.githubusercontent.com', $versionTxtUrl, false));

        if ($this->getApplication()->getVersion() !== $latest || $loadUnstable) {
            $output->writeln(sprintf("Updating to version <info>%s</info>.", $latest));

            $rfs->copy('raw.github.com', $remoteFilename, $tempFilename);

            if (!file_exists($tempFilename)) {
                $output->writeln('<error>The download of the new n98-magerun version failed for an unexpected reason');

                return 1;
            }

            try {
                \error_reporting(E_ALL); // supress notices

                @chmod($tempFilename, 0777 & ~umask());
                // test the phar validity
                $phar = new \Phar($tempFilename);
                // free the variable to unlock the file
                unset($phar);
                @rename($tempFilename, $localFilename);
                $output->writeln('<info>Successfully updated n98-magerun</info>');

                if ($loadUnstable) {
                    $changeLogContent = $rfs->getContents(
                        'raw.github.com',
                        'https://raw.github.com/netz98/n98-magerun/develop/changes.txt',
                        false
                    );
                } else {
                    $changeLogContent = $rfs->getContents(
                        'raw.github.com',
                        'https://raw.github.com/netz98/n98-magerun/master/changes.txt',
                        false
                    );
                }

                if ($changeLogContent) {
                    $output->writeln($changeLogContent);
                }

                if ($loadUnstable) {
                    $unstableFooterMessage = <<<UNSTABLE_FOOTER
<comment>
!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
!! DEVELOPMENT VERSION. DO NOT USE IN PRODUCTION !!
!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
</comment>
UNSTABLE_FOOTER;
                    $output->writeln($unstableFooterMessage);
                }

                $this->_exit();
            } catch (\Exception $e) {
                @unlink($tempFilename);
                if (!$e instanceof \UnexpectedValueException && !$e instanceof \PharException) {
                    throw $e;
                }
                $output->writeln('<error>The download is corrupted ('.$e->getMessage().').</error>');
                $output->writeln('<error>Please re-run the self-update command to try again.</error>');
            }
        } else {
            $output->writeln("<info>You are using the latest n98-magerun version.</info>");
        }
        */
    }

} 