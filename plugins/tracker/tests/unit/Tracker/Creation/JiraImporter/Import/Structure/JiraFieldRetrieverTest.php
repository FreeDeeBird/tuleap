<?php
/**
 * Copyright (c) Enalean, 2020 - Present. All Rights Reserved.
 *
 *  This file is a part of Tuleap.
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

declare(strict_types=1);

namespace Tuleap\Tracker\Creation\JiraImporter\Import\Structure;

use Psr\Log\NullLogger;
use Tuleap\Tracker\Creation\JiraImporter\ClientWrapper;
use Tuleap\Tracker\Test\Tracker\Creation\JiraImporter\Stub\JiraCloudClientStub;

final class JiraFieldRetrieverTest extends \Tuleap\Test\PHPUnit\TestCase
{
    private string $jira_project_id = 'projID';

    private string $jira_issue_type_name = 'issueName';

    public function testItExportsJiraFieldAndBuildAnArraySortedById(): void
    {
        $wrapper = new class extends JiraCloudClientStub {
            public array $urls = [
                ClientWrapper::JIRA_CORE_BASE_URL . "/issue/createmeta?projectKeys=projID&issuetypeIds=issueName&expand=projects.issuetypes.fields" => [
                    'projects' => [
                        [
                            'issuetypes' => [
                                [
                                    'fields' => [
                                        'summary' => [
                                            'required' => true,
                                            'schema' => [
                                                'type' => 'string',
                                                'system' => 'summary',
                                            ],
                                            'name' => 'Summary',
                                            'key' => 'summary',
                                            'hasDefaultValue' => false,
                                            'operation' => [
                                                'set',
                                            ],
                                        ],
                                        'custom_01' => [
                                            'required' => false,
                                            'schema' => [
                                                'type' => 'user',
                                                'custom' => 'com.atlassian.jira.toolkit:lastupdaterorcommenter',
                                                'customId' => 10071,
                                            ],
                                            'name' => '[opt] Last updator',
                                            'key' => 'customfield_10071',
                                            'hasDefaultValue' => false,
                                            'operation' => [
                                                'set',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ];
        };

        $field_retriever = new JiraFieldRetriever($wrapper, new NullLogger());
        $result          = $field_retriever->getAllJiraFields(
            $this->jira_project_id,
            $this->jira_issue_type_name,
            new FieldAndValueIDGenerator(),
        );

        $this->assertCount(2, $result);
        $this->assertArrayHasKey("summary", $result);
        $this->assertArrayHasKey("custom_01", $result);

        $system_field_representation = $result['summary'];
        $this->assertEquals("summary", $system_field_representation->getId());
        $this->assertEquals("Summary", $system_field_representation->getLabel());
        $this->assertNotNull($system_field_representation->getSchema());
        $this->assertTrue($system_field_representation->isRequired());

        $custom_field_representation = $result['custom_01'];
        $this->assertEquals("custom_01", $custom_field_representation->getId());
        $this->assertEquals("[opt] Last updator", $custom_field_representation->getLabel());
        $this->assertNotNull($custom_field_representation->getSchema());
        $this->assertFalse($custom_field_representation->isRequired());
    }

    public function testReturnsAnEmptyArrayWhenNoFieldFound(): void
    {
        $wrapper         = new class extends JiraCloudClientStub {
        };
        $field_retriever = new JiraFieldRetriever($wrapper, new NullLogger());

        $result = $field_retriever->getAllJiraFields(
            $this->jira_project_id,
            $this->jira_issue_type_name,
            new FieldAndValueIDGenerator(),
        );

        $this->assertEquals([], $result);
    }
}
