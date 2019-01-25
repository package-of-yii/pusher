## Introduction
A pusher extension for Yii2.

A maintained, restructured and reorganized version of [yii2-pusher](https://github.com/br0sk/yii2-pusher)

Makes Pusher integration easier with Yii2.

## Official Documentation
Add this config to your configuration file. 

    'pusher' => [
        'class' => '',
        /*
         * Mandatory parameters.
         */
        'appId' => 'YOUR_APP_ID',
        'appKey' => 'YOUR_APP_KEY',
        'appSecret' => 'YOUR_APP_SECRET',
        /*
         * Optional parameters.
         */
        'options' => ['encrypted' => true, 'cluster' => 'YOUR_APP_CLUSTER']
    ],

You can go to [Pusher Dashboard](https://dashboard.pusher.com/) and create a new app if not created.
Add appID, appKey, appSecret and appCluster to the above configuration.

Typical Example of usage:

    Yii::$app->pusher->push('my-channel', 'my_event', 'hello world');

## License

Package Of Yii Pusher software is licensed under the [MIT license](https://opensource.org/licenses/MIT).
