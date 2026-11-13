# SightPress skeleton

Start a new SightPress project. SightPress is a powerful multi-site, multilingual CMS for everyone, designed with
blind and low-vision creators and audiences in mind, built on modern, object-oriented PHP.

This project holds your configuration and the files that start the app. All features come from
[SightPress core](https://github.com/sightpress/core).

## Requirements

- PHP 8.5 or later
- Composer
- Docker (for the local environment, coming soon)

## Development

Clone `core` and `module-interface` next to this repo (Composer finds them at `../core` and `../module-interface`),
then:

```sh
composer install
composer check
```

Composer commands:

- `composer check`: everything that must pass before a commit. It validates `composer.json`, checks the code
  style, runs static analysis, runs the unit suite with the coverage gate, then runs the integration suite.
- `composer test`: both test suites. `composer test:unit` and `composer test:integration` run one suite each.
- `composer test:coverage`: the unit suite with coverage. It fails if unit tests cover less than 100% of the lines
  or methods in `src/`, and it lists what is not covered. `@codeCoverageIgnore` is not allowed. The HTML report
  is written to `build/coverage/html/`.
- `composer fix`: applies Rector refactorings, then fixes the code style.

Coverage needs the PCOV extension, or Xdebug in coverage mode. If your PHP has neither, use the
development image from `core` (`../core/docker/dev-php/`), which has PHP 8.5, PCOV, SQLite and Composer.
Run it from this folder:

```sh
docker build -t sightpress/dev-php:8.5 ../core/docker/dev-php
docker run --rm --user "$(id -u):$(id -g)" -e HOME=/tmp -v "$PWD/..:/workspace" -w /workspace/skeleton \
  sightpress/dev-php:8.5 composer check
```

The parent folder is mounted so that the sibling repos are visible inside the container.

## Licence

GNU General Public License v3.0 or later. See [LICENSE](LICENSE).
