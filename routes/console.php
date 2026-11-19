<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Create (or promote) a super admin interactively; the password is never echoed or logged.
Artisan::command('app:create-admin', function () {
    $name = $this->ask('Nama');
    $email = strtolower(trim((string) $this->ask('Email login')));
    if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return $this->error('Email tidak valid.') ?? 1;
    }
    $password = (string) $this->secret('Password (min. 10 karakter, tidak ditampilkan)');
    if (mb_strlen($password) < 10 || $password !== (string) $this->secret('Ulangi password')) {
        return $this->error('Password terlalu pendek atau tidak sama.') ?? 1;
    }
    \App\Models\User::updateOrCreate(['email' => $email], [
        'name' => $name ?: 'Administrator',
        'password' => $password,
        'role' => \App\Models\User::ROLE_SUPER_ADMIN,
        'is_active' => true,
    ]);
    $this->info("Super Admin {$email} siap. Masuk di ".url('/admin'));
})->purpose('Buat atau perbarui akun Super Admin secara interaktif');

// Store a secret (e.g. DB_PASSWORD, MAIL_PASSWORD) in .env without it appearing on screen or in shell history.
Artisan::command('app:env-secret {key}', function (string $key) {
    $key = strtoupper($key);
    if (! preg_match('/^[A-Z][A-Z0-9_]*$/', $key)) {
        return $this->error('Nama kunci tidak valid.') ?? 1;
    }
    $value = (string) $this->secret("Nilai {$key} (tidak ditampilkan)");
    $path = base_path('.env');
    $env = (string) @file_get_contents($path);
    $line = $key.'="'.addcslashes($value, '"\\$').'"';
    $env = preg_match("/^{$key}=.*$/m", $env)
        ? preg_replace("/^{$key}=.*$/m", str_replace(['\\', '$'], ['\\\\', '\\$'], $line), $env)
        : rtrim($env)."\n".$line."\n";
    file_put_contents($path, $env);
    $this->info("{$key} disimpan di .env.");
})->purpose('Simpan nilai rahasia ke .env tanpa ditampilkan');
