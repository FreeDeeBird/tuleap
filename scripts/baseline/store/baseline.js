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
*/

import { getBaseline, getBaselineArtifacts } from "../api/rest-querier";
import { presentLinkedArtifactsAsGraph } from "../presenters/baseline-artifact";
import { fetchAllArtifacts } from "../api/request-manufacturer";

export default {
    namespaced: true,

    state: {
        baseline: null,
        is_baseline_loading: false,
        is_baseline_loading_failed: false
    },

    actions: {
        async load({ commit, dispatch, rootGetters }, baseline_id) {
            commit("startBaselineLoading");
            try {
                const baseline_loading = getBaseline(baseline_id);
                const first_depth_artifacts_loading = getBaselineArtifacts(baseline_id);

                const baseline = await baseline_loading;
                const first_depth_artifacts = await first_depth_artifacts_loading;

                const author_loading = dispatch(
                    "loadUser",
                    { user_id: baseline.author_id },
                    { root: true }
                );
                const artifact_loading = dispatch(
                    "loadArtifact",
                    { artifact_id: baseline.artifact_id },
                    { root: true }
                );

                await author_loading;
                const author = rootGetters.findUserById(baseline.author_id);

                await artifact_loading;
                const artifact = rootGetters.findArtifactById(baseline.artifact_id);

                const all_artifacts = await fetchAllArtifacts(baseline_id, first_depth_artifacts);
                const first_depth_artifacts_as_graph = presentLinkedArtifactsAsGraph(
                    first_depth_artifacts,
                    all_artifacts
                );

                commit("updateBaseline", {
                    ...baseline,
                    author,
                    artifact,
                    first_depth_artifacts: first_depth_artifacts_as_graph
                });
            } catch (e) {
                commit("failBaselineLoading");
            } finally {
                commit("stopBaselineLoading");
            }
        }
    },

    mutations: {
        startBaselineLoading: state => {
            state.is_baseline_loading_failed = false;
            state.is_baseline_loading = true;
        },
        failBaselineLoading: state => {
            state.is_baseline_loading_failed = true;
        },
        stopBaselineLoading: state => {
            state.is_baseline_loading = false;
        },
        updateBaseline: (state, baseline) => {
            state.baseline = baseline;
        }
    }
};
