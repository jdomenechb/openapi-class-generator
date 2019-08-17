<?php

declare(strict_types=1);

/**
 * This file is part of the openapi-class-generator package.
 *
 * (c) Jordi Domènech Bonilla
 */

namespace Jdomenechb\OpenApiClassGenerator\Command;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateCommand extends Command
{
    protected function configure() : void
    {
        $this
            ->setDescription('Generates classes from contracts')
            ->setHelp('This command allows you to generate PHP classes from the given contracts.')
            ->setName('generate');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

    }
}