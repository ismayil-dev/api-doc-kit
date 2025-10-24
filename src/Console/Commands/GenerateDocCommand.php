<?php

namespace IsmayilDev\ApiDocKit\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use IsmayilDev\ApiDocKit\Processors\ApiResourceProcessor;
use IsmayilDev\ApiDocKit\Processors\DataSchemaProcessor;
use IsmayilDev\ApiDocKit\Processors\EnumSchemaProcessor;
use IsmayilDev\ApiDocKit\Processors\ResponseResourceProcessor;
use OpenApi\Attributes\Server;
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
        $doc->servers = $this->getServers();

        Storage::put('documentation/openapi.yaml', $doc->toYaml());

        $this->info('Documentation generated successfully!');
    }

    /**
     * Get configured servers from service container or use defaults
     *
     * @return array<Server>
     */
    private function getServers(): array
    {
        if ($this->laravel->bound('api-doc-kit.servers')) {
            return $this->laravel->make('api-doc-kit.servers');
        }

        return $this->getDefaultServers();
    }

    /**
     * Get default servers based on APP_URL and environment
     *
     * @return array<Server>
     */
    private function getDefaultServers(): array
    {
        $url = config('app.url') ?? 'http://localhost';
        $environment = config('app.env', 'local');

        $description = match ($environment) {
            'production' => 'Production',
            'staging' => 'Staging',
            'local' => 'Local Development',
            default => ucfirst($environment),
        };

        return [
            new Server(
                url: $url,
                description: $description
            ),
        ];
    }
}
