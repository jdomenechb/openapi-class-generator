<?php

/**
 * This file is part of the openapi-class-generator package.
 *
 * (c) Jordi Domènech Bonilla
 */

namespace Jdomenechb\OpenApiClassGenerator\CodeGenerator;


use Jdomenechb\OpenApiClassGenerator\Model\ApiService;

interface ApiServiceCodeGenerator
{
    public function generate(ApiService $apiService) :void;
}