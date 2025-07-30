<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'address',
        'avatar',
        'balance',
        'user_id',
        'branch_id',
        'company_id',
    ];

    protected $appends = ['avatar_url'];

    protected static function booted()
    {
        static::addGlobalScope('branch', function (Builder $builder) {
            $user = Auth::user();
            if (!$user) {
                return; // Don't apply scope if no user is authenticated
            }
            $company_id = $user->company_id;
            $branch_id = $user->branch_id;
            $role = $user->role;
            if ($role == "admin") {
                $builder->where('company_id', $company_id);
            } else {
                $builder->where('company_id', $company_id)->where('branch_id', $branch_id);
            }
        });
    }

    public function getAvatarUrl()
    {
        if ($this->avatar) {
            return Storage::url($this->avatar);
        }
        return asset('images/img-placeholder.jpg');
    }

    public function getAvatarUrlAttribute()
    {
        return $this->getAvatarUrl();
    }

    // Define relationships here (e.g., with the Product model)
}
