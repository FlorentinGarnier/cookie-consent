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
use FlorentinGarnier\CookieConsent\ConsentPolicy;
use FlorentinGarnier\CookieConsent\UnknownCategoryException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ConsentPolicy::class)]
#[CoversClass(Category::class)]
#[CoversClass(UnknownCategoryException::class)]
final class ConsentPolicyTest extends TestCase
{
    public function testTheRequiredCategoriesAreAlwaysGranted(): void
    {
        self::assertSame(['necessary'], self::policy()->grantedCategories([]));
    }

    public function testTheGrantedCategoriesFollowTheOrderOfThePolicy(): void
    {
        self::assertSame(['necessary', 'analytics', 'media'], self::policy()->grantedCategories(['media', 'analytics', 'media']));
    }

    public function testAnUnknownCategoryIsRejected(): void
    {
        $this->expectException(UnknownCategoryException::class);
        $this->expectExceptionMessage('The consent policy has no "advertising" category.');

        self::policy()->grantedCategories(['analytics', 'advertising']);
    }

    public function testACategoryMustBeAName(): void
    {
        $this->expectException(UnknownCategoryException::class);

        self::policy()->grantedCategories([['analytics']]);
    }

    public function testItIsBuiltFromTheConfiguration(): void
    {
        $policy = ConsentPolicy::fromConfiguration('3', [
            'necessary' => ['required' => true, 'services' => [], 'cookies' => []],
            'analytics' => ['required' => false, 'services' => ['Google Analytics'], 'cookies' => ['_ga', '_ga_*']],
        ]);

        self::assertSame('3', $policy->version);
        self::assertEquals(new Category('analytics', false, ['Google Analytics'], ['_ga', '_ga_*']), $policy->categories()['analytics']);
        self::assertTrue($policy->categories()['necessary']->required);
    }

    public function testAConsentGrantsTheChosenAndTheRequiredCategoriesOfTheCurrentVersion(): void
    {
        $givenAt = new \DateTimeImmutable('2026-10-20T10:00:00+00:00');

        $consent = self::policy()->consent(['media'], null, $givenAt);

        self::assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $consent->id);
        self::assertSame('1', $consent->policyVersion);
        self::assertSame(['necessary', 'media'], $consent->categories);
        self::assertSame($givenAt, $consent->givenAt);
    }

    public function testANewChoiceKeepsTheIdentifierOfThePreviousConsent(): void
    {
        self::assertSame('0c7a3f9e-1b2d-4c5e-8f9a-0b1c2d3e4f5a', self::policy()->consent([], '0c7a3f9e-1b2d-4c5e-8f9a-0b1c2d3e4f5a')->id);
    }

    private static function policy(): ConsentPolicy
    {
        return new ConsentPolicy('1', [new Category('necessary', true), new Category('analytics'), new Category('media')]);
    }
}
