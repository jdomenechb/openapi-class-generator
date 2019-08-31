<?php

declare(strict_types=1);

/**
 * This file is part of the openapi-class-generator package.
 *
 * (c) Jordi Domènech Bonilla
 */

namespace Jdomenechb\OpenApiClassGenerator\Command;

use Jdomenechb\OpenApiClassGenerator\ApiParser\ApiBuilder;
use Jdomenechb\OpenApiClassGenerator\CodeGenerator\ApiCodeGenerator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class GenerateCommand extends Command
{
    /** @var ApiBuilder */
    private $apiBuilder;

    /** @var ApiCodeGenerator */
    private $apiCodeGenerator;

    /**
     * GenerateCommand constructor.
     *
     * @param ApiBuilder $apiBuilder
     * @param ApiCodeGenerator $apiCodeGenerator
     */
    public function __construct(ApiBuilder $apiBuilder, ApiCodeGenerator $apiCodeGenerator)
    {
        parent::__construct();

        $this->apiBuilder = $apiBuilder;
        $this->apiCodeGenerator = $apiCodeGenerator;
    }

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
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $inputPath = $input->getArgument('inputPath');
        $outputPath = $input->getArgument('outputPath');

        // Clean output path
        $filesystem = new Filesystem();
        $filesystem->remove($outputPath);
        $filesystem->mkdir($outputPath);

        $finder = new Finder();
        $finder->files()->in($inputPath)->name(['*.yaml', '*.yml', '*.json']);

        $i = 0;

        foreach ($finder as $file) {
            $apiService = $this->apiBuilder->fromFile($file->getRealPath());

            $this->apiCodeGenerator->generate($apiService);

            $output->writeln('Processed contract: ' . $file->getRelativePath());
            ++$i;
        }

        if ($i === 0) {
            $output->writeln('No files processed');
        }

    }
}