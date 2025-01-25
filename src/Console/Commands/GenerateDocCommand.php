<?php

namespace IsmayilDev\ApiDocKit\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use IsmayilDev\ApiDocKit\Processors\ApiResourceProcessor;
use OpenApi\Attributes\Server;
use OpenApi\Attributes\ServerVariable;
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

        $openApi = new Generator;
        $servers = [new Server(
            url: 'https://localhost',
            description: 'Localhost',
            variables: [
                new ServerVariable('email', 'Your email', 'me@ismayil.dev'),
                new ServerVariable('password', 'Your password', 'password'),
            ]
        )];
        $insertMatch = function (array $pipes) {
            foreach ($pipes as $ii => $pipe) {
                if ($pipe instanceof BuildPaths) {
                    return $ii;
                }
            }

            return null;
        };

        $openApi->withProcessor(
            function (Pipeline $pipeline) use ($insertMatch) {
                $pipeline->insert(app(ApiResourceProcessor::class), $insertMatch);
            }
        );

        $doc = $openApi->generate([app_path()]);
        $doc->servers = $servers;

        Storage::put('documentation/openapi.yaml', $doc->toYaml());

        $this->info('Documentation generated successfully!');
    }
}
