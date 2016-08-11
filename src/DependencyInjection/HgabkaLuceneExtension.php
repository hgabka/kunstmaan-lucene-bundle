<?php

namespace Hgabka\LuceneBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class HgabkaLuceneExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');

        $indexPath = $config['index_path'];

        $fs = new Filesystem();
        if (!$fs->isAbsolutePath($indexPath)) {
            $indexPath = $container->getParameter('kernel.root_dir') . DIRECTORY_SEPARATOR . $indexPath;
        }

        $container->setParameter('hgabka_lucene.index_path', $indexPath);
    }
}
