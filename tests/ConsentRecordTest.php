<?php

/*
 * This file is part of the florentingarnier/cookie-consent package.
 *
 * (c) Florentin Garnier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FlorentinGarnier\CookieConsent\Tests;

use FlorentinGarnier\CookieConsent\Consent;
use FlorentinGarnier\CookieConsent\ConsentRecord;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ConsentRecord::class)]
final class ConsentRecordTest extends TestCase
{
    public function testItProvesTheConsent(): void
    {
        $givenAt = new \DateTimeImmutable('2026-10-20T10:00:00+00:00');

        $record = ConsentRecord::fromConsent(new Consent('0c7a3f9e-1b2d-4c5e-8f9a-0b1c2d3e4f5a', '2', ['necessary', 'media'], $givenAt), 'FASHION_WEB');

        self::assertNull($record->getId());
        self::assertSame('0c7a3f9e-1b2d-4c5e-8f9a-0b1c2d3e4f5a', $record->getConsentId());
        self::assertSame('2', $record->getPolicyVersion());
        self::assertSame(['necessary', 'media'], $record->getCategories());
        self::assertSame($givenAt, $record->getCreatedAt());
        self::assertSame('FASHION_WEB', $record->getChannelCode());
    }

    public function testASubclassIsCreatedFromTheConsent(): void
    {
        $record = ExtendedConsentRecord::fromConsent(new Consent('0c7a3f9e-1b2d-4c5e-8f9a-0b1c2d3e4f5a', '1', [], new \DateTimeImmutable()));

        self::assertNull($record->getChannelCode());
    }
}

final class ExtendedConsentRecord extends ConsentRecord
{
}
