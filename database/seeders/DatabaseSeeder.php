<?php

namespace Database\Seeders;

use App\Models\CredentialRequest;
use App\Models\Employer;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::factory()->admin()->create([
            'name' => 'Portal Admin', 'email' => 'admin@example.com',
        ]);

        $employerUser = User::factory()->create([
            'name' => 'Acme Recruiter', 'email' => 'employer@example.com',
        ]);
        $employer = Employer::factory()->create([
            'user_id' => $employerUser->id, 'company_name' => 'Acme Corp',
        ]);

        CredentialRequest::factory()->count(3)->create(['employer_id' => $employer->id]);
    }
}
