<?php

declare(strict_types=1);

namespace Neighborhoods\SnowflakeSqlApiComponentFitness\ClientV1\MultiplePartitions;

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
    public function buildRequestForMultiplePartitions(): ClientV1\StatementsRequestInterface
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
WITH RECURSIVE NumberSequence (n, long_text_field) AS (
  SELECT 1 AS n, 'Lorem ipsum dolor sit amet' as long_text_field
  UNION ALL
  SELECT n + 1, concat(long_text_field, ', Lorem ipsum dolor sit amet') FROM NumberSequence WHERE n < 100000 
)
SELECT n FROM NumberSequence ORDER BY n
            ");
        $request->setBody($body);
        // Do not set the Header. That will be set by added by the Client
        return $request;
    }

    public function buildGetPartitionRequest(string $handle, int $partition = 0): ClientV1\StatementsRequestInterface
    {
        $request = $this->getClientV1StatementsRequestFactory()->create();
        // method and path are required
        $request->setMethod('GET');
        $request->setPath('/api/v2/statements/' . $handle);
        // query parameters are optional on this particular endpoint
        $request->setQueryParameters(
            $this->getClientV1StatementsRequestQueryParametersFactory()
                ->create()
                ->setPartition($partition)
        );
        // This particular endpoint doesn't accept a body
        // Do not set the Header. That will be set by added by the Client
        return $request;
    }

    public function displayPartitionInfo(ClientV1\ResultSetInterface $result): void
    {
        $ongoing = ($result->getHttpStatusCode() === 202);
        echo $ongoing ? 'Statement did not finish' : 'Statement finished';
        echo PHP_EOL;
        if ($result->hasStatementHandles()) {
            echo 'Mutliple handles since multiple statements were submitted' . PHP_EOL;
            foreach ($result->getStatementHandles() as $handle) {
                echo $handle . PHP_EOL;
            }
            echo 'Use GET /api/v2/statements/{handle} endpoint for each individually' . PHP_EOL;
            return;
        }
        if ($result->hasStatementHandle()) {
            echo 'Statement handle is ' . $result->getStatementHandle() . PHP_EOL;
        } else {
            echo 'Statement handle is not set' . PHP_EOL;
        }
        if ($ongoing) {
            echo 'Use GET /api/v2/statements/{handle} after a while' . PHP_EOL;
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

            echo 'Page contains ' . count($result->getData()) . ' rows.' . PHP_EOL;
        }
    }
}
