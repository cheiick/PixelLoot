<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    /**
     * Les champs assignables en masse.
     */
    protected $fillable = ['name', 'slug', 'description'];

    /**
     * Un produit appartient à une catégorie.
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
