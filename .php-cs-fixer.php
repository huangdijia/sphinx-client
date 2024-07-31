<?php

declare(strict_types=1);
/**
 * This file is part of vendor/package.
 *
 * @link     https://github.com/vendor/package
 * @document https://github.com/vendor/package/blob/main/README.md
 * @contact  Your name <your-mail@gmail.com>
 */
use Huangdijia\PhpCsFixer\Config;
use PhpCsFixer\Runner\Parallel\ParallelConfig;

require __DIR__ . '/vendor/autoload.php';

return (new Config())
    ->setParallelConfig(new ParallelConfig(4, 20))
    ->setHeaderComment(
        projectName: 'vendor/package',
        projectLink: 'https://github.com/vendor/package',
        projectDocument: 'https://github.com/vendor/package/blob/main/README.md',
        contacts: [
            'Your name' => 'your-mail@gmail.com',
        ],
    )
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->exclude('public')
            ->exclude('runtime')
            ->exclude('vendor')
            ->in(__DIR__)
            ->append([
                __FILE__,
            ])
    )
    ->setUsingCache(false);
