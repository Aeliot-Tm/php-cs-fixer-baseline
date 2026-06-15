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

use Aeliot\PhpCsFixerBaseline\Exception\InvalidArgumentException;
use Aeliot\PhpCsFixerBaseline\Service\BuilderConfigFactory;
use Aeliot\PhpCsFixerBaseline\Service\Saver;
use Aeliot\PhpCsFixerBaseline\Service\Updater;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @internal
 */
#[AsCommand(name: 'update', description: 'Update hash for files already in baseline')]
final class UpdateCommand extends Command
{
    public function __construct(
        private readonly BuilderConfigFactory $builderConfigFactory,
        private readonly Updater $updater,
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
                'config-dir',
                'd',
                InputOption::VALUE_REQUIRED,
                'Config files path',
                '',
            )
            ->addOption(
                'workdir',
                'w',
                InputOption::VALUE_REQUIRED,
                'Working directory',
            )
            ->addArgument(
                'path',
                InputArgument::IS_ARRAY | InputArgument::REQUIRED,
                'Path to file already in baseline (repeatable)',
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var list<string> $filePaths */
        $filePaths = $input->getArgument('path');

        if ([] === $filePaths) {
            throw new InvalidArgumentException('At least one path argument is required.');
        }

        $context = $this->builderConfigFactory->resolveBaselineOptions($input);
        $baseline = $this->updater->update($context, $filePaths);
        $this->saver->save($baseline);
        $output->writeln(\sprintf('Ok, %d file(s) updated in baseline', \count($filePaths)));

        return self::SUCCESS;
    }
}
