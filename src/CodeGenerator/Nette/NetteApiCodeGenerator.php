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

namespace Jdomenechb\OpenApiClassGenerator\CodeGenerator\Nette;

use Exception;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Jdomenechb\OpenApiClassGenerator\CodeGenerator\ApiCodeGenerator;
use Jdomenechb\OpenApiClassGenerator\CodeGenerator\ClassFileWriter;
use Jdomenechb\OpenApiClassGenerator\Model\Api;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpFile;
use Psr\Http\Message\ResponseInterface;

class NetteApiCodeGenerator implements ApiCodeGenerator
{
    /** @var ClassFileWriter */
    private $fileWriter;

    /**
     * @var NettePathCodeGenerator
     */
    private $pathCodeGenerator;

    /**
     * NetteApiCodeGenerator constructor.
     *
     * @param ClassFileWriter        $fileWriter
     * @param NettePathCodeGenerator $pathCodeGenerator
     */
    public function __construct(ClassFileWriter $fileWriter, NettePathCodeGenerator $pathCodeGenerator)
    {
        $this->fileWriter = $fileWriter;
        $this->pathCodeGenerator = $pathCodeGenerator;
    }

    /**
     * @param Api $apiService
     *
     * @throws Exception
     */
    public function generate(Api $apiService): void
    {
        $file = new PhpFile();
        $file->setStrictTypes();

        $namespace = $file->addNamespace($apiService->namespace() . '\\' . $apiService->name());
        $namespace->addUse(ClientInterface::class);
        $namespace->addUse(ResponseInterface::class);
        $namespace->addUse(GuzzleException::class);

        $classRepName = $apiService->name() . 'Service';

        $classRep = new ClassType($classRepName);
        $namespace->add($classRep);

        $description = $apiService->description();

        if ($description) {
            $classRep->addComment($description);
        }

        $classRep->addComment('@version ' . $apiService->version());

        if ($apiService->author() && $apiService->authorEmail()) {
            $classRep->addComment('@author ' . $apiService->author() . ' <' . $apiService->authorEmail() . '>');
        } elseif ($apiService->author()) {
            $classRep->addComment('@author ' . $apiService->author());
        } elseif ($apiService->authorEmail()) {
            $classRep->addComment('@author ' . $apiService->authorEmail());
        }

        $classRep->addComment('@api');
        $classRep->setFinal();

        $classRep->addProperty('client')
            ->setVisibility('private')
            ->addComment('@var ClientInterface');

        $construct = $classRep->addMethod('__construct')
            ->addBody('$this->client = $client;');

        $construct->addParameter('client')
            ->setTypeHint(ClientInterface::class);

        foreach ($apiService->paths() as $path) {
            $this->pathCodeGenerator->generate($classRep, $namespace, $path);
        }

        $this->fileWriter->write((string) $file, $classRepName, $namespace->getName());
    }
}
