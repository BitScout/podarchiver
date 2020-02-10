# PodArchiver

PodArchiver is a simple PHP script for archiving podcast episodes that can be configured in a YAML file.

## Setup

You can either use this Docker container manually on you own Linux server or on an UnRaid system:

### Manually

To create the Docker container run:

`docker run -d --rm --name podarchiver --mount type=bind,source="$(pwd)"/config,target=/root/config,readonly --mount type=bind,source="$(pwd)"/podcasts,target=/root/podcasts ckollross/podarchiver`

- The source directory should contain a copy of `config.yml.dist` (renamed `config.yml`).
- The `podcasts` directory is where the downloaded episodes will be stored.

You can then download episodes like this:

`docker exec podarchiver php podarchiver.php`

### UnRaid

To use this on UnRaid, just follow these steps:

TODO
