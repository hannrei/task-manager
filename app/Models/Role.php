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
 *   @OA\Property(
 *     property="id",
 *     title="id",
 *     description="Role id",
 *     format="uuid",
 *     example="123e4567-e89b-12d3-a456-426614174000"
 *   ),
 *   @OA\Property(
 *     property="name",
 *     title="name",
 *     description="Role name",
 *     type="string",
 *     example="admin"
 *   ),
 *   @OA\Property(
 *     property="created_at",
 *     title="created_at",
 *     description="Role created at",
 *     example="2021-01-01 00:00:00"
 *   ),
 *   @OA\Property(
 *     property="updated_at",
 *     title="updated_at",
 *     description="Role updated at",
 *     example="2021-01-01 00:00:00"
 *   )
 * )
 */

class Role extends Model
{
    use HasFactory, HasUuids;


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
