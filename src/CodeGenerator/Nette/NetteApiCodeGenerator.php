<?php

declare(strict_types=1);

/**
 * This file is part of the openapi-class-generator package.
 *
 * (c) Jordi Domènech Bonilla
 */

namespace Jdomenechb\OpenApiClassGenerator\CodeGenerator\Nette;

use Doctrine\Common\Inflector\Inflector;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Jdomenechb\OpenApiClassGenerator\CodeGenerator\ApiCodeGenerator;
use Jdomenechb\OpenApiClassGenerator\Model\Api;
use JMS\Serializer\SerializerInterface;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpNamespace;
use Psr\Http\Message\ResponseInterface;
use function count;

class NetteApiCodeGenerator implements ApiCodeGenerator
{
    /** @var NetteObjectSchemaCodeGenerator */
    private $schemaCodeGenerator;

    /**
     * NetteApiCodeGenerator constructor.
     *
     * @param NetteObjectSchemaCodeGenerator $schemaCodeGenerator
     */
    public function __construct(NetteObjectSchemaCodeGenerator $schemaCodeGenerator)
    {
        $this->schemaCodeGenerator = $schemaCodeGenerator;
    }

    public function generate(Api $apiService) :void
    {
        $namespace = new PhpNamespace($apiService->namespace() . '\\Api');
        $namespace->addUse(ClientInterface::class);
        $namespace->addUse(ResponseInterface::class);
        $namespace->addUse(GuzzleException::class);

        $classRep = new ClassType($apiService->name());
        $namespace->add($classRep);

        if ($apiService->description()) {
            $classRep->addComment($apiService->description());
        }

        $classRep->addComment('@version ' . $apiService->version());

        if ($apiService->author() && $apiService->authorEmail()) {
            $classRep->addComment('@author ' . $apiService->author() . ' <' . $apiService->authorEmail() . '>');
        } elseif ($apiService->author()) {
            $classRep->addComment('@author ' . $apiService->author());
        } elseif ($apiService->authorEmail()) {
            $classRep->addComment('@author ' . $apiService->authorEmail());
        }

        $classRep->setFinal();

        $classRep->addProperty('client')
            ->setVisibility('private')
            ->addComment('@var ClientInterface');

        $construct = $classRep->addMethod('__construct')
            ->addBody('$this->client = $client;');

        $construct->addParameter('client')
            ->setTypeHint(ClientInterface::class);

        foreach ($apiService->operations() as $operation) {
            $referenceMethodName = $operation->method() . $operation->path();
            $formats = $operation->formats();
            $nFormats = count($formats);

            if ($nFormats === 0) {
                $methodName = Inflector::camelize(preg_replace('#\W#', ' ', $referenceMethodName));

                $classRep->addMethod($methodName)
                    ->setVisibility('public')
                    ->addBody('return $this->client->request(?, ?);', [$operation->method(), $operation->path()])
                    ->setReturnType(ResponseInterface::class)
                    ->addComment('@throws GuzzleException')
                    ;
            }

            foreach ($formats as $format) {
                $methodName = $referenceMethodName;

                if ($nFormats > 1) {
                    $methodName .= ' ' . $format->format();
                }

                $methodName = Inflector::camelize(preg_replace('#\W#', ' ', $methodName));

                $requestRef = $this->schemaCodeGenerator->generate($format->schema(), $namespace, $format->format(), $methodName);

                $method = $classRep->addMethod($methodName)
                    ->setVisibility('public')
                    ->setReturnType(ResponseInterface::class)
                    ->addComment('@var \\' . $namespace->getName() . '\\' . $requestRef->getName() . ' $requestBody')
                    ->addComment('@return ResponseInterface')
                    ->addComment('@throws GuzzleException');

                $method
                    ->addParameter('requestBody')
                    ->setTypeHint($namespace->getName() . '\\' . $requestRef->getName())
                    ;

                if ($format->format() === 'json') {
                    $method
                        ->addBody('$serializedRequestBody = \json_encode($requestBody);')
                        ->addBody('$response = $this->client->request(?, ?, [\'body\' => $serializedRequestBody, \'headers\' => [\'Content-Type\' => \'application/json\']]);', [$operation->method(), $operation->path()])
                        ;
                } else {
                    throw new \RuntimeException('Unrecognized format ' . $format->format());
                }

                $method
                    ->addBody('return $response;')
                    ;

            }
        }

        file_put_contents('output.php', "<?php\n\n" . (string) $namespace);
    }

}