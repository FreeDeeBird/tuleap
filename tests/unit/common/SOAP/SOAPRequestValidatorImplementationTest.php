<?php
/**
 * Copyright (c) Enalean, 2012-Present. All Rights Reserved.
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

namespace Tuleap\SOAP;

use EventManager;
use Exception;
use Mockery;
use PFUser;
use Project;
use Project_AccessException;
use ProjectManager;
use Tuleap\Project\ProjectAccessChecker;
use Tuleap\Test\User\AnonymousUserTestProvider;
use Tuleap\User\CurrentUserWithLoggedInInformation;
use UserManager;

final class SOAPRequestValidatorImplementationTest extends \Tuleap\Test\PHPUnit\TestCase
{
    use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    public function testNoExceptionIsRaisedWhenTheUserCanAccessTheProject(): void
    {
        $access_checker = Mockery::mock(ProjectAccessChecker::class);
        $validator      = new SOAPRequestValidatorImplementation(
            Mockery::mock(ProjectManager::class),
            Mockery::mock(UserManager::class),
            $access_checker,
            new EventManager()
        );

        $access_checker->shouldReceive('checkUserCanAccessProject')->andReturn(true)->once();

        $validator->assertUserCanAccessProject(Mockery::mock(PFUser::class), Mockery::mock(Project::class));
    }

    public function testAnExceptionIsRaisedWhenTheUserCanNotAccessTheProject(): void
    {
        $access_checker = Mockery::mock(ProjectAccessChecker::class);
        $validator      = new SOAPRequestValidatorImplementation(
            Mockery::mock(ProjectManager::class),
            Mockery::mock(UserManager::class),
            $access_checker,
            new EventManager()
        );

        $access_checker->shouldReceive('checkUserCanAccessProject')->andThrow(
            new class extends Project_AccessException {
            }
        );

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('User do not have access to the project');
        $validator->assertUserCanAccessProject(Mockery::mock(PFUser::class), Mockery::mock(Project::class));
    }

    public function testCurrentUserIsRetrievedWhenSessionKeyIsValid(): void
    {
        $user_manager = Mockery::mock(UserManager::class);
        $validator    = new SOAPRequestValidatorImplementation(
            Mockery::mock(ProjectManager::class),
            $user_manager,
            Mockery::mock(ProjectAccessChecker::class),
            new EventManager()
        );

        $expected_user = Mockery::mock(PFUser::class);
        $expected_user->shouldReceive('isAnonymous')->andReturn(false);
        $session_key = 'my_session_key';
        $user_manager->shouldReceive('getCurrentUserWithLoggedInInformation')->with($session_key)->andReturn(
            CurrentUserWithLoggedInInformation::fromLoggedInUser($expected_user)
        );

        $user = $validator->continueSession($session_key);
        $this->assertSame($expected_user, $user);
    }

    public function testAnExceptionIsThrownWhenSessionKeyIsInvalid(): void
    {
        $user_manager = Mockery::mock(UserManager::class);
        $validator    = new SOAPRequestValidatorImplementation(
            Mockery::mock(ProjectManager::class),
            $user_manager,
            Mockery::mock(ProjectAccessChecker::class),
            new EventManager()
        );

        $user_manager->shouldReceive('getCurrentUserWithLoggedInInformation')->andReturn(
            CurrentUserWithLoggedInInformation::fromAnonymous(new AnonymousUserTestProvider())
        );

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid session');
        $validator->continueSession('session_key');
    }
}
