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

namespace FlorentinGarnier\CookieConsent;

/**
 * The cookie storing the consent, which the browser script reads too: it must not be HTTP only. Its value is the JSON
 * {"id": "…", "version": "…", "categories": […], "date": "…"}: changing it is a breaking change.
 *
 * This class only encodes and decodes the value: setting the cookie is left to the framework.
 */
final class ConsentCookie
{
    private const ID_PATTERN = '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/';

    public function __construct(
        private readonly ConsentPolicy $policy,
        private readonly string $name = 'cookie_consent',
        private readonly int $lifetimeInDays = 182,
        private readonly ?string $domain = null,
    ) {
    }

    /**
     * Returns the consent stored in the value of the cookie, or null if the visitor has not consented to the current
     * version of the policy.
     */
    public function read(mixed $value): ?Consent
    {
        $consent = $this->decode($value);

        return null !== $consent && $consent->policyVersion === $this->policy->version ? $consent : null;
    }

    /**
     * Returns the identifier of the previous consent of the visitor, even to an earlier version of the policy.
     */
    public function readId(mixed $value): ?string
    {
        return $this->decode($value)?->id;
    }

    public function encode(Consent $consent): string
    {
        return json_encode([
            'id' => $consent->id,
            'version' => $consent->policyVersion,
            'categories' => $consent->categories,
            'date' => $consent->givenAt->format(\DATE_ATOM),
        ], \JSON_THROW_ON_ERROR | \JSON_UNESCAPED_SLASHES);
    }

    public function expiresAt(Consent $consent): \DateTimeImmutable
    {
        return $consent->givenAt->modify(\sprintf('+%d days', $this->lifetimeInDays));
    }

    public function name(): string
    {
        return $this->name;
    }

    /**
     * Null for the host of the request only, ".example.com" to share the consent between subdomains.
     */
    public function domain(): ?string
    {
        return $this->domain;
    }

    private function decode(mixed $value): ?Consent
    {
        if (!\is_string($value)) {
            return null;
        }

        try {
            $data = json_decode($value, true, 3, \JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return null;
        }

        if (!\is_array($data) || !self::isValid($data)) {
            return null;
        }

        $givenAt = \DateTimeImmutable::createFromFormat(\DATE_ATOM, $data['date']);

        return false === $givenAt ? null : new Consent($data['id'], $data['version'], array_values($data['categories']), $givenAt);
    }

    /**
     * @param array<array-key, mixed> $data
     *
     * @phpstan-assert-if-true array{id: string, version: string, categories: array<string>, date: string} $data
     */
    private static function isValid(array $data): bool
    {
        return \is_string($data['id'] ?? null) && 1 === preg_match(self::ID_PATTERN, $data['id']) &&
            \is_string($data['version'] ?? null) &&
            \is_array($data['categories'] ?? null) && array_is_list($data['categories']) &&
            [] === array_filter($data['categories'], static fn (mixed $category): bool => !\is_string($category)) &&
            \is_string($data['date'] ?? null);
    }
}
