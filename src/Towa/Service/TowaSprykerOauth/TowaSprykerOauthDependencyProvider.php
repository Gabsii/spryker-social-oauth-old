<?php

/**
 * This file is part of the Spryker Commerce OS.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Towa\Service\TowaSprykerOauth;

use Spryker\Service\Kernel\AbstractBundleDependencyProvider;
use Spryker\Service\Kernel\Container;
use Symfony\Component\HttpFoundation\RequestStack;

class TowaSprykerOauthDependencyProvider extends AbstractBundleDependencyProvider
{
    public const SERVICE_REQUEST_STACK = 'request_stack';

    /**
     * @param \Spryker\Service\Kernel\Container $container
     *
     * @return \Spryker\Service\Kernel\Container
     */
    public function provideServiceDependencies(Container $container)
    {
        $container = parent::provideServiceDependencies($container);
        $container = $this->addRequestStack($container);

        return $container;
    }

    /**
     * @param \Spryker\Service\Kernel\Container $container
     *
     * @return \Spryker\Service\Kernel\Container
     */
    protected function addRequestStack(Container $container): Container
    {
        $container->set(static::SERVICE_REQUEST_STACK, function () {
            return new RequestStack();
        });

        return $container;
    }
}
