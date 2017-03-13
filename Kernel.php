<?php

/*
 * This file is part of the mc-framework project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace McFramework;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Loader\ClosureLoader;
use Symfony\Component\DependencyInjection\Loader\DirectoryLoader;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * The kernel is the heart of the oslab console system.
 *
 * @author Michael COULLERET <michael@coulleret.pro>
 */
final class Kernel
{
    const VERSION = '1.0.0';

    /**
     * @var EventDispatcher
     */
    protected $dispatcher;

    /**
     * @var string
     */
    protected $environment;

    /**
     * @var bool
     */
    protected $debug = false;

    /**
     * @var string
     */
    protected $rootDir;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var boolean
     */
    protected $booted = false;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * Constructor
     *
     * @param string          $environment
     * @param bool            $debug
     */
    public function __construct($environment = 'prod', $debug = false)
    {
        $this->environment = $environment;
        $this->debug = $debug;

        $this->boot();
    }

    /**
     * Boots the current kernel.
     */
    public function boot()
    {
        if (true === $this->booted) {
            return;
        }

        $this->container = $this->buildContainer();

        $this->booted = true;
    }

    /**
     * Gets the name of the kernel.
     *
     * @return string The kernel name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Gets the environment.
     *
     * @return string The current environment
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * Checks if debug mode is enabled.
     *
     * @return bool true if debug mode is enabled, false otherwise
     */
    public function isDebug()
    {
        return $this->debug;
    }

    /**
     * Gets the cache directory.
     *
     * @return string The cache directory
     */
    public function getCacheDir()
    {
        return $this->rootDir.'/var/cache/'.$this->environment;
    }

    /**
     * Gets the log directory.
     *
     * @return string The log directory
     */
    public function getLogDir()
    {
        return $this->rootDir.'/var/logs';
    }

    /**
     * Gets the charset of the application.
     *
     * @return string The charset
     */
    public function getCharset()
    {
        return 'UTF-8';
    }

    /**
     * Gets the application root dir.
     *
     * @return string The application root dir
     */
    public function getRootDir()
    {
        if (null === $this->rootDir) {
            $r = new \ReflectionObject($this);
            $this->rootDir = dirname($r->getFileName());
        }

        return $this->rootDir;
    }

    /**
     * Loads the container configuration.
     *
     * @param LoaderInterface $loader A LoaderInterface instance
     */
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load($this->getRootDir().'/../app/config/config_'.$this->getEnvironment().'.yml');
    }

    /**
     * Builds the service container.
     *
     * @return ContainerBuilder The compiled service container
     *
     * @throws \RuntimeException
     */
    protected function buildContainer()
    {
        $container = $this->getContainerBuilder();

        return $container;
    }

    /**
     * Gets a new ContainerBuilder instance used to build the service container.
     *
     * @return ContainerBuilder
     */
    protected function getContainerBuilder()
    {
        $container = new ContainerBuilder();
        $container->getParameterBag()->add($this->getKernelParameters());

        return $container;
    }

    /**
     * Returns a loader for the container.
     *
     * @param ContainerInterface $container The service container
     *
     * @return DelegatingLoader The loader
     */
    protected function getContainerLoader(ContainerInterface $container)
    {
        $locator = new FileLocator($this);
        $resolver = new LoaderResolver(array(
            new YamlFileLoader($container, $locator),
            new PhpFileLoader($container, $locator),
            new DirectoryLoader($container, $locator),
            new ClosureLoader($container),
        ));

        return new DelegatingLoader($resolver);
    }

    /**
     * Gets the container class.
     *
     * @return string The container class
     */
    protected function getContainerClass()
    {
        return $this->name.ucfirst($this->environment).($this->debug ? 'Debug' : '').'ProjectContainer';
    }

    /**
     * Returns the kernel parameters.
     *
     * @return array An array of kernel parameters
     */
    protected function getKernelParameters()
    {
        return array_merge(
            array(
                'kernel.root_dir' => realpath($this->rootDir) ?: $this->rootDir,
                'kernel.environment' => $this->environment,
                'kernel.debug' => $this->debug,
                'kernel.name' => $this->name,
                'kernel.cache_dir' => realpath($this->getCacheDir()) ?: $this->getCacheDir(),
                'kernel.logs_dir' => realpath($this->getLogDir()) ?: $this->getLogDir(),
                'kernel.charset' => $this->getCharset(),
                'kernel.container_class' => $this->getContainerClass(),
            )
        );
    }
}
