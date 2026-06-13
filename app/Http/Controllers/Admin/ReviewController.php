<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        $query = Review::with(['user', 'barber', 'appointment']);

        if ($request->filled('rating')) {
            $query->where('rating', $request->rating);
        }

        if ($request->filled('barber_id')) {
            $query->where('barber_id', $request->barber_id);
        }

        $reviews = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();
        
        $barbers = \App\Models\Barber::all();

        return view('reviews.index', compact('reviews', 'barbers'));
    }

    public function destroy(Review $review)
    {
        $review->delete();

        return redirect()->route('admin.reviews.index')->with('success', 'Xóa đánh giá thành công!');
    }
}
