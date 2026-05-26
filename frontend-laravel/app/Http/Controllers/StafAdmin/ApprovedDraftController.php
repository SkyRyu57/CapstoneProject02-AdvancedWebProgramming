<?php

namespace App\Http\Controllers\StafAdmin;

use App\Http\Controllers\Concerns\InteractsWithBackend;
use App\Http\Controllers\Controller;
use App\Services\BackendApi;
use Illuminate\Http\Request;

class ApprovedDraftController extends Controller
{
    use InteractsWithBackend;

    public function index(BackendApi $api)
    {
        if ($redirect = $this->ensureRole('staf_admin')) {
            return $redirect;
        }

        $response = $this->api($api)->get('/staf-admin/approved-drafts');

        return view('staf-admin.approved-drafts', [
            'user' => session('auth_user'),
            'drafts' => $response->json('drafts', []),
            'receipts' => $response->json('receipts', []),
            'error' => $response->successful() ? null : $response->json('message', 'Draf disetujui belum bisa dimuat.'),
        ]);
    }

    public function storeReceipt(Request $request, BackendApi $api)
    {
        if ($redirect = $this->ensureRole('staf_admin')) {
            return $redirect;
        }

        $payload = $request->validate([
            'draft_item_id' => ['required', 'integer'],
            'received_date' => ['required', 'date'],
            'quantity' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string'],
        ]);

        return $this->backWithApiResult(
            $this->api($api)->post('/staf-admin/receipts', $payload),
            'Penerimaan barang berhasil dicatat.',
        );
    }
}
