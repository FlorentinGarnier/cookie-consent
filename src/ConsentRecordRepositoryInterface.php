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
 * Keeps the proofs of the choices of the visitors.
 */
interface ConsentRecordRepositoryInterface
{
    /**
     * Records the consent the visitor has just given.
     */
    public function record(Consent $consent, ?string $channelCode = null): ConsentRecord;

    /**
     * @return int The number of removed records
     */
    public function removeCreatedBefore(\DateTimeImmutable $date): int;
}
