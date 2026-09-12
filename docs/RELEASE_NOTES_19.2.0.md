# pH7Builder 19.2.0 — Release Notes

Released on 12 September 2026, this minor maintenance release refreshes 19
locked PHP dependencies and adds offline compatibility coverage for the SDKs
used by pH7Builder. It includes all login and navigation fixes from 19.1.0.

## Highlights

- Updates AWS, Braintree and Twilio SDKs within their existing major versions.
- Updates Guzzle, certificate bundles, GeoIP decoding and Symfony components.
- Includes upstream Braintree request-path validation fixes and MaxMind decoder
  resource limits. These are dependency hardening changes, not a claim that
  a vulnerability was found in pH7Builder's own application code.
- Refreshes PHPStan, PHP CS Fixer and supporting development dependencies.
- Adds offline checks for HTTP promises, S3 signing, SMS request construction,
  Braintree client tokens, the bundled GeoIP database and MIME email generation.

## Dependency versions

| Package | From | To |
| --- | --- | --- |
| aws/aws-sdk-php | 3.390.2 | 3.395.0 |
| braintree/braintree_php | 6.36.0 | 6.37.0 |
| composer/ca-bundle | 1.5.13 | 1.5.14 |
| friendsofphp/php-cs-fixer | 3.95.18 | 3.95.25 |
| guzzlehttp/guzzle | 7.15.2 | 7.15.5 |
| guzzlehttp/promises | 2.5.1 | 2.5.3 |
| guzzlehttp/psr7 | 2.13.0 | 2.13.1 |
| maxmind-db/reader | 1.13.1 | 1.14.0 |
| myclabs/deep-copy | 1.13.4 | 1.14.0 |
| phpstan/phpstan | 2.2.7 | 2.2.13 |
| symfony/event-dispatcher | 7.4.15 | 7.4.17 |
| symfony/filesystem | 7.4.15 | 7.4.18 |
| symfony/finder | 7.4.14 | 7.4.17 |
| symfony/mime | 7.4.15 | 7.4.18 |
| symfony/polyfill-intl-idn | 1.38.1 | 1.42.0 |
| symfony/polyfill-intl-normalizer | 1.38.0 | 1.42.0 |
| symfony/process | 7.4.13 | 7.4.18 |
| symfony/service-contracts | 3.7.1 | 3.7.3 |
| twilio/sdk | 8.11.6 | 8.12.1 |

## Compatibility and upgrade

PHP 8.2+, MySQL 8.0+ and schema `1.6.6` are unchanged. No database migration is
needed from 19.1.0. Bootstrap, jQuery, jQuery UI and Smarty versions are unchanged.

**Optional extension requirement:** hosts with compiled `ext-maxminddb` must use
`>=1.14.0 <2.0.0`. The updated reader conflicts with older extension versions;
pure-PHP installations do not need the extension. Custom Twilio integrations
must review the retired WhatsApp Senders v1 endpoints and preview API changes.
The bundled SMS integration continues to use the 2010 Messages API.
See [MaxMind's release notes](https://github.com/maxmind/MaxMind-DB-Reader-php/releases/tag/v1.14.0),
[Twilio's release notes](https://github.com/twilio/twilio-php/releases/tag/8.12.1) and
[Braintree's changelog](https://github.com/braintree/braintree_php/blob/6.37.0/CHANGELOG.md).

Back up first and follow the [Upgrade Guide](https://github.com/pH7Software/pH7-Social-Dating-CMS/blob/v19.2.0/docs/UPGRADING.md).
Deploy the complete package, preserve configuration and user data, remove the
installer on existing sites, and clear caches. Source deployments must install
dependencies from the committed lock file. Offline tests do not verify provider
credentials, SMS/email delivery, storage access or successful live payments;
check your configured services in staging before going live.

The GeoIP reader update does not refresh location data. The bundled legacy
GeoLite2-City snapshot is still dated 3 December 2019; obtain current data with
your own free MaxMind account and follow the
[database update instructions](https://github.com/pH7Software/pH7-Social-Dating-CMS/blob/v19.2.0/_protected/framework/Geo/Ip/update-geo-database-version.txt).

The `pH7Builder-v19.2.0.zip` asset includes production dependencies. Verify its
attached SHA-256 checksum, then use the
[Quick Start](https://github.com/pH7Software/pH7-Social-Dating-CMS/blob/v19.2.0/docs/QUICK_START.md) and
[Launch Checklist](https://github.com/pH7Software/pH7-Social-Dating-CMS/blob/v19.2.0/docs/LAUNCH_CHECKLIST.md).

[All changes since 19.1.0](https://github.com/pH7Software/pH7-Social-Dating-CMS/compare/v19.1.0...v19.2.0)
