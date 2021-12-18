<?php

namespace GrotonSchool\OAuth2\Client\Provider;

use Exception;
use GrotonSchool\Path\Path;
use GuzzleHttp\Client;

class SkyAPI
{
    /** @var Client */
    private $client;

    /** @var BlackbaudSKY */
    private $sky;

    /** @var string */
    private $path;

    public function __construct(BlackbaudSky $sky, string $path)
    {
        assert(!empty($sky), new Exception('BlackbaudSKY instance required'));
        $this->sky = $sky;
        $this->path = $path;
        $this->client = new Client(['base_uri' => Path::join($this->sky->getBaseApiUrl(), $this->path) . '/']);
    }

    public function send(string $method, string $url, array $options = []): mixed
    {
        // TODO deal with refreshing tokens (need callback to store new refresh token)
        usleep(100000); // FIXME https://developer.blackbaud.com/skyapi/docs/in-depth-topics/api-request-throttling
        $request = $this->sky->getAuthenticatedRequest($method, $url, $this->sky->getAccessToken(), $options);
        return json_decode($this->client->send($request)->getBody()->getContents(), true);
    }

    public function get(string $url, array $options = []): mixed
    {
        return $this->send('get', $url, $options);
    }

    public function post(string $url, array $options = []): mixed
    {
        return $this->send('post', $url, $options);
    }

    public function put(string $url, array $options = []): mixed
    {
        return $this->send('put', $url, $options);
    }

    public function delete(string $url, array $options = []): mixed
    {
        return $this->send('delete', $url, $options);
    }

    public function endpoint(string $path): SkyAPI
    {
        return new SkyAPI($this->sky, Path::join($this->path, $path));
    }
}
