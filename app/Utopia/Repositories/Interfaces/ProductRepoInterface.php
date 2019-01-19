<?php
namespace App\Utopia\Repositories\Interfaces;

use App\Http\Requests\ProductStoreRequest;
use App\Http\Requests\ProductUpdateRequest;
use App\Product;

interface ProductRepoInterface
{
    public function create(ProductStoreRequest $request);

    public function update(ProductUpdateRequest $request, Product $product);

    public function delete(Product $product);
}