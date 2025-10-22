<?php

namespace IsmayilDev\ApiDocKit\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use IsmayilDev\ApiDocKit\Processors\ApiResourceProcessor;
use IsmayilDev\ApiDocKit\Processors\DataSchemaProcessor;
use IsmayilDev\ApiDocKit\Processors\EnumSchemaProcessor;
use IsmayilDev\ApiDocKit\Processors\ResponseResourceProcessor;
use OpenApi\Attributes\Server;
use OpenApi\Attributes\ServerVariable;
use OpenApi\Generator;
use OpenApi\Pipeline;
use OpenApi\Processors\BuildPaths;
use OpenApi\Processors\ExpandEnums;

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
        $insertAfterExpandEnums = function (array $pipes) {
            foreach ($pipes as $ii => $pipe) {
                if ($pipe instanceof ExpandEnums) {
                    return $ii + 1; // Insert AFTER ExpandEnums
                }
            }

            return null;
        };

        $insertBeforeBuildPaths = function (array $pipes) {
            foreach ($pipes as $ii => $pipe) {
                if ($pipe instanceof BuildPaths) {
                    return $ii;
                }
            }

            return null;
        };

        $openApi->withProcessor(
            function (Pipeline $pipeline) use ($insertAfterExpandEnums, $insertBeforeBuildPaths) {
                // EnumSchemaProcessor: Processes #[Enum] attributes on PHP enums
                // MUST run AFTER ExpandEnums to prevent swagger-php from overwriting our enum settings
                $pipeline->insert(app(EnumSchemaProcessor::class), $insertAfterExpandEnums);
                // DataSchemaProcessor: Processes #[DataSchema] attributes on DTOs/data classes
                $pipeline->insert(app(DataSchemaProcessor::class), $insertBeforeBuildPaths);
                // ResponseResourceProcessor: Processes #[ResponseResource] attributes on API resources
                $pipeline->insert(app(ResponseResourceProcessor::class), $insertBeforeBuildPaths);
                //                $pipeline->insert(app(ModelSchemaProcessor::class), $insertBeforeBuildPaths);
                // ApiResourceProcessor: Processes #[ApiEndpoint] attributes on controllers
                $pipeline->insert(app(ApiResourceProcessor::class), $insertBeforeBuildPaths);
            }
        );

        $doc = $openApi->generate([app_path()]);
        $doc->servers = $servers;

        Storage::put('documentation/openapi.yaml', $doc->toYaml());

        $this->info('Documentation generated successfully!');
    }
}
