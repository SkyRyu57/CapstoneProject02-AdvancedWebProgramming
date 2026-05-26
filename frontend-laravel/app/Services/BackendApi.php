<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;

class BackendApi
{
    public function post(string $path, array $payload = []): Response
    {
        return $this->client()->post($this->url($path), $payload);
    }

    public function patch(string $path, array $payload = []): Response
    {
        return $this->client()->patch($this->url($path), $payload);
    }

    public function patchMultipart(string $path, array $payload = [], array $files = []): Response
    {
        $request = $this->client();

        foreach ($files as $name => $file) {
            if ($file instanceof UploadedFile) {
                $request = $request->attach(
                    $name,
                    fopen($file->getRealPath(), 'r'),
                    $file->getClientOriginalName(),
                );
            }
        }

        return $request->asMultipart()->post($this->url($path), $payload);
    }

    public function get(string $path): Response
    {
        return $this->client()->get($this->url($path));
    }

    public function delete(string $path): Response
    {
        return $this->client()->delete($this->url($path));
    }

    public function withToken(string $token): self
    {
        $clone = clone $this;
        $clone->token = $token;

        return $clone;
    }

    private ?string $token = null;

    private function client(): PendingRequest
    {
        $request = Http::acceptJson()->timeout(10);

        if ($this->token) {
            $request = $request->withToken($this->token);
        }

        return $request;
    }

    private function url(string $path): string
    {
        return rtrim(config('services.backend_api.url'), '/').'/'.ltrim($path, '/');
    }
}
