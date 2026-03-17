<?php

declare(strict_types=1);

namespace Neighborhoods\SnowflakeSqlApiComponentFitness\SingleStatementClientV1\OngoingException;

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
            ->addSourcePath('vendor/neighborhoods/snowflake-sql-api-component/fab/SingleStatementClientV1')
            ->addSourcePath('vendor/neighborhoods/snowflake-sql-api-component/src/SingleStatementClientV1')
            ->addSourcePath('src/SingleStatementClientV1/OngoingException')
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
        echo 'Starting query which runs forever. ';
        echo 'An "ongoing" response will be received when the execution threshold gets exceeded...' . PHP_EOL;
        try {
            $worker->executeEternalStatement();
            echo 'execute method finished without throwing an exception.' . PHP_EOL;
        } catch (\Throwable $exception) {
            echo 'Caught exception ' . get_class($exception) . PHP_EOL;
            echo $exception->getMessage() . PHP_EOL;
        }
    }
}
