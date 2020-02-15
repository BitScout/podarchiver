<?php

$archiver = new PodArchiver();
$archiver->run();

class PodArchiver
{
    private $config;
    private $targetDir;

    public function __construct(string $configFileName = 'config/config.yaml')
    {
        if (!file_exists($configFileName)) {
            throw new Exception("Config file not found: {$configFileName}");
        }

        $this->config    = yaml_parse_file($configFileName);
        $this->targetDir = array_key_exists('target_dir', $this->config) ? $this->config['target_dir'] : 'podcasts';
    }

    public function run()
    {
        echo "\nPodArchiver STARTED\n";

        if (!file_exists($this->targetDir)) {
            echo "\nCreating target directory";
            mkdir($this->targetDir);
        }

        foreach ($this->config['feeds'] as $feedName => $feed) {
            echo "\n\n\tFeed: {$feedName}";
            $feed['name'] = $feedName;

            $this->treatFeed($feed);
        }

        echo "\n\nPodArchiver DONE\n\n";
    }

    public function treatFeed(array $feedConfig)
    {
        if (array_key_exists('disabled', $feedConfig) && $feedConfig['disabled']) {
            echo "\n\n\t\tSkipping podcast because it is disabled";

            return;
        }

        $feedUrl        = $feedConfig['feed_url'];
        $feedDirName    = $feedConfig['directory_name'];
        $filenameRegexp = !empty($feedConfig['filename_regexp']) ? $feedConfig['filename_regexp'] : false;
        $filenameOutput = !empty($feedConfig['filename_output']) ? $feedConfig['filename_output'] : false;

        $feedDir = sprintf('%s/%s', $this->targetDir, $feedDirName);

        if (!file_exists($feedDir)) {
            echo "\n\tCreating podcast's target directory";
            mkdir($feedDir);
            chmod($feedDir, 0777);
        }

        $blogFeed = new BlogFeed($feedUrl);

        foreach ($blogFeed->getPosts() as $post) {
            $filename = explode('?', basename($post->enclosure))[0];

            echo "\n\n\t\tTreating file ".$filename;
            echo "\n\t\tTitle: ".$post->title;

            if ($filenameRegexp && $filenameOutput) {
                $filename = preg_replace($filenameRegexp, $filenameOutput, $filename);
                $filename = $this->replaceFilenameVariables($filename, $post);

                if ('.' !== dirname($filename)) {
                    $subDir = sprintf('%s/%s', $feedDir, dirname($filename));

                    if (!file_exists($subDir)) {
                        mkdir($subDir);
                        chmod($subDir, 0777);
                    }
                }
            }

            $targetFilePath = sprintf('%s/%s', $feedDir, $filename);

            if ($filenameRegexp && $filenameOutput) {
                echo "\n\t\tas target filename ".$filename;
            }

            if (file_exists($targetFilePath)) {
                echo "\n\t\tSkipping because file already exists";

                continue;
            }

            echo "\n\t\tDownloading ... ";

            $file = file_get_contents($post->enclosure);
            file_put_contents($targetFilePath, $file);
            chmod($targetFilePath, 0666);

            echo 'finished';
            sleep(1);
        }
    }

    private function replaceFilenameVariables(string $filename, BlogPost $post): string
    {
        foreach ($this->getVariablesFromTimestamp($post->timestamp) as $field => $value) {
            $filename = str_replace($field, $value, $filename);
        }

        return $filename;
    }

    private function getVariablesFromTimestamp($timestamp): array
    {
        $fieldNames = str_split('YmdHis');
        $variables  = [];

        foreach ($fieldNames as $field) {
            $variables[sprintf('{%s}', $field)] = date($field, $timestamp);
        }

        return $variables;
    }
}

class BlogPost
{
    public $timestamp;
    public $enclosure;
    public $title;
}

class BlogFeed
{
    private $posts = [];

    public function __construct($url)
    {
        if (!($x = simplexml_load_file($url))) {
            echo "\n\t\tERROR: Feed could not be loaded: {$url}\n";

            return;
        }

        foreach ($x->channel->item as $item) {
            $post            = new BlogPost();
            $post->timestamp = strtotime($item->pubDate);
            $post->enclosure = (string) $item->enclosure->attributes()['url'];
            $post->title     = (string) $item->title;

            $this->posts[] = $post;
        }
    }

    public function getPosts()
    {
        return $this->posts;
    }
}
