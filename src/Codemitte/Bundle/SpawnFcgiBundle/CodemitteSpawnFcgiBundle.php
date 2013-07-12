<?php
namespace Codemitte\Bundle\SpawnFcgiBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\Console\Application;
use Codemitte\Bundle\SpawnFcgiBundle\Command\Start;
use Codemitte\Bundle\SpawnFcgiBundle\Command\Stop;
use Codemitte\Bundle\SpawnFcgiBundle\Command\Restart;

/**
 * Description of SpawnFcgiBundle
 *
 * @author joshi
 */
class CodemitteSpawnFcgiBundle extends Bundle
{
    public function registerCommands(Application $app)
    {
        $start      = new Start();
        $stop       = new Stop();
        $restart    = new Restart();

        $start->setName('fcgi:start');
        $stop->setName('fcgi:stop');
        $restart->setName('fcgi:restart');

        $app->addCommands(array(
            $start,
            $stop,
            $restart
        ));
    }
}