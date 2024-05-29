## Baseline for PHP CS Fixer

It's simple baseline for [PHP CS Fixer](https://github.com/PHP-CS-Fixer/PHP-CS-Fixer).

**Pros:**
- It helps to start using of PHP CS Fixer without preparing of all project.
- It may speed up pipelines on CI for big projects.

Base project has requires for it but not implemented yet: https://github.com/PHP-CS-Fixer/PHP-CS-Fixer/issues/6451

So, it's some work around till baseline will be implemented in the PHP CS Fixer.

### Installation

1. Require package by composer:
    ```shell
    composer require --dev aeliot/php-cs-fixer-baseline
    ```
2. Extract `Finder` from the config of PHP CS Fixer to the separate file. 
   It expects `.php-cs-fixer-finder.php` at the root of the project.
3. Add filtering of files detected by Finder
   ```php
   $finder->filter((new FilterFactory())->createFilter(__DIR__ . '/.php-cs-fixer-baseline.json', $config));
   ```
4. Generate baseline. Just call script without options when all config files uses default names.
   ```shell
   vendor/bin/pcsf-baseline 
   ```
   See options of it below.

You can see how it is configured in this project.

### Options of baseline generator

| Short name | Long name | Description                            | Default value               |
|------------|-----------|----------------------------------------|-----------------------------|
| b          | baseline  | Name of baseline file                  | .php-cs-fixer-baseline.json |
| c          | config    | Name of config file                    | .php-cs-fixer.dist.php      |
| f          | finder    | Name of file with definition of Finder | .php-cs-fixer-finder.php    |

Path to files can be absolute or related or omitted at all. It the last case it is expected that files 
in the root directory of project.
