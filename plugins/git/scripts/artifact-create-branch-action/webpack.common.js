/*
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

const path = require("path");
const { webpack_configurator } = require("@tuleap/build-system-configurator");

const context = path.resolve(__dirname);
const { VueLoaderPlugin } = require("vue-loader");

module.exports = {
    entry: {
        "git-artifact-create-branch": "./src/index.ts",
    },
    context,
    module: {
        rules: [
            ...webpack_configurator.configureTypescriptRules(),
            {
                test: /\.vue$/,
                exclude: /node_modules/,
                loader: "vue-loader",
            },
            webpack_configurator.rule_scss_loader,
        ],
    },
    resolve: {
        extensions: [".ts", ".js", ".vue"],
    },
    resolveLoader: {
        alias: webpack_configurator.easygettext_loader_alias,
    },
    plugins: [
        new VueLoaderPlugin(),
        ...webpack_configurator.getCSSExtractionPlugins(),
        require("@tuleap/po-gettext-plugin").default.webpack(),
    ],
};
