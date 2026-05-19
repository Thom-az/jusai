<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class SupabaseStorageService
{
    public function __construct(
        private readonly string $supabaseUrl,
        private readonly string $serviceRoleKey,
    ) {}

    public function upload(string $bucket, string $path, string $binary, string $mimeType): string
    {
        $response = Http::withHeaders([
            'Authorization' => "Bearer {$this->serviceRoleKey}",
            'Content-Type'  => $mimeType,
        ])->withBody($binary, $mimeType)
          ->post("{$this->supabaseUrl}/storage/v1/object/{$bucket}/{$path}");

        if ($response->failed()) {
            throw new RuntimeException("Supabase upload failed: {$response->body()}");
        }

        return $path;
    }

    public function download(string $bucket, string $path): string
    {
        $response = Http::withHeaders([
            'Authorization' => "Bearer {$this->serviceRoleKey}",
        ])->get("{$this->supabaseUrl}/storage/v1/object/{$bucket}/{$path}");

        if ($response->failed()) {
            throw new RuntimeException("Supabase download failed: {$response->body()}");
        }

        return $response->body();
    }

    public function delete(string $bucket, string $path): void
    {
        $response = Http::withHeaders([
            'Authorization' => "Bearer {$this->serviceRoleKey}",
        ])->delete("{$this->supabaseUrl}/storage/v1/object/{$bucket}/{$path}");

        if ($response->failed()) {
            throw new RuntimeException("Supabase delete failed: {$response->body()}");
        }
    }

    public function getPublicUrl(string $bucket, string $path): string
    {
        return "{$this->supabaseUrl}/storage/v1/object/public/{$bucket}/{$path}";
    }

    public function getSignedUrl(string $bucket, string $path, int $expiresIn = 3600): string
    {
        $response = Http::withHeaders([
            'Authorization' => "Bearer {$this->serviceRoleKey}",
        ])->post("{$this->supabaseUrl}/storage/v1/object/sign/{$bucket}/{$path}", [
            'expiresIn' => $expiresIn,
        ]);

        if ($response->failed()) {
            throw new RuntimeException("Supabase signed URL failed: {$response->body()}");
        }

        $signedUrl = $response->json('signedURL');

        return "{$this->supabaseUrl}{$signedUrl}";
    }
}
