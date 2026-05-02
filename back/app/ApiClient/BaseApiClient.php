<?php

namespace App\ApiClient;

use Exception;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

abstract class BaseApiClient
{
    public function __construct(
        protected string $baseUrl,
        protected string $apiKey
    ) {}

    /**
     * Authorize the request
     */
    abstract protected function authorize(PendingRequest $request): PendingRequest;

    /**
     * Get the data from the API
     */
    public function get(string $endpoint, array $params = []): array
    {
        $request = Http::baseUrl($this->baseUrl)
            ->timeout(10)
            ->acceptJson();

        $request = $this->authorize($request);

        try {
            $response = $request->get($endpoint, $params);
        } catch (Exception $e) {
            throw new Exception('API Error: '.$e->getMessage());
        }
        $response = $this->normalizeResponse($response);

        $this->handleError($response);

        return (array) $response->json();
    }

    /**
     * Handle the error
     */
    protected function handleError(Response $response): void
    {
        if ($response->failed()) {
            throw new Exception('API Error: '.$response->body());
        }
    }

    /**
     * Normalize the response
     *
     * @param  Response|PromiseInterface  $response
     */
    protected function normalizeResponse($response): Response
    {
        if ($response instanceof PromiseInterface) {
            return $response->wait();
        }

        return $response;
    }
}
