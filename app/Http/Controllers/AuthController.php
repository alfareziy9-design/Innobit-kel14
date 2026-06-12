<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Password wajib diisi.',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            if (! Auth::user()->isActive()) {
                Auth::logout();

                return back()
                    ->withErrors(['email' => 'Akun Anda sedang ditangguhkan.'])
                    ->onlyInput('email');
            }

            $request->session()->regenerate();

            $dashboard = match (true) {
                Auth::user()->isAdmin() => route('admin.dashboard'),
                Auth::user()->isAuthor() => route('author.dashboard'),
                default => route('home'),
            };

            return redirect()->intended($dashboard);
        }

        return back()
            ->withErrors(['email' => 'Email atau password salah.'])
            ->onlyInput('email');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'role' => ['required', Rule::in(['user', 'author'])],
            'email' => [
                'required',
                'email',
                'max:100',
                'unique:users,email',
                function (string $attribute, mixed $value, \Closure $fail) use ($request): void {
                    if ($request->input('role') !== 'author') {
                        return;
                    }

                    $authorEmailWarning = 'Penulis dikhususkan untuk Mahasiswa Sistem Informasi UPN "Veteran" Jawa Timur';

                    if (! preg_match('/^(\d{11})@student\.upnjatim\.ac\.id$/i', (string) $value, $matches)) {
                        $fail($authorEmailWarning);

                        return;
                    }

                    $npm = (int) $matches[1];

                    if ($npm < 20082010001 || $npm > 26082010999) {
                        $fail($authorEmailWarning);
                    }
                },
            ],
            'password' => ['required', 'min:6', 'confirmed'],
        ], [
            'name.required' => 'Nama wajib diisi.',
            'role.required' => 'Jenis akun wajib dipilih.',
            'role.in' => 'Jenis akun tidak valid.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        $role = Role::where('name', $data['role'])->firstOrFail();

        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'photo' => null,
            'role' => $role->name,
            'role_id' => $role->id,
        ]);

        return back()->with('success', 'Registrasi berhasil. Silakan login.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
