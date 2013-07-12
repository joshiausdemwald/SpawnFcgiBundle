<?php
namespace Codemitte\Command\Bundle\SpawnFcgiBundle\Command;

use \RuntimeException;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\Process;

/**
 * Description of Stop
 *
 * @author joshi
 */
class Stop extends SpawnFcgiCommand
{
    protected function configure()
    {
        parent::configure();
        
        $this
            ->setName('stop')
            ->setDescription('Stops the fcgi daemon');
    }
    
    protected function execute(InputInterface $input, OutputInterface $output) 
    {
        if(!file_exists('.spawn-fcgi'))
        {
            throw new RuntimeException('File .spawn-fcgi not found. Did you actually start the fcgi daemon?');
        }
        
        $pid = file_get_contents('.spawn-fcgi');
        
        $process = new Process("kill $pid");
        
        $process->run();
        
        if($process->isSuccessful())
        {
            unlink('.spawn-fcgi');
            
            $output->writeln('FCGI Process ' . $pid . ' stopped.');
        }
        else
        {
            throw new RuntimeException($process->getErrorOutput());
        }
    }
}