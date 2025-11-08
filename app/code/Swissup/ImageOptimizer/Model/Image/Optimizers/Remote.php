<?php

namespace Swissup\ImageOptimizer\Model\Image\Optimizers;

// @phpstan-ignore-next-line
class Remote extends \Spatie\ImageOptimizer\Optimizers\BaseOptimizer
{
    /**
     * @var \GuzzleHttp\Client|null
     */
    private $httpClient;

    /**
     * @var string
     */
    public $binaryName = 'echo';

    /**
     *
     * @param  \Spatie\ImageOptimizer\Image  $image
     * @return boolean
     */
    public function canHandle(\Spatie\ImageOptimizer\Image $image): bool
    {
        return in_array($image->mime(), [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
        ]);
    }

    /**
     * @return string
     */
    private function getBin()
    {
        return "{$this->binaryPath}{$this->binaryName}";/** @phpstan-ignore-line */
    }

    /**
     *
     * @return mixed
     */
    private function getHostnameParam()
    {
        $url = isset($this->options['baseUrl']) ? $this->options['baseUrl']: '';/** @phpstan-ignore-line */
        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            return;
        }
        $uri = \Laminas\Uri\UriFactory::factory($url);
        return $uri->getHost();
    }

    /**
     * @return string
     */
    private function getApiUrl()
    {
        return (string) $this->options['apiUrl'];/** @phpstan-ignore-line */
    }

    /**
     * @return string
     */
    private function getApiKey()
    {
        return (string) $this->options['apiKey'];/** @phpstan-ignore-line */
    }

    /**
     * @return string
     */
    private function getFilePathParam()
    {
        $mediaDir = (string) $this->options['mediaDir'];/** @phpstan-ignore-line */
        $mediaDir = rtrim($mediaDir, '/');
        $filename = (string) $this->getImagePath();

        if (substr($filename, 0, strlen($mediaDir)) == $mediaDir) {
            $filename = substr($filename, strlen($mediaDir));
        }

        return $filename;
    }

    /**
     * @return \GuzzleHttp\Client
     */
    private function getHttpClient()
    {
        if ($this->httpClient === null) {
            $this->httpClient = new \GuzzleHttp\Client([
//                'timeout'  => 2.0,
            ]);
        }

        return $this->httpClient;
    }

    /**
     * @return array|bool|float|int|object|string|null
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function optimize()
    {
        $client = $this->getHttpClient();
        $response = $client->request('POST', "{$this->getApiUrl()}/image/optimize", [
            'headers' => [
                'X-Authorization' => $this->getApiKey()
            ],
            'multipart' => [
                [
                    'name'     => 'filename',
                    'contents' => $this->getFilePathParam()
                ],
                [
                    'name'     => 'hostname',
                    'contents' => $this->getHostnameParam()
                ],
                [
                    'name'     => 'image',
                    'contents' => \GuzzleHttp\Psr7\Utils::tryFopen($this->getImagePath(), 'r')
                ]
            ]
        ]);
        if ($response->getStatusCode() !== 200) {
            return false;
        }
        $body = $response->getBody();
        return \GuzzleHttp\Utils::jsonDecode($body, true);
    }

    /**
     * @param $url
     * @param $outputFile
     * @return void
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function download($url, $outputFile)
    {
        $client = $this->getHttpClient();
        $resource = \GuzzleHttp\Psr7\Utils::tryFopen($outputFile, 'w');
        $client->request('GET', $url, ['sink' => $resource]);
    }

    /**
     *
     * @return string
     */
    public function getCommand(): string
    {
        $result = $this->optimize();

        if ($result
            && isset($result['dest'])
            && isset($result['src_size'])
            && isset($result['dest_size'])
            && $result['dest_size'] < $result['src_size']
        ) {
            $this->download($result['dest'], $this->getImagePath());
        }

        return '';
    }

    private function getImagePath()
    {
        return (string) $this->imagePath;/** @phpstan-ignore-line */
    }
}
