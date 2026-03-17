<?php

declare(strict_types=1);

namespace Neighborhoods\SnowflakeSqlApiComponentFitness\ClientV1\CancelRunningStatement;

use Neighborhoods\SnowflakeSqlApiComponent\ClientV1;

class Worker
{
    use ClientV1\Client\AwareTrait;
    use ClientV1\JwtTokenGenerator\AwareTrait;
    use ClientV1\StatementsRequest\Body\BindVariable\Map\Builder\Factory\AwareTrait;
    use ClientV1\StatementsRequest\Body\Factory\AwareTrait;
    use ClientV1\StatementsRequest\Factory\AwareTrait;
    use ClientV1\StatementsRequest\QueryParameters\Factory\AwareTrait;
    use ClientV1\ResultSet\DataCaster\Factory\AwareTrait;

    public function configureJwtTokenGenerator(): void
    {
        $this->getClientV1Client()
            ->setClientV1JwtTokenGenerator($this->getClientV1JwtTokenGenerator());
    }

    public function callApi(ClientV1\StatementsRequestInterface $request): ClientV1\ResultSetInterface
    {
        $resultSet = $this->getClientV1Client()->authorizeAndSend($request);
        return $resultSet;
    }
    public function buildEternalRunningRequest(): ClientV1\StatementsRequestInterface
    {
        $request = $this->getClientV1StatementsRequestFactory()->create();
        // method and path are required
        $request->setMethod('POST');
        $request->setPath('/api/v2/statements');
        // query parameters are optional on this particular endpoint
        $request->setQueryParameters(
            $this->getClientV1StatementsRequestQueryParametersFactory()
                ->create()
                ->setPartition(0)
        );
        // Body is required on this particular endpoint
        $body = $this->getClientV1StatementsRequestBodyFactory()
            ->create()
            ->setStatement("
WITH RECURSIVE InfiniteSequence AS (
    SELECT 1 AS num
    UNION ALL
    SELECT num + 1
    FROM InfiniteSequence
)
SELECT * FROM InfiniteSequence;
            ");
        $request->setBody($body);
        // Do not set the Header. That will be set by added by the Client
        return $request;
    }

    public function buildCancelationRequest(string $statementHandle): ClientV1\StatementsRequestInterface
    {
        $request = $this->getClientV1StatementsRequestFactory()->create();
        $request->setMethod('POST');
        $request->setPath('/api/v2/statements/' . $statementHandle . '/cancel');
        return $request;
    }

    public function displayResult(ClientV1\ResultSetInterface $result): void
    {
        $ongoing = ($result->getHttpStatusCode() === 202);
        echo $ongoing ? 'Ongoing...' : 'Done!';
        echo PHP_EOL;
        if ($result->hasStatementHandles()) {
            echo 'Mutliple handles since multiple statements were submitted' . PHP_EOL;
            foreach ($result->getStatementHandles() as $handle) {
                echo $handle . PHP_EOL;
            }
            echo 'Use GET /api/v2/statements/{handle} endpoint for each individually' . PHP_EOL;
            return;
        }
        echo 'Statement handle is ' . $result->getStatementHandle() . PHP_EOL;
        if ($ongoing) {
            echo 'Use GET /api/v2/statements/{handle} after a while ';
            echo 'or cancel with POST /api/v2/statements/{handle}/cancel' . PHP_EOL;
        } else {
            if ($result->hasResultSetMetaData()) {
                echo 'ResultSetMetaData is present.' . PHP_EOL;
                echo 'Number of pages is ' . count($result->getResultSetMetaData()->getPartitionInfo()) . PHP_EOL;
                foreach ($result->getResultSetMetaData()->getPartitionInfo() as $index => $partitionInfo) {
                    echo 'Partition at index ' . $index . ' contain ';
                    echo $partitionInfo->getRowCount() . ' rows.' . PHP_EOL;
                }
            } else {
                echo 'ResultSetMetaData is not present.' . PHP_EOL;
            }
        }
    }
}
