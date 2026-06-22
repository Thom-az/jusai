<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDocumentRequest;
use App\Jobs\ProcessAiReview;
use App\Models\AiReview;
use App\Models\Document;
use App\Models\LegalCase;
use App\Services\SupabaseStorageService;
use App\Traits\OrganizationScoped;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DocumentController extends Controller
{
    use OrganizationScoped;

    public function __construct(private readonly SupabaseStorageService $storage) {}

    public function index(Request $request): View
    {
        $query = $this->scopedQuery(Document::class)->with(['legalCase']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->whereRaw('lower(title) like ?', ['%' . mb_strtolower($term) . '%'])
                  ->orWhereRaw('lower(original_filename) like ?', ['%' . mb_strtolower($term) . '%']);
            });
        }

        $documents = $query->orderByDesc('updated_at')->paginate(20)->withQueryString();

        return view('documentos.index', compact('documents'));
    }

    public function create(Request $request): View
    {
        $cases = $this->scopedQuery(LegalCase::class)
            ->whereNotIn('status', ['encerrado', 'arquivado'])
            ->orderBy('title')
            ->get();

        $selectedCaseId = $request->query('case_id');

        return view('documentos.create', compact('cases', 'selectedCaseId'));
    }

    public function store(StoreDocumentRequest $request): JsonResponse|RedirectResponse
    {
        $file     = $request->file('file');
        $binary   = file_get_contents($file->getRealPath());
        $mimeType = $file->getMimeType();
        $orgId    = $this->orgId();
        $caseId   = $request->input('legal_case_id');

        if ($caseId) {
            $this->scopedQuery(LegalCase::class)->findOrFail($caseId);
        }

        $uuid    = (string) Str::uuid();
        $slug    = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        $ext     = $file->getClientOriginalExtension();
        $segment = $caseId ?? 'standalone';
        $path    = "{$orgId}/{$segment}/{$uuid}-{$slug}.{$ext}";

        $this->storage->upload('case-documents', $path, $binary, $mimeType);

        $document = Document::create([
            'organization_id'   => $orgId,
            'legal_case_id'     => $caseId,
            'title'             => $request->input('title'),
            'original_filename' => $file->getClientOriginalName(),
            'storage_path'      => $path,
            'file_size'         => $file->getSize(),
            'mime_type'         => $mimeType,
            'status'            => 'processing',
            'uploaded_by'       => auth()->id(),
        ]);

        if ($caseId && str_contains($mimeType, 'pdf')) {
            $aiReview = AiReview::create([
                'organization_id'       => $orgId,
                'legal_case_id'         => $caseId,
                'document_id'           => $document->id,
                'type'                  => 'resumo_caso',
                'result'                => '',
                'status'                => 'processando',
                'requires_human_review' => true,
                'created_by'            => auth()->id(),
            ]);

            ProcessAiReview::dispatch($aiReview);
        }

        $this->logActivity('documento_enviado', "Documento \"{$document->title}\" enviado.", Document::class, $document->id);

        $redirectUrl = $caseId
            ? route('cases.show', $caseId) . '#documentos'
            : route('documents.show', $document);

        if ($request->expectsJson()) {
            session()->flash('success', 'Documento enviado com sucesso.');
            return response()->json(['redirect' => $redirectUrl]);
        }

        return redirect($redirectUrl)->with('success', 'Documento enviado com sucesso.');
    }

    public function edit(string $id): View
    {
        $document = $this->scopedQuery(Document::class)->with('legalCase')->findOrFail($id);

        $cases = $this->scopedQuery(LegalCase::class)
            ->whereNotIn('status', ['encerrado', 'arquivado'])
            ->orderBy('title')
            ->get();

        return view('documentos.edit', compact('document', 'cases'));
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $document = $this->scopedQuery(Document::class)->findOrFail($id);

        $data = $request->validate([
            'title'         => ['required', 'string', 'max:255'],
            'legal_case_id' => ['nullable', 'uuid'],
        ]);

        if ($data['legal_case_id']) {
            $this->scopedQuery(LegalCase::class)->findOrFail($data['legal_case_id']);
        }

        $document->update($data);

        $this->logActivity('documento_atualizado', "Documento \"{$document->title}\" atualizado.", Document::class, $document->id);

        return redirect()->route('documents.show', $document)->with('success', 'Documento atualizado.');
    }

    public function show(string $id): View
    {
        $document    = $this->scopedQuery(Document::class)->with(['legalCase', 'aiReviews'])->findOrFail($id);
        $downloadUrl = null;

        try {
            $downloadUrl = $this->storage->getSignedUrl('case-documents', $document->storage_path);
        } catch (\Throwable) {
            // signed URL not critical — document may still be processing
        }

        return view('documentos.show', compact('document', 'downloadUrl'));
    }

    public function status(string $id): JsonResponse
    {
        $document = $this->scopedQuery(Document::class)->findOrFail($id);

        return response()->json([
            'ready'  => $document->status !== 'processing',
            'status' => $document->status,
        ]);
    }

    public function previewUrl(string $id): JsonResponse
    {
        $document = $this->scopedQuery(Document::class)->findOrFail($id);

        try {
            $url = $this->storage->getSignedUrl('case-documents', $document->storage_path, 1800);
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Não foi possível gerar a URL de visualização.'], 422);
        }

        return response()->json([
            'url'      => $url,
            'mime'     => $document->mime_type,
            'filename' => $document->original_filename,
            'title'    => $document->title,
        ]);
    }

    public function destroy(string $id): RedirectResponse
    {
        $document = $this->scopedQuery(Document::class)->findOrFail($id);

        try {
            $this->storage->delete('case-documents', $document->storage_path);
        } catch (\Throwable) {
            // best-effort; proceed with DB delete even if storage fails
        }

        $caseId = $document->legal_case_id;
        $title  = $document->title;
        $document->delete();

        $this->logActivity('documento_excluido', "Documento \"{$title}\" excluído.", Document::class, $id);

        if ($caseId) {
            return redirect()->route('cases.show', $caseId)->with('deleted', 'Documento excluído.');
        }

        return redirect()->route('documents.index')->with('deleted', 'Documento excluído.');
    }
}
