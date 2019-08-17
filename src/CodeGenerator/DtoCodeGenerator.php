<?php

declare(strict_types=1);

/**
 * This file is part of the openapi-class-generator package.
 *
 * (c) Jordi Domènech Bonilla
 */

namespace Jdomenechb\OpenApiClassGenerator\CodeGenerator;

use Jdomenechb\OpenApiClassGenerator\Model\Dto;

interface DtoCodeGenerator
{
    public function generate(Dto $dto);
}