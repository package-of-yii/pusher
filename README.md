## Introduction
A pusher extension for Yii2.

A maintained, restructured and reorganized version of [yii2-pusher](https://github.com/br0sk/yii2-pusher)

Makes Pusher integration easier with Yii2.

## Official Documentation
Add this config to your configuration file. 

    'pusher' => [
        'class' => 'POYii\Pusher\Pusher',
        'appId' => 'YOUR_APP_ID',
        'appKey' => 'YOUR_APP_KEY',
        'appSecret' => 'YOUR_APP_SECRET',
        'options' => ['encrypted' => true, 'cluster' => 'YOUR_APP_CLUSTER']
    ],

You can go to [Pusher Dashboard](https://dashboard.pusher.com/) and create a new app if not created.
Add appID, appKey, appSecret and appCluster to the above configuration.

Typical Example of usage:

Php:

    Yii::$app->pusher->push('my-channel', 'my_event', 'hello world');

Frontend:

    <!DOCTYPE html>
    <head>
      <title>Pusher Test</title>
      <script src="https://js.pusher.com/4.3/pusher.min.js"></script>
      <script>
    
        // Enable pusher logging - don't include this in production
        Pusher.logToConsole = true;
    
        var pusher = new Pusher('YOUR_APP_KEY', {
          cluster: 'eu',
          forceTLS: true
        });
    
        var channel = pusher.subscribe('my-channel');
        channel.bind('my-event', function(data) {
          alert(JSON.stringify(data));
        });
      </script>
    </head>
    <body>
      <h1>Pusher Test</h1>
      <p>
        Try publishing an event to channel <code>my-channel</code>
        with event name <code>my-event</code>.
      </p>
    </body>

## License

Package Of Yii Pusher software is licensed under the [MIT license](https://opensource.org/licenses/MIT).
