<?php

namespace App\Http\Controllers;

use App\Models\LegalCase;
use App\Traits\OrganizationScoped;
use Illuminate\View\View;

class ChatController extends Controller
{
    use OrganizationScoped;

    public function show(string $caso): View
    {
        $caso = $this->scopedQuery(LegalCase::class)->findOrFail($caso);

        return view('casos.chat', compact('caso'));
    }
}
