<?php

declare(strict_types=1);

namespace Neighborhoods\SnowflakeSqlApiComponentFitness\SingleStatementClientV1\ExecutePaginated;

use Generator;
use Neighborhoods\SnowflakeSqlApiComponent\ClientV1;
use Neighborhoods\SnowflakeSqlApiComponent\SingleStatementClientV1;

class Worker
{
    use SingleStatementClientV1\Client\AwareTrait;
    use ClientV1\JwtTokenGenerator\AwareTrait;

    public function configureJwtTokenGenerator(): void
    {
        $this->getSingleStatementClientV1Client()
            ->setClientV1JwtTokenGenerator($this->getClientV1JwtTokenGenerator());
    }

    public function executePaginated(): Generator
    {
        return $this->getSingleStatementClientV1Client()->executePaginated("
WITH RECURSIVE NumberSequence (n, long_text_field) AS (
  SELECT 1 AS n, 'Lorem ipsum dolor sit amet' as long_text_field
  UNION ALL
  SELECT n + 1, concat(long_text_field, ', Lorem ipsum dolor sit amet') FROM NumberSequence WHERE n < 100000 
)
SELECT n FROM NumberSequence ORDER BY n
            ");
    }

    public function displayResult(array $data): void
    {
        echo 'Number of rows is ' . count($data) . PHP_EOL;
        echo 'First row is ' . $data[0]['N'] . PHP_EOL;
        echo 'Last row is ' . end($data)['N'] . PHP_EOL;
    }
}
