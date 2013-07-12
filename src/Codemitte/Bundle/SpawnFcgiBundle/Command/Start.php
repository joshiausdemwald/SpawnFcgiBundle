<?php
namespace Codemitte\Bundle\SpawnFcgiBundle\Command;

use \RuntimeException;
use \BadFunctionCallException;

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
        $process = new Process('command -v ' . escapeshellarg($input->getOption('spawn-fcgi-binary')));
        
        $process->run();

        // CHECK FOR spawn-fcgi EXECUTABLE
        if( ! $process->isSuccessful() || null === $process->getOutput())
        {
            throw new BadFunctionCallException('spawn-fcgi command could not be found on your system. Error: "' . $process->getErrorOutput() . '"');
        }
    }
    
    protected function configure()
    {
        parent::configure();
        
        $this
            ->setName('start')
            ->setDescription('Starts the fcgi daemon');
        
        $this->addOption('spawn-fcgi-binary', 'spawn-fcgi', InputOption::VALUE_REQUIRED, 'Path to the SPAWN-FCGI executable (usually shipped with lighty, default location /usr/bin).', $this->getContainer()->getParameter('spawn_fcgi_binary'));
        $this->addOption('cgi-program', 'cgi', InputOption::VALUE_REQUIRED, 'Path to the program that should be spawned.', '/usr/bin/php-cgi', $this->getContainer()->getParameter('cgi_program'));
        $this->addOption('fcgi-socket-path', 'fcgi-socket', InputOption::VALUE_REQUIRED, 'Path to fcgi process Socket.', '/tmp/test.int.socket', $this->getContainer()->getParameter('fcgi_socket_path'));
        $this->addOption('fcgi-user', 'user', InputOption::VALUE_REQUIRED, 'The username that spawns the fcgi process.', $this->getContainer()->get('fcgi_user'));
        $this->addOption('fcgi-group', 'group', InputOption::VALUE_REQUIRED, 'The groupname that spawns the fcgi process.', $this->getContainer()->get('fcgi_group'));
        $this->addOption('allowed-env', 'env', InputOption::VALUE_OPTIONAL, 'The environment variables to pass to the fcgi handler.', null);
        $this->addOption('php-additional-ini-dir', 'phprc', InputOption::VALUE_OPTIONAL, 'Path to additional folder to search ini files from. PHP Process specific setting.', null);
        $this->addOption('php-fcgi-children', 'fcgi-children',  InputOption::VALUE_REQUIRED, 'PHP Process specific setting.', 5);
        $this->addOption('php-fcgi-max-requests', 'max-requests',  InputOption::VALUE_REQUIRED, 'PHP Process specific setting.', 1000);
        $this->addOption('fcgi-webserver-address', 'webserver address', InputOption::VALUE_REQUIRED, 'The fcgi daemons webserver address.', '127.0.0.1');
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
        $processBuilder = new ProcessBuilder(array(
            $input->getOption('spawn-fcgi-binary'),
            '-s' . $input->getOption('fcgi-socket-path'),
            "-f{$input->getOption('cgi-program')}",
            "-u{$input->getOption('fcgi-user')}",
            "-g{$input->getOption('fcgi-group')}",
            "-C{$input->getOption('php-fcgi-children')}"
        ));

        $allowedEnv = array_merge(
                explode(' ', 'PATH USER'),
                explode(' ', $input->getOption('allowed-env')),
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

        $processBuilder->setEnv('PHP_FCGI_MAX_REQUESTS', $input->getOption('php-fcgi-max-requests'));
        $processBuilder->setEnv('FCGI_WEB_SERVER_ADDRS', $input->getOption('fcgi-webserver-address'));
        $processBuilder->setEnv('PHPRC', $input->getOption('php-additional-ini-dir'));

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