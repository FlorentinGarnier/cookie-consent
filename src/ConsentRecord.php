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
 * The proof of a choice of a visitor. It holds no personal data: neither the IP address nor the user.
 *
 * It can be extended, for instance to map it with an ORM.
 *
 * @phpstan-consistent-constructor
 */
class ConsentRecord
{
    private ?int $id = null;

    /**
     * @param list<string> $categories
     * @param string|null  $channelCode The site, channel or tenant on which the visitor consented, if the application has several
     */
    public function __construct(
        private string $consentId,
        private string $policyVersion,
        private array $categories,
        private \DateTimeImmutable $createdAt,
        private ?string $channelCode = null,
    ) {
    }

    public static function fromConsent(Consent $consent, ?string $channelCode = null): static
    {
        return new static($consent->id, $consent->policyVersion, $consent->categories, $consent->givenAt, $channelCode);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getConsentId(): string
    {
        return $this->consentId;
    }

    public function getPolicyVersion(): string
    {
        return $this->policyVersion;
    }

    /**
     * @return list<string>
     */
    public function getCategories(): array
    {
        return $this->categories;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getChannelCode(): ?string
    {
        return $this->channelCode;
    }
}
