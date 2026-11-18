# The scheduler

## What it does

Some things happen at a set time: a page that is approved and scheduled is published at its time, a scheduled page
is taken off the site at its end time, and modules add their own jobs (for example the analytics roll-up). All of
them run when `bin/sightpress schedule:run` runs, for every site. It needs to run **once a minute**.

The command is safe to run as often as you like: when a run is still going, the next one stops straight away
("Another run is still going"), and nothing is done twice. A run that was missed (the server was off) catches up on
the next one: a page whose time has passed is published then.

## Locally

`docker compose up` starts a `scheduler` service next to the app. It waits until the dependencies are installed,
then runs the command every 60 seconds. See what it does with `docker compose logs -f scheduler`.

## On a server

Add one line to the crontab of the user that runs the site (`crontab -e`), with the path of your project:

```
* * * * * cd /var/www/my-site && php bin/sightpress schedule:run --no-interaction >> var/log/schedule.log 2>&1
```

With containers, run a second container from the app's image with the same settings and volumes, whose command is a
loop of `php bin/sightpress schedule:run --no-interaction` and `sleep 60`, like the `scheduler` service in
`docker-compose.yml`. Run it in one place only per installation: the lock that stops two runs at once is a file in the project's `var/` folder, so it only covers runs that share that folder.

## Code and tests

- `docker-compose.yml` (`scheduler`), `docker-compose.siblings.yml`
- The command itself is core's (`schedule:run`, P-3).
