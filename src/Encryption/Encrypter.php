<?php

namespace POYii\Pusher\Encryption;


/**
 * Inspired From Laravel Encrypter.
 * 
 * Class Encrypter
 * @package POYii\Pusher\Encryption
 */
class Encrypter
{
    /**
     * The encryption key.
     *
     * @var string
     */
    protected $key;
    /**
     * The algorithm used for encryption.
     *
     * @var string
     */
    protected $cipher;

    /**
     * Create a new encrypter instance.
     *
     * @param  string $key
     * @param  string $cipher
     * @return void
     *
     * @throws \RuntimeException
     */
    public function __construct($key, $cipher = 'AES-128-CBC')
    {
        $key = substr((string)$key, 0, $this->getStringBit($cipher));
        if (static::supported($key, $cipher)) {
            $this->key = $key;
            $this->cipher = $cipher;
        } else {
            throw new RuntimeException('The only supported ciphers are AES-128-CBC and AES-256-CBC with the correct key lengths.');
        }
    }

    /**
     * Determine if the given key and cipher combination is valid.
     *
     * @param  string $key
     * @param  string $cipher
     * @return bool
     */
    public static function supported($key, $cipher)
    {
        $length = mb_strlen($key, '8bit');
        return ($cipher === 'AES-128-CBC' && $length === 16) ||
            ($cipher === 'AES-256-CBC' && $length === 32);
    }

    /**
     * Create a new encryption key for the given cipher.
     *
     * @param  string $cipher
     * @return string
     */
    public static function generateKey($cipher)
    {
        return random_bytes($cipher === 'AES-128-CBC' ? 16 : 32);
    }

    /**
     * Encrypt the given value.
     *
     * @param  mixed $value
     * @param  bool $serialize
     * @return string
     *
     * @throws EncryptException
     */
    public function encrypt($value, $serialize = true)
    {
        $iv = random_bytes(openssl_cipher_iv_length($this->cipher));
        $value = \openssl_encrypt(
            $serialize ? serialize($value) : $value,
            $this->cipher, $this->key, 0, $iv
        );
        if ($value === false) {
            throw new EncryptException('Could not encrypt the data.');
        }
        $mac = $this->hash($iv = base64_encode($iv), $value);
        $json = json_encode(compact('iv', 'value', 'mac'));
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new EncryptException('Could not encrypt the data.');
        }
        return base64_encode($json);
    }

    /**
     * Encrypt a string without serialization.
     *
     * @param  string $value
     * @return string
     */
    public function encryptString($value)
    {
        return $this->encrypt($value, false);
    }

    /**
     * Decrypt the given value.
     *
     * @param  mixed $payload
     * @param  bool $unserialize
     * @return mixed
     *
     * @throws DecryptException
     */
    public function decrypt($payload, $unserialize = true)
    {
        $payload = $this->getJsonPayload($payload);
        $iv = base64_decode($payload['iv']);
        $decrypted = \openssl_decrypt(
            $payload['value'], $this->cipher, $this->key, 0, $iv
        );
        if ($decrypted === false) {
            throw new DecryptException('Could not decrypt the data.');
        }
        return $unserialize ? unserialize($decrypted) : $decrypted;
    }

    /**
     * Decrypt the given string without unserialization.
     *
     * @param  string $payload
     * @return string
     */
    public function decryptString($payload)
    {
        return $this->decrypt($payload, false);
    }

    /**
     * Create a MAC for the given value.
     *
     * @param  string $iv
     * @param  mixed $value
     * @return string
     */
    protected function hash($iv, $value)
    {
        return hash_hmac('sha256', $iv . $value, $this->key);
    }

    /**
     * Get the JSON array from the given payload.
     *
     * @param  string $payload
     * @return array
     *
     * @throws DecryptException
     */
    protected function getJsonPayload($payload)
    {
        $payload = $this->decodePayload($payload);
        if (!$this->validPayload($payload)) {
            throw new DecryptException('The payload is invalid.');
        }
        if (!$this->validMac($payload)) {
            throw new DecryptException('The MAC is invalid.');
        }
        return $payload;
    }

    /**
     * Verify that the encryption payload is valid.
     *
     * @param  mixed $payload
     * @return bool
     */
    protected function validPayload($payload)
    {
        return is_array($payload) && isset($payload['iv'], $payload['value'], $payload['mac']) &&
            strlen(base64_decode($payload['iv'], true)) === openssl_cipher_iv_length($this->cipher);
    }

    /**
     * Determine if the MAC for the given payload is valid.
     *
     * @param  array $payload
     * @return bool
     */
    protected function validMac(array $payload)
    {
        $calculated = $this->calculateMac($payload, $bytes = random_bytes(16));
        return hash_equals(
            hash_hmac('sha256', $payload['mac'], $bytes, true), $calculated
        );
    }

    /**
     * Calculate the hash of the given payload.
     *
     * @param  array $payload
     * @param  string $bytes
     * @return string
     */
    protected function calculateMac($payload, $bytes)
    {
        return hash_hmac(
            'sha256', $this->hash($payload['iv'], $payload['value']), $bytes, true
        );
    }

    /**
     * Get the encryption key.
     *
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Get the string bit for the cipher used.
     *
     * @param $cipher
     * @return int
     */
    private function getStringBit($cipher)
    {
        return $cipher === 'AES-128-CBC' ? 16 : 32;
    }

    /**
     * First step on decoding the payload.
     *
     * @param $payload
     * @return mixed
     */
    private function decodePayload($payload)
    {
        return json_decode(base64_decode($payload), true);
    }

    /**
     * Check if the cryptic hash is valid or not.
     *
     * @param $payload
     * @return bool
     */
    private function validHashedPayload($payload)
    {
        $payload = $this->decodePayload($payload);

        return ($this->validPayload($payload) && $this->validMac($payload));
    }

    /**
     * Encrypt the payload with sha1 encryption.
     *
     * @param $payload
     * @return string
     */
    public function encryptSha1($payload)
    {
        return sha1($payload);
    }
}