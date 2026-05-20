<?php

namespace App\Actions\Fortify;

use App\Actions\Teams\CreateTeam;
use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    public function __construct(private CreateTeam $createTeam)
    {
        //
    }

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            ...$this->profileRules(),
            'password' => $this->passwordRules(),
            'shop_name' => ['required', 'string', 'max:255'],
            'subdomain' => ['required', 'string', 'alpha_dash', 'max:50', 'unique:tenants,subdomain'],
        ])->validate();

        return DB::transaction(function () use ($input) {
            $tenant = Tenant::create([
                'name' => $input['shop_name'],
                'subdomain' => strtolower($input['subdomain']),
                'status' => 'trial',
                'trial_ends_at' => now()->addDays(14),
            ]);

            // Bind the active tenant into the container so subsequent creations inherit tenant_id
            app()->instance('tenant', $tenant);

            $user = User::create([
                'tenant_id' => $tenant->id,
                'name' => $input['name'],
                'email' => $input['email'],
                'password' => $input['password'],
            ]);

            $this->createTeam->handle($user, 'Main Branch', isPersonal: true);

            return $user;
        });
    }
}
