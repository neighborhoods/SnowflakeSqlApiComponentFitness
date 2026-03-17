<?php

declare(strict_types=1);

namespace Neighborhoods\SnowflakeSqlApiComponentFitness\ClientV1\TwoStatements;

use Neighborhoods\DependencyInjectionContainerBuilderComponent\TinyContainerBuilder;
use RuntimeException;
use Symfony\Component\DependencyInjection\Compiler\AnalyzeServiceReferencesPass;
use Symfony\Component\DependencyInjection\Compiler\InlineServiceDefinitionsPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class Proxy
{
    public function run(): void
    {
        $rootPath = realpath(dirname(__DIR__, 3));
        if ($rootPath === false) {
            throw new RuntimeException('Absolute path of the root directory not found.');
        }
        $tdc = 'vendor/neighborhoods/throwable-diagnostic-component';
        $container = (new TinyContainerBuilder())
            ->setContainerBuilder((new ContainerBuilder()))
            ->setRootPath($rootPath)
            ->addSourcePath('vendor/neighborhoods/snowflake-sql-api-component/fab/ClientV1')
            ->addSourcePath('vendor/neighborhoods/snowflake-sql-api-component/src/ClientV1')
            ->addSourcePath('src/ClientV1/TwoStatements')
            ->addSourcePath('src/Vendor')
            ->addSourcePath($tdc . '/fab/ThrowableDiagnosticV1')
            ->addSourcePath($tdc . '/src/ThrowableDiagnosticV1')
            ->addSourcePath($tdc . '/fab/ThrowableDiagnosticV1Decorators/GuzzleV1')
            ->addSourcePath($tdc . '/src/ThrowableDiagnosticV1Decorators/GuzzleV1')
            ->addCompilerPass(new AnalyzeServiceReferencesPass())
            ->addCompilerPass(new InlineServiceDefinitionsPass())
            ->makePublic(Worker::class)
            ->build();
        /** @var Worker $worker */
        $worker = $container->get(Worker::class);
        $initialResponse = $worker->callApi(
            $worker->buildDoubleHelloWorldRequest()
        );
        $worker->displayResult(
            $initialResponse
        );
        foreach ($initialResponse->getStatementHandles() as $handle) {
            echo PHP_EOL . 'Fetching data for handle ' . $handle .  PHP_EOL;
            $worker->displayResult(
                $worker->callApi(
                    $worker->buildGetPartitionRequest($handle)
                )
            );
        }
    }
}
