<?php
namespace Codemitte\Bundle\SpawnFcgiBundle\Command;

use \RuntimeException;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;

class Start extends SpawnFcgiCommand
{
    protected function initialize(InputInterface $input, OutputInterface $output)
    {

    }
    
    protected function configure()
    {
        parent::configure();
        
        $this
            ->setName('start')
            ->setDescription('Starts the fcgi daemon');
        
        $this->addOption('spawn-fcgi-binary', 'spawn-fcgi', InputOption::VALUE_OPTIONAL, 'Path to the SPAWN-FCGI executable (usually shipped with lighty.)');
        $this->addOption('cgi-program', 'cgi', InputOption::VALUE_OPTIONAL, 'Path to the program that should be spawned.');
        $this->addOption('fcgi-socket-path', 'null', InputOption::VALUE_OPTIONAL, 'Path to fcgi process Socket.');
        $this->addOption('fcgi-user', 'user', InputOption::VALUE_OPTIONAL, 'The username that spawns the fcgi process.');
        $this->addOption('fcgi-group', 'group', InputOption::VALUE_OPTIONAL, 'The groupname that spawns the fcgi process.');
        $this->addOption('allowed-env', 'env', InputOption::VALUE_OPTIONAL, 'The environment variables to pass to the fcgi handler.');
        $this->addOption('php-additional-ini-dir', 'phprc', InputOption::VALUE_OPTIONAL, 'Path to additional folder to search ini files from. PHP Process specific setting.');
        $this->addOption('php-fcgi-children', 'fcgi-children',  InputOption::VALUE_OPTIONAL, 'PHP Process specific setting.');
        $this->addOption('php-fcgi-max-requests', 'max-requests',  InputOption::VALUE_OPTIONAL, 'PHP Process specific setting.');
        $this->addOption('fcgi-webserver-address', 'webserver address', InputOption::VALUE_OPTIONAL, 'The fcgi daemons webserver address.');
    }

    /**
     * Execute -- start process and store PID in a recoverable way.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws \RuntimeException
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output) 
    {
        $spawn_fcgi_binary = $input->getOption('spawn-fcgi-binary') ? $input->getOption('spawn-fcgi-binary') : $this->getContainer()->getParameter('codemitte_spawn_fcgi.spawn_fcgi_binary');
        $cgi_program = $input->getOption('cgi-program') ? $input->getOption('cgi-program') : $this->getContainer()->getParameter('codemitte_spawn_fcgi.cgi_program');
        $fcgi_socket_path = $input->getOption('fcgi-socket-path') ? $input->getOption('fcgi-socket-path') : $this->getContainer()->getParameter('codemitte_spawn_fcgi.fcgi_socket_path');
        $fcgi_user = $input->getOption('fcgi-user') ? $input->getOption('fcgi-user') : $this->getContainer()->getParameter('codemitte_spawn_fcgi.fcgi_user');
        $fcgi_group = $input->getOption('fcgi-group') ? $input->getOption('fcgi-group') : $this->getContainer()->getParameter('codemitte_spawn_fcgi.fcgi_group');
        $allowed_env = $input->getOption('allowed-env') ? $input->getOption('allowed-env') : $this->getContainer()->getParameter('codemitte_spawn_fcgi.allowed_env');
        $php_additional_ini_dir = $input->getOption('php-additional-ini-dir') ? $input->getOption('php-additional-ini-dir') : $this->getContainer()->getParameter('codemitte_spawn_fcgi.php_additional_ini_dir');
        $php_fcgi_children = $input->getOption('php-fcgi-children') ? $input->getOption('php-fcgi-children') : $this->getContainer()->getParameter('codemitte_spawn_fcgi.php_fcgi_children');
        $php_fcgi_max_requests = $input->getOption('php-fcgi-max-requests') ? $input->getOption('php-fcgi-max-requests') : $this->getContainer()->getParameter('codemitte_spawn_fcgi.php_fcgi_max_requests');
        $fcgi_webserver_address = $input->getOption('fcgi-webserver-address') ? $input->getOption('fcgi-webserver-address') : $this->getContainer()->getParameter('codemitte_spawn_fcgi.fcgi_webserver_address');

        $processBuilder = new ProcessBuilder(array(
            $spawn_fcgi_binary,
            '-s' . $fcgi_socket_path,
            "-f{$cgi_program}",
            "-u{$fcgi_user}",
            "-g{$fcgi_group}",
            "-C{$php_fcgi_children}"
        ));

        $allowedEnv = array_merge(
                explode(' ', 'PATH USER'),
                explode(' ', $allowed_env),
                explode(' ', 'PHP_FCGI_MAX_REQUESTS FCGI_WEB_SERVER_ADDRS PHPRC')
        ); 
        
        $e = array();
        
        foreach($allowedEnv as $env)
        {
            if(is_string($env) && strlen($env) > 0)
            {
                $processBuilder->setEnv($env, '=$(eval echo "$' . $env . '")');

                $e[] = $env . '=$(eval echo "$' . $env . '")';
            }
        }

        $processBuilder->setEnv('PHP_FCGI_MAX_REQUESTS', $php_fcgi_max_requests);
        $processBuilder->setEnv('FCGI_WEB_SERVER_ADDRS', $fcgi_webserver_address);
        $processBuilder->setEnv('PHPRC', $php_additional_ini_dir);

        $process = $processBuilder->getProcess();

        $process->run();

        if($process->isSuccessful())
        {
          if(preg_match('#PID: ([0-9]+?)$#', $process->getOutput(), $result))
          {
              $pid = $result[1];
              file_put_contents('.spawn-fcgi', $pid);
              $output->writeln('FCGI Process ' . $pid . ' started.');
          }
          else
          {
              throw new RuntimeException('Could not retrieve a valid PID.');
          }
        }
        else
        {
            throw new RuntimeException($process->getErrorOutput());
        }
    }
}