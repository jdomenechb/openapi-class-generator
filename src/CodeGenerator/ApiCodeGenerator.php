<?php

/**
 * This file is part of the openapi-class-generator package.
 *
 * (c) Jordi Domènech Bonilla
 */

namespace Jdomenechb\OpenApiClassGenerator\CodeGenerator;


use Jdomenechb\OpenApiClassGenerator\Model\Api;

interface ApiCodeGenerator
{
    public function generate(Api $apiService) :void;
}