<?php


namespace Sammy1992\Haina\Core\Exceptions;

use Psr\Http\Message\ResponseInterface;

class HttpException extends Exception
{
    /**
     * @var ResponseInterface
     */
    public $response;

    /**
     * @var ResponseInterface|array|null
     */
    public $formattedResponse;

    /**
     * HttpException constructor.
     *
     * @param $message
     * @param ResponseInterface|null $response
     * @param ResponseInterface|array|null $formattedResponse
     * @param int $code
     */
    public function __construct($message, ResponseInterface $response = null, $formattedResponse = null, $code = 0)
    {
        parent::__construct($message, $code);

        $this->response = $response;

        $this->formattedResponse = $formattedResponse;

        if ($response) $response->getBody()->rewind();
    }
}