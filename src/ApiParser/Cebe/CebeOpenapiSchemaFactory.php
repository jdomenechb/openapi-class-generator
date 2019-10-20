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
use Jdomenechb\OpenApiClassGenerator\Model\Schema\String\UriRefSchema;
use Jdomenechb\OpenApiClassGenerator\Model\Schema\String\UriSchema;
use Jdomenechb\OpenApiClassGenerator\Model\Schema\VectorSchema;
use RuntimeException;

class CebeOpenapiSchemaFactory
{
    public function build(Schema $schema, string $name): AbstractSchema
    {
        if ($schema->allOf) {
            $serializedSchema = json_decode(json_encode($schema->getSerializableData()), true);
            $merge = $this->array_merge_recursive_distinct(...$serializedSchema['allOf']);
            $schema = new Schema($merge);
        }

        switch ($schema->type) {
            case 'object':
            case null:
                return $this->createObject($schema, $name);
            case 'string':
                return $this->createString($schema);

            case 'number':
                return $this->createNumber($schema);

            case 'integer':
                return new IntegerSchema();

            case 'array':
                return $this->createArray($schema, $name);

            case 'boolean':
                return new BooleanSchema();

            default:
                throw new RuntimeException(\sprintf('Schema type "%s" not recognized', $schema->type));
        }
    }

    /**
     * @param Schema $schema
     * @param string $name
     *
     * @return ObjectSchema
     */
    private function createObject(Schema $schema, string $name): ObjectSchema
    {
        $obj = new ObjectSchema($name);

        foreach ($schema->properties as $propertyName => $property) {
            $dtoProperty = new ObjectSchemaProperty(
                $propertyName,
                \in_array($propertyName, $schema->required ?? [], true),
                $this->build($property, $propertyName)
            );

            $obj->addProperty($dtoProperty);
        }

        return $obj;
    }

    /**
     * @param Schema $schema
     *
     * @return DateTimeSchema|EmailSchema|PasswordSchema|StringSchema|UriSchema
     */
    private function createString(Schema $schema)
    {
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

                case 'uriref':
                    $obj = new UriRefSchema();
                    break;

                default:
                    throw new RuntimeException(\sprintf('String schema format "%s" not recognized', $schema->format));
            }
        } else {
            $obj = new StringSchema();
        }

        return $obj;
    }

    /**
     * @param Schema $schema
     *
     * @return FloatSchema|NumberSchema
     */
    private function createNumber(Schema $schema)
    {
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
    }

    /**
     * @param Schema $schema
     * @param string $name
     *
     * @return VectorSchema
     */
    private function createArray(Schema $schema, string $name): VectorSchema
    {
        if (null === $schema->items) {
            throw new RuntimeException('Property "items" is required for schema with type array');
        }

        return new VectorSchema($this->build($schema->items, $name . 'Item'));
    }

    /**
     * @param array $array1
     * @param array $array2
     *
     * @return array
     */
    private function array_merge_recursive_distinct(array &$array1, array &$array2): array
    {
        $merged = $array1;

        foreach ($array2 as $key => &$value) {
            if (is_array($value) && isset ($merged [$key]) && is_array($merged [$key])) {
                $merged [$key] = $this->array_merge_recursive_distinct($merged [$key], $value);
            } else {
                if (is_int($key)) {
                    $merged[] = $value;
                } else {
                    $merged[$key] = $value;
                }

            }
        }

        return $merged;
    }
}
