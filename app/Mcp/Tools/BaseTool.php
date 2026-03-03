<?php

namespace App\Mcp\Tools;

use App\Models\Account;
use App\Models\Contact;
use App\Models\Gender;
use Illuminate\Support\Facades\Auth;
use Laravel\Mcp\Server\Tool;

abstract class BaseTool extends Tool
{

    protected ?Contact $contact = null;

    protected function getGenderByName(?string $name): ?Gender
    {
        if ($name === null) return null;
        return Account::find(Auth::user()->account_id)->genders
            ->firstWhere('name', $name);
    }

    protected function buildData(array $data): array
    {

        $data = array_merge([
            'account_id' => Auth::user()->account_id,
            'author_id' => Auth::id(),
            'vault_id' => config('mcp.vault_id'),
        ], $data);

        if ($this->contact) {
            $data['contact_id'] = $this->contact->id;
        }

        return $data;
    }



}
