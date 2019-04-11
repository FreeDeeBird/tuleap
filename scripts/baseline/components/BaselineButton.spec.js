/*
 * Copyright (c) Enalean, 2019. All Rights Reserved.
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
 *
 */

import { mount } from "@vue/test-utils";
import localVue from "../support/local-vue.js";
import BaselineButton from "./BaselineButton.vue";

describe("BaselineButton", () => {
    const spinner_selector = '[data-test-type="spinner"]';
    const button_selector = '[data-test-type="button"]';
    let wrapper;

    beforeEach(() => {
        wrapper = mount(BaselineButton, {
            localVue,
            propsData: {
                icon: "delete"
            }
        });
    });

    it("does not show spinner", () => {
        expect(wrapper.contains(spinner_selector)).toBeFalsy();
    });
    it("shows icon", () => {
        expect(wrapper.contains(".fa-delete")).toBeTruthy();
    });
    it("enables button", () => {
        expect(wrapper.find(button_selector).attributes().disabled).toBeFalsy();
    });

    describe("when clicking", () => {
        beforeEach(() => wrapper.trigger("click"));

        it("emits click", () => {
            expect(wrapper.emitted().click).toBeTruthy();
        });
    });

    describe("when loading", () => {
        beforeEach(() => wrapper.setProps({ loading: true }));

        it("shows spinner", () => {
            expect(wrapper.contains(spinner_selector)).toBeTruthy();
        });
    });

    describe("when disabled", () => {
        beforeEach(() => wrapper.setProps({ disabled: true }));

        it("disables button", () => {
            expect(wrapper.find(button_selector).attributes().disabled).toBeTruthy();
        });
    });
});
