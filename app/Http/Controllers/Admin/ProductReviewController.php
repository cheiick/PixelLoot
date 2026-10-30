<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductReview;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductReviewController extends Controller
{
    public function index(): View
    {
        return view('admin.reviews.index', [
            'reviews' => ProductReview::with(['user', 'product'])->latest()->paginate(20),
        ]);
    }

    public function update(Request $request, ProductReview $review): RedirectResponse
    {
        $validated = $request->validate(['status' => ['required', 'in:approved,rejected']]);
        $review->update(['status' => $validated['status']]);

        return back()->with('success', 'Le statut de l’avis a été mis à jour.');
    }

    public function destroy(ProductReview $review): RedirectResponse
    {
        $review->delete();

        return back()->with('success', 'L’avis a été supprimé.');
    }
}
