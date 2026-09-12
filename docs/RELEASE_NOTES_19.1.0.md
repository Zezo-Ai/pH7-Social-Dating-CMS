# pH7Builder 19.1.0 — Release Notes

Released on 12 September 2026, this maintenance update restores reliable login
validation and nested admin navigation following the reports in
[issue #1251](https://github.com/pH7Software/pH7-Social-Dating-CMS/issues/1251).

## Highlights

- Fixes an internal server error when a member enters an incorrect password or
  an unknown email. The login handler now accepts the integer error codes
  returned by the model and shows the intended validation message.
- Restores nested admin menus, including Mod → Blog/Newsletter/Billing and
  Tools → Info, by preventing parent dropdowns from clipping their submenus or
  collapsing when a nested toggle is tapped on mobile.
- Improves Apprise dialog button and tipTip tooltip contrast in light and dark
  themes, with larger dialog button targets.
- Avoids a PHP 8.2 deprecation for profiles without a birth date while preserving
  the existing age fallback.
- Handles concurrent cache-directory creation without logging a false
  permissions error; genuine creation failures still raise an exception.
- Aligns the contributor theme preview with production plugin markup and adds
  regression coverage for these fixes.

## Compatibility and upgrade

No intentional breaking changes. PHP 8.2+ and MySQL 8.0+ remain required, and the
SQL schema stays at `1.6.6`. No database migration is needed from 19.0.1.
Dependency, Bootstrap, jQuery and jQuery UI versions are unchanged.

Back up first and verify a staging copy. Preserve configuration, uploads, custom
modules/themes, language packs and credentials. Deploy PHP and browser assets
together, remove the installer on existing sites, and clear application, CDN
and browser caches. Test valid and invalid logins, signup, Stay signed in and
nested admin menus. Custom login or theme overrides may need the same fixes.
Follow the [Upgrade Guide](https://github.com/pH7Software/pH7-Social-Dating-CMS/blob/v19.1.0/docs/UPGRADING.md).

The `pH7Builder-v19.1.0.zip` asset includes production dependencies. Verify it
with the attached `.sha256` file, then follow the
[Quick Start](https://github.com/pH7Software/pH7-Social-Dating-CMS/blob/v19.1.0/docs/QUICK_START.md) and
[Launch Checklist](https://github.com/pH7Software/pH7-Social-Dating-CMS/blob/v19.1.0/docs/LAUNCH_CHECKLIST.md).
SMTP delivery, SMS, GeoNames and payments still require checks with your own
provider configuration before going live.

[All changes since 19.0.1](https://github.com/pH7Software/pH7-Social-Dating-CMS/compare/v19.0.1...v19.1.0)
