<?php
/**
 * Copyright (c) Enalean, 2021-Present. All Rights Reserved.
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

namespace Tuleap\ProgramManagement\REST\v1;

/**
 * @psalm-immutable
 */
final class BacklogPatchRepresentation
{
    /**
     * @var array {@type \Tuleap\ProgramManagement\REST\v1\TopBacklogElementInvolvedInChangeRepresentation}{@max 10}
     * @psalm-var TopBacklogElementInvolvedInChangeRepresentation[]
     */
    public $add;

    /**
     * @var array {@type \Tuleap\ProgramManagement\REST\v1\TopBacklogElementInvolvedInChangeRepresentation}{@max 10}
     * @psalm-var TopBacklogElementInvolvedInChangeRepresentation[]
     */
    public $remove;

    /**
     * @var bool {@required false}
     */
    public $remove_from_program_increment_to_add_to_the_backlog = false;

    /**
     * @var TopBacklogElementToOrderInvolvedInChangeRepresentation | null $order {@type \Tuleap\ProgramManagement\REST\v1\TopBacklogElementToOrderInvolvedInChangeRepresentation} {@required false}
     */
    public $order;
}
