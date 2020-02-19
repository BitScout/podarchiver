<?php

namespace App\Entity;

class Feed
{
    /**
     * @var array
     */
    private $entries;

    /**
     * @throws \Exception
     */
    public function __construct(string $url)
    {
        if (!($xml = simplexml_load_file($url))) {
            throw new \InvalidArgumentException('Feed XML could not be parsed');
        }

        foreach ($xml->channel->item as $item) {
            $this->entries[] = new FeedEntry($item);
        }
    }

    public function getEntries(): array
    {
        return $this->entries;
    }
}
