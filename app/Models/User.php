<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;


/**
 * @OA\Schema(
 *   schema="User",
 *   title="User",
 *   description="User model",
 *   @OA\Property(
 *     property="id",
 *     description="User id",
 *     format="uuid",
 *     example="123e4567-e89b-12d3-a456-426614174000"
 *   ),
 *   @OA\Property(
 *     property="name",
 *     description="User name",
 *     example="John Doe"
 *   ),
 *   @OA\Property(
 *     property="email",
 *     description="User email",
 *     example="john.doe@email.com"
 *   ),
 *   @OA\Property(
 *     property="old_email",
 *     description="User old email. Used when updating the email address to store the old email address, in case the user cannot verify the new email address.",
 *     example="john.doe@oldmail.com"
 *   ),
 *   @OA\Property(
 *     property="email_verified_at",
 *     description="User email verified at",
 *     example="2021-01-01 00:00:00"
 *   ),
 *   @OA\Property(
 *     property="password",
 *     description="The password hash",
 *     example="$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi"
 *   ),
 *   @OA\Property(
 *     property="remember_token",
 *     description="The remember token",
 *     example="NzD2RB0pM9"
 *   ),
 *   @OA\Property(
 *     property="created_at",
 *     description="The created at date",
 *     example="2021-01-01 00:00:00"
 *   ),
 *   @OA\Property(
 *     property="updated_at",
 *     description="The updated at date",
 *     example="2021-01-01 00:00:00"
 *   )
 * )
 */
class User extends Authenticatable implements JWTSubject, MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
     * Returns the roles that belong to the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class)->withTimestamps();
    }

    /**
     * Checks if the user has the given role.
     *
     * @param string $role The name of the role to check for.
     * @return bool
     */
    public function hasRole(string $role): bool
    {
        return $this->roles()->where('name', $role)->exists();
    }

    /**
     * Checks if the user has the admin role.
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    /**
     * Checks if the user has the user role.
     *
     * @return bool
     */
    public function isUser(): bool
    {
        return $this->hasRole('user');
    }

    /**
     * Returns the tasks that belong to the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'assigned_to');
    }

    /**
     * Returns the tasks that were created by the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tasksCreated(): HasMany
    {
        return $this->hasMany(Task::class, 'created_by');
    }
}
