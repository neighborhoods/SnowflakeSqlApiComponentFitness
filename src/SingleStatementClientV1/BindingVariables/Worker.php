<?php

declare(strict_types=1);

namespace Neighborhoods\SnowflakeSqlApiComponentFitness\SingleStatementClientV1\BindingVariables;

use DateTimeImmutable;
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

    public function executeStatementWithManyDataTypes(): array
    {
        return $this->getSingleStatementClientV1Client()->execute(
            "select ? as int_value,
                ? as real_value,
                ? as bool_value,
                ? as text_value,
                ? as null_value,
                ? as time_here,
                current_timestamp() as time_there",
            [1, 1.2, true, 'text', null, new DateTimeImmutable()]
        );
    }

    public function displayResult(array $data): void
    {
        echo 'Data is present below.' . PHP_EOL;
        var_dump($data);
    }
}
