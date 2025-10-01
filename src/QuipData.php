<?php

class QuipData
{
    protected string $file;

    public function __construct(string $file = __DIR__ . '/../resources/data/quipvid.json')
    {
        $this->file = $file;
    }

    public function all(): array
    {
        if (!file_exists($this->file)) {
            throw new \RuntimeException("Data file not found: {$this->file}");
        }

        $raw = file_get_contents($this->file);
        $json = json_decode($raw, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException("JSON parse error: " . json_last_error_msg());
        }

        return $json['data'] ?? $json;
    }
}
