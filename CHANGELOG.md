# Changelog

All notable changes to this project are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and this project adheres to
[Semantic Versioning](https://semver.org/spec/v2.0.0.html). Until 1.0.0, minor versions may contain breaking
changes.

## [Unreleased]

## [0.1.0] - 2026-11-12

### Added

- The consent policy, its categories and the consent of a visitor, extracted from
  atriametal/sylius-cookie-consent-plugin 0.1.1.
- `ConsentCookie`, which encodes and decodes the value of the consent cookie, without setting it.
- `ConsentRecord`, the proof of a choice, and `ConsentRecordRepositoryInterface`.

[Unreleased]: https://github.com/FlorentinGarnier/cookie-consent/compare/v0.1.0...HEAD
[0.1.0]: https://github.com/FlorentinGarnier/cookie-consent/releases/tag/v0.1.0
