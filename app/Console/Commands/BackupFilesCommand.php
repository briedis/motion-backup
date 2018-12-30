<?php


namespace App\Console\Commands;


use App\Lib\FileBackup;
use Illuminate\Console\Command;

class BackupFilesCommand extends Command
{
    protected $signature = 'mb:backup';

    protected $description = 'Backup files';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        /** @var FileBackup $fileBackup */
        $fileBackup = app(FileBackup::class);

        $fileBackup->processNext($this);
    }
}