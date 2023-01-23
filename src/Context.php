<?php
/**
 * @author 小日日
 * @time 2023/1/23
 */

namespace roc;

use Swoole\Http\Request;
use Swoole\Http\Response;


class Context
{

    private Request $request;
    private Response $response;

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function setRequest(Request $request): void
    {
        $this->request = $request;
    }

    public function getResponse(): Response
    {
        return $this->response;
    }

    public function setResponse(Response $response): void
    {
        $this->response = $response;
    }

    public function write(int $code, $data)
    {
        $this->response->setStatusCode($code);
        $this->response->write($data);
        $this->response->end();
    }

    public function writeJson($data): void
    {
        $this->response->setStatusCode(200);
        $this->response->header('Content-Type', 'application/json');
        $content = json_encode($data, JSON_UNESCAPED_UNICODE);
        $json_err = json_last_error();
        if ($json_err !== 0) {
            $this->response->setStatusCode(500);
            $this->response->end("json encode error: {$json_err}");
        } else {
            $this->response->end($content);
        }
    }
}