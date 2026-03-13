<?php

declare(strict_types=1);

namespace Neighborhoods\SnowflakeSqlApiComponentFitness\SingleStatementClientV1\OngoingException;

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

    public function executeEternalStatement(): array
    {
        return $this->getSingleStatementClientV1Client()->execute("
WITH RECURSIVE InfiniteSequence AS (
    SELECT 1 AS num
    UNION ALL
    SELECT num + 1
    FROM InfiniteSequence
)
SELECT * FROM InfiniteSequence;
            ");
    }
}
