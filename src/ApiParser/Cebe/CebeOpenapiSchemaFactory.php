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

use cebe\openapi\spec\Schema;
use Jdomenechb\OpenApiClassGenerator\Model\Schema\AbstractSchema;
use Jdomenechb\OpenApiClassGenerator\Model\Schema\BooleanSchema;
use Jdomenechb\OpenApiClassGenerator\Model\Schema\IntegerSchema;
use Jdomenechb\OpenApiClassGenerator\Model\Schema\Number\FloatSchema;
use Jdomenechb\OpenApiClassGenerator\Model\Schema\Number\NumberSchema;
use Jdomenechb\OpenApiClassGenerator\Model\Schema\ObjectSchema;
use Jdomenechb\OpenApiClassGenerator\Model\Schema\ObjectSchemaProperty;
use Jdomenechb\OpenApiClassGenerator\Model\Schema\String\DateTimeSchema;
use Jdomenechb\OpenApiClassGenerator\Model\Schema\String\EmailSchema;
use Jdomenechb\OpenApiClassGenerator\Model\Schema\String\PasswordSchema;
use Jdomenechb\OpenApiClassGenerator\Model\Schema\String\StringSchema;
use Jdomenechb\OpenApiClassGenerator\Model\Schema\String\UriSchema;
use Jdomenechb\OpenApiClassGenerator\Model\Schema\VectorSchema;
use RuntimeException;

class CebeOpenapiSchemaFactory
{
    public function build(Schema $schema, string $name): AbstractSchema
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

                        case 'date-time':
                            $obj = new DateTimeSchema();
                            break;

                        case 'uri':
                            $obj = new UriSchema();
                            break;

                        default:
                            throw new RuntimeException(\sprintf('String schema format "%s" not recognized', $schema->format));
                    }
                } else {
                    $obj = new StringSchema();
                }

                return $obj;

            case 'number':
                if ($schema->format) {
                    switch ($schema->format) {
                        case 'float':
                        case 'double':
                            $obj = new FloatSchema();
                            break;

                        default:
                            throw new RuntimeException(\sprintf('Number schema format "%s" not recognized', $schema->format));
                    }
                } else {
                    $obj = new NumberSchema();
                }

                return $obj;

            case 'integer':
                return new IntegerSchema();

            case 'array':
                return new VectorSchema($this->build($schema->items, $name . 'Item'));

            case 'boolean':
                return new BooleanSchema();

            default:
                throw new RuntimeException(\sprintf('Schema type "%s" not recognized', $schema->type));
        }
    }
}
