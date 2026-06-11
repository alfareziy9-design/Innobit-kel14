<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected static function booted(): void
    {
        static::saving(function (User $user): void {
            if ($user->isDirty('role')) {
                $role = Role::where('name', $user->role)->first();

                if ($role) {
                    $user->role_id = $role->id;
                }

                return;
            }

            if ($user->isDirty('role_id') && $user->role_id) {
                $user->role = Role::find($user->role_id)?->name ?? 'user';
            }
        });
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'photo',
        'role',
        'role_id',
        'account_status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function articles()
    {
        return $this->hasMany(Article::class, 'author_id');
    }

    public function articleReviews()
    {
        return $this->hasMany(ArticleReview::class, 'reviewer_id');
    }

    public function contactMessages()
    {
        return $this->hasMany(ContactMessage::class);
    }

    public function learningCollections()
    {
        return $this->hasMany(LearningCollection::class);
    }

    public function roleModel()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function roleName(): string
    {
        return $this->roleModel?->name ?? 'user';
    }

    public function isAdmin(): bool
    {
        return $this->roleName() === 'admin';
    }

    public function isAuthor(): bool
    {
        return $this->roleName() === 'author';
    }

    public function canWriteArticles(): bool
    {
        return $this->isAdmin() || $this->isAuthor();
    }

    public function isActive(): bool
    {
        return $this->account_status === 'active';
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class, 'actor_id');
    }

    public function getPhotoUrlAttribute(): ?string
    {
        return $this->photo ? Storage::disk('public')->url($this->photo) : null;
    }
}
