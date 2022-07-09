<?php

/**
 * This file is part of the Spryker Commerce OS.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Towa\Service\TowaSprykerOAuth;

use Towa\Service\TowaSprykerOAuth\Model\ClientRegistry;
use Spryker\Service\Kernel\AbstractServiceFactory;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @method \Towa\Service\TowaSprykerOauth\TowaSprykerOAuthConfig getConfig()
 */
class TowaSprykerOAuthServiceFactory extends AbstractServiceFactory
{

    /**
     * @return \Towa\Service\TowaSprykerOauth\Model\ClientRegistry
     */
    public function createClientRegistry(): ClientRegistry
    {
        return new ClientRegistry(
            $this->getRequestStack(),
            $this->getConfig()->getAllSupportedTypes(),
            $this->getConfig()->getAuthConfig()
        );
    }

    /**
     * @return \Symfony\Component\HttpFoundation\RequestStack
     */
    public function getRequestStack(): RequestStack
    {
        return $this->getProvidedDependency(TowaSprykerOAuthDependencyProvider::SERVICE_REQUEST_STACK);
    }
}
