<?php

namespace OsLab\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ContainerAwareCommand
 *
 * @author Florent <orions07@gmail.com>
 */
abstract class AbstractContainerAwareCommand extends Command implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @return ContainerInterface
     *
     * @throws \LogicException
     */
    public function getContainer()
    {
        if (null === $this->container) {
            $application = $this->getApplication();
            if (null === $application) {
                throw new \LogicException('The container cannot be retrieved as the application instance is not yet set.');
            }
            $this->container = $application->getContainer();
        }

        return $this->container;
    }
}
