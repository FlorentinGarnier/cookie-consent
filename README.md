# Cookie Consent

[![CI](https://github.com/FlorentinGarnier/cookie-consent/actions/workflows/ci.yml/badge.svg)](https://github.com/FlorentinGarnier/cookie-consent/actions/workflows/ci.yml)
[![Latest Version](https://img.shields.io/packagist/v/florentingarnier/cookie-consent.svg)](https://packagist.org/packages/florentingarnier/cookie-consent)
[![Total Downloads](https://img.shields.io/packagist/dt/florentingarnier/cookie-consent.svg)](https://packagist.org/packages/florentingarnier/cookie-consent)
[![License: MIT](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)
![PHP](https://img.shields.io/badge/php-%5E8.2-777bb4.svg)

The rules of a cookie consent asked purpose by purpose, for any PHP application: the categories the visitors
consent to, the consent cookie that the server and the browser read, and the proof of each choice.

- Framework-agnostic: plain PHP 8.2, without any dependency.
- No personal data: a consent is identified by a random identifier, never by the IP address or the user.

| Package | Purpose |
|---------|---------|
| **florentingarnier/cookie-consent** (this one) | The consent policy, the cookie and the records, for any PHP application |
| [florentingarnier/cookie-consent-bundle](https://github.com/FlorentinGarnier/cookie-consent-bundle) | Symfony integration: banner, browser script blocking the scripts and embedded contents, Doctrine records, console command |
| [atriametal/sylius-cookie-consent-plugin](https://github.com/atriametal/sylius-cookie-consent-plugin) | Shows the banner in the Sylius shop and lists the records in the admin |

## Requirements

- PHP 8.2 or later

## Installation

```bash
composer require florentingarnier/cookie-consent
```

## Usage

### The policy

A policy lists the categories the visitors are asked to consent to. A required category is always granted: keep it
for the cookies strictly necessary to the site. Changing the version of the policy asks every visitor again.

```php
use FlorentinGarnier\CookieConsent\Category;
use FlorentinGarnier\CookieConsent\ConsentPolicy;

$policy = new ConsentPolicy('1', [
    new Category('necessary', required: true),
    new Category('analytics', services: ['Google Analytics'], cookies: ['_ga', '_ga_*']),
    new Category('media', services: ['YouTube']),
]);
```

`services` are the third parties shown to the visitor; `cookies` are the cookies to delete when the visitor
withdraws the consent, `*` matching any characters.

### The consent

```php
use FlorentinGarnier\CookieConsent\ConsentCookie;
use FlorentinGarnier\CookieConsent\UnknownCategoryException;

$cookie = new ConsentCookie($policy, name: 'cookie_consent', lifetimeInDays: 182, domain: null);

// The visitor chose the categories in $chosen, for instance ['media']
try {
    $consent = $policy->consent($chosen, previousId: $cookie->readId($_COOKIE['cookie_consent'] ?? null));
} catch (UnknownCategoryException $exception) {
    // answer 400 Bad Request
}

setcookie($cookie->name(), $cookie->encode($consent), [
    'expires' => $cookie->expiresAt($consent)->getTimestamp(),
    'path' => '/',
    'domain' => $cookie->domain() ?? '',
    'secure' => true,
    'httponly' => false, // the browser script reads it
    'samesite' => 'Lax',
]);

// Later
$cookie->read($_COOKIE['cookie_consent'] ?? null)?->grants('analytics'); // null without consent to this version
```

The required categories are always granted, in the order of the policy. The identifier of a consent stays the same
when the visitor changes the choice, even after a new version of the policy, so that the successive choices of a
visitor can be found from the identifier shown to the visitor.

The value of the cookie is the JSON `{"id": "…", "version": "…", "categories": […], "date": "…"}`. A malformed value
is ignored: the visitor is asked again.

### The proof of consent

Implement `ConsentRecordRepositoryInterface` with your storage. `ConsentRecord` holds the identifier of the consent,
the version of the policy, the granted categories, the date and, if your application has several sites or channels,
the code of the one on which the visitor consented. It can be extended to be mapped with an ORM:
`ConsentRecord::fromConsent()` creates an instance of the subclass it is called on.

Remove the records older than the retention period you announce, for instance 13 months, with
`removeCreatedBefore()`.

## Contributing

Contributions are welcome: see [CONTRIBUTING.md](CONTRIBUTING.md). Please report security issues privately, as
described in [SECURITY.md](SECURITY.md).

## License

[MIT](LICENSE)
