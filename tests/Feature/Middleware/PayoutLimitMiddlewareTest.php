<?php

declare(strict_types=1);

namespace Tests\Feature\Middleware;

use App\Http\Middleware\CheckPayoutAmount;
use App\Http\Middleware\CheckPayoutDailyLimit;
use App\Models\Payout;
use App\Models\User;
use App\Services\PayoutLimitService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use Tests\Support\SetsUpPayoutLimitSchema;
use Tests\TestCase;

class PayoutLimitMiddlewareTest extends TestCase
{
    use SetsUpPayoutLimitSchema;

    private const PHONE = '03001234567';

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpPayoutLimitSchema();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_amount_middleware_allows_50000(): void
    {
        $response = $this->runAmountMiddleware(['amount' => 50000]);

        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_amount_middleware_rejects_50001(): void
    {
        $response = $this->runAmountMiddleware(['amount' => 50001]);

        $this->assertSame(422, $response->getStatusCode());
        $this->assertSame('error', $response->getData(true)['status']);
        $this->assertStringContainsString('Invalid payout amount', $response->getData(true)['message']);
    }

    public function test_amount_middleware_rejects_below_minimum(): void
    {
        $response = $this->runAmountMiddleware(['amount' => 0]);

        $this->assertSame(422, $response->getStatusCode());
        $this->assertStringContainsString('Invalid payout amount', $response->getData(true)['message']);
    }

    public function test_daily_limit_allows_exact_remaining_10000(): void
    {
        $user = $this->makeUser();
        $this->insertPayout($user->id, 90000, Payout::STATUS_SUCCESS);

        $response = $this->runDailyLimitMiddleware([
            'client_email' => $user->email,
            'phone' => self::PHONE,
            'amount' => 10000,
        ]);

        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_daily_limit_rejects_when_request_exceeds_remaining(): void
    {
        $user = $this->makeUser();
        $this->insertPayout($user->id, 90000, Payout::STATUS_SUCCESS);

        $response = $this->runDailyLimitMiddleware([
            'client_email' => $user->email,
            'phone' => self::PHONE,
            'amount' => 10001,
        ]);

        $this->assertSame(429, $response->getStatusCode());
        $this->assertSame('Daily payout limit exceeded.', $response->getData(true)['message']);
    }

    public function test_daily_limit_rejects_50000_when_only_10000_remaining(): void
    {
        $user = $this->makeUser();
        $this->insertPayout($user->id, 90000, Payout::STATUS_SUCCESS);

        $response = $this->runDailyLimitMiddleware([
            'client_email' => $user->email,
            'phone' => self::PHONE,
            'amount' => 50000,
        ]);

        $this->assertSame(429, $response->getStatusCode());
        $this->assertSame('Daily payout limit exceeded.', $response->getData(true)['message']);
    }

    public function test_daily_limit_middleware_returns_429_when_limit_reached(): void
    {
        $user = $this->makeUser();
        $this->insertPayout($user->id, 100000, Payout::STATUS_SUCCESS);

        $response = $this->runDailyLimitMiddleware([
            'client_email' => $user->email,
            'phone' => self::PHONE,
            'amount' => 1000,
        ]);

        $this->assertSame(429, $response->getStatusCode());
        $data = $response->getData(true);
        $this->assertSame('error', $data['status']);
        $this->assertSame('Daily payout limit exceeded.', $data['message']);
    }

    public function test_daily_limit_middleware_returns_429_after_overshoot_day(): void
    {
        $user = $this->makeUser();
        $this->insertPayout($user->id, 90000, Payout::STATUS_SUCCESS);
        $this->insertPayout($user->id, 50000, Payout::STATUS_SUCCESS);

        $response = $this->runDailyLimitMiddleware([
            'client_email' => $user->email,
            'phone' => self::PHONE,
            'amount' => 1000,
        ]);

        $this->assertSame(429, $response->getStatusCode());
        $this->assertSame('Daily payout limit exceeded.', $response->getData(true)['message']);
    }

    public function test_remaining_80000_allows_50000(): void
    {
        $user = $this->makeUser();
        $this->insertPayout($user->id, 20000, Payout::STATUS_SUCCESS);

        $response = $this->runDailyLimitMiddleware([
            'client_email' => $user->email,
            'phone' => self::PHONE,
            'amount' => 50000,
        ]);

        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_daily_limit_does_not_leak_totals_in_response(): void
    {
        $user = $this->makeUser();
        $this->insertPayout($user->id, 90000, Payout::STATUS_SUCCESS);

        $response = $this->runDailyLimitMiddleware([
            'client_email' => $user->email,
            'phone' => self::PHONE,
            'amount' => 50000,
        ]);

        $payload = json_encode($response->getData(true));
        $this->assertStringNotContainsString('10000', (string) $payload);
        $this->assertStringNotContainsString('90000', (string) $payload);
        $this->assertStringNotContainsString('remaining', strtolower((string) $payload));
    }

    public function test_user_a_limit_does_not_affect_user_b_middleware(): void
    {
        $userA = $this->makeUser(['email' => 'limit-a@example.com']);
        $userB = $this->makeUser(['email' => 'limit-b@example.com']);
        $this->insertPayout($userA->id, 100000, Payout::STATUS_SUCCESS);

        $response = $this->runDailyLimitMiddleware([
            'client_email' => $userB->email,
            'phone' => self::PHONE,
            'amount' => 1000,
        ]);

        $this->assertSame(200, $response->getStatusCode());
    }

    private function runAmountMiddleware(array $payload): Response
    {
        $request = Request::create('/api/payout/checkout', 'POST', $payload);
        $middleware = new CheckPayoutAmount();

        return $middleware->handle($request, fn () => response()->json(['ok' => true], 200));
    }

    private function runDailyLimitMiddleware(array $payload): Response
    {
        $request = Request::create('/api/payout/checkout', 'POST', $payload);
        $middleware = new CheckPayoutDailyLimit(new PayoutLimitService());

        return $middleware->handle($request, fn () => response()->json(['ok' => true], 200));
    }

    private function makeUser(array $overrides = []): User
    {
        return User::query()->create(array_merge([
            'name' => 'Merchant',
            'email' => 'merchant'.uniqid('', true).'@example.com',
            'password' => 'secret',
            'user_role' => 'client',
            'payout_daily_limit' => null,
        ], $overrides));
    }

    private function insertPayout(int $userId, float $amount, string $status): void
    {
        $now = Carbon::now('Asia/Karachi')->format('Y-m-d H:i:s');

        DB::table('payouts')->insert([
            'user_id' => $userId,
            'amount' => $amount,
            'status' => $status,
            'phone' => self::PHONE,
            'transaction_type' => 'easypaisa',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
}
