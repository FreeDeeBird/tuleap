<!--
  - Copyright (c) Enalean, 2018 - Present. All Rights Reserved.
  -
  - This file is a part of Tuleap.
  -
  - Tuleap is free software; you can redistribute it and/or modify
  - it under the terms of the GNU General Public License as published by
  - the Free Software Foundation; either version 2 of the License, or
  - (at your option) any later version.
  -
  - Tuleap is distributed in the hope that it will be useful,
  - but WITHOUT ANY WARRANTY; without even the implied warranty of
  - MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  - GNU General Public License for more details.
  -
  - You should have received a copy of the GNU General Public License
  - along with Tuleap. If not, see <http://www.gnu.org/licenses/>.
  -->

<template>
    <section
        class="tlp-pane git-repository-card"
        v-bind:class="{
            'git-repository-card-two-columns': !isFolderDisplayMode,
            'git-repository-in-folder': isFolderDisplayMode && is_in_folder(),
        }"
    >
        <div class="tlp-pane-container">
            <a
                v-bind:href="getRepositoryPath()"
                class="git-repository-card-link"
                data-test="git-repository-path"
            >
                <div class="tlp-pane-header git-repository-card-header">
                    <h2
                        class="tlp-pane-title git-repository-card-title"
                        data-test="repository_name"
                    >
                        <span
                            v-if="is_in_folder() && !isFolderDisplayMode"
                            class="git-repository-card-path"
                            data-test="git-repository-card-path"
                        >
                            {{ folder_path() }}
                        </span>
                        {{ repository_label() }}
                    </h2>
                    <div class="git-repository-links-spacer"></div>
                    <pull-request-badge
                        v-if="!isGitlabRepository()"
                        v-bind:number-pull-request="number_pull_requests()"
                        v-bind:repository-id="repository.id"
                    />
                    <div class="git-repository-card-last-update">
                        <i class="far fa-clock git-repository-card-last-update-icon"></i>
                        {{ formatted_last_update_date() }}
                    </div>
                    <a
                        v-if="is_admin() && !isGitlabRepository()"
                        v-bind:href="repository_admin_url()"
                        class="git-repository-card-admin-link"
                        data-test="git-repository-card-admin-link"
                    >
                        <i
                            class="fas fa-fw fa-cog"
                            v-bind:title="$gettext('Go to repository administration')"
                        ></i>
                    </a>
                    <git-lab-administration
                        v-if="isGitlabRepository()"
                        v-bind:is_admin="is_admin()"
                        v-bind:repository="repository"
                    />
                </div>
                <section
                    class="tlp-pane-section git-repository-card-header"
                    v-if="
                        hasRepositoryDescription() ||
                        isGitlabRepository() ||
                        isRepositoryHandledByGerrit()
                    "
                >
                    <p
                        v-if="hasRepositoryDescription()"
                        class="git-repository-card-description"
                        v-bind:class="{ 'gitlab-description': isGitlabRepository() }"
                        data-test="git-repository-card-description"
                    >
                        {{ repository.description }}
                    </p>
                    <div
                        v-if="mustDisplayAdditionalInformation()"
                        class="git-repository-links-spacer"
                    ></div>
                    <i
                        v-if="isRepositoryHandledByGerrit()"
                        class="fas fa-tlp-gerrit git-gerrit-icon"
                        v-bind:title="$gettext('This repository is handled by Gerrit.')"
                        data-test="git-repository-card-gerrit-icon"
                    ></i>
                    <i
                        v-if="isGitlabRepository()"
                        class="fab fa-gitlab git-gitlab-icon"
                        v-bind:class="{ 'git-gitlab-icon-align-to-date': !is_admin() }"
                        v-bind:title="$gettext('This repository comes from GitLab.')"
                        data-test="git-repository-card-gitlab-icon"
                    ></i>
                    <i
                        v-if="isGitlabRepository() && !isGitlabRepositoryWellConfigured()"
                        class="fas fa-exclamation-triangle git-gitlab-integration-not-well-configured"
                        v-bind:title="$gettext('Webhook must be regenerated.')"
                    ></i>
                </section>
            </a>
        </div>
    </section>
</template>
<script lang="ts">
import Vue from "vue";
import { Getter } from "vuex-class";
import { Component, Prop } from "vue-property-decorator";
import TimeAgo from "javascript-time-ago";
import GitLabAdministration from "./GitLabAdministration.vue";
import PullRequestBadge from "./PullRequestBadge.vue";
import { isGitlabRepository, isGitlabRepositoryWellConfigured } from "../gitlab/gitlab-checker";
import { isRepositoryHandledByGerrit } from "../gerrit/gerrit-checker";
import { getDashCasedLocale, getProjectId, getUserIsAdmin } from "../repository-list-presenter";
import { getRepositoryListUrl } from "../breadcrumb-presenter";
import type { FormattedGitLabRepository, Repository } from "../type";
import { sprintf } from "sprintf-js";

const DEFAULT_DESCRIPTION = "-- Default description --";

@Component({ components: { GitLabAdministration, PullRequestBadge } })
export default class GitRepository extends Vue {
    @Prop({ required: true })
    readonly repository!: Repository | FormattedGitLabRepository;

    @Getter
    readonly isFolderDisplayMode!: boolean;

    isGitlabRepository(): boolean {
        return isGitlabRepository(this.repository);
    }
    isGitlabRepositoryWellConfigured(): boolean {
        return isGitlabRepositoryWellConfigured(this.repository);
    }
    isRepositoryHandledByGerrit(): boolean {
        return isRepositoryHandledByGerrit(this.repository);
    }
    mustDisplayAdditionalInformation(): boolean {
        return (
            this.isRepositoryHandledByGerrit() ||
            this.isGitlabRepository() ||
            this.isGitlabRepositoryWellConfigured()
        );
    }
    hasRepositoryDescription(): boolean {
        return this.repository.description !== DEFAULT_DESCRIPTION;
    }
    repository_admin_url(): string {
        return `/plugins/git/?action=repo_management&group_id=${getProjectId()}&repo_id=${
            this.repository.id
        }`;
    }
    is_admin(): boolean {
        return getUserIsAdmin();
    }
    formatted_last_update_date(): string {
        const date = new Date(this.repository.last_update_date);
        const time_ago = new TimeAgo(getDashCasedLocale());

        return sprintf(this.$gettext("Updated %s"), time_ago.format(date));
    }
    isRepository(repository: Repository | FormattedGitLabRepository): repository is Repository {
        return Object.prototype.hasOwnProperty.call(repository, "permissions");
    }
    number_pull_requests(): number {
        if (this.isRepository(this.repository)) {
            return Number.parseInt(this.repository.additional_information.opened_pull_requests, 10);
        }

        return 0;
    }
    repository_label(): string {
        return this.repository.label;
    }
    is_in_folder(): number {
        return this.repository.path_without_project.length;
    }
    getRepositoryPath(): string {
        if (this.isGitlabRepository() && this.repository.gitlab_data) {
            return this.repository.gitlab_data.gitlab_repository_url;
        }
        return getRepositoryListUrl() + this.repository.normalized_path;
    }
    folder_path(): string {
        return this.repository.path_without_project + "/";
    }
}
</script>
