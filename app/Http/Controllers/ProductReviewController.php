<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProductReviewController extends Controller
{
    public function store(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['required', 'string', 'min:10', 'max:3000'],
        ]);

        $hasPurchased = Order::where('user_id', $request->user()->id)
            ->whereIn('status', ['paid', 'shipped', 'delivered'])
            ->whereHas('items', fn ($query) => $query->where('product_id', $product->id))
            ->exists();

        if (! $hasPurchased) {
            throw ValidationException::withMessages([
                'review' => 'Vous devez avoir acheté ce produit avant de pouvoir laisser un avis.',
            ]);
        }

        $review = $product->reviews()->where('user_id', $request->user()->id)->first();
        if ($review && $review->status !== 'rejected') {
            throw ValidationException::withMessages([
                'review' => 'Vous avez déjà envoyé un avis pour ce produit.',
            ]);
        }

        DB::transaction(function () use ($product, $request, $validated, $review): void {
            if ($review) {
                $review->update([
                    'rating' => $validated['rating'],
                    'comment' => $validated['comment'],
                    'status' => 'pending',
                ]);
                return;
            }

            $product->reviews()->create([
                'user_id' => $request->user()->id,
                'rating' => $validated['rating'],
                'comment' => $validated['comment'],
                'status' => 'pending',
            ]);
        });

        return back()->with('review_success', 'Merci ! Votre avis sera publié après vérification.');
    }
}
