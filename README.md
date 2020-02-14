
# PodArchiver

PodArchiver is a simple PHP script for archiving podcast episodes that can be configured in a YAML file.

## Setup

You can either use this Docker container manually on you own Linux server or on an UnRaid system:

### Manually

To create the Docker container run:

`docker run -d --rm --name podarchiver --mount type=bind,source="$(pwd)"/config,target=/root/config,readonly --mount type=bind,source="$(pwd)"/podcasts,target=/root/podcasts ckollross/podarchiver`

- The `config` source directory should contain a copy of `config.yml.dist` (renamed `config.yml`).
- The `podcasts` source directory is where the downloaded episodes will be stored.

You can then download episodes like this:

`docker exec podarchiver php podarchiver.php`

### UnRaid

To use this on UnRaid, just follow these steps:

1. Create a share "podcasts" with a subdirectory called "config". (You can call them differently, but you will need to also change the host paths in the config below.)
2. Click `Docker` -> `Add Container`
3. Set a `Name` you like, for example "podarchiver"
4. Enter "ckollross/podarchiver" as the `Repository`, keep the default `Network Type` and `Console shell command`
5. Add the following two paths by clicking `Add another Path, Port, Variable, Label or Device`:

```
Config Type:    Path
Name:           Config
Container Path: /root/config
Host Path:      /mnt/disk1/podcasts/config/
Access Mode:    Read Only

Config Type:    Path
Name:           Data
Container Path: /root/podcasts
Host Path:      /mnt/disk1/podcasts/
Access Mode:    Read/Write
```

Finally, click `Apply`.

In the "config" folder of your "podcasts" share, create a text file called "config.yaml" and copy&paste the following sample configuration:

```
feeds:
	# Just a name for displaying purposes, for example in the console output
    Tagesschau:
		# Optional, set to true to skip this feed. Useful to still keep the config.
        disabled: false
		
		# Mandatory, the URL of the podcast feed
        feed_url: https://www.tagesschau.de/export/video-podcast/webxl/tagesschau_https/
		
		# Mandatory, directory within the target_dir to which the episodes will be saved
        directory_name: tagesschau
		
		# Optional, first parameter for PHP's preg_replace to change the file name on disk
		# You can test your own on https://www.phpliveregex.com/#tab-preg-replace
        filename_regexp: /TV\-(\d{4})(\d{2})(\d{2})\-\d{4}\-\d{4}\.webxl\.h264\.(.*)/
		
		# Optional, second parameter for PHP's preg_replace to change the file name on disk.
		# This may also include subdirectories, such as the episode year in this example:
        filename_output: $1/Tagesschau_$1-$2-$3_webxl.$4
```

Ideally, start with only one podcast and see how it works out before adding more.
Now you can run `docker exec podarchiver php podarchiver.php` in the UnRaid console to download podcast episodes.
