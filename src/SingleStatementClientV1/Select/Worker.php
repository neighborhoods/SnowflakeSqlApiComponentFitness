<?php

declare(strict_types=1);

namespace Neighborhoods\SnowflakeSqlApiComponentFitness\SingleStatementClientV1\Select;

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

    public function executeHelloWorldStatement(): array
    {
        return $this->getSingleStatementClientV1Client()->execute("SELECT 'Hello World!'");
    }

    public function displayResult(array $data): void
    {
        echo 'Data is present below.' . PHP_EOL;
        var_dump($data);
    }
}
