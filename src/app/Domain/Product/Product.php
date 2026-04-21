<?php

namespace App\Domain\Product;

use App\Domain\Product\Events\ProductDeactivated;
use App\Domain\Product\Exceptions\VariantNotFoundException;
use Ramsey\Uuid\UuidInterface;

class Product
{
    /** @var ProductVariant[] */
    private array $variants = [];

    /** @var object[] */
    private array $domainEvents = [];

    public function __construct(
        private UuidInterface $id,
        private string $name,
        private ProductType $type,
        private ?string $description = null,
        private bool $active = true,
    ) {}

    public static function create(
        UuidInterface $id,
        string $name,
        ProductType $type,
        ?string $description = null,
    ): self {
        return new self($id, $name, $type, $description);
    }

    public static function reconstitute(
        UuidInterface $id,
        string $name,
        ProductType $type,
        ?string $description,
        bool $active,
    ): self {
        return new self($id, $name, $type, $description, $active);
    }

    public function update(string $name, ProductType $type, ?string $description): void
    {
        $this->name        = $name;
        $this->type        = $type;
        $this->description = $description;
    }

    public function deactivate(): void
    {
        $this->active = false;
        $this->domainEvents[] = new ProductDeactivated($this->id);
    }

    public function addVariant(ProductVariant $variant): void
    {
        $this->variants[] = $variant;
    }

    public function removeVariant(UuidInterface $variantId): void
    {
        foreach ($this->variants as $variant) {
            if ($variant->getId()->equals($variantId)) {
                $variant->deactivate();
                return;
            }
        }

        throw new VariantNotFoundException($variantId->toString());
    }

    public function getId(): UuidInterface    { return $this->id; }
    public function getName(): string         { return $this->name; }
    public function getType(): ProductType    { return $this->type; }
    public function getDescription(): ?string { return $this->description; }
    public function isActive(): bool          { return $this->active; }

    /** @return ProductVariant[] */
    public function getVariants(): array      { return $this->variants; }

    /** @return object[] */
    public function pullDomainEvents(): array
    {
        $events = $this->domainEvents;
        $this->domainEvents = [];
        return $events;
    }
}
