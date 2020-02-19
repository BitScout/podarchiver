<?php

namespace App\Entity;

class FeedEntry
{
    /**
     * @var \DateTime
     */
    private $timestamp;

    /**
     * @var string
     */
    private $enclosure;

    /**
     * @var string
     */
    private $title;

    /**
     * @throws \Exception
     */
    public function __construct(\SimpleXMLElement $item)
    {
        $this->timestamp = new \DateTime($item->pubDate);
        $this->enclosure = (string) $item->enclosure->attributes()['url'];
        $this->title     = trim($item->title);
    }

    public function getTimestamp(): \DateTime
    {
        return $this->timestamp;
    }

    public function getEnclosure(): string
    {
        return $this->enclosure;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): FeedEntry
    {
        $this->title = $title;

        return $this;
    }
}
