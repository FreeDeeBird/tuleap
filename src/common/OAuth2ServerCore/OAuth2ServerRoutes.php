<?php
/**
 * Copyright (c) Enalean, 2022-Present. All Rights Reserved.
 *
 * This file is a part of Tuleap.
 *
 * Tuleap is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Tuleap is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Tuleap. If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

namespace Tuleap\OAuth2ServerCore;

use BackendLogger;
use DateInterval;
use EventManager;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use ProjectManager;
use Tuleap\Authentication\SplitToken\PrefixedSplitTokenSerializer;
use Tuleap\Authentication\SplitToken\SplitTokenVerificationStringHasher;
use Tuleap\Cryptography\KeyFactory;
use Tuleap\Http\HTTPFactoryBuilder;
use Tuleap\Http\Response\JSONResponseBuilder;
use Tuleap\Http\Server\Authentication\BasicAuthLoginExtractor;
use Tuleap\Http\Server\DisableCacheMiddleware;
use Tuleap\Http\Server\RejectNonHTTPSRequestMiddleware;
use Tuleap\Http\Server\ServiceInstrumentationMiddleware;
use Tuleap\OAuth2ServerCore\App\PrefixOAuth2ClientSecret;
use Tuleap\OAuth2ServerCore\RefreshToken\PrefixOAuth2RefreshToken;
use Tuleap\OAuth2ServerCore\AccessToken\OAuth2AccessTokenDAO;
use Tuleap\OAuth2ServerCore\App\AppDao;
use Tuleap\OAuth2ServerCore\App\OAuth2AppCredentialVerifier;
use Tuleap\OAuth2ServerCore\Grant\AuthorizationCode\OAuth2AuthorizationCodeDAO;
use Tuleap\OAuth2ServerCore\Grant\AuthorizationCode\OAuth2AuthorizationCodeRevoker;
use Tuleap\OAuth2ServerCore\Grant\OAuth2ClientAuthenticationMiddleware;
use Tuleap\OAuth2ServerCore\Grant\TokenRevocationController;
use Tuleap\OAuth2ServerCore\OpenIDConnect\IDToken\OpenIDConnectSigningKeyDAO;
use Tuleap\OAuth2ServerCore\OpenIDConnect\IDToken\OpenIDConnectSigningKeyFactory;
use Tuleap\OAuth2ServerCore\OpenIDConnect\JWK\JWKSDocumentEndpointController;
use Tuleap\OAuth2ServerCore\OpenIDConnect\Scope\OAuth2SignInScope;
use Tuleap\OAuth2ServerCore\RefreshToken\OAuth2RefreshTokenDAO;
use Tuleap\User\OAuth2\AccessToken\PrefixOAuth2AccessToken;
use Tuleap\User\OAuth2\BearerTokenHeaderParser;
use User_LoginManager;
use User_PasswordExpirationChecker;

final class OAuth2ServerRoutes
{
    private const INSTRUMENTATION_NAME        = 'oauth2_server_core';
    public const ID_TOKEN_EXPIRATION_DELAY    = 'PT2M';
    public const SIGNING_KEY_EXPIRATION_DELAY = 'PT1H';

    private function __construct()
    {
    }

    public static function routeOAuth2UserInfoEndpoint(): \Tuleap\OAuth2ServerCore\User\UserInfoController
    {
        $response_factory = HTTPFactoryBuilder::responseFactory();
        $stream_factory   = HTTPFactoryBuilder::streamFactory();
        $password_handler = \PasswordHandlerFactory::getPasswordHandler();
        $event_manager    = EventManager::instance();

        return new \Tuleap\OAuth2ServerCore\User\UserInfoController(
            new JSONResponseBuilder($response_factory, $stream_factory),
            new SapiEmitter(),
            new ServiceInstrumentationMiddleware(self::INSTRUMENTATION_NAME),
            new RejectNonHTTPSRequestMiddleware($response_factory, $stream_factory),
            new \Tuleap\User\OAuth2\ResourceServer\OAuth2ResourceServerMiddleware(
                $response_factory,
                new BearerTokenHeaderParser(),
                new PrefixedSplitTokenSerializer(new PrefixOAuth2AccessToken()),
                $event_manager,
                OAuth2SignInScope::fromItself(),
                new User_LoginManager(
                    $event_manager,
                    \UserManager::instance(),
                    new \Tuleap\User\PasswordVerifier($password_handler),
                    new User_PasswordExpirationChecker(),
                    $password_handler
                )
            )
        );
    }

    public static function routeJWKSDocument(): JWKSDocumentEndpointController
    {
        $response_factory = HTTPFactoryBuilder::responseFactory();
        $stream_factory   = HTTPFactoryBuilder::streamFactory();
        return new JWKSDocumentEndpointController(
            new OpenIDConnectSigningKeyFactory(
                new KeyFactory(),
                new OpenIDConnectSigningKeyDAO(),
                new DateInterval(self::SIGNING_KEY_EXPIRATION_DELAY),
                new DateInterval(self::ID_TOKEN_EXPIRATION_DELAY),
            ),
            new DateInterval(self::ID_TOKEN_EXPIRATION_DELAY),
            new JSONResponseBuilder($response_factory, $stream_factory),
            new SapiEmitter(),
            new ServiceInstrumentationMiddleware(self::INSTRUMENTATION_NAME),
            new RejectNonHTTPSRequestMiddleware($response_factory, $stream_factory),
        );
    }

    public static function routeTokenRevocation(): TokenRevocationController
    {
        $response_factory           = HTTPFactoryBuilder::responseFactory();
        $stream_factory             = HTTPFactoryBuilder::streamFactory();
        $app_dao                    = new AppDao();
        $authorization_code_revoker = new OAuth2AuthorizationCodeRevoker(
            new OAuth2AuthorizationCodeDAO()
        );
        $split_token_hasher         = new SplitTokenVerificationStringHasher();
        return new \Tuleap\OAuth2ServerCore\Grant\TokenRevocationController(
            $response_factory,
            $stream_factory,
            new \Tuleap\OAuth2ServerCore\RefreshToken\OAuth2RefreshTokenRevoker(
                new PrefixedSplitTokenSerializer(new PrefixOAuth2RefreshToken()),
                $authorization_code_revoker,
                new OAuth2RefreshTokenDAO(),
                $split_token_hasher
            ),
            new \Tuleap\OAuth2ServerCore\AccessToken\OAuth2AccessTokenRevoker(
                new PrefixedSplitTokenSerializer(new PrefixOAuth2AccessToken()),
                $authorization_code_revoker,
                new OAuth2AccessTokenDAO(),
                $split_token_hasher
            ),
            new SapiEmitter(),
            new ServiceInstrumentationMiddleware(self::INSTRUMENTATION_NAME),
            new RejectNonHTTPSRequestMiddleware($response_factory, $stream_factory),
            new DisableCacheMiddleware(),
            new OAuth2ClientAuthenticationMiddleware(
                new PrefixedSplitTokenSerializer(new PrefixOAuth2ClientSecret()),
                new OAuth2AppCredentialVerifier(
                    new \Tuleap\OAuth2ServerCore\App\AppFactory($app_dao, ProjectManager::instance()),
                    $app_dao,
                    new SplitTokenVerificationStringHasher()
                ),
                new BasicAuthLoginExtractor(),
                BackendLogger::getDefaultLogger()
            )
        );
    }
}
