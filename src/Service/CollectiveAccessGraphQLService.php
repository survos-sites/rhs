<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class CollectiveAccessGraphQLService
{
    private const JWT_REFRESH_AFTER_SECONDS = 480;

    private ?string $jwt = null;
    private ?string $jwtContext = null;
    private int $jwtAuthenticatedAt = 0;

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        #[Autowire('%env(CA_SERVER)%')] private readonly ?string $baseUrl = null,
        #[Autowire('%env(CA_USERNAME)%')] private readonly ?string $username = null,
        #[Autowire('%env(CA_PASSWORD)%')] private readonly ?string $password = null,

    ) {
    }

    public function auth(): string
    {
        return $this->getJwt($this->baseUrl, $this->username, $this->password);
    }

    public function getObjectCount(
        string $search,
        array $bundles,
        ?string $baseUrl = null,
        ?string $username = null,
        ?string $password = null,
    ): int {
        $result = $this->searchObjects(
            search: $search,
            bundles: $bundles,
            start: 0,
            limit: 1,
            baseUrl: $baseUrl,
            username: $username,
            password: $password,
        );

        return (int) ($result['data']['search']['count'] ?? 0);
    }

    public function searchObjects(
        string $search,
        array $bundles,
        int $start,
        int $limit,
        ?string $baseUrl = null,
        ?string $username = null,
        ?string $password = null,
    ): array {
        $jwt = $this->auth();
        $bundlesJson = json_encode(array_values($bundles), JSON_THROW_ON_ERROR);

        $query = sprintf(
            '{ search(table: "ca_objects", search: "%s", bundles: %s, start: %d, limit: %d) { table, count, results { result { id, table, idno, bundles { name, code, dataType, values { value, locale } } } } } }',
            addslashes($search),
            $bundlesJson,
            $start,
            $limit,
        );

        return $this->request(
            baseUrl: $this->baseUrl,
            endpoint: '/service/Search',
            headers: [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $jwt,
            ],
            query: $query,
        );
    }

    private function getJwt(string $baseUrl, string $username, string $password): string
    {

        $context = sha1($baseUrl . "\0" . $username . "\0" . $password);

        if (
            $this->jwt !== null
            && $this->jwtContext === $context
            && (time() - $this->jwtAuthenticatedAt) <= self::JWT_REFRESH_AFTER_SECONDS
        ) {
            return $this->jwt;
        }

        $query = sprintf(
            '{ login(username: "%s", password: "%s") { jwt } }',
            addslashes($username),
            addslashes($password),
        );

        $data = $this->request(
            baseUrl: $baseUrl,
            endpoint: '/service/Auth',
            headers: ['Content-Type' => 'application/json'],
            query: $query,
        );

        $jwt = $data['data']['login']['jwt'] ?? null;
        if (!is_string($jwt) || $jwt === '') {
            throw new \RuntimeException('CollectiveAccess authentication failed');
        }

        $this->jwt = $jwt;
        $this->jwtContext = $context;
        $this->jwtAuthenticatedAt = time();

        return $jwt;
    }

    private function request(string $baseUrl, string $endpoint, array $headers, string $query): array
    {
        $response = $this->httpClient->request('POST', rtrim($baseUrl, '/') . $endpoint, [
            'headers' => $headers,
            'json' => ['query' => $query],
        ]);

        return $response->toArray(false);
    }

    private function resolveBaseUrl(?string $baseUrl): string
    {
        $baseUrl = $baseUrl ?? '';
        if (!$baseUrl) {
            throw new \InvalidArgumentException('CollectiveAccess base URL must be provided via argument or CA_BASE_URL');
        }

        return $baseUrl;
    }

    private function resolveUsername(?string $username): string
    {
        $username = $username ?? '';
        if (!$username) {
            throw new \InvalidArgumentException('CollectiveAccess username must be provided via --username or CA_USERNAME');
        }

        return $username;
    }

    private function resolvePassword(?string $password): string
    {
        $password = $password ?? '';
        if ($password === '') {
            $password = $this->getEnv('CA_PASSWORD') ?? ($this->defaultPassword ?? '');
        }

        if ($password === '') {
            throw new \InvalidArgumentException('CollectiveAccess password must be provided via --password or CA_PASSWORD');
        }

        return $password;
    }

    private function getEnv(string $key): ?string
    {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? null;

        return is_string($value) && $value !== '' ? $value : null;
    }
}
