<?php

/**
 * This file is part of the openapi-class-generator package.
 *
 * (c) Jordi Domènech Bonilla
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Jdomenechb\OpenApiClassGenerator\CodeGenerator\Nette;

use Jdomenechb\OpenApiClassGenerator\CodeGenerator\RawExpression;
use Jdomenechb\OpenApiClassGenerator\Model\Path;
use Jdomenechb\OpenApiClassGenerator\Model\SecurityScheme\HttpSecurityScheme;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Method;
use Nette\PhpGenerator\PhpNamespace;
use RuntimeException;

class NetteGuzzleBodyCodeGenerator
{
    /** @var NetteResponseCodeGenerator */
    private $responseCodeGenerator;

    /**
     * NetteGuzzleBodyCodeGenerator constructor.
     *
     * @param NetteResponseCodeGenerator $responseCodeGenerator
     */
    public function __construct(NetteResponseCodeGenerator $responseCodeGenerator)
    {
        $this->responseCodeGenerator = $responseCodeGenerator;
    }

    public function generate(string $namespaceName, Method $method, Path $path, ?string $requestFormat): void
    {
        $guzzleRequestParameters = ['http_errors' => false];
        $serialize = false;
        $serializeBody = '';

        switch ($requestFormat) {
            case 'json':
                $serialize = true;
                $serializeBody = '\\json_encode($requestBody)';

                $guzzleRequestParameters['headers']['Content-Type'] = 'application/json';
                break;

            case 'form':
                $serialize = true;
                $serializeBody = '\\http_build_query($requestBody->serialize())';

                $guzzleRequestParameters['headers']['Content-Type'] = 'application/x-www-form-urlencoded';
                break;

            case null:
                // Do nothing;
                break;

            default:
                throw new RuntimeException('Unrecognized format: ' . $requestFormat);
        }

        // Parameters
        $uri = \addslashes($path->path());

        foreach ($path->parameters() as $parameter) {
            if ('path' === $parameter->in()) {
                $uri = \str_replace('{' . $parameter->name() . '}', '\' . $' . $parameter->name() . ' . \'', $uri);
            } elseif ('query' === $parameter->in()) {
                $guzzleRequestParameters['query'][$parameter->name()] = new RawExpression('$' . $parameter->name());
            }
        }

        $uri = "'${uri}'";

        $uri = \preg_replace(["#''\\s*\\.\\s*#", "#\\s*\\.\\s*''#"], '', $uri);

        // Security
        foreach ($path->securitySchemes() as $securityScheme) {
            if ($securityScheme instanceof HttpSecurityScheme) {
                switch ($securityScheme->scheme()) {
                    case 'bearer':
                        $guzzleRequestParameters['headers']['Authorization'] = new RawExpression(
                            "'Bearer ' . \$bearer"
                        );
                        break;
                }
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
                    '    $cResponse = $this->client->request(?, ' . $uri . ($guzzleReqParamsStringSerialized ? ', ' : '') . $guzzleReqParamsStringSerialized . ');',
                    [$path->method()]
                )
                ->addBody('} else {');
        }

        $method->addBody(
            ($serialize ? '    ' : '') . '$cResponse = $this->client->request(?, ' . $uri . ($guzzleReqParamsString ? ', ' : '') . $guzzleReqParamsString . ');',
            [$path->method()]
        );

        if ($serialize) {
            $method->addBody('}');
        }

        $method->addBody('');

        // Responses
        $defaultResponse = null;

        $method->addBody('$statusCode = $cResponse->getStatusCode();');
        $method->addBody('$contentType = $cResponse->getHeader(\'Content-Type\');');
        $method->addBody('');
        $method->addBody('switch (true) {');

        foreach ($path->responses() as $response) {
            $statusCode = $response->statusCode();

            if ($statusCode === null) {
                $defaultResponse = $response;

                continue;
            }

            $responseInfo = $this->responseCodeGenerator->generate($response, $namespaceName . '\\Response', $method->getName() . $statusCode);

            if ($responseInfo) {
                if (\count($response->mediaTypes())) {
                    foreach ($response->mediaTypes() as $mediaType) {
                        $unserializeBody = '$cResponse->getBody()->getContents()';

                        switch ($mediaType->format()) {
                            case 'json':
                                $contentType = 'application/json';
                                $unserializeBody = '\\json_decode(' . $unserializeBody . ', true)';
                                break;

                            case 'form':
                                $contentType = 'application/x-www-form-urlencoded';
                                $unserializeBody = '\\parse_str(' . $unserializeBody . ')';
                                break;

                            default:
                                throw new RuntimeException('Unrecognized format: ' . $requestFormat);
                        }

                        $method->addBody(
                            "    case \$statusCode === ${statusCode} && \$contentType === '$contentType':"
                        );

                        $responseClass = $responseInfo[$mediaType->format()]['class'];
                        $responseDtoClass = $responseInfo[$mediaType->format()]['dtoClass'];

                        if ($mediaType->schema()) {
                            $method->addBody(
                                '        return new \\' . $responseClass . '(' . $mediaType->schema(
                                )->getPhpFromArrayValue($unserializeBody, $responseDtoClass) . ');'
                            );
                        } else {
                            $method->addBody('        return new \\' . $responseClass . '();');
                        }

                        $method->addBody('');
                    }
                }
            } else {
                $responseClass = $responseInfo[null]['class'];

                $method->addBody(
                    '        return new \\' . $responseClass . '();'
                );
            }
        }

        $method->addBody('    default:');

        if ($defaultResponse === null) {
            $exceptionClass = '\\' . $namespaceName . '\\Exception\\RequestException';
            $method->addBody('        throw new ' . $exceptionClass . '($cResponse);');
            $method->addComment('@throws ' . $exceptionClass);
        } else {
            // TODO
        }

        $method->addBody('}');
    }

    /**
     * @param mixed $item
     *
     * @return string
     */
    private function serialize($item): string
    {
        if (\is_array($item)) {
            $output = [];

            foreach ($item as $key => $value) {
                $output[] = $this->serialize($key) . ' => ' . $this->serialize($value);
            }

            return '[' . \implode(', ', $output) . ']';
        }

        if (\is_string($item)) {
            return "'" . \addslashes($item) . "'";
        }

        if (\is_bool($item)) {
            return $item ? 'true' : 'false';
        }

        return $item;
    }
}
