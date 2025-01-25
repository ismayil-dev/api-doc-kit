<?php

declare(strict_types=1);

namespace IsmayilDev\ApiDocKit\Console\Commands;

use Illuminate\Console\Command;
use IsmayilDev\ApiDocKit\Helper\ModelHelper;

class ScanModelsCommand extends Command
{
    protected $signature = 'scan:models';

    protected $description = 'Scan models for documentation';

    public function handle()
    {
        $this->info('Scanning models...');

        new ModelHelper;

        $this->info('Models scanned successfully!');
    }
}
