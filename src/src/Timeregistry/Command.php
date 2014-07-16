<?php
namespace Timeregistry;

use GuzzleHttp\Client;
use Symfony\Component\Console\Command\Command as BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;

abstract class Command extends BaseCommand
{
    
    const DEV = false;

    private $apifilename = '.timeregistry_apikey.key';
    private $dev_apifilename = '.dev_timeregistry_apikey.key';

    private $apiurl = 'http://timelog.scandesigns.dk/api/v2';
    private $dev_apiurl = 'http://localhost:8000/app_dev.php/api/v2';

    private $key;
    private $user;

    private $encryptKey = 'zlcnaklxcnacnksdolvnkacdmnsdolvnsdolvnsoklvnasca';

    protected $dateCompatible = '(should be combitable with http://dk1.php.net/manual/en/datetime.formats.date.php)';

    private function getApiFile()
    {
        $home = getenv('HOME');
        return sprintf('%s/%s', realpath($home), (self::DEV ? $this->dev_apifilename : $this->apifilename));
    }
    
    private function getApiUrl($url)
    {
        return sprintf('%s/%s', (self::DEV ? $this->dev_apiurl : $this->apiurl), $url);
    }

    protected function checkDateCompatible(OutputInterface $output, $date)
    {
        if (date_create($date) === false) {
            $output->writeln(array(
                '<error>Your date input is not valid</error>',
                'You wrote ' . $date
            ));
            exit;
        }
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param $project
     * @return integer|void
     */
    protected function findProject(InputInterface $input, OutputInterface $output, $project)
    {
        if ($project) {
            $searchprojects = $this->getRequest($output, 'project/search/' . urlencode($project));
        } else {
            $searchprojects = $this->getRequest($output, 'project/search');
        }

        /** @var \Symfony\Component\Console\Helper\QuestionHelper $questionHelper */
        $questionHelper = $this->getHelperSet()->get('question');
        switch (count($searchprojects)) {
            case 0:
                $output->writeln(array(
                    '<error>No projects was found - No time added</error>',
                    'Project: <info>' . $project . '</info>',
                ));
                exit;
            case 1:
                $question = new ConfirmationQuestion(
                    'Is this the correct project? <info>' . $searchprojects[0]['name'] . '</info> (Y/n): ',
                    'Y'
                );
                if (!$questionHelper->ask($input, $output, $question)) {
                    exit;
                }
                $p = $searchprojects[0]['name'];
                break;
            default:
                $question = new ChoiceQuestion(
                    '<error>Which project did you mean?</error>',
                    array_column($searchprojects, 'name'),
                    null
                );
                $question->setErrorMessage('Project %s is invalid');
                $p = $questionHelper->ask($input, $output, $question);
                break;
        }
        
        foreach($searchprojects as $search) {
            if ($search['name'] === $p) {
                return $search['id'];
            }
        }
        
        $output->writeln('Could not find project');
        return $this->findProject($input, $output, $project);
        
    }

    protected function setApiKey(OutputInterface $output)
    {
        if (file_exists($this->getApiFile())) {
            $keys = file_get_contents($this->getApiFile());
            list($key, $user) = explode(':', $keys);

            $key = $this->decrypt($key);
            $user = $this->decrypt($user);
            
        } else {
            $output->writeln(array(
                '<error>Could not find api key file</error>',
                '<info>Please run "api:setkey <key> <email>"</info>'
            ));
            exit;
        }

        $this->key = $key;
        $this->user = $user;

        $this->getRequest($output, 'key/check');
    }

    protected function saveApiKey(InputInterface $input, OutputInterface $output)
    {
        $key = $input->getArgument('apikey');
        $user = $input->getArgument('user');
        if ($key) {
            $this->key = $key;
            $this->user = $user;
            $this->getRequest($output, 'key/check');
            file_put_contents(
                $this->getApiFile(),
                sprintf('%s:%s', $this->encrypt($key), $this->encrypt($user))
            );
        }
    }

    private function request(OutputInterface $output, $url, $method, array $extras)
    {
        $client = new Client();
        $request = $client->createRequest($method, $this->getApiUrl($url), array(
            'query' => array_merge(
                array(
                    'key' => $this->key,
                    'user' => $this->user
                ),
                $extras
            )
        ));
        
        /** @var \GuzzleHttp\Message\ResponseInterface|void $response */
        $response = $client->send($request);

        $json = $response->json();
        if ($json['status'] != 'OK') {
            $output->writeln('<error>' . $json['msg'] . '</error>');
            exit;
        }

        return $json['msg'];

    }

    protected function getRequest(OutputInterface $output, $url, array $extras = array())
    {
        return $this->request($output, $url, 'GET', $extras);
    }

    protected function postRequest(OutputInterface $output, $url, array $extras = array())
    {
        $json = $this->request($output, $url, 'POST', $extras);
        $output->writeln($json);
        exit;
    }

    private function encrypt($string)
    {
        return base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($this->encryptKey), $string, MCRYPT_MODE_CBC, md5(md5($this->encryptKey))));
    }

    private function decrypt($encrypted)
    {
        return rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($this->encryptKey), base64_decode($encrypted), MCRYPT_MODE_CBC, md5(md5($this->encryptKey))), "\0");
    }

}