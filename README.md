## Baseline for PHP CS Fixer

It's simple baseline for [PHP CS Fixer](https://github.com/PHP-CS-Fixer/PHP-CS-Fixer).

**Pros:**
- It helps to start using of PHP CS Fixer without preparing of all project.
  Case you don't need to fix them all at the beginning. Only new & changed on each iteration.
- It may speed up pipelines on CI for big projects.

Base project has requires for it but not implemented yet: https://github.com/PHP-CS-Fixer/PHP-CS-Fixer/issues/6451

So, it's some work around till baseline will be implemented in the PHP CS Fixer.

### Installation

Download PHAR directly to root directory of the project or in another place as you wish.
```shell
curl -O https://github.com/Aeliot-Tm/php-cs-fixer-baseline/releases/download/v1.2.0/pcsf-baseline.phar
```

Or require package by composer:
```shell
composer require --dev aeliot/php-cs-fixer-baseline
```

### Configuration

1. Extract `Finder` from the config of PHP CS Fixer to the separate file.
   It expects `.php-cs-fixer-finder.php` at the root of the project.
2. Add filtering of files detected by Finder.
   ```php
   use Aeliot\PhpCsFixerBaseline\Service\FilterFactory;

   $finder->filter((new FilterFactory())->createFilter(__DIR__ . '/.php-cs-fixer-baseline.json', $config));
   ```
3. Autoload classes from PHAR (optional).
   If you use this project as PHAR file, you need to require autoloader of it to use provided filter.
   Do it in the main config file of PHP CS Fixer (`.php-cs-fixer.dist.php`)
   ```php
   Phar::loadPhar('/path/to/pcsf-baseline.phar', 'pcsf-baseline.phar');
   require_once 'phar://pcsf-baseline.phar/vendor/autoload.php';
   ```

### Using
1. Generate baseline. Just call script without options when all config files uses default names.
   - Call PHAR
     ```shell
     php pcsf-baseline.phar
     ```
   - Or call script installed via Composer:
     ```shell
     vendor/bin/pcsf-baseline
     ```
   See options of it below. You can see how it is configured in this project.
2. Use PHP CS Fixer as usual. All files mentioned in the baseline will be scip till they are not changed.

This script may store relative paths to files in baseline file. It may be useful when baseline used
in different environments. Pass option `relative` for this when build baseline. Directory where the script called
will be used in this case. Or you can customise working directory by option `workdir`.

The same with the filter for PHP CS Fixer. You may customize working directory by third option for filter factory.
Or current working directory will be used.

### Options of baseline generator

| Short name | Long name | Description                                                                               | Default value               |
|------------|-----------|-------------------------------------------------------------------------------------------|-----------------------------|
| b          | baseline  | Name of baseline file                                                                     | .php-cs-fixer-baseline.json |
| c          | config    | Name of config file                                                                       | .php-cs-fixer.dist.php      |
| f          | finder    | Name of file with definition of Finder                                                    | .php-cs-fixer-finder.php    |
| r          | relative  | Store relative paths in baseline file. It should be omitted when passed option `workdir`. |                             |
| w          | workdir   | Working directory. This part will erased when store relative paths in baseline file.      |                             |

Options `baseline`, `config`, `finder` can be absolute or related or omitted at all. In the last case it expects
that files are in the root directory of project.

When option `relative` is passed and option `workdir` is omitted then result of function
[`getcwd()`](https://www.php.net/manual/en/function.getcwd.php) will be used to get current working directory.

### Restrictions for using of relative paths
1. Option `workdir` MUST be absolute. You cannot use "double dots" in it.
2. Used function `realpath()` for normalisation of paths of files returned by `Finder`. For proper cutting of `workdir`
   out of file path to make it relative. It may return unexpected result based on current user permissions.
   Look for restrictions of this function in [official documentation](https://www.php.net/manual/en/function.realpath.php).
3. When the function `realpath()` returns an empty result or path of file returned by `Finder` is not from working
   directory then path stored "as is".
