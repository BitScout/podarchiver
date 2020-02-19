<?php

namespace App\Command;

use App\Entity\Feed;
use App\Entity\FeedEntry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class DownloadCommand extends Command
{
    protected static $defaultName = 'app:download';

    /**
     * @var InputInterface
     */
    private $input;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var string
     */
    private $config;

    /**
     * @var array
     */
    private $targetDir;

    public function __construct(Filesystem $filesystem)
    {
        parent::__construct();

        $this->filesystem = $filesystem;
    }

    protected function configure(): void
    {
        $this->setDescription('Downloads podcast episodes based on the file `podconf/config.yaml`.');
    }

    /**
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln("\n<info>PodArchiver STARTED</info>\n");

        $this->input  = $input;
        $this->output = $output;

        $this->loadConfig();
        $this->iterateFeeds();

        $output->writeln("\n<info>PodArchiver DONE</info>\n");

        return 0;
    }

    /**
     * @throws \Exception
     */
    private function iterateFeeds(): void
    {
        foreach ($this->config['feeds'] as $feedName => $feed) {
            $this->output->writeln(sprintf("\nFeed: %s", $feedName));
            $feed['name'] = $feedName;

            $this->treatFeed($feed);
        }
    }

    /**
     * @throws \Exception
     */
    private function treatFeed(array $feedConfig): void
    {
        if (array_key_exists('disabled', $feedConfig) && $feedConfig['disabled']) {
            $this->output->writeln("\tSkipping podcast because it is disabled");

            return;
        }

        $feedUrl        = $feedConfig['feed_url'];
        $feedDirName    = $feedConfig['directory_name'];
        $filenameRegexp = !empty($feedConfig['filename_regexp']) ? $feedConfig['filename_regexp'] : false;
        $filenameOutput = !empty($feedConfig['filename_output']) ? $feedConfig['filename_output'] : false;

        $feedDir = sprintf('%s/%s', $this->targetDir, $feedDirName);

        if (!$this->filesystem->exists($feedDir)) {
            $this->output->writeln("\tCreating podcast's target directory");

            $this->filesystem->mkdir($feedDir);
            $this->filesystem->chmod($feedDir, 0777);
        }

        $feed = new Feed($feedUrl);

        /** @var FeedEntry $entry */
        foreach ($feed->getEntries() as $entry) {
            $filename = explode('?', basename($entry->getEnclosure()))[0];

            $this->output->writeln(sprintf("\n\tTreating file %s", $filename));
            $this->output->writeln(sprintf("\tTitle: %s", $entry->getTitle()));

            // TODO
        }
    }

    /**
     * @throws \Exception
     */
    private function loadConfig(string $configFilename = 'podconf/config.yaml'): void
    {
        if (!file_exists($configFilename)) {
            throw new \Exception(sprintf('Config file not found: %s', $configFilename));
        }

        $this->config    = \yaml_parse_file($configFilename);
        $this->targetDir = array_key_exists('target_dir', $this->config) ? $this->config['target_dir'] : 'podcasts';

        $feedCount         = count($this->config['feeds']);
        $feedCountDisabled = 0;

        foreach ($this->config['feeds'] as $feedName => $feed) {
            if (array_key_exists('disabled', $feed) && $feed['disabled']) {
                ++$feedCountDisabled;
            }
        }

        $this->output->writeln(sprintf(
            "Feed config loaded, found %u enabled feeds and %u disabled feeds\n",
            $feedCount - $feedCountDisabled,
            $feedCountDisabled
        ));
    }
}
