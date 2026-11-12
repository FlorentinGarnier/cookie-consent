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

use FlorentinGarnier\CookieConsent\Category;
use FlorentinGarnier\CookieConsent\Consent;
use FlorentinGarnier\CookieConsent\ConsentCookie;
use FlorentinGarnier\CookieConsent\ConsentPolicy;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(ConsentCookie::class)]
#[CoversClass(Consent::class)]
final class ConsentCookieTest extends TestCase
{
    private const ID = '0c7a3f9e-1b2d-4c5e-8f9a-0b1c2d3e4f5a';

    public function testItReadsTheConsentItEncoded(): void
    {
        $consent = new Consent(self::ID, '2', ['necessary', 'media'], new \DateTimeImmutable('2026-10-20T10:00:00+00:00'));

        $read = self::cookie('2')->read(self::cookie('2')->encode($consent));

        self::assertEquals($consent, $read);
        self::assertNotNull($read);
        self::assertTrue($read->grants('media'));
        self::assertFalse($read->grants('analytics'));
    }

    public function testTheValueIsTheJsonTheBrowserScriptReads(): void
    {
        $consent = new Consent(self::ID, '2', ['necessary'], new \DateTimeImmutable('2026-10-20T10:00:00+00:00'));

        self::assertSame(
            '{"id":"' . self::ID . '","version":"2","categories":["necessary"],"date":"2026-10-20T10:00:00+00:00"}',
            self::cookie('2')->encode($consent),
        );
    }

    public function testAConsentToAnEarlierVersionOfThePolicyIsIgnoredButKeepsItsIdentifier(): void
    {
        $value = self::cookie('1')->encode(new Consent(self::ID, '1', ['necessary'], new \DateTimeImmutable()));

        self::assertNull(self::cookie('2')->read($value));
        self::assertSame(self::ID, self::cookie('2')->readId($value));
    }

    /**
     * @return iterable<string, array{mixed}>
     */
    public static function malformedValues(): iterable
    {
        yield 'no cookie' => [null];
        yield 'not JSON' => ['{not-json'];
        yield 'not an object' => ['"consent"'];
        yield 'invalid identifier' => ['{"id":"1","version":"2","categories":[],"date":"2026-10-20T10:00:00+00:00"}'];
        yield 'categories not a list' => ['{"id":"' . self::ID . '","version":"2","categories":{"a":"media"},"date":"2026-10-20T10:00:00+00:00"}'];
        yield 'category not a name' => ['{"id":"' . self::ID . '","version":"2","categories":[1],"date":"2026-10-20T10:00:00+00:00"}'];
        yield 'invalid date' => ['{"id":"' . self::ID . '","version":"2","categories":[],"date":"yesterday"}'];
        yield 'too deep' => ['{"id":"' . self::ID . '","version":"2","categories":[[["media"]]],"date":"2026-10-20T10:00:00+00:00"}'];
    }

    #[DataProvider('malformedValues')]
    public function testAMalformedCookieIsIgnored(mixed $value): void
    {
        self::assertNull(self::cookie('2')->read($value));
        self::assertNull(self::cookie('2')->readId($value));
    }

    public function testTheCookieExpiresAfterItsLifetime(): void
    {
        $givenAt = new \DateTimeImmutable('2026-10-20T10:00:00+00:00');
        $cookie = new ConsentCookie(self::policy('2'), 'consent', 30, '.example.com');

        self::assertEquals(new \DateTimeImmutable('2026-11-19T10:00:00+00:00'), $cookie->expiresAt(new Consent(self::ID, '2', [], $givenAt)));
        self::assertSame('consent', $cookie->name());
        self::assertSame('.example.com', $cookie->domain());
    }

    public function testTheIdentifiersAreRandomVersion4Uuids(): void
    {
        $id = Consent::generateId();

        self::assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $id);
        self::assertNotSame($id, Consent::generateId());
    }

    private static function cookie(string $version): ConsentCookie
    {
        return new ConsentCookie(self::policy($version));
    }

    private static function policy(string $version): ConsentPolicy
    {
        return new ConsentPolicy($version, [new Category('necessary', true), new Category('media')]);
    }
}
