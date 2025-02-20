/**
 * Copyright (c) Enalean, 2020 - present. All Rights Reserved.
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

import { expect } from "vitest";

export function expectChangeEventToHaveBeenFiredOnSourceSelectBox(
    source_select_box: HTMLSelectElement,
    nb_times: number
): void {
    if (nb_times === 0) {
        expect(source_select_box.dispatchEvent).not.toHaveBeenCalled();
        return;
    }

    if (nb_times > 1) {
        expect(source_select_box.dispatchEvent).toHaveBeenCalledTimes(nb_times);
    }

    expect(source_select_box.dispatchEvent).toHaveBeenCalledWith(new Event("change"));
}
