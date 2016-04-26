<?php

use Aws\Laravel\AwsServiceProvider;

return [

    /*
    |--------------------------------------------------------------------------
    | AWS SDK Configuration
    |--------------------------------------------------------------------------
    |
    | The configuration options set in this file will be passed directly to the
    | `Aws\Sdk` object, from which all client objects are created. The minimum
    | required options are declared here, but the full set of possible options
    | are documented at:
    | http://docs.aws.amazon.com/aws-sdk-php/v3/guide/guide/configuration.html
    |
    */
    'credentials' => [
        'key'    => env('AWS_KEY', 'my-aws-key'),
        'secret' => env('AWS_SECRET', 'my-aws-secret'),
    ],
    'region' => env('AWS_REGION', 'us-west-2'),
    'version' => 'latest',
    'ua_append' => [
        'L5MOD/' . AwsServiceProvider::VERSION,
    ],
    's3' => [
        'region' => env('S3_IMAGES_REGION', 'my-aws-key'),
        'credentials' => [
            'key'    => env('S3_IMAGES_KEY', 'my-aws-key'),
            'secret' => env('S3_IMAGES_SECRET', 'my-aws-secret'),
        ],
    ],
];
