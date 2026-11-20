# Changelog

## [0.4.0](https://github.com/sightpress/skeleton/compare/0.3.0...0.4.0) (2026-11-20)


### Features

* add the themes section to config/tenant.php (T-1) ([#26](https://github.com/sightpress/skeleton/issues/26)) ([fd7b962](https://github.com/sightpress/skeleton/commit/fd7b962b2856d387048cba61159aac5717ef7db7))
* publish the installed themes' preview pages after composer install and update, for the admin theme picker (T-2) ([#28](https://github.com/sightpress/skeleton/issues/28)) ([e79fab8](https://github.com/sightpress/skeleton/commit/e79fab83204de2dcdd3bb37937fb095fde1e4bf2))

## [0.3.0](https://github.com/sightpress/skeleton/compare/0.2.0...0.3.0) (2026-11-19)


### Bug fixes

* install core 0.3, so a new project gets every M2 feature (Q-5) ([#24](https://github.com/sightpress/skeleton/issues/24)) ([d6e44d7](https://github.com/sightpress/skeleton/commit/d6e44d784476777e0d93baa66a320ce91ec7d785))
* set PHP's and nginx's upload limits together from .env, and show SightPress's own page when a request is too large (M-5) ([#21](https://github.com/sightpress/skeleton/issues/21)) ([866c783](https://github.com/sightpress/skeleton/commit/866c78363367797b50e2a46f84c6173939203f0a))

## [0.2.0](https://github.com/sightpress/skeleton/compare/0.1.0...0.2.0) (2026-11-19)


### Features

* run the scheduled jobs once a minute with a scheduler service, and document the crontab line for servers (P-3) ([#15](https://github.com/sightpress/skeleton/issues/15)) ([ba2b8ee](https://github.com/sightpress/skeleton/commit/ba2b8ee35ed81d06afe138ef6f8730fe15929f82))


### Documentation

* add a code of conduct and a contributing guide ([#20](https://github.com/sightpress/skeleton/issues/20)) ([738c014](https://github.com/sightpress/skeleton/commit/738c014cc3014deae3b0d7ad6957bb4b440e460b))
* show the modules defaults and locks in config/tenant.php (F-2) ([#18](https://github.com/sightpress/skeleton/issues/18)) ([57003ea](https://github.com/sightpress/skeleton/commit/57003ea0624fb247b4e16acf2ec1ef79c7a4e17d))

## 0.1.0 (2026-11-17)


### Features

* add the project skeleton and local environment (S-6) ([#3](https://github.com/sightpress/skeleton/issues/3)) ([82b8400](https://github.com/sightpress/skeleton/commit/82b8400742c7c1e01908aecee5db1b4054f0fee3))
* boot core's kernel from public/index.php (C-1.1) ([#9](https://github.com/sightpress/skeleton/issues/9)) ([efbe67b](https://github.com/sightpress/skeleton/commit/efbe67bcc66b4d5934ea368ee0553600f04c24ff))
* install core from Packagist, with the sibling checkouts as an opt-in for SightPress development (C-8.5) ([#14](https://github.com/sightpress/skeleton/issues/14)) ([4a5a84b](https://github.com/sightpress/skeleton/commit/4a5a84b6dbccf37a9fc666536cb628c3975e2383))
* keep the site registry in config/sites.php (ADR-0002) ([#8](https://github.com/sightpress/skeleton/issues/8)) ([32bdba6](https://github.com/sightpress/skeleton/commit/32bdba646a10951757de6edd398d9afce75040b2))
* run core's console from bin/sightpress (C-2.3) ([#11](https://github.com/sightpress/skeleton/issues/11)) ([1c3fdda](https://github.com/sightpress/skeleton/commit/1c3fddac19a8d64a9f6e949eace5355fb102e8e6))


### Documentation

* say what a new site starts with: starter pages, menus and icons (C-6 wiring) ([#13](https://github.com/sightpress/skeleton/issues/13)) ([a3f1228](https://github.com/sightpress/skeleton/commit/a3f1228ff61ebe45397465cd761990e7b054db1e))
