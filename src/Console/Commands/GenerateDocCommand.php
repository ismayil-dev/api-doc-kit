<?php

namespace IsmayilDev\ApiDocKit\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use IsmayilDev\ApiDocKit\Processors\ApiResourceProcessor;
use IsmayilDev\ApiDocKit\Processors\DataSchemaProcessor;
use IsmayilDev\ApiDocKit\Processors\ModelSchemaProcessor;
use IsmayilDev\ApiDocKit\Processors\ResponseResourceProcessor;
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
                // DataSchemaProcessor: Processes #[DataSchema] attributes on DTOs/data classes
                $pipeline->insert(app(DataSchemaProcessor::class), $insertMatch);
                // ResponseResourceProcessor: Processes #[ResponseResource] attributes on API resources
                $pipeline->insert(app(ResponseResourceProcessor::class), $insertMatch);
                //                $pipeline->insert(app(ModelSchemaProcessor::class), $insertMatch);
                // ApiResourceProcessor: Processes #[ApiEndpoint] attributes on controllers
                $pipeline->insert(app(ApiResourceProcessor::class), $insertMatch);
            }
        );

        $doc = $openApi->generate([app_path()]);
        $doc->servers = $servers;

        Storage::put('documentation/openapi.yaml', $doc->toYaml());

        $this->info('Documentation generated successfully!');
    }
}
