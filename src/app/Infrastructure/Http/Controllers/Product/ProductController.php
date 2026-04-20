<?php

namespace App\Infrastructure\Http\Controllers\Product;

use App\Application\Product\AddProductVariant\AddProductVariantDTO;
use App\Application\Product\AddProductVariant\AddProductVariantUseCase;
use App\Application\Product\DeactivateProduct\DeactivateProductUseCase;
use App\Application\Product\GetProduct\GetProductUseCase;
use App\Application\Product\RegisterProduct\RegisterProductDTO;
use App\Application\Product\RegisterProduct\RegisterProductUseCase;
use App\Application\Product\RegisterProduct\RegisterVariantDTO;
use App\Application\Product\RemoveProductVariant\RemoveProductVariantUseCase;
use App\Application\Product\UpdateProduct\UpdateProductDTO;
use App\Application\Product\UpdateProduct\UpdateProductUseCase;
use App\Infrastructure\Http\Requests\Product\AddVariantRequest;
use App\Infrastructure\Http\Requests\Product\RegisterProductRequest;
use App\Infrastructure\Http\Requests\Product\UpdateProductRequest;
use App\Infrastructure\Http\Resources\Product\ProductResource;
use App\Infrastructure\Http\Resources\Product\ProductVariantResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controller;

class ProductController extends Controller
{
    public function __construct(
        private RegisterProductUseCase      $register,
        private UpdateProductUseCase        $update,
        private DeactivateProductUseCase    $deactivate,
        private GetProductUseCase           $get,
        private AddProductVariantUseCase    $addVariant,
        private RemoveProductVariantUseCase $removeVariant,
    ) {}

    public function index(): AnonymousResourceCollection
    {
        return ProductResource::collection($this->get->list());
    }

    public function show(string $id): ProductResource
    {
        return new ProductResource($this->get->execute($id));
    }

    public function store(RegisterProductRequest $request): JsonResponse
    {
        $variants = array_map(
            fn ($v) => new RegisterVariantDTO(
                sku:          $v['sku'],
                unit:         $v['unit'],
                minimumStock: $v['minimum_stock'],
                color:        $v['color'] ?? null,
                size:         $v['size'] ?? null,
            ),
            $request->input('variants')
        );

        $product = $this->register->execute(new RegisterProductDTO(
            name:        $request->input('name'),
            type:        $request->input('type'),
            variants:    $variants,
            description: $request->input('description'),
        ));

        return (new ProductResource($product))
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdateProductRequest $request, string $id): ProductResource
    {
        $product = $this->update->execute(new UpdateProductDTO(
            id:          $id,
            name:        $request->input('name'),
            type:        $request->input('type'),
            description: $request->input('description'),
        ));

        return new ProductResource($product);
    }

    public function destroy(string $id): JsonResponse
    {
        $this->deactivate->execute($id);

        return response()->json(null, 204);
    }

    public function addVariant(AddVariantRequest $request, string $productId): JsonResponse
    {
        $variant = $this->addVariant->execute(new AddProductVariantDTO(
            productId:    $productId,
            sku:          $request->input('sku'),
            unit:         $request->input('unit'),
            minimumStock: $request->input('minimum_stock'),
            color:        $request->input('color'),
            size:         $request->input('size'),
        ));

        return (new ProductVariantResource($variant))
            ->response()
            ->setStatusCode(201);
    }

    public function removeVariant(string $productId, string $variantId): JsonResponse
    {
        $this->removeVariant->execute($productId, $variantId);

        return response()->json(null, 204);
    }
}
