<?php

namespace App\Domains\Contact\ManageCountry\Web\Controllers;

use App\Domains\Contact\ManageCountry\Services\UpdateCountry;
use App\Domains\Contact\ManageCountry\Web\ViewHelpers\ModuleCountryViewHelper;
use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ContactModuleCountryController extends Controller
{
    public function update(Request $request, string $vaultId, string $contactId): JsonResponse
    {
        (new UpdateCountry)->execute([
            'account_id' => Auth::user()->account_id,
            'author_id' => Auth::user()->id,
            'vault_id' => $vaultId,
            'contact_id' => $contactId,
            'country' => $request->input('country'),
        ]);

        $contact = Contact::findOrFail($contactId);

        return response()->json([
            'data' => ModuleCountryViewHelper::data($contact),
        ], 200);
    }
}
