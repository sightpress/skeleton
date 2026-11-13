# SightPress skeleton

Start a new SightPress project. SightPress is a powerful multi-site, multilingual CMS for everyone, designed with
blind and low-vision creators and audiences in mind, built on modern, object-oriented PHP.

This project holds your configuration and the files that start the app. All features come from
[SightPress core](https://github.com/sightpress/core).

## Requirements

- PHP 8.5 or later
- Composer
- Docker with Docker Compose (for the local environment)

## Local environment

Clone `core` and `module-interface` next to this repo (the PHP container and Composer find them at `../core` and
`../module-interface`), then:

```sh
cp .env.example .env   # optional: every setting has a default
docker compose up
```

Open <http://localhost:8080>. Until a site exists, every address shows the "No site configured" page (HTTP 404).
The first start builds the PHP image and runs `composer install` if `vendor/` is missing.

What runs:

- `nginx` on <http://localhost:8080> and `php` (PHP-FPM 8.5 with PCOV and Composer)
- `mailpit`, which catches every e-mail: <http://localhost:8025>
- `minio`, S3-compatible file storage: console on <http://localhost:9001> (user and password in `.env`)

No database runs by default; SightPress uses SQLite. To start a database server, add a profile:

```sh
docker compose --profile mysql up      # or: mariadb, postgres
```

Database containers are only reachable from the other containers, by service name (`mysql`, `mariadb`, `postgres`).
All ports listen on 127.0.0.1 only; change them in `.env` if they are taken.

Shortcuts (`make help` lists them):

- `make up` (add `PROFILE=mysql`, `mariadb` or `postgres`) and `make down`; or `composer up` and `composer down`
- `make check`: `composer check` inside the PHP container
- `make site-create NAME=demo` or `composer site:create demo`: create a site (not available yet)

The command line tool is `bin/sightpress` (`bin/sightpress list` shows its commands).

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

Coverage needs the PCOV extension, or Xdebug in coverage mode. If your PHP has neither, run the checks in the PHP
container with `make check`, or use the development image from `core` (`../core/docker/dev-php/`), which has
PHP 8.5, PCOV, SQLite and Composer. Run it from this folder:

```sh
docker build -t sightpress/dev-php:8.5 ../core/docker/dev-php
docker run --rm --user "$(id -u):$(id -g)" -e HOME=/tmp -v "$PWD/..:/workspace" -w /workspace/skeleton \
  sightpress/dev-php:8.5 composer check
```

The parent folder is mounted so that the sibling repos are visible inside the container.

### Continuous integration

Every pull request and every push to `main` runs the `CI` workflow (`composer check`), with `core` and
`module-interface` checked out next to this repo: `composer validate`, code style, PHPStan, the unit suite with the
100% coverage gate, the integration suite and `composer audit`. The coverage report is uploaded as an artifact.

Commit messages follow [Conventional Commits](https://www.conventionalcommits.org/); release-please uses them to
keep a release pull request and `CHANGELOG.md` up to date.

`main` is protected by the ruleset in `.github/rulesets/main.json`. Changes reach it only through a pull request that
passes the `composer check` check and has one approval. Pull requests are squash-merged. Admins can bypass the ruleset.

## Licence

GNU General Public License v3.0 or later. See [LICENSE](LICENSE).
