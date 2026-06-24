<?php

namespace App\Traits;

trait HasVideoField
{
    public function isYoutubeVideo(): bool
    {
        return (bool) $this->video && (bool) preg_match('/(?:youtube\.com|youtu\.be)/i', $this->video);
    }

    public function getYoutubeEmbedUrlAttribute(): ?string
    {
        if (!$this->isYoutubeVideo()) {
            return null;
        }

        if (!preg_match('/(?:youtube\.com\/(?:watch\?v=|embed\/|shorts\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $this->video, $matches)) {
            return null;
        }

        $id = $matches[1];

        return "https://www.youtube.com/embed/{$id}?autoplay=1&mute=1&loop=1&playlist={$id}&controls=1&playsinline=1&rel=0";
    }
}
