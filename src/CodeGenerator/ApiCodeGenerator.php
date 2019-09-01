<?php

/**
 * This file is part of the openapi-class-generator package.
 *
 * (c) Jordi Domènech Bonilla
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Jdomenechb\OpenApiClassGenerator\CodeGenerator;

use Jdomenechb\OpenApiClassGenerator\Model\Api;

interface ApiCodeGenerator
{
    public function generate(Api $apiService): void;
}
