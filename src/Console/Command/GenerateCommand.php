<?php

declare(strict_types=1);

/*
 * This file is part of the PHP CS Fixer Baseline project.
 *
 * (c) Anatoliy Melnikov <5785276@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Aeliot\PhpCsFixerBaseline\Console\Command;

use Aeliot\PhpCsFixerBaseline\Service\Builder;
use Aeliot\PhpCsFixerBaseline\Service\BuilderConfigFactory;
use Aeliot\PhpCsFixerBaseline\Service\Saver;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @internal
 */
#[AsCommand(name: 'generate', description: 'Generate PHP CS Fixer baseline file')]
final class GenerateCommand extends Command
{
    public function __construct(
        private readonly BuilderConfigFactory $builderConfigFactory,
        private readonly Builder $builder,
        private readonly Saver $saver,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption(
                'absolute',
                'a',
                InputOption::VALUE_NONE,
                'Store absolute paths in baseline file',
            )
            ->addOption(
                'baseline',
                'b',
                InputOption::VALUE_REQUIRED,
                'Pathname of baseline file',
                '.php-cs-fixer-baseline.json',
            )
            ->addOption(
                'config',
                'c',
                InputOption::VALUE_REQUIRED,
                'Pathname of config file',
                '.php-cs-fixer.dist.php',
            )
            ->addOption(
                'config-dir',
                'd',
                InputOption::VALUE_REQUIRED,
                'Config files path',
                '',
            )
            ->addOption(
                'finder',
                'f',
                InputOption::VALUE_REQUIRED,
                'Pathname of file with definition of Finder',
                '.php-cs-fixer-finder.php',
            )
            ->addOption(
                'workdir',
                'w',
                InputOption::VALUE_REQUIRED,
                'Working directory',
            )
            ->addOption(
                'invalid-only',
                null,
                InputOption::VALUE_NONE,
                'Include in baseline only files that would be changed by PHP CS Fixer (dry-run)',
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $builderConfig = $this->builderConfigFactory->createFromInput($input);
        $baseline = $this->builder->create($builderConfig);
        $this->saver->save($baseline);
        $output->writeln(\sprintf('Ok, %s files added to baseline', $baseline->getLockedFilesCount()));

        return self::SUCCESS;
    }
}
