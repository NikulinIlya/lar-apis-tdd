<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductStoreRequest;
use App\Http\Requests\ProductUpdateRequest;
use App\Image;
use App\Product;
use App\Http\Resources\Product as ProductResource;
use App\Http\Resources\ProductCollection;

class ProductController extends Controller
{
    public function index()
    {
        return new ProductCollection(Product::paginate());
    }

    public function store(ProductStoreRequest $request)
    {
        if($request->hasFile('image')) {
            $path = $request->file('image')->store('product_images', 'public');
            $imageId = Image::create([
                'path' => $path
            ])->id;
        } else $imageId = null;

        $product = Product::create([
            'image_id' => $imageId,
            'name' => $request->name,
            'slug' => str_slug($request->name),
            'price' => $request->price
        ]);

        return response()->json(new ProductResource($product), 201);
    }

    public function show(int $id)
    {
        $product = Product::findOrFail($id);

        return response()->json(new ProductResource($product));
    }

    public function update(ProductUpdateRequest $request, int $id)
    {
        $product = Product::findOrFail($id);

        if($request->hasFile('image')) {
            $path = $request->file('image')->store('product_images', 'public');
            $imageId = Image::create([
                'path' => $path
            ])->id;
        } else $imageId = $product->image_id;

        $product->update([
            'image_id' => $imageId,
            'name' => $request->name,
            'slug' => str_slug($request->name),
            'price' => $request->price
        ]);

        return response()->json(new ProductResource($product));
    }

    public function destroy(int $id)
    {
        $product = Product::findOrFail($id);

        $product->delete();

        return response()->json(null, 204);
    }
}
