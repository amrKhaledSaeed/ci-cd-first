<?php

declare(strict_types=1);

namespace App\Http\Controllers\Store;

use App\Data\Product\ProductListData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Store\ProductIndexRequest;
use App\Models\Product;
use App\Services\Store\StorefrontService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(
        ProductIndexRequest $request,
        StorefrontService $storefrontService,
    ): View {
        /** @var array{search?: mixed, category_id?: mixed, sort?: mixed, per_page?: mixed} $validated */
        $validated = $request->validated();
        $listingData = ProductListData::fromArray($validated);
        $resolvedUser = auth()->check() ? user() : null;

        return view('store.products.index', $storefrontService->listing($listingData, $resolvedUser));
    }

    public function show(
        Product $product,
        StorefrontService $storefrontService,
    ): View|RedirectResponse {
        try {
            $resolvedUser = auth()->check() ? user() : null;
            $detail = $storefrontService->productDetail($product->slug, $resolvedUser);
        } catch (ModelNotFoundException) {
            return redirect()
                ->route('products.index')
                ->withErrors([
                    'product' => __('The selected product is unavailable.'),
                ]);
        }

        return view('store.products.show', $detail);
    }
}
