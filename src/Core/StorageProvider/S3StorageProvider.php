<?php

namespace Core\StorageProvider;

use Aws\S3\S3Client;
use Core\Exception\MissingParamsException;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use WyriHaximus\SliFly\FlysystemServiceProvider;

/**
 * Storage class to manage Storage provider from FlySystem
 *
 * Class StorageProvider
 * @package Core\Provider
 */
class S3StorageProvider implements ServiceProviderInterface
{
    /**
     * @param Container $app
     *
     * @return string
     * @throws MissingParamsException
     */
    public function register(Container $app)
    {
        $s3Params = $app['params']->get('aws_s3');
        if (in_array("", $s3Params)) {
            throw new MissingParamsException("One of AWS S3 parameters in empty ! ");
        }
        $s3Client = new S3Client(
            [
                'credentials' => [
                    'key' => $s3Params['access_id'],
                    'secret' => $s3Params['secret_key'],
                ],
                'region' => $s3Params['region'],
                'version' => 'latest',
            ]
        );

        $app->register(
            new FlysystemServiceProvider(),
            [
                'flysystem.filesystems' => [
                    'upload_dir' => [
                        'adapter' => 'League\Flysystem\AwsS3v3\AwsS3Adapter',
                        'args' => [
                            $s3Client,
                            $s3Params['bucket_name'],
                        ],
                    ],
                ],
            ]
        );

        $app['flysystems']['file_path_resolver'] = function () use ($s3Params) {
            return sprintf('https://s3.%s.amazonaws.com/%s/', $s3Params['region'], $s3Params['bucket_name']).'%s';
        };
    }
}
