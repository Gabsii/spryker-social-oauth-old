<?php

/**
 * This file is part of the Spryker Commerce OS.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Towa\Yves\TowaSprykerOAuth\Authentication;

use Exception;
use KnpU\OAuth2ClientBundle\Client\OAuth2ClientInterface;
use KnpU\OAuth2ClientBundle\Security\Authenticator\SocialAuthenticator;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use Towa\Service\TowaSprykerOauth\Plugin\PostGetUser\PostGetUserInterface;
use SprykerShop\Yves\AgentPage\Plugin\Router\AgentPageRouteProviderPlugin;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Towa\Shared\TowaSprykerOAuth\TowaSprykerOAuthConstants;

class AgentAuthenticator extends SocialAuthenticator
{
    public const AUTHENTICATOR_KEY = 'AGENT_AUTHENTICATOR';

    public const AUTHORIZATION_CODE = 'authorization_code';

    private AbstractProvider $provider;

    private OAuth2ClientInterface $providerClient;

    private AuthenticationSuccessHandler $successHandler;

    private AuthenticationFailureHandler $failureHandler;

    /**
     * @var PostGetUserInterface[] $parameterFilters
     */
    private array $postGetUserPlugins;

    /**
     * @param \League\OAuth2\Client\Provider\AbstractProvider $provider
     * @param \KnpU\OAuth2ClientBundle\Client\OAuth2ClientInterface $providerClient
     * @param \Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface $successHandler
     * @param \Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface $failureHandler
     * @param \Towa\Service\TowaSprykerOauth\Plugin\PostGetUser\PostGetUserInterface[] $postGetUserPlugins
     */
    public function __construct(
        AbstractProvider $provider,
        OAuth2ClientInterface $providerClient,
        AuthenticationSuccessHandlerInterface $successHandler,
        AuthenticationFailureHandlerInterface $failureHandler,
        array $postGetUserPlugins = []

    ) {
        $this->providerClient = $providerClient;
        $this->provider = $provider;
        $this->successHandler = $successHandler;
        $this->failureHandler = $failureHandler;
        $this->postGetUserPlugins = $postGetUserPlugins;
    }

    /**
     * @inheritDoc
     */
    public function start(Request $request, ?AuthenticationException $authException = null)
    {
        return new RedirectResponse(
            AgentPageRouteProviderPlugin::ROUTE_NAME_LOGIN,
            Response::HTTP_TEMPORARY_REDIRECT
        );
    }

    /**
     * @inheritDoc
     */
    public function supports(Request $request)
    {
        return str_contains($request->getPathInfo(), TowaSprykerOAuthConstants::ROUTE_NAME_AGENT_LOGIN_CHECK);
    }

    /**
     * @inheritDoc
     */
    public function getCredentials(Request $request)
    {
        return $this->provider->getAccessToken(
            self::AUTHORIZATION_CODE,
            $request->query->all()
        );
    }

    /**
     * @inheritDoc
     *
     * @throws \Exception
     */
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        /** @var ResourceOwnerInterface $resourceOwner */
        $resourceOwner = $this->providerClient->fetchUserFromToken($credentials);

        if (!$resourceOwner->getEmail()) {
            throw new Exception('No email given for resourceOwner');
        }

        $user = $userProvider->loadUserByUsername($resourceOwner->getEmail());

        foreach($this->postGetUserPlugins as $postGetUserPlugin) {
            $user = $postGetUserPlugin->execute($user, $resourceOwner);
        }

        return $user;
    }

    /**
     * @inheritDoc
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        return $this->failureHandler->onAuthenticationFailure($request, $exception);
    }

    /**
     * @inheritDoc
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        return $this->successHandler->onAuthenticationSuccess($request, $token);
    }
}
