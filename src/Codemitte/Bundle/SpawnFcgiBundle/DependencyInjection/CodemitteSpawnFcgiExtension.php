<?php
/**
 * Copyright (C) 2012 code mitte GmbH - Zeughausstr. 28-38 - 50667 Cologne/Germany
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of
 * this software and associated documentation files (the "Software"), to deal in the
 * Software without restriction, including without limitation the rights to use,
 * copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the
 * Software, and to permit persons to whom the Software is furnished to do so, subject
 * to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED,
 * INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A
 * PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 * HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
 * SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

namespace Codemitte\Bundle\SpawnFcgiBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\Resource\FileResource;

/**
 * CodemitteForceExtension
 *
 * @author Johannes Heinen <johannes.heinen@code-mitte.de>
 * @copyright 2012 code mitte GmbH, Cologne, Germany
 * @package Bundle
 * @subpackage ForceBundle
 */
class CodemitteForceExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        // $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));

        $processor     = new Processor();

        $configuration = new Configuration();

        $config = $processor->process($configuration->getConfigTree(), $configs);

        $container->setParameter('codemitte_spawn_fcgi.fcgi_webserver_address', $config['fcgi_webserver_address']);
        $container->setParameter('codemitte_spawn_fcgi.php_fcgi_max_requests', $config['php_fcgi_max_requests']);
        $container->setParameter('codemitte_spawn_fcgi.php_fcgi_children', $config['php_fcgi_children']);
        $container->setParameter('codemitte_spawn_fcgi.php_additional_ini_dir', $config['php_additional_ini_dir']);
        $container->setParameter('codemitte_spawn_fcgi.allowed_env', $config['allowed_env']);
        $container->setParameter('codemitte_spawn_fcgi.spawn-fcgi-binary', $config['spawn-fcgi-binary']);
        $container->setParameter('codemitte_spawn_fcgi.cgi_program', $config['cgi_program']);
        $container->setParameter('codemitte_spawn_fcgi.fcgi_socket_path', $config['fcgi_socket_path']);
        $container->setParameter('codemitte_spawn_fcgi.fcgi_user', $config['fcgi_user']);
        $container->setParameter('codemitte_spawn_fcgi.fcgi_group', $config['fcgi_group']);
        // $loader->load('services.xml');
    }

    /**
     * Returns the base path for the XSD files.
     *
     * @return string The XSD base path
     */
    public function getXsdValidationBasePath()
    {
        return __DIR__.'/../Resources/config/schema';
    }

    public function getNamespace()
    {
        return 'http://code-mitte.de/schema/dic/forcetk';
    }
}
