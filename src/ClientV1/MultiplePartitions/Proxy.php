<?php

declare(strict_types=1);

namespace Neighborhoods\SnowflakeSqlApiComponentFitness\ClientV1\MultiplePartitions;

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
            ->addSourcePath('src/ClientV1/MultiplePartitions')
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
        echo 'Requesting data that does not fit on one partition...' . PHP_EOL;
        $initialResponse = $worker->callApi(
            $worker->buildRequestForMultiplePartitions()
        );
        $worker->displayPartitionInfo($initialResponse);
        for (
            $partitionIndex = 1; // start with 1, because partition with index 0 was provided right away
             $partitionIndex < count($initialResponse->getResultSetMetaData()->getPartitionInfo());
             $partitionIndex++
        ) {
            echo PHP_EOL . 'Getting partition ' . $partitionIndex . PHP_EOL;
            $worker->displayPartitionInfo(
                $worker->callApi(
                    $worker->buildGetPartitionRequest($initialResponse->getStatementHandle(), $partitionIndex)
                )
            );
        }
    }
}
