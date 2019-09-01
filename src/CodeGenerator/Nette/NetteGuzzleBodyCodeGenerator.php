<?php


namespace Jdomenechb\OpenApiClassGenerator\CodeGenerator\Nette;


use Jdomenechb\OpenApiClassGenerator\CodeGenerator\RawExpression;
use Jdomenechb\OpenApiClassGenerator\Model\Path;
use Nette\PhpGenerator\Method;
use RuntimeException;

class NetteGuzzleBodyCodeGenerator
{
    public function generate(Method $method, Path $path, ?string $format) : void
    {
        $guzzleRequestParameters = [];
        $serialize = false;
        $serializeBody = '';

        if ($format === 'json') {
            $serialize = true;
            $serializeBody = '\json_encode($requestBody);';

            $guzzleRequestParameters['headers']['Content-Type'] = 'application/json';
        } else if ($format !== null) {
            throw new RuntimeException('Unrecognized format ' . $format);
        }

        // Parameters
        $uri = addslashes($path->path());

        foreach ($path->parameters() as $parameter) {
            if ($parameter->in() === 'path') {
                $uri = str_replace('{' . $parameter->name() . '}', '\' . $' . $parameter->name() . ' . \'', $uri);
            } elseif ($parameter->in() === 'query') {
                $guzzleRequestParameters['query'][$parameter->name()] = new RawExpression('$' . $parameter->name());
            }
        }

        $uri = "'$uri'";

        $uri = preg_replace("#''\s*\.\s*#", '', $uri);
        $uri = preg_replace("#\s*\.\s*''#", '', $uri);

        // Security
        foreach ($path->securitySchemes() as $securityScheme) {
            switch ($securityScheme->scheme()) {
                case 'bearer':
                    $guzzleRequestParameters['headers']['Authorization'] = new RawExpression("'Bearer ' . \$bearer");
                    break;
            }
        }

        $guzzleReqParamsString = $this->serialize($guzzleRequestParameters);

        if ($serialize) {
            $guzzleReqParamsStringSerialized = $this->serialize(
                $guzzleRequestParameters + ['body' => new RawExpression('$serializedRequestBody')]
            );

            $method
                ->addBody('if ($requestBody !== null) {')
                ->addBody('    $serializedRequestBody = ' . $serializeBody . ';')
                ->addBody(
                    '    $response = $this->client->request(?, ' . $uri . ($guzzleReqParamsStringSerialized? ', ': '') . $guzzleReqParamsStringSerialized . ');',
                    [$path->method()]
                )
                ->addBody('} else {');
        }

        $method->addBody(
            ($serialize ? '    ' : '') . '$response = $this->client->request(?, ' . $uri . ($guzzleReqParamsString? ', ': '') . $guzzleReqParamsString . ');',
            [$path->method()]
        );

        if ($serialize) {
            $method->addBody('}');
        }

        $method->addBody('');
        $method->addBody('return $response;');

    }

    /**
     * @param $item
     *
     * @return string
     */
    private function serialize($item): string
    {
        if (is_array($item)) {
            $output = [];

            foreach ($item as $key => $value) {
                $output[] = $this->serialize($key) . ' => ' . $this->serialize($value);
            }

            return '[' . implode(', ', $output) . ']';
        }

        if (is_string($item)) {
            return "'" . addslashes($item) . "'";
        }

        if ($item instanceof RawExpression) {
            return (string) $item;
        }

        return '';
    }
}