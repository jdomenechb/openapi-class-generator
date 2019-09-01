<?php

declare(strict_types=1);

/**
 * This file is part of the openapi-class-generator package.
 *
 * (c) Jordi Domènech Bonilla
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Jdomenechb\OpenApiClassGenerator\ApiParser\Cebe;

use cebe\openapi\exceptions\TypeErrorException;
use cebe\openapi\exceptions\UnresolvableReferenceException;
use cebe\openapi\Reader;
use cebe\openapi\spec\OpenApi;
use cebe\openapi\SpecObjectInterface;
use RuntimeException;

class CebeOpenapiFileReader
{
    /**
     * @param string $filename
     *
     * @throws TypeErrorException
     * @throws UnresolvableReferenceException
     *
     * @return OpenApi|SpecObjectInterface
     */
    public function read(string $filename): OpenApi
    {
        $ext = \strtolower(\pathinfo($filename, PATHINFO_EXTENSION));

        if (\in_array($ext, ['yaml', 'yml'])) {
            $contract = Reader::readFromYamlFile($filename);
        } elseif ('json' === $ext) {
            $contract = Reader::readFromJsonFile($filename);
        } else {
            throw new RuntimeException('Invalid contract extension: ' . $ext);
        }

        return $contract;
    }
}
