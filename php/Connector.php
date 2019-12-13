<?php

namespace Synolia;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\ResponseInterface;

require (__DIR__.'/../vendor/autoload.php');

trait Connector
{
    /**
     * @param string $method
     * @param string $uri
     * @param array $options
     * @param bool $insertTokenInRequest
     * @return ResponseInterface
     * @throws \Exception
     */
    public function makeRequest(string $method, string $uri, array $options = [], bool $insertTokenInRequest = true): ResponseInterface
    {
        $options = [RequestOptions::JSON => $options];
        if ($insertTokenInRequest) {
            $options[RequestOptions::HEADERS] = [
                'Authorization' => 'Bearer '.$this->getToken(),
                'Accept' => 'application/json',
            ];
        }
        $uri = Configuration::BASE_URI.$uri;
        $client = new Client();

        try {
            $response = $client->request($method, $uri, $options);
        } catch (ClientException $e) {
            echo PHP_EOL.PHP_EOL;
            echo 'code :'.PHP_EOL;
            var_dump($e->getCode());
            echo 'method :'.PHP_EOL;
            var_dump($method);
            echo 'uri :'.PHP_EOL;
            var_dump($uri);
            echo 'message :'.PHP_EOL;
            var_dump($e->getMessage());
            echo 'options :'.PHP_EOL;
            var_dump($options);
            echo PHP_EOL;

            die('-->KO'.PHP_EOL.PHP_EOL);
        }

        echo PHP_EOL;
        echo PHP_EOL;
        echo "-->OK::".$method.'>'.$uri;
        echo PHP_EOL;
        echo PHP_EOL;

        return $response;
    }

    /**
     * @return string
     * @throws \Exception
     */
    private function getToken(): string
    {
        if (is_readable(Configuration::TOKEN_FILE) && file_get_contents(Configuration::TOKEN_FILE) !== false) {
            $token = json_decode(file_get_contents(Configuration::TOKEN_FILE));
            $tokenStatus = $this->getTokenStatus($token, time() - filemtime(Configuration::TOKEN_FILE));

            if ($tokenStatus === true) {
                return $token->access_token;
            }
            if ($tokenStatus === false) {
                return $this->refreshToken($token);
            }
        }

        return $this->getNewToken();
    }

    /**
     * @param \stdClass $token
     * @return string
     * @throws \Exception
     */
    private function refreshToken(\stdClass $token): string
    {
        $response = $this->makeRequest('POST', '/oauth2/token', [
            'grant_type' => 'refresh_token',
            'refresh_token' => $token->refresh_token,
            'client_id' => 'sugar',
            'client_secret' => '',
        ], false);

        if ($response->getStatusCode() !== 200) {
            throw new \Exception('Unable to refresh token from the API!');
        }

        $accessToken = json_decode($response->getBody())->access_token;
        $this->writeTokenInFile($response->getBody());

        return $accessToken;
    }

    /**
     * @return string
     * @throws \Exception
     */
    private function getNewToken(): string
    {
        $response = $this->makeRequest('POST', '/oauth2/token', [
            'grant_type' => 'password',
            'client_id' => 'sugar',
            'client_secret' => '',
            'username' => Configuration::USERNAME,
            'password' => Configuration::PASSWORD,
            'platform' => 'base'
        ], false);

        if ($response->getStatusCode() !== 200) {
            throw new \Exception('Unable to get a new token from the API!');
        }

        $accessToken = json_decode($response->getBody())->access_token;
        $this->writeTokenInFile($response->getBody());

        return $accessToken;
    }

    /**
     * If true the token is valid, if false it needs to be refreshed, if null the token is out of date.
     * @param \stdClass $token
     * @param int $secondsSinceCreation
     * @return bool|null
     */
    private function getTokenStatus(\stdClass $token, int $secondsSinceCreation)
    {
        if ($secondsSinceCreation < $token->expires_in) {
            return true;
        } elseif ($secondsSinceCreation < $token->refresh_expires_in) {
            return false;
        }

        return null;
    }

    /**
     * @param string $tokenAsJson
     */
    private function writeTokenInFile(string $tokenAsJson): void
    {
        if (is_readable(Configuration::TOKEN_FILE)) {
            unlink(Configuration::TOKEN_FILE);
        }

        touch(Configuration::TOKEN_FILE);
        file_put_contents(Configuration::TOKEN_FILE, $tokenAsJson);
    }
}
