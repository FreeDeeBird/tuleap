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

namespace TuleapCodingStandard\Tuleap\OAuth2Server\App;

use PHPUnit\Framework\TestCase;
use Tuleap\OAuth2Server\App\ClientIdentifier;
use Tuleap\OAuth2Server\App\InvalidClientIdentifierKey;

final class ClientIdentifierTest extends TestCase
{
    public function testIdentifierKeyIsHeldAsIs(): void
    {
        $identifier_key = 'tlp-client-id-123';
        $identifier     = ClientIdentifier::fromClientId($identifier_key);

        $this->assertSame($identifier_key, $identifier->toString());
    }

    public function testIdentifierKeyNotCorrectlyFormattedIsRejected(): void
    {
        $this->expectException(InvalidClientIdentifierKey::class);
        ClientIdentifier::fromClientId('invalid-id-123');
    }

    public function testGetInternalIdReturnsInternalDatabaseId(): void
    {
        $numeric_id = 28;
        $identifier = ClientIdentifier::fromClientId('tlp-client-id-' . $numeric_id);

        $this->assertSame($numeric_id, $identifier->getInternalId());
    }

    public function testGetInternalIdCastsToInteger(): void
    {
        $identifier = ClientIdentifier::fromClientId('tlp-client-id-007');

        $this->assertSame(7, $identifier->getInternalId());
    }
}
