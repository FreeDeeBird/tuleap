<?php
/**
 * Copyright (c) Enalean, 2022 - present. All Rights Reserved.
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

namespace Tuleap\ProgramManagement\Adapter\Program\Backlog\AsynchronousCreation;

use Tuleap\ProgramManagement\Adapter\Workspace\ProjectProxy;
use Tuleap\ProgramManagement\Adapter\Workspace\UserProxy;
use Tuleap\ProgramManagement\Domain\Events\TeamSynchronizationEvent;
use Tuleap\ProgramManagement\Domain\Program\Backlog\AsynchronousCreation\ProcessTeamSynchronization;
use Tuleap\ProgramManagement\Domain\Program\Backlog\TeamSynchronization\MissingProgramIncrementCreator;
use Tuleap\ProgramManagement\Domain\Workspace\LogMessage;

final class SynchronizeTeamProcessor implements ProcessTeamSynchronization
{
    public function __construct(
        private LogMessage $logger,
        private \ProjectManager $project_manager,
        private \UserManager $user_manager,
        private MissingProgramIncrementCreator $missing_program_increment_creator,
    ) {
    }

    public function processTeamSynchronization(TeamSynchronizationEvent $event): void
    {
        $this->logger->debug(
            sprintf(
                "Team %d of Program %d needs PI and Iterations synchronization",
                $event->getTeamId(),
                $event->getProgramId(),
            )
        );

        $user = $this->user_manager->getUserById($event->getUserId());
        if (! $user) {
            $this->logger->error(
                sprintf(
                    "User %d not found, exiting...",
                    $event->getUserId()
                )
            );
            return;
        }
        $user_identifier = UserProxy::buildFromPFUser($user);

        $team       = $this->project_manager->getProject($event->getTeamId());
        $team_proxy = ProjectProxy::buildFromProject($team);

        $this->missing_program_increment_creator->detectAndCreateMissingProgramIncrements($event, $user_identifier, $team_proxy, $this->logger);
    }
}
