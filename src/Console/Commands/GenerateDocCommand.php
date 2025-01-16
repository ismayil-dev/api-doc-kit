<?php

namespace IsmayilDev\LaravelDocKit\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use IsmayilDev\LaravelDocKit\Processors\PathDiscoverProcessor;
use OpenApi\Attributes\Info;
use OpenApi\Attributes\Server;
use OpenApi\Generator;
use OpenApi\Pipeline;
use OpenApi\Processors\BuildPaths;

class GenerateDocCommand extends Command
{
    protected $signature = 'doc:generate';

    protected $description = 'Generate OpenAPI documentation for your API.';

    public function handle()
    {
        $this->info('Generating OpenAPI documentation...');
        $info = new Info(
            version: '1.0.0',
            description: 'Your App documentation',
            title: 'Your App API'
        );

        $openApi = Generator::scan([app_path()]);
        $openApi->info = $info;

//        $openApi = (new Generator());
//
//        $insertMatch = function (array $pipes) {
//            foreach ($pipes as $ii => $pipe) {
//                if ($pipe instanceof BuildPaths) {
//                    return $ii;
//                }
//            }
//
//            return null;
//        };
//
//        $openApi->withProcessor(
//            function (Pipeline $pipeline) use ($insertMatch) {
//                $pipeline->insert(new PathDiscoverProcessor(), $insertMatch);
//            }
//        );
//
//        $servers = [new Server(url: 'https://localhost', description: 'Localhost')];
//
        ;

        Storage::put('documentation/openapi.yaml', $openApi->toYaml());

    }
}