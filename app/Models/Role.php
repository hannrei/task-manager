<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @OA\Schema(
 *   schema="Role",
 *   title="Role",
 *   description="The role model provides authorization to users.",
 * )
 */
class Role extends Model
{
    use HasFactory, HasUuids;

    /**
     * @OA\Property(
     *    title="id",
     *    description="Role id",
     *    format="uuid",
     *    example="123e4567-e89b-12d3-a456-426614174000"
     * )
     */
    private $id;

    /**
     * @OA\Property(
     *    title="name",
     *    description="Role name",
     *    type="string",
     *    example="admin"
     * )
     */
    private $name;

    /**
     * @OA\Property(
     *    title="created_at",
     *    description="Role created at",
     *    example="2021-01-01 00:00:00"
     * )
     */
    private $created_at;

    /**
     * @OA\Property(
     *    title="updated_at",
     *    description="Role updated at",
     *    example="2021-01-01 00:00:00"
     * )
     */
    private $updated_at;

    /**
     * Many-to-many relationship with users.
     *
     * @return BelongsToMany
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }
}
