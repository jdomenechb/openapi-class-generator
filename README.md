# OpenAPI Class Generator
[![Build Status](https://travis-ci.org/jdomenechb/openapi-class-generator.svg?branch=master)](https://travis-ci.org/jdomenechb/openapi-class-generator) [![Mutation testing badge](https://badge.stryker-mutator.io/github.com/jdomenechb/openapi-class-generator/master)](https://stryker-mutator.github.io)

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

The namespace for the generated files can be defined by using `--namespace`:

```bash
vendor/bin/ocg generate contracts src-generated --namespace An\\Org\\Namespace
```

