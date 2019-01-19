<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductStoreRequest;
use App\Http\Requests\ProductUpdateRequest;
use App\Image;
use App\Product;
use App\Http\Resources\Product as ProductResource;
use App\Http\Resources\ProductCollection;
use App\Utopia\Repositories\Interfaces\ProductRepoInterface;

class ProductController extends Controller
{
    protected $productRepo;

    public function __construct(ProductRepoInterface $productRepo)
    {
        $this->productRepo = $productRepo;
    }

    public function index()
    {
        return new ProductCollection(Product::paginate());
    }

    public function store(ProductStoreRequest $request)
    {
        $product = $this->productRepo->create($request);

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

        $product = $this->productRepo->update($request, $product);

        return response()->json(new ProductResource($product));
    }

    public function destroy(int $id)
    {
        $product = Product::findOrFail($id);

        $this->productRepo->delete($product);

        return response()->json(null, 204);
    }
}
