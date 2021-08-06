<?php
/**
 * Copyright (c) Enalean, 2021 - Present. All Rights Reserved.
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
 * along with Tuleap. If not, see http://www.gnu.org/licenses/.
 */

declare(strict_types=1);

namespace Tuleap\ProgramManagement\Domain;

use Tuleap\ProgramManagement\Domain\Program\Backlog\NotAllowedToPrioritizeException;
use Tuleap\ProgramManagement\Domain\Program\Plan\VerifyPrioritizeFeaturesPermission;
use Tuleap\ProgramManagement\Domain\Program\ProgramIdentifier;
use Tuleap\ProgramManagement\Domain\Workspace\UserIdentifier;
use Tuleap\ProgramManagement\Domain\Workspace\UserPermissions;

/**
 * @psalm-immutable
 */
final class UserCanPrioritize
{
    public int $id;

    private function __construct(int $id)
    {
        $this->id = $id;
    }

    /**
     * @throws NotAllowedToPrioritizeException
     */
    public static function fromUser(
        VerifyPrioritizeFeaturesPermission $permission,
        UserPermissions $user_permissions,
        UserIdentifier $user_identifier,
        ProgramIdentifier $program
    ): self {
        if (! $permission->canUserPrioritizeFeatures($program, $user_permissions, $user_identifier)) {
            throw new NotAllowedToPrioritizeException($user_identifier->id, $program->getId());
        }

        return new self($user_identifier->id);
    }
}
