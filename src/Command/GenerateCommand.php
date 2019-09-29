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

namespace Jdomenechb\OpenApiClassGenerator\Command;

use cebe\openapi\exceptions\TypeErrorException;
use cebe\openapi\exceptions\UnresolvableReferenceException;
use Exception;
use InvalidArgumentException;
use Jdomenechb\OpenApiClassGenerator\ApiParser\Cebe\CebeOpenapiApiBuilder;
use Jdomenechb\OpenApiClassGenerator\ApiParser\Cebe\CebeOpenapiFileReader;
use Jdomenechb\OpenApiClassGenerator\ApiParser\Cebe\CebeOpenapiPathFactory;
use Jdomenechb\OpenApiClassGenerator\ApiParser\Cebe\CebeOpenapiSchemaFactory;
use Jdomenechb\OpenApiClassGenerator\ApiParser\Cebe\CebeOpenapiSecurityFactory;
use Jdomenechb\OpenApiClassGenerator\ApiParser\Cebe\CebeOpenapiSecuritySchemeFactory;
use Jdomenechb\OpenApiClassGenerator\CodeGenerator\ClassFileWriter;
use Jdomenechb\OpenApiClassGenerator\CodeGenerator\Nette\NetteAbstractSchemaCodeGenerator;
use Jdomenechb\OpenApiClassGenerator\CodeGenerator\Nette\NetteApiCodeGenerator;
use Jdomenechb\OpenApiClassGenerator\CodeGenerator\Nette\NetteGuzzleBodyCodeGenerator;
use Jdomenechb\OpenApiClassGenerator\CodeGenerator\Nette\NetteObjectSchemaCodeGenerator;
use Jdomenechb\OpenApiClassGenerator\CodeGenerator\Nette\NettePathCodeGenerator;
use Jdomenechb\OpenApiClassGenerator\CodeGenerator\Nette\NettePathParameterCodeGenerator;
use Jdomenechb\OpenApiClassGenerator\CodeGenerator\Nette\NetteRequestBodyFormatCodeGenerator;
use Jdomenechb\OpenApiClassGenerator\CodeGenerator\Nette\NetteRequestExceptionCodeGenerator;
use Jdomenechb\OpenApiClassGenerator\CodeGenerator\Nette\NetteResponseInterfaceCodeGenerator;
use Jdomenechb\OpenApiClassGenerator\CodeGenerator\Nette\NetteSecuritySchemeCodeGenerator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class GenerateCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->setDescription('Generates classes from contracts')
            ->setHelp('This command allows you to generate PHP classes from the given contracts.')
            ->setName('generate')
            ->addArgument('inputPath', InputArgument::REQUIRED, 'Input folder path where the contracts can be found.')
            ->addArgument(
                'outputPath',
                InputArgument::REQUIRED,
                'Output folder of the generated source files. WARNING! The folder will be erased entirely before starting the process.'
            )
            ->addOption('namespace', null, InputOption::VALUE_REQUIRED, 'Namespace to use for the generated classes', 'Ocg');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @throws TypeErrorException
     * @throws UnresolvableReferenceException
     * @throws Exception
     *
     * @return int|void|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $inputPath = $input->getArgument('inputPath');
        $outputPath = $input->getArgument('outputPath');
        $namespace = $input->getOption('namespace');

        if (!\is_string($inputPath)) {
            throw new InvalidArgumentException('inputPath must be a string');
        }

        if (!\is_string($outputPath)) {
            throw new InvalidArgumentException('outputPath must be a string');
        }

        if (!\is_string($namespace)) {
            throw new InvalidArgumentException('namespace must be a string');
        }

        $output->writeln('Using namespace: ' . $namespace);

        $fileWriter = new ClassFileWriter($outputPath);
        $abstractSchemaCodeGenerator = new NetteAbstractSchemaCodeGenerator(new NetteObjectSchemaCodeGenerator($fileWriter));

        $apiBuilder = new CebeOpenapiApiBuilder(
            new CebeOpenapiFileReader(),
            new CebeOpenapiSecuritySchemeFactory(),
            new CebeOpenapiSecurityFactory(),
            new CebeOpenapiPathFactory(new CebeOpenapiSchemaFactory())
        );

        $apiCodeGenerator = new NetteApiCodeGenerator(
            $fileWriter,
            new NettePathCodeGenerator(
                new NetteRequestBodyFormatCodeGenerator(
                    $abstractSchemaCodeGenerator,
                    new NetteGuzzleBodyCodeGenerator()
                ),
                new NettePathParameterCodeGenerator($abstractSchemaCodeGenerator),
                new NetteGuzzleBodyCodeGenerator(),
                new NetteSecuritySchemeCodeGenerator()
            ),
            new NetteRequestExceptionCodeGenerator($fileWriter),
            new NetteResponseInterfaceCodeGenerator($fileWriter)
        );

        // Clean output path
        $filesystem = new Filesystem();
        $filesystem->remove($outputPath);
        $filesystem->mkdir($outputPath);

        $finder = new Finder();
        $finder->files()->in($inputPath)->name(['*.yaml', '*.yml', '*.json']);

        $anyProcessed = false;

        foreach ($finder as $file) {
            $realPath = $file->getRealPath();

            if (false === $realPath) {
                $realPath = $file->getPathname();
            }

            $apiService = $apiBuilder->fromFile($realPath, $namespace);
            $apiCodeGenerator->generate($apiService);

            $output->writeln('Processed contract: ' . $file->getBasename());

            $anyProcessed = true;
        }

        if (!$anyProcessed) {
            $output->writeln('No files processed');
        }
    }
}
