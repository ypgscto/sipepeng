<?php

namespace App\Services\Siakad;

use App\Models\SiakadApiLog;
use Illuminate\Support\Str;

class SiakadApiLogger
{
    public function log(
        string $purpose,
        string $httpMethod,
        string $endpoint,
        ?array $requestQuery,
        ?array $requestBody,
        ?int $responseStatus,
        ?string $responseBody,
        int $durationMs,
        bool $isSuccess,
        ?string $errorMessage = null,
        ?string $correlationId = null,
    ): void {
        SiakadApiLog::query()->create([
            'purpose' => $purpose,
            'http_method' => $httpMethod,
            'endpoint' => $endpoint,
            'request_query' => $requestQuery,
            'request_body' => $this->redactBody($requestBody),
            'response_status' => $responseStatus,
            'response_body' => $this->truncate($responseBody),
            'duration_ms' => $durationMs,
            'is_success' => $isSuccess,
            'error_message' => $errorMessage,
            'triggered_by' => auth()->id(),
            'correlation_id' => $correlationId ?? (string) Str::uuid(),
            'created_at' => now(),
        ]);
    }

    /**
     * @param  array<string, mixed>|null  $body
     * @return array<string, mixed>|null
     */
    protected function redactBody(?array $body): ?array
    {
        if ($body === null) {
            return null;
        }

        if (array_key_exists('password', $body)) {
            $body['password'] = '***';
        }

        return $body;
    }

    protected function truncate(?string $body, int $max = 8192): ?string
    {
        if ($body === null) {
            return null;
        }

        if (strlen($body) <= $max) {
            return $body;
        }

        return substr($body, 0, $max).'…[truncated]';
    }
}
