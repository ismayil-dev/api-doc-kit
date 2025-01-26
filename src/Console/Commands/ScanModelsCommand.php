<?php

declare(strict_types=1);

namespace IsmayilDev\ApiDocKit\Console\Commands;

use Illuminate\Console\Command;
use IsmayilDev\ApiDocKit\Mappers\ModelMapper;

class ScanModelsCommand extends Command
{
    protected $signature = 'scan:models';

    protected $description = 'Scan models for documentation';

    public function handle()
    {
        $this->info('Scanning models...');

        new ModelMapper;

        $this->info('Models scanned successfully!');
    }
}
