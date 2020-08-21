<?php
/**
 * Copyright (c) Enalean, 2020-Present. All Rights Reserved.
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

namespace Tuleap\HelpDropdown;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use UserPreferencesDao;

class ReleaseNoteManagerTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @var ReleaseNoteManager
     */
    private $release_note_manager;
    /**
     * @var \Mockery\LegacyMockInterface|\Mockery\MockInterface|ReleaseLinkDao
     */
    private $release_note_dao;
    /**
     * @var Mockery\LegacyMockInterface|Mockery\MockInterface|UserPreferencesDao
     */
    private $user_preferences_dao;

    protected function setUp(): void
    {
        $this->release_note_dao     = Mockery::mock(ReleaseLinkDao::class);
        $this->user_preferences_dao = Mockery::mock(UserPreferencesDao::class);
        $this->release_note_manager = new ReleaseNoteManager(
            $this->release_note_dao,
            $this->user_preferences_dao,
            "11.17.99.666"
        );
    }

    public function testGetReleaseNoteLink(): void
    {
        $expected_result = "https://www.tuleap.org/resources/release-notes/tuleap-11-17";

        $dao_links = [
            "actual_link" => "",
            "tuleap_version" => "11-17"
        ];

        $this->release_note_dao->shouldReceive("getReleaseLink")->andReturn($dao_links);
        $this->user_preferences_dao->shouldReceive("deletePreferenceForAllUsers")->never();

        $this->assertEquals($expected_result, $this->release_note_manager->getReleaseNoteLink());
    }

    public function testGetReleaseNoteLinkWithCustomLink(): void
    {
        $expected_result = "https://whatever.com";

        $dao_links = [
            "actual_link" => "https://whatever.com",
            "tuleap_version" => "11-17"
        ];

        $this->release_note_dao->shouldReceive("getReleaseLink")->andReturn($dao_links);
        $this->user_preferences_dao->shouldReceive("deletePreferenceForAllUsers")->never();

        $this->assertEquals($expected_result, $this->release_note_manager->getReleaseNoteLink());
    }

    public function testGetReleaseNoteLinkWithNullLink(): void
    {
        $expected_result = "https://www.tuleap.org/resources/release-notes/tuleap-11-17";

        $dao_links = [
            "actual_link" => null,
            "tuleap_version" => "11-17"
        ];

        $this->release_note_dao->shouldReceive("getReleaseLink")->andReturn($dao_links);
        $this->user_preferences_dao->shouldReceive("deletePreferenceForAllUsers")->never();

        $this->assertEquals($expected_result, $this->release_note_manager->getReleaseNoteLink());
    }

    public function testGetReleaseNoteLinkShouldChangeIfVersionIsUpgraded(): void
    {
        $expected_result = "https://www.tuleap.org/resources/release-notes/tuleap-11-17";

        $dao_old_links = [
            "actual_link" => "https://whatever.com",
            "tuleap_version" => "11-16"
        ];

        $this->release_note_dao->shouldReceive("getReleaseLink")->andReturn($dao_old_links)->once();
        $this->release_note_dao->shouldReceive("updateTuleapVersion");
        $this->user_preferences_dao->shouldReceive("deletePreferenceForAllUsers")->once();

        $this->assertEquals($expected_result, $this->release_note_manager->getReleaseNoteLink());
    }

    public function testGetReleaseNoteLinkIfNotLinkInDatabase(): void
    {
        $expected_result = "https://www.tuleap.org/resources/release-notes/tuleap-11-17";

        $this->release_note_dao->shouldReceive("getReleaseLink")->andReturn(null)->once();
        $this->release_note_dao->shouldReceive("createReleaseNoteLink");
        $this->user_preferences_dao->shouldReceive("deletePreferenceForAllUsers")->once();

        $this->assertEquals($expected_result, $this->release_note_manager->getReleaseNoteLink());
    }
}
