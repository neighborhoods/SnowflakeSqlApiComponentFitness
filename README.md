# Snowfalke SQL API Component Fitness

Examples of how to use the [Snowfalke SQL API Component](https://github.com/neighborhoods/SnowflakeSqlApiComponent).
All the examples use [DICBC](https://github.com/neighborhoods/DependencyInjectionContainerBuilderComponent) to build the Dependency Injection Container.

## Examples

Some examples demonstrate the usage of the [Client V1](#client-v1), while others demonstrate the [Single Statement Client V1](#single-statement-client-v1).

### Client V1

Examples include:
* [Single Select Statement](#single-select-statement)
* [Two Statements](#two-statements)
* [Multiple Partitions](#multiple-partitions)
* [Data Types](#data-types)
* [Cancel Running Statement](#cancel-running-statement)

#### Single Select Statement

This example makes a single select statement. The response data is available right away and fits on a single page.
This example can be used as a start point for all the other examples.

#### Two Statements

This example makes two select statements. The response contains two handles. Separate requests are made to fetch the results of every individual statement. The response data of the individual statements is available right away and fits on a single page.

#### Multiple Partitions

This example makes one select statements. The response data is available right away. The response data does not fit on a single page/partition. The data is provided in 3 partitions.

#### Data Types

This example makes a single select statement. The statement uses binding variables of various PHP data types. The response data is available right away and fits on a single page. The columns of the response have various data types.

#### Cancel Running Statement

This example makes one recursive select statement with an infinite loop. The initial response indicates the statement is ongoing. The running statement is canceled.

### Single Statement Client V1

Examples include:
* [Select](#select)
* [Execute Paginated](#execute-paginated)
* [Binding Variables](#binding-variables)
* [Ongoing Exception](#ongoing-exception)

#### Select

This example makes a single select statement. The response data is available right away and fits on a single page.
This example can be used as a start point for all the other examples.

#### Execute Paginated

This example makes one select statements. The response data is available right away. The response data does not fit on a single page/partition. The data is provided in 3 partitions. The `executePaginated()` method is used to process one page at a time, instead of processing all the data at once.

#### Binding Variables

This example makes a single select statement. The statement uses binding variables of various PHP data types. The response data is available right away and fits on a single page. The columns of the response have various data types.

#### Ongoing Exception

This example makes one recursive select statement with an infinite loop. The initial response indicates the statement is ongoing, which results in an Ongoing Exception to be thrown. The running statement is canceled under the hood.

## Setup

To run the examples locally follow these steps.

Clone the GitHub repository.
``` bash
# using ssh (recommended)
$ git clone git@github.com:neighborhoods/SnowflakeSqlApiComponentFitness.git
# or HTTP
$ git clone https://github.com/neighborhoods/SnowflakeSqlApiComponentFitness.git
```
Step into the folder and install dependencies. Make `.env` file based on the `.env.example`.
``` bash
$ cd SnowflakeSqlApiComponentFitness
$ composer install
$ cp .env.example .env
```
Update the contents of the `.env` file. The env file contains the credentials for authenticating using [key-pair authentication](https://docs.snowflake.com/en/user-guide/key-pair-auth). The private key should use the RS256 signature algorithm.

Run an example using the `bin/run_proxy.php` script by providing it with the relative path to the example directory.
``` bash
$ bin/run_proxy.php src/ClientV1/SingleSelectStatement
```
