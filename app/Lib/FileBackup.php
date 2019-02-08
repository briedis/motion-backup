<?php

namespace App\Lib;

use ChrisWhite\B2\Client;
use Exception;
use Illuminate\Console\Command;
use InvalidArgumentException;

class FileBackup
{
    /** @var Client */
    private $client;

    /** @var string No trailing slash */
    private $path;

    /** @var string */
    private $bucket;

    public function __construct(Client $client, string $sourceDirectory, string $bucket)
    {
        $this->path = realpath($sourceDirectory);

        if (!is_dir($this->path)) {
            throw new InvalidArgumentException('Directory provided does not exist [' . $this->path . ']');
        }

        $this->client = $client;
        $this->bucket = $bucket;
    }

    public function processNext(Command $cli)
    {
        $cli->info('Processing path: ' . $this->path);

        $files = $this->getFiles();

        if (!$files) {
            $cli->info('No files to backup!');
            return;
        }

        $cli->table(['File', 'Size', 'Age'], $files);

        foreach ($files as $v) {
            $this->processFile($v, $cli);
        }
    }

    private function getFiles(): array
    {
        // Backup files older than this (seconds)
        $ageThreshold = 60;

        $list = glob($this->path . '/*.avi');

        $re = [];

        foreach ($list as $v) {
            $age = time() - filemtime($v);
            if ($age < $ageThreshold) {
                // File too young
                continue;
            }

            $re[] = [
                basename($v),
                round(filesize($v) / 1024) . 'kB',
                round($age / 60, 1) . 'min',
            ];
        }

        return $re;
    }

    private function processFile($file, Command $cli)
    {
        $filename = $file[0];
        $sourcePathname = $this->path . DIRECTORY_SEPARATOR . $filename;

        $started = time();
        $cli->info("Uploading file [{$filename}] {$file[1]}");

        $targetPathname = date('Ymd') . '/' . $filename;

        try {
            $this->client->upload([
                'BucketId' => $this->bucket,
                'FileName' => $targetPathname,
                'Body' => fopen($sourcePathname, 'r'),
            ]);
        } catch (Exception $e) {
            \Log::error("Failed to upload " . $targetPathname . " Exception: " . $e->getMessage());
            $cli->error('Failed to upload: ' . $e->getMessage());
            return false;
        }

        $cli->info('Done! Took ' . (time() - $started) . ' seconds. Deleting source file.');

        unlink($sourcePathname);
    }
}