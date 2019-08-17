<?php

declare(strict_types=1);

/**
 * This file is part of the openapi-class-generator package.
 *
 * (c) Jordi Domènech Bonilla
 */

namespace Jdomenechb\OpenApiClassGenerator\ApiParser\Cebe;


use cebe\openapi\exceptions\TypeErrorException;
use cebe\openapi\exceptions\UnresolvableReferenceException;
use cebe\openapi\Reader;
use cebe\openapi\spec\OpenApi;
use cebe\openapi\SpecObjectInterface;
use RuntimeException;
use function in_array;

class CebeOpenapiFileReader
{
    /**
     * @param string $filename
     *
     * @return OpenApi|SpecObjectInterface
     * @throws TypeErrorException
     * @throws UnresolvableReferenceException
     */
    public function read(string $filename) : OpenApi
    {
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (in_array($ext, ['yaml', 'yml'])) {
            $contract = Reader::readFromYamlFile($filename);
        } elseif ($ext === 'json') {
            $contract = Reader::readFromJsonFile($filename);
        } else {
            throw new RuntimeException('Invalid contract extension: ' . $ext);
        }

        return $contract;
    }
}