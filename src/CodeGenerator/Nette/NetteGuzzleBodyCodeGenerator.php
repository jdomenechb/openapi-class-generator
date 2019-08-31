<?php


namespace Jdomenechb\OpenApiClassGenerator\CodeGenerator\Nette;


use Jdomenechb\OpenApiClassGenerator\CodeGenerator\RawExpression;
use Jdomenechb\OpenApiClassGenerator\Model\Path;
use Nette\PhpGenerator\Method;
use RuntimeException;

class NetteGuzzleBodyCodeGenerator
{
    /** @var array */
    private $guzzleRequestParameters;

    public function generate(Method $method, Path $path, ?string $format) : void
    {
        $serialize = false;
        $serializeBody = '';

        if ($format === 'json') {
            $serialize = true;
            $serializeBody = '\json_encode($requestBody);';

            $this->guzzleRequestParameters['headers']['Content-Type'] = 'application/json';
        } else if ($format !== null) {
            throw new RuntimeException('Unrecognized format ' . $format);
        }

        $guzzleReqParamsString = $this->serialize($this->guzzleRequestParameters);

        $uri = addslashes($path->path());

        foreach ($path->parameters() as $parameter) {
            /** @var */
            if ($parameter->in() === 'path') {
                $uri = str_replace('{' . $parameter->name() . '}', '\' . $' . $parameter->name() . ' . \'', $uri);
            }
        }

        $uri = "'$uri'";

        $uri = preg_replace("#''\s*\.\s*#", '', $uri);
        $uri = preg_replace("#\s*\.\s*''#", '', $uri);

        if ($serialize) {
            $guzzleReqParamsStringSerialized = $this->serialize(
                $this->guzzleRequestParameters + ['body' => new RawExpression('$serializedRequestBody')]
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