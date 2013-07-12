<?php
/**
 * Copyright (C) 2012 code mitte GmbH - Zeughausstr. 28-38 -    50667 Cologne/Germany
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

/**
 * Configuration
 *
 * @author Johannes Heinen <johannes.heinen@code-mitte.de>
 * @copyright 2012 code mitte GmbH, Cologne, Germany
 * @package Bundle
 * @subpackage ForceBundle
 */
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\NodeInterface;

/**
 * Configuration structure.
 *
 * @author Johannes Heinen <johannes.heinen@code-mitte.de>
 */
class Configuration
{
    /**
     * Generates the configuration tree.
     *
     * @return NodeInterface
     */
    public function getConfigTree()
    {
        $treeBuilder = new TreeBuilder();

        $rootNode = $treeBuilder->root('codemitte_spawn_fcgi', 'array');

        $rootNode
            ->children()
                ->scalarNode('fcgi_webserver_address')->defaultValue('http://localhost:80/')->end()
                ->integerNode('php_fcgi_max_requests')->defaultValue(1000)->end()
                ->integerNode('php_fcgi_children')->defaultValue(4)->end()
                ->scalarNode('php_additional_ini_dir')->defaultNull()->end()
                ->scalarNode('allowed_env')->defaultNull()->end()
                ->scalarNode('spawn_fcgi_binary')->defaultValue('/usr/bin/spawn-fcgi')->end()
                ->scalarNode('cgi_program')->defaultValue('/usr/bin/php-cgi')->end()
                ->scalarNode('fcgi_socket_path')->isRequired()->end()
                ->scalarNode('fcgi_user')->defaultValue(get_current_user())->end()
                ->scalarNode('fcgi_group')->defaultValue(get_current_user())->end()
            ->end()
        ;

        return $treeBuilder->buildTree();
    }
}
