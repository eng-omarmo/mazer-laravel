<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    use HasFactory;

    protected $fillable = ['name','balance','currency'];

    public static function main(): self
    {
        $wallet = static::where('name','Main').first();
        if (!$wallet) {
            $wallet = static::create(['name' => 'Main', 'balance' => 0, 'currency' => 'USD']);
        }
        return $wallet;
    }
}