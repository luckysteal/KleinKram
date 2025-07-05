<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class HashAdminPassword extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hash:admin-password {password}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Hashes a password for the ADMIN_PASSWORD .env variable.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Hashed password:');
        $this->line(Hash::make($this->argument('password')));
    }
}
