<?php

namespace Tests\Feature;

use App\Application\Stock\CancelMovement\CancelMovementUseCase;
use App\Application\Stock\CheckMinimumStock\CheckMinimumStockHandler;
use App\Infrastructure\Events\Listeners\QueuedStockAlertListener;
use App\Application\Stock\RecordEntry\RecordEntryDTO;
use App\Application\Stock\RecordEntry\RecordEntryUseCase;
use App\Application\Stock\RecordExit\RecordExitDTO;
use App\Application\Stock\RecordExit\RecordExitUseCase;
use App\Domain\Shared\Ports\NotificationPort;
use App\Domain\Stock\Events\StockBelowMinimumDetected;
use App\Infrastructure\Persistence\Eloquent\Models\ProductModel;
use App\Infrastructure\Persistence\Eloquent\Models\ProductVariantModel;
use App\Infrastructure\Persistence\Eloquent\Models\StockMovementModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class StockAlertTest extends TestCase
{
    use RefreshDatabase;

    private string $variantId;

    protected function setUp(): void
    {
        parent::setUp();

        $product = ProductModel::create([
            'id'   => \Ramsey\Uuid\Uuid::uuid4()->toString(),
            'name' => 'Produto Teste',
            'type' => 'PRODUTO_FINAL',
        ]);

        $this->variantId = \Ramsey\Uuid\Uuid::uuid4()->toString();

        ProductVariantModel::create([
            'id'            => $this->variantId,
            'product_id'    => $product->id,
            'sku'           => 'SKU-001',
            'unit'          => 'UN',
            'minimum_stock' => 10,
        ]);
    }

    public function test_event_dispatched_when_exit_drops_balance_below_minimum(): void
    {
        Event::fake([StockBelowMinimumDetected::class]);

        $this->recordEntry(15);
        $this->recordExit(8);

        Event::assertDispatched(StockBelowMinimumDetected::class, function ($event) {
            return $event->variantId->toString() === $this->variantId
                && $event->currentBalance === 7
                && $event->minimumStock === 10;
        });
    }

    public function test_no_event_when_balance_stays_above_minimum(): void
    {
        Event::fake([StockBelowMinimumDetected::class]);

        $this->recordEntry(20);
        $this->recordExit(5);

        Event::assertNotDispatched(StockBelowMinimumDetected::class);
    }

    public function test_event_dispatched_when_cancelling_entry_drops_balance_below_minimum(): void
    {
        Event::fake([StockBelowMinimumDetected::class]);

        // balance = 15, depois 20; cancelar a primeira (15) → balance = 5 < minimum(10)
        $this->recordEntry(15);
        $this->recordEntry(5);

        $firstEntryId = StockMovementModel::where('variant_id', $this->variantId)
            ->orderBy('created_at')
            ->first()->id;

        $useCase = app(CancelMovementUseCase::class);
        $useCase->execute($firstEntryId, 'cancelamento de teste');

        Event::assertDispatched(StockBelowMinimumDetected::class, function ($event) {
            return $event->currentBalance === 5 && $event->minimumStock === 10;
        });
    }

    public function test_listener_implements_should_queue_and_targets_correct_queue(): void
    {
        $listener = app(QueuedStockAlertListener::class);

        $this->assertInstanceOf(\Illuminate\Contracts\Queue\ShouldQueue::class, $listener);
        $this->assertSame('stock-alerts', $listener->queue);
    }

    public function test_handler_calls_notification_port(): void
    {
        $notifier = $this->createMock(NotificationPort::class);
        $notifier->expects($this->once())
            ->method('sendStockAlert')
            ->with($this->variantId, 7, 10);

        $handler = new CheckMinimumStockHandler($notifier);
        $handler->handle(new StockBelowMinimumDetected(
            variantId:      \Ramsey\Uuid\Uuid::fromString($this->variantId),
            currentBalance: 7,
            minimumStock:   10,
        ));
    }

    private function recordEntry(int $quantity): void
    {
        /** @var RecordEntryUseCase $useCase */
        $useCase = app(RecordEntryUseCase::class);
        $useCase->execute(new RecordEntryDTO(
            variantId: $this->variantId,
            quantity:  $quantity,
            reason:    'setup',
        ));
    }

    private function recordExit(int $quantity): void
    {
        /** @var RecordExitUseCase $useCase */
        $useCase = app(RecordExitUseCase::class);
        $useCase->execute(new RecordExitDTO(
            variantId: $this->variantId,
            quantity:  $quantity,
            reason:    'saída de teste',
        ));
    }
}
