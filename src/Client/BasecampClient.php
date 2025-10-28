<?php

declare(strict_types=1);

namespace Schmunk42\BasecampApi\Client;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Schmunk42\BasecampApi\Authentication\AuthenticationInterface;
use Schmunk42\BasecampApi\Exception\AuthenticationException;
use Schmunk42\BasecampApi\Exception\RequestException;
use Schmunk42\BasecampApi\Resource\CalendarEventsResource;
use Schmunk42\BasecampApi\Resource\CommentsResource;
use Schmunk42\BasecampApi\Resource\DocumentsResource;
use Schmunk42\BasecampApi\Resource\EventsResource;
use Schmunk42\BasecampApi\Resource\GroupsResource;
use Schmunk42\BasecampApi\Resource\MessagesResource;
use Schmunk42\BasecampApi\Resource\PeopleResource;
use Schmunk42\BasecampApi\Resource\ProjectsResource;
use Schmunk42\BasecampApi\Resource\TodolistsResource;
use Schmunk42\BasecampApi\Resource\TodosResource;
use Schmunk42\BasecampApi\Resource\TopicsResource;
use Schmunk42\BasecampApi\Resource\UploadsResource;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Main client for interacting with Basecamp 2 (BCX) API
 */
final class BasecampClient
{
    private const BASE_URL = 'https://basecamp.com';
    private const API_VERSION = '/api/v1';
    private const USER_AGENT = 'php-bcx-client (https://github.com/schmunk42/php-bcx-client)';

    private HttpClientInterface $httpClient;
    private LoggerInterface $logger;

    // Lazy-loaded resource clients
    private ?ProjectsResource $projects = null;
    private ?TodolistsResource $todolists = null;
    private ?TodosResource $todos = null;
    private ?PeopleResource $people = null;
    private ?MessagesResource $messages = null;
    private ?CommentsResource $comments = null;
    private ?DocumentsResource $documents = null;
    private ?UploadsResource $uploads = null;
    private ?EventsResource $events = null;
    private ?CalendarEventsResource $calendarEvents = null;
    private ?TopicsResource $topics = null;
    private ?GroupsResource $groups = null;

    public function __construct(
        private readonly string $accountId,
        private readonly AuthenticationInterface $authentication,
        ?HttpClientInterface $httpClient = null,
        ?LoggerInterface $logger = null,
    ) {
        $this->httpClient = $httpClient ?? HttpClient::create();
        $this->logger = $logger ?? new NullLogger();
    }

    /**
     * Get Projects resource client
     */
    public function projects(): ProjectsResource
    {
        return $this->projects ??= new ProjectsResource($this);
    }

    /**
     * Get Todolists resource client
     */
    public function todolists(): TodolistsResource
    {
        return $this->todolists ??= new TodolistsResource($this);
    }

    /**
     * Get Todos resource client
     */
    public function todos(): TodosResource
    {
        return $this->todos ??= new TodosResource($this);
    }

    /**
     * Get People resource client
     */
    public function people(): PeopleResource
    {
        return $this->people ??= new PeopleResource($this);
    }

    /**
     * Get Messages resource client
     */
    public function messages(): MessagesResource
    {
        return $this->messages ??= new MessagesResource($this);
    }

    /**
     * Get Comments resource client
     */
    public function comments(): CommentsResource
    {
        return $this->comments ??= new CommentsResource($this);
    }

    /**
     * Get Documents resource client
     */
    public function documents(): DocumentsResource
    {
        return $this->documents ??= new DocumentsResource($this);
    }

    /**
     * Get Uploads (Attachments) resource client
     */
    public function uploads(): UploadsResource
    {
        return $this->uploads ??= new UploadsResource($this);
    }

    /**
     * Get Events resource client
     */
    public function events(): EventsResource
    {
        return $this->events ??= new EventsResource($this);
    }

    /**
     * Get Calendar Events resource client
     */
    public function calendarEvents(): CalendarEventsResource
    {
        return $this->calendarEvents ??= new CalendarEventsResource($this);
    }

    /**
     * Get Topics resource client
     */
    public function topics(): TopicsResource
    {
        return $this->topics ??= new TopicsResource($this);
    }

    /**
     * Get Groups resource client
     */
    public function groups(): GroupsResource
    {
        return $this->groups ??= new GroupsResource($this);
    }

    /**
     * Make a GET request to the API
     *
     * @param array<string, mixed> $query
     * @return array<string, mixed>
     */
    public function get(string $endpoint, array $query = []): array
    {
        return $this->request('GET', $endpoint, ['query' => $query]);
    }

    /**
     * Make a POST request to the API
     *
     * @param array<string, mixed>|string $data Array for JSON or string for raw body
     * @param array<string, string> $headers Optional custom headers for raw body uploads
     * @return array<string, mixed>
     */
    public function post(string $endpoint, array|string $data = [], array $headers = []): array
    {
        if (is_string($data)) {
            // Raw body upload (e.g., file attachments)
            return $this->request('POST', $endpoint, [
                'body' => $data,
                'headers' => $headers,
            ]);
        }

        return $this->request('POST', $endpoint, ['json' => $data]);
    }

    /**
     * Make a PUT request to the API
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function put(string $endpoint, array $data = []): array
    {
        return $this->request('PUT', $endpoint, ['json' => $data]);
    }

    /**
     * Make a DELETE request to the API
     */
    public function delete(string $endpoint): void
    {
        $this->request('DELETE', $endpoint);
    }

    /**
     * Make an HTTP request to the Basecamp API
     *
     * @param array<string, mixed> $options
     * @return array<string, mixed>
     * @throws AuthenticationException
     * @throws RequestException
     */
    private function request(string $method, string $endpoint, array $options = []): array
    {
        if (!$this->authentication->isValid()) {
            throw new AuthenticationException('Authentication token is invalid or expired');
        }

        $url = sprintf('%s/%s%s%s', self::BASE_URL, $this->accountId, self::API_VERSION, $endpoint);

        $headers = array_merge(
            [
                'User-Agent' => self::USER_AGENT,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
            $this->authentication->getHeaders()
        );

        $options['headers'] = $headers;

        $this->logger->debug('Basecamp API Request', [
            'method' => $method,
            'url' => $url,
            'options' => $options,
        ]);

        try {
            $response = $this->httpClient->request($method, $url, $options);
            $statusCode = $response->getStatusCode();

            // DELETE requests typically return 204 No Content
            if ($statusCode === 204 || $method === 'DELETE') {
                return [];
            }

            $content = $response->getContent();
            $data = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

            $this->logger->debug('Basecamp API Response', [
                'status' => $statusCode,
                'data' => $data,
            ]);

            return $data;
        } catch (HttpExceptionInterface $e) {
            $statusCode = $e->getResponse()->getStatusCode();
            $responseBody = $e->getResponse()->getContent(false);

            $this->logger->error('Basecamp API Error', [
                'status' => $statusCode,
                'response' => $responseBody,
                'exception' => $e->getMessage(),
            ]);

            if ($statusCode === 401) {
                throw new AuthenticationException('Authentication failed', $statusCode, $e);
            }

            throw new RequestException(
                sprintf('Request failed with status %d: %s', $statusCode, $e->getMessage()),
                $statusCode,
                $responseBody
            );
        }
    }

    public function getAccountId(): string
    {
        return $this->accountId;
    }
}
