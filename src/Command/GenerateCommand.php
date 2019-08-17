<?php

declare(strict_types=1);

/**
 * This file is part of the openapi-class-generator package.
 *
 * (c) Jordi Domènech Bonilla
 */

namespace Jdomenechb\OpenApiClassGenerator\Command;

use Jdomenechb\OpenApiClassGenerator\ApiParser\ApiBuilder;
use Jdomenechb\OpenApiClassGenerator\CodeGenerator\ApiServiceCodeGenerator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

class GenerateCommand extends Command
{
    /** @var ApiBuilder */
    private $apiParser;

    /** @var ApiServiceCodeGenerator */
    private $codeGenerator;

    /**
     * GenerateCommand constructor.
     *
     * @param ApiBuilder $apiParser
     */
    public function __construct(ApiBuilder $apiParser, ApiServiceCodeGenerator $codeGenerator)
    {
        parent::__construct();

        $this->apiParser = $apiParser;
        $this->codeGenerator = $codeGenerator;
    }

    /**
     *
     */
    protected function configure() : void
    {
        $this
            ->setDescription('Generates classes from contracts')
            ->setHelp('This command allows you to generate PHP classes from the given contracts.')
            ->setName('generate');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $finder = new Finder();
        $finder->files()->in('contracts')->name(['*.yaml', '*.yml', '*.json']);

        foreach ($finder as $file) {
            $apiService = $this->apiParser->fromFile($file->getRealPath());

            $this->codeGenerator->generate($apiService);
        }
    }
}