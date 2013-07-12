<?php
namespace Codemitte\Bundle\SpawnFcgiBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

/**
 * Description of Restart
 *
 * @author joshi
 */
class Restart extends Start
{
    protected function configure()
    {
        parent::configure();
        
        $this
            ->setName('restart')
            ->setDescription('Restarts the fcgi daemon');
        
    }
    
    protected function execute(InputInterface $input, OutputInterface $output) 
    {
        throw new \ErrorException('Not yet implemented!');
    }   
}
