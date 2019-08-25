<?php


namespace HainaSDK\src\Notify;


use Sammy1992\Haina\Core\Support\AES;

/**
 * Class Hanlder
 */
abstract class Hanlder
{
    /**
     * @var array
     */
    protected $message;

    /**
     * @var
     */
    protected $app;

    /**
     * Hanlder constructor.
     *
     * @param $app
     */
    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * @return array
     */
    public function getMessage(): array
    {
        if (!empty($this->message)) return $this->message;

        $message = json_decode((string)$this->app->request->getContents(), true);

        return $this->message = $message;
    }

    /**
     * @return bool|string|null
     * @throws \Sammy1992\Haina\Core\Exceptions\Exception
     */
    public function decryptMessage()
    {
        $message = $this->getMessage();
        if (empty($message['data'])) {
            return null;
        }

        return AES::decrypt($message['data'], $this->app->config['aeskey']);
    }


    abstract public function handler(\Closure $closure);
}