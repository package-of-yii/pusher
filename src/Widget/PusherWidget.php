<?php

namespace POYii\Pusher\Widget;


use POYii\Pusher\Pusher;
use yii\base\Widget;
use Yii;
use yii\helpers\Json;
use yii\web\View;
use yii\helpers\Html;

class PusherWidget extends Widget
{
    public $events = [];

    public $options = [];

    public function run()
    {
        if (!$pusher = $this->getComponentByClass(Pusher::class)) {
            return;
        }

        Yii::$app->view->registerJsFile('https://js.pusher.com/4.3/pusher.min.js', ['position' => View::POS_HEAD]);
        $values = ($this->options['channelValues']) ?: [];
        $channel = $pusher->getUserChannels($values);
        $events = Json::encode($this->events);
        $pusherOptions = Json::encode($pusher->options);
        $script = <<<JS
var pusher = new Pusher("{$pusher->appKey}", $pusherOptions);
var channel = pusher.subscribe("{$channel}");
var events = $events;
for (var name in events) { channel.bind(name, events[name]); }
JS;

        Yii::$app->view->registerJs($script, View::POS_HEAD);

        return Html::tag('div', $channel, ['id' => 'pusher-channel', 'class' => 'hide']);
    }

    /**
     * Gets application component
     * @param $class
     * @return \yii\base\Component|null
     */
    private function getComponentByClass($class)
    {
        foreach (Yii::$app->getComponents() as $name => $config) {
            $componentClass = is_array($config) ? @$config['class'] : $config;
            if ($componentClass == $class) {
                return Yii::$app->$name;
            }
        }

        return null;
    }
}