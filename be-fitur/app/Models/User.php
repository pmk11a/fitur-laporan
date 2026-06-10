<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
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

    public function preferences()
    {
        return $this->hasMany(UserPreference::class);
    }

    public function getPreference(string $namespace, string $key, $default = null)
    {
        $pref = $this->preferences()
            ->where('namespace', $namespace)
            ->where('key', $key)
            ->first();

        return $pref ? $pref->value : $default;
    }

    public function setPreference(string $namespace, string $key, $value): UserPreference
    {
        return $this->preferences()->updateOrCreate(
            ['namespace' => $namespace, 'key' => $key],
            ['value' => $value]
        );
    }
}
