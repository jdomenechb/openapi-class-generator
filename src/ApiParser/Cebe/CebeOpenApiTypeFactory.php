<?php

declare(strict_types=1);

/**
 * This file is part of the openapi-class-generator package.
 *
 * (c) Jordi Domènech Bonilla
 */

namespace Jdomenechb\OpenApiClassGenerator\ApiParser\Cebe;

use cebe\openapi\spec\Schema;
use Jdomenechb\OpenApiClassGenerator\Model\Schema\AbstractSchema;
use Jdomenechb\OpenApiClassGenerator\Model\Schema\ObjectSchema;
use Jdomenechb\OpenApiClassGenerator\Model\Schema\ObjectSchemaProperty;
use Jdomenechb\OpenApiClassGenerator\Model\Schema\String\EmailSchema;
use Jdomenechb\OpenApiClassGenerator\Model\Schema\String\PasswordSchema;
use Jdomenechb\OpenApiClassGenerator\Model\Schema\String\StringSchema;

class CebeOpenApiTypeFactory
{
    public function build(Schema $schema, string $name) : AbstractSchema
    {
        switch ($schema->type) {
            case 'object':
                $obj = new ObjectSchema($name);

                foreach ($schema->properties as $propertyName => $property) {
                    $dtoProperty = new ObjectSchemaProperty($propertyName, \in_array($propertyName, $schema->required, true), $this->build($property, $propertyName));

                    $obj->addProperty($dtoProperty);
                }

                return $obj;

            case 'string':
                if ($schema->format) {
                    switch ($schema->format) {
                        case 'email':
                            $obj = new EmailSchema();
                            break;

                        case 'password':
                            $obj = new PasswordSchema();
                            break;

                        default:
                            throw new \RuntimeException(sprintf('String schema format "%s" not recognized', $schema->format));
                    }
                } else {
                    $obj = new StringSchema();
                }

                return $obj;

            default:
                throw new \RuntimeException(sprintf('Schema type "%s" not recognized', $schema->type));
        }
    }
}