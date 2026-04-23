<?php

namespace App\Providers;

use App\Application\Stock\GenerateStockReport\StockReportRepositoryPort;
use App\Domain\Product\Ports\ProductRepositoryPort;
use App\Domain\Product\Ports\ProductVariantRepositoryPort;
use App\Domain\Shared\Ports\ClockPort;
use App\Domain\Shared\Ports\EventDispatcherPort;
use App\Domain\Shared\Ports\IdGeneratorPort;
use App\Domain\Shared\Ports\NotificationPort;
use App\Domain\Shared\Ports\PasswordHasherPort;
use App\Domain\Shared\Ports\TransactionPort;
use App\Domain\Stock\Ports\StockBalanceRepositoryPort;
use App\Domain\Stock\Ports\StockMovementRepositoryPort;
use App\Domain\User\Ports\UserRepositoryPort;
use App\Domain\User\Ports\UserTokenPort;
use App\Infrastructure\Auth\BcryptPasswordHasher;
use App\Infrastructure\Auth\SanctumTokenAdapter;
use App\Infrastructure\Events\LaravelEventDispatcherAdapter;
use App\Infrastructure\Identity\UuidV4Generator;
use App\Infrastructure\Notification\LogNotificationAdapter;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentProductRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentProductVariantRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentStockBalanceRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentStockMovementRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentStockReportRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentUserRepository;
use App\Infrastructure\Time\SystemClock;
use App\Infrastructure\Transaction\LaravelTransactionAdapter;
use Illuminate\Support\ServiceProvider;

class DomainServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ClockPort::class, SystemClock::class);
        $this->app->bind(IdGeneratorPort::class, UuidV4Generator::class);
        $this->app->bind(NotificationPort::class, LogNotificationAdapter::class);
        $this->app->bind(EventDispatcherPort::class, LaravelEventDispatcherAdapter::class);
        $this->app->bind(TransactionPort::class, LaravelTransactionAdapter::class);

        $this->app->bind(ProductRepositoryPort::class, EloquentProductRepository::class);
        $this->app->bind(ProductVariantRepositoryPort::class, EloquentProductVariantRepository::class);
        $this->app->bind(StockMovementRepositoryPort::class, EloquentStockMovementRepository::class);
        $this->app->bind(StockBalanceRepositoryPort::class, EloquentStockBalanceRepository::class);
        $this->app->bind(StockReportRepositoryPort::class, EloquentStockReportRepository::class);

        $this->app->bind(UserRepositoryPort::class, EloquentUserRepository::class);
        $this->app->bind(UserTokenPort::class, SanctumTokenAdapter::class);
        $this->app->bind(PasswordHasherPort::class, BcryptPasswordHasher::class);
    }
}
