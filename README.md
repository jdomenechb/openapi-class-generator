# OpenAPI Class Generator

The aim of this library is to turn an OpenAPI v.3.x contract into PHP classes ready to use for communicate with the service behind the contract.

## Installation

```bash
composer require jdomenechb/openapi-class-generator
```

## Usage

```bash
vendor/bin/ocg generate <inputPathFolder> <outputPathFolder>
```

**WARNING:** The contents of the output folder will be deleted entirely at each generation.