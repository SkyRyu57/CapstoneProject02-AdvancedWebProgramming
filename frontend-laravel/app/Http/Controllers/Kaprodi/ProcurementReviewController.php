<?php

namespace App\Http\Controllers\Kaprodi;

use App\Http\Controllers\Concerns\InteractsWithBackend;
use App\Http\Controllers\Controller;
use App\Services\BackendApi;
use Illuminate\Http\Request;

class ProcurementReviewController extends Controller
{
    use InteractsWithBackend;

    public function index(BackendApi $api)
    {
        if ($redirect = $this->ensureRole('kaprodi')) {
            return $redirect;
        }

        $response = $this->api($api)->get('/kaprodi/procurement-drafts');

        return view('kaprodi.drafts', [
            'user' => session('auth_user'),
            'drafts' => $response->json('drafts', []),
            'error' => $response->successful() ? null : $response->json('message', 'Draf pengadaan belum bisa dimuat.'),
        ]);
    }

    public function show(BackendApi $api, int $id)
    {
        if ($redirect = $this->ensureRole('kaprodi')) {
            return $redirect;
        }

        $response = $this->api($api)->get("/kaprodi/procurement-drafts/{$id}");

        return view('kaprodi.draft-show', [
            'user' => session('auth_user'),
            'draft' => $response->json('draft'),
            'error' => $response->successful() ? null : $response->json('message', 'Draf pengadaan belum bisa dimuat.'),
        ]);
    }

    public function reviewItem(Request $request, BackendApi $api, int $draftId, int $itemId)
    {
        if ($redirect = $this->ensureRole('kaprodi')) {
            return $redirect;
        }

        $payload = $request->validate([
            'approval_status' => ['required', 'in:pending,approved,rejected'],
        ]);

        return $this->backWithApiResult(
            $this->api($api)->patch("/kaprodi/procurement-drafts/{$draftId}/items/{$itemId}/review", $payload),
            'Status item berhasil diperbarui.',
        );
    }

    public function finalize(BackendApi $api, int $draftId)
    {
        if ($redirect = $this->ensureRole('kaprodi')) {
            return $redirect;
        }

        return $this->backWithApiResult(
            $this->api($api)->patch("/kaprodi/procurement-drafts/{$draftId}/finalize"),
            'Draf berhasil difinalisasi.',
        );
    }
}
