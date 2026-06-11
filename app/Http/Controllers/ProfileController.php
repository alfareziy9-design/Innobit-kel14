<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\ArticleCollection;
use App\Models\ArticleView;
use App\Models\Favorite;
use App\Models\QuizAttempt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Throwable;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user()->load('roleModel');
        $learningStats = [
            'articles_read' => ArticleView::query()
                ->where('user_id', $user->id)
                ->distinct('article_id')
                ->count('article_id'),
            'favorites' => Favorite::where('user_id', $user->id)->count(),
            'collections' => ArticleCollection::where('user_id', $user->id)->count(),
            'quiz_attempts' => QuizAttempt::where('user_id', $user->id)->count(),
        ];
        $articleStats = null;

        if ($user->canWriteArticles()) {
            $counts = Article::query()
                ->where('author_id', $user->id)
                ->selectRaw('status, count(*) as aggregate')
                ->groupBy('status')
                ->pluck('aggregate', 'status');

            $articleStats = [
                'total' => $counts->sum(),
                'draft' => (int) ($counts['draft'] ?? 0),
                'review' => (int) ($counts['review'] ?? 0),
                'published' => (int) ($counts['published'] ?? 0),
                'rejected' => (int) ($counts['rejected'] ?? 0),
            ];
        }

        return view('profile.show', compact('user', 'learningStats', 'articleStats'));
    }

    public function update(Request $request)
    {
        $user = $request->user();
        $emailChanged = $request->input('email') !== $user->email;
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:100', Rule::unique('users', 'email')->ignore($user->id)],
            'current_password' => ['nullable', Rule::requiredIf($emailChanged), 'current_password'],
        ], [
            'name.required' => 'Nama wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah digunakan oleh akun lain.',
            'current_password.required' => 'Password saat ini wajib diisi untuk mengubah email.',
            'current_password.current_password' => 'Password saat ini tidak sesuai.',
        ]);

        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
        ]);

        return back()->with('success', 'Informasi profil berhasil diperbarui.');
    }

    public function updatePhoto(Request $request)
    {
        $data = $request->validate([
            'photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'photo.required' => 'Foto profil wajib dipilih.',
            'photo.image' => 'File harus berupa gambar.',
            'photo.mimes' => 'Foto profil harus berupa JPG, JPEG, PNG, atau WEBP.',
            'photo.max' => 'Ukuran foto profil maksimal 2MB.',
        ]);

        $user = $request->user();
        $oldPhoto = $user->photo;
        $newPhoto = $data['photo']->store('profile-photos', 'public');

        try {
            DB::transaction(fn () => $user->update(['photo' => $newPhoto]));
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($newPhoto);

            throw $exception;
        }

        if ($oldPhoto) {
            Storage::disk('public')->delete($oldPhoto);
        }

        return back()->with('success', 'Foto profil berhasil diperbarui.');
    }

    public function destroyPhoto(Request $request)
    {
        $user = $request->user();
        $oldPhoto = $user->photo;

        DB::transaction(fn () => $user->update(['photo' => null]));

        if ($oldPhoto) {
            Storage::disk('public')->delete($oldPhoto);
        }

        return back()->with('success', 'Foto profil berhasil dihapus.');
    }

    public function updatePassword(Request $request)
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'current_password.required' => 'Password saat ini wajib diisi.',
            'current_password.current_password' => 'Password saat ini tidak sesuai.',
            'password.required' => 'Password baru wajib diisi.',
            'password.min' => 'Password baru minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password baru tidak cocok.',
        ]);

        $request->user()->update([
            'password' => Hash::make($data['password']),
        ]);

        return back()->with('success', 'Password berhasil diperbarui.');
    }
}
