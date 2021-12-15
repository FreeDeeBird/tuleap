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
 * along with Tuleap. If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

namespace Tuleap\ProgramManagement\Domain\Program\Backlog\ProgramIncrement\Content;

use Tuleap\ProgramManagement\Domain\Program\Backlog\Feature\Feature;
use Tuleap\ProgramManagement\Tests\Stub\RetrieveBackgroundColorStub;
use Tuleap\ProgramManagement\Tests\Stub\RetrieveFeatureCrossReferenceStub;
use Tuleap\ProgramManagement\Tests\Stub\RetrieveFeatureTitleStub;
use Tuleap\ProgramManagement\Tests\Stub\RetrieveFeatureURIStub;
use Tuleap\ProgramManagement\Tests\Stub\RetrieveTrackerOfFeatureStub;
use Tuleap\ProgramManagement\Tests\Stub\SearchFeaturesStub;
use Tuleap\ProgramManagement\Tests\Stub\UserIdentifierStub;
use Tuleap\ProgramManagement\Tests\Stub\VerifyFeatureHasAtLeastOneUserStoryStub;
use Tuleap\ProgramManagement\Tests\Stub\VerifyFeatureIsVisibleStub;
use Tuleap\ProgramManagement\Tests\Stub\VerifyHasAtLeastOnePlannedUserStoryStub;
use Tuleap\ProgramManagement\Tests\Stub\VerifyIsProgramIncrementStub;
use Tuleap\ProgramManagement\Tests\Stub\VerifyIsVisibleArtifactStub;

final class ProgramIncrementContentSearcherTest extends \Tuleap\Test\PHPUnit\TestCase
{
    private const PROGRAM_INCREMENT_ID  = 202;
    private const BUG_ID                = 689;
    private const BUG_TITLE             = 'alkalescent';
    private const BUG_TRACKER_ID        = 32;
    private const BUG_SHORT_NAME        = 'bug';
    private const BUG_COLOR             = 'fiesta-red';
    private const USER_STORY_ID         = 337;
    private const USER_STORY_TITLE      = 'tracklessly';
    private const USER_STORY_TRACKER_ID = 34;
    private const USER_STORY_SHORT_NAME = 'user_stories';
    private const USER_STORY_COLOR      = 'graffiti-yellow';
    private const BASE_URI              = '/plugins/tracker/?aid=';
    private SearchFeaturesStub $features_searcher;

    protected function setUp(): void
    {
        $this->features_searcher = SearchFeaturesStub::withFeatureIds(self::BUG_ID, self::USER_STORY_ID);
    }

    /**
     * @return Feature[]
     */
    private function getFeatures(): array
    {
        $retriever = new ProgramIncrementContentSearcher(
            VerifyIsProgramIncrementStub::withValidProgramIncrement(),
            VerifyIsVisibleArtifactStub::withAlwaysVisibleArtifacts(),
            $this->features_searcher,
            VerifyFeatureIsVisibleStub::withAlwaysVisibleFeatures(),
            RetrieveFeatureTitleStub::withSuccessiveTitles(self::BUG_TITLE, self::USER_STORY_TITLE),
            new RetrieveFeatureURIStub(),
            RetrieveFeatureCrossReferenceStub::withSuccessiveShortNames(
                self::BUG_SHORT_NAME,
                self::USER_STORY_SHORT_NAME
            ),
            RetrieveTrackerOfFeatureStub::withSuccessiveIds(self::BUG_TRACKER_ID, self::USER_STORY_TRACKER_ID),
            RetrieveBackgroundColorStub::withSuccessiveColors(self::BUG_COLOR, self::USER_STORY_COLOR),
            VerifyHasAtLeastOnePlannedUserStoryStub::withNothingPlanned(),
            VerifyFeatureHasAtLeastOneUserStoryStub::withStories()
        );

        return $retriever->retrieveProgramIncrementContent(
            self::PROGRAM_INCREMENT_ID,
            UserIdentifierStub::buildGenericUser()
        );
    }

    public function testItBuildsACollectionOfOpenFeatures(): void
    {
        [$first_feature, $second_feature] = $this->getFeatures();

        self::assertSame(self::BUG_ID, $first_feature->feature_identifier->getId());
        self::assertSame(self::BUG_TITLE, $first_feature->title);
        self::assertSame('bug #' . self::BUG_ID, $first_feature->cross_reference);
        self::assertSame(self::BASE_URI . self::BUG_ID, $first_feature->uri);
        self::assertSame(self::BUG_TRACKER_ID, $first_feature->feature_tracker_identifier->getId());
        self::assertSame(self::BUG_COLOR, $first_feature->background_color->getBackgroundColorName());
        self::assertFalse($first_feature->is_linked_to_at_least_one_planned_user_story);
        self::assertTrue($first_feature->has_at_least_one_story);

        self::assertSame(self::USER_STORY_ID, $second_feature->feature_identifier->getId());
        self::assertSame(self::USER_STORY_TITLE, $second_feature->title);
        self::assertSame('user_stories #' . self::USER_STORY_ID, $second_feature->cross_reference);
        self::assertSame(self::BASE_URI . self::USER_STORY_ID, $second_feature->uri);
        self::assertSame(self::USER_STORY_TRACKER_ID, $second_feature->feature_tracker_identifier->getId());
        self::assertSame(self::USER_STORY_COLOR, $second_feature->background_color->getBackgroundColorName());
        self::assertFalse($second_feature->is_linked_to_at_least_one_planned_user_story);
        self::assertTrue($second_feature->has_at_least_one_story);
    }

    public function testItReturnsEmptyArrayWhenThereAreNoFeatures(): void
    {
        $this->features_searcher = SearchFeaturesStub::withoutFeatures();
        self::assertCount(0, $this->getFeatures());
    }
}
