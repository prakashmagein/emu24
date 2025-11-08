<?php

namespace Swissup\ImageOptimizer\Model\Image\Optimizers;

class OptimizerChainFactory
{
    /**
     * @param array $config
     * @return \Spatie\ImageOptimizer\OptimizerChain
     */
    public static function create(array $config = []): \Spatie\ImageOptimizer\OptimizerChain/** @phpstan-ignore-line */
    {
        $defaultQuality = 85;
        $quality = isset($config['quality']) ? $config['quality'] : $defaultQuality;

        $optimizerChain = (new \Spatie\ImageOptimizer\OptimizerChain());
        $isRemote = isset($config['remote']) && $config['remote'] === true;

        if ($isRemote) {
            $options = [];

            foreach(['baseUrl', 'mediaDir', 'apiUrl', 'apiKey'] as $key) {
                if (isset($config[$key])) {
                    $options[$key] = $config[$key];
                }
            }
            /* @phpstan-ignore-next-line */
            $optimizerChain->addOptimizer(new \Swissup\ImageOptimizer\Model\Image\Optimizers\Remote($options));
        } else {
            $optimizerChain->addOptimizer(new \Spatie\ImageOptimizer\Optimizers\Jpegoptim([
                    '--max=' . $quality, // set maximum quality to
                    '--strip-all',  // this strips out all text information such as comments and EXIF data
                    '--all-progressive',  // this will make sure the resulting image is a progressive one
                ]))
                ->addOptimizer(new \Spatie\ImageOptimizer\Optimizers\Pngquant([
                    '--quality=' . $quality,
                    '--force', // required parameter for this package
                    '--skip-if-larger',
                ]))
                ->addOptimizer(new \Spatie\ImageOptimizer\Optimizers\Optipng([
                    '-i0', // this will result in a non-interlaced, progressive scanned image
                    '-o2',  // this set the optimization level to two (multiple IDAT compression trials)
                    '-quiet', // required parameter for this package
                ]))
            //            ->addOptimizer(new \Spatie\ImageOptimizer\Optimizers\Svgo([
            //                '--disable={cleanupIDs,removeViewBox}', // disabling because it is know to cause troubles
            //            ]))
                ->addOptimizer(new \Spatie\ImageOptimizer\Optimizers\Gifsicle([
                    '-b', // required parameter for this package
                    '-O3', // this produces the slowest but best results
                ]));

            if (class_exists(\Spatie\ImageOptimizer\Optimizers\Cwebp::class)) {
                $optimizerChain->addOptimizer(new \Spatie\ImageOptimizer\Optimizers\Cwebp([
                    '-m 6',
                    '-pass 10',
                    '-mt',
                    '-q ' . $quality,
                ]));
            }

            if (isset($config['convert_to_webp']) && $config['convert_to_webp'] === true) {
                $optimizerChain
                    ->addOptimizer(new \Swissup\ImageOptimizer\Model\Image\Optimizers\ConvertToWebp([/** @phpstan-ignore-line */
                        '-m 6',
                        '-pass 10',
                        '-mt',
                        '-q ' . $quality,
                    ]));
            }
        }

        return $optimizerChain;
    }
}
