<?php

namespace POYii\Pusher;

use yii\base\Component;
use yii\base\InvalidConfigException;


/**
 * Class Pusher
 * @package POYii\Pusher
 */
class Pusher extends Component
{
    /**
     * @var object
     */
    private $pusher = null;

    /**
     * @var string
     */
    public $appId = null;

    /**
     * @var string
     */
    public $appKey = null;

    /**
     * @var string
     */
    public $appSecret = null;

    /**
     * @var array
     */
    private $selectableOptions = ['host', 'port', 'timeout', 'encrypted', 'cluster'];

    /**
     * @var array
     */
    public $options = [];

    /**
     * Performing the initialization of the $appId, $appKey, $appSecret and $options before the parent
     * Component is initialized.
     */
    public function init()
    {
        parent::init();

        if (!$this->appId) {
            throw new InvalidConfigException('AppId cannot be empty!');
        }

        if (!$this->appKey) {
            throw new InvalidConfigException('AppKey cannot be empty!');
        }

        if (!$this->appSecret) {
            throw new InvalidConfigException('AppSecret cannot be empty!');
        }

        foreach (array_keys($this->options) as $key) {
            if (in_array($key, $this->selectableOptions) === false) {
                throw new InvalidConfigException($key . ' is not a valid option!');
            }
        }

        if ($this->pusher === null) {
            $this->pusher = new \Pusher($this->appKey, $this->appSecret, $this->appId, $this->options);
        }
    }

    /**
     * Proxy the calls to the Pusher object if the methods doesn't explicitly exist in this class.
     *
     * @param $method
     * @param $params
     * @return mixed
     */
    public function __call($method, $params)
    {
        if (method_exists($this->pusher, $method)) {
            return call_user_func_array([$this->pusher, $method], $params);
        }

        return parent::__call($method, $params);
    }

    /**
     * Trigger an event by providing event name and payload.
     * Optionally provide a socket ID to exclude a client (most likely the sender).
     *
     * @param array $channels An array of channel names to publish the event on
     * @param $event
     * @param $data
     * @param null $socketId
     * @param bool $debug
     * @param bool $encoded
     * @return bool|string
     */
    public function push(array $channels, $event, $data, $socketId = null, $debug = false, $encoded = false)
    {
        $this->pusher->trigger($channels, $event, $data, $socketId, $debug, $encoded);
    }
}
