<?php

declare(strict_types=1);

/**
 * This file is part of the openapi-class-generator package.
 *
 * (c) Jordi Domènech Bonilla
 */

namespace Jdomenechb\OpenApiClassGenerator\Command;

use cebe\openapi\exceptions\TypeErrorException;
use cebe\openapi\exceptions\UnresolvableReferenceException;
use Jdomenechb\OpenApiClassGenerator\ApiParser\Cebe\CebeOpenapiApiBuilder;
use Jdomenechb\OpenApiClassGenerator\ApiParser\Cebe\CebeOpenapiFileReader;
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
use Jdomenechb\OpenApiClassGenerator\CodeGenerator\Nette\NetteSecuritySchemeCodeGenerator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Exception;

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
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int|void|null
     * @throws TypeErrorException
     * @throws UnresolvableReferenceException
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $inputPath = $input->getArgument('inputPath');
        $outputPath = $input->getArgument('outputPath');
        $namespace = $input->getOption('namespace');

        $output->writeln('Using namespace: ' . $namespace);

        $fileWriter = new ClassFileWriter($outputPath);
        $abstractSchemaCodeGenerator = new NetteAbstractSchemaCodeGenerator(new NetteObjectSchemaCodeGenerator($fileWriter));

        $apiBuilder = new CebeOpenapiApiBuilder(new CebeOpenapiFileReader(), new CebeOpenapiSchemaFactory(), new CebeOpenapiSecuritySchemeFactory(), new CebeOpenapiSecurityFactory());
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
            )
        );

        // Clean output path
        $filesystem = new Filesystem();
        $filesystem->remove($outputPath);
        $filesystem->mkdir($outputPath);

        $finder = new Finder();
        $finder->files()->in($inputPath)->name(['*.yaml', '*.yml', '*.json']);

        $i = 0;

        foreach ($finder as $file) {
            $apiService = $apiBuilder->fromFile($file->getRealPath(), $namespace);
            $apiCodeGenerator->generate($apiService);

            $output->writeln('Processed contract: ' . $file->getBasename());
            ++$i;
        }

        if ($i === 0) {
            $output->writeln('No files processed');
        }
    }
}