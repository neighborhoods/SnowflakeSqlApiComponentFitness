<?php

declare(strict_types=1);

namespace Neighborhoods\SnowflakeSqlApiComponentFitness\ClientV1\TwoStatements;

use Neighborhoods\SnowflakeSqlApiComponent\ClientV1;

class Worker
{
    use ClientV1\Client\AwareTrait;
    use ClientV1\JwtTokenGenerator\AwareTrait;
    use ClientV1\StatementsRequest\Body\BindVariable\Map\Builder\Factory\AwareTrait;
    use ClientV1\StatementsRequest\Body\Factory\AwareTrait;
    use ClientV1\StatementsRequest\Body\Parameters\Factory\AwareTrait;
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

    public function buildDoubleHelloWorldRequest(): ClientV1\StatementsRequestInterface
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
            ->setStatement("SELECT 'Hello World';SELECT 'Hello Again'")
            ->setParameters(
                $this->getClientV1StatementsRequestBodyParametersFactory()
                    ->create()
                    ->setMultiStatementCount('2')
            );
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

    public function displayResult(ClientV1\ResultSetInterface $result): void
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
        echo 'Statement handle is ' . $result->getStatementHandle() . PHP_EOL;
        if ($ongoing) {
            echo 'Use GET /api/v2/statements/{handle} after a while' . PHP_EOL;
        } else {
            echo 'Number of pages is ' . count($result->getResultSetMetaData()->getPartitionInfo()) . PHP_EOL;
            echo 'Use GET /api/v2/statements/{handle} until you get to the last page' . PHP_EOL;
            $currentPageData = $this->getClientV1ResultSetDataCasterFactory()
                ->create()
                ->setRows($result->getData())
                ->setClientV1ResultSetResultSetMetaDataRowTypeMap($result->getResultSetMetaData()->getRowType())
                ->cast();
            echo 'Casted current page data is present below.' . PHP_EOL;
            var_dump($currentPageData);
        }
    }
}
