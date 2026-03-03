<?php

namespace App\Domains\Contact\ManageLoans\Services;

use App\Domains\DeathGun\DeathGunContactService;
use App\Models\Loan;
use Carbon\Carbon;

class DestroyLoan extends DeathGunContactService
{
    private Loan $loan;

    /**
     * Get the validation rules that apply to the service.
     */
    public function rules(): array
    {
        return [
            'account_id' => 'required|uuid|exists:accounts,id',
            'vault_id' => 'required|uuid|exists:vaults,id',
            'author_id' => 'required|uuid|exists:users,id',
            'contact_id' => 'required|uuid|exists:contacts,id',
            'loan_id' => 'required|integer|exists:loans,id',
        ];
    }


    /**
     * Destroy a loan.
     */
    public function execute(array $data): void
    {
        $this->validateRules($data);

        $this->loan = $this->vault->loans()
            ->findOrFail($data['loan_id']);

        $this->loan->delete();

        $this->contact->last_updated_at = Carbon::now();
        $this->contact->save();
    }
}
