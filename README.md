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

```sh
composer create-project sightpress/skeleton my-site
cd my-site
cp .env.example .env   # optional: every setting has a default
docker compose up
```

Open <http://localhost:8080>. Until a site exists, every address shows the "No site configured" page (HTTP 404).
The first start builds the PHP image and runs `composer install` if `vendor/` is missing.

What runs:

- `nginx` on <http://localhost:8080> and `php` (PHP-FPM 8.5 with PCOV and Composer)
- `scheduler`, which runs `bin/sightpress schedule:run` once a minute (scheduled publishing and other jobs at a set
  time); on a server, use one crontab line instead (see `docs-todo/scheduler.md`)
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
- `make site-create NAME=demo` or `composer site:create demo`: create a site (asks for the rest)

The command line tool is `bin/sightpress` (`bin/sightpress list` shows its commands).

### Your first site

1. Set `APP_SECRET` in `.env` (the file explains how to make one) and keep a copy of it somewhere safe.
2. Run `make site-create NAME=demo` and answer the questions. In development use the domain `localhost`, a database
   such as `sqlite:////app/sites/demo.sqlite`, and add `--url=http://localhost:8080` so the printed link works:

   ```sh
   docker compose run --rm --no-deps php bin/sightpress site:create demo --domain=localhost \
     --db=sqlite:////app/sites/demo.sqlite --url=http://localhost:8080
   ```

3. It prints a one-time setup code and the page to type it on. Go there, enter the code and choose the first
   admin's password, then sign in at http://localhost:8080/login.
4. Open http://localhost:8080/ to see the public site. Every new site starts with six published pages (Home, About,
   Contact with a contact form, Privacy, Terms and an Accessibility statement), main and footer menus, and icons and
   a share image made from its name, all in the default theme. Outside production (`APP_ENV`), every page asks
   search engines not to index it.

## Development

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

### Working on SightPress itself

To change `core` or `module-interface` and see the result here, clone them next to this repo
(`../core`, `../module-interface`) and use those checkouts instead of the packages from Packagist:

- With Docker: uncomment `COMPOSE_FILE` in `.env`. `docker-compose.siblings.yml` mounts the two folders in the PHP
  container, and its entrypoint adds them to Composer as path repositories. Delete `vendor/` once so that the next
  start installs again.
- Without Docker: add them to your global Composer configuration, then run `composer update` in this folder (the
  paths are relative to it):

  ```sh
  composer config --global repositories.sightpress-core '{"type": "path", "url": "../core", "options": {"symlink": true}}'
  composer config --global repositories.sightpress-module-interface '{"type": "path", "url": "../module-interface", "options": {"symlink": true}}'
  ```

  `composer config --global --unset repositories.sightpress-core` (and `…-module-interface`) undoes it.
- `make check` runs the checks in the PHP container, so it uses the checkouts too when `COMPOSE_FILE` is set.

### Continuous integration

Every pull request and every push to `main` runs the `CI` workflow (`composer check`), with the `main` branches of
`core` and `module-interface` checked out next to this repo and used as path repositories: `composer validate`, code
style, PHPStan, the unit suite with the 100% coverage gate, the integration suite and `composer audit`. The coverage report is uploaded as an artifact.

Commit messages follow [Conventional Commits](https://www.conventionalcommits.org/); release-please uses them to
keep a release pull request and `CHANGELOG.md` up to date.

`main` is protected by the ruleset in `.github/rulesets/main.json`. Changes reach it only through a pull request that
passes the `composer check` check and has one approval. Pull requests are squash-merged. Admins can bypass the ruleset.

## Contributing

Everyone can help, not only developers. See [CONTRIBUTING.md](CONTRIBUTING.md) for the ways to take part, and
the [Code of Conduct](CODE_OF_CONDUCT.md) that everyone who takes part agrees to follow.

## Licence

GNU General Public License v3.0 or later. See [LICENSE](LICENSE).
