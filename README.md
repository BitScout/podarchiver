# PodArchiver

Copy config.yml.dist to config/config.yml and change it as needed.

To create the container run:

docker run -it --name podarchiver --mount type=bind,source="$(pwd)"/config,target=/root/config,readonly --mount type=bind,source="$(pwd)"/podcasts,target=/root/podcasts ckollross/podarchiver

You can then download episodes like this:

TODO

