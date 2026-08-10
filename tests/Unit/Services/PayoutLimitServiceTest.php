<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Payout;
use App\Models\User;
use App\Services\PayoutLimitService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\Support\SetsUpPayoutLimitSchema;
use Tests\TestCase;

class PayoutLimitServiceTest extends TestCase
{
    use SetsUpPayoutLimitSchema;

    private const PHONE = '03001234567';

    private PayoutLimitService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpPayoutLimitSchema();
        $this->service = new PayoutLimitService();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_allows_when_successful_total_is_zero(): void
    {
        $user = $this->makeUser();

        $this->assertFalse($this->service->hasReachedDailyLimit($user, self::PHONE));
        $this->assertSame(0.0, $this->service->getTodaySuccessfulPayoutTotal($user, self::PHONE));
    }

    public function test_allows_when_total_is_40000(): void
    {
        $user = $this->makeUser();
        $this->insertPayout($user->id, 40000, Payout::STATUS_SUCCESS, 'easypaisa');

        $this->assertSame(40000.0, $this->service->getTodaySuccessfulPayoutTotal($user, self::PHONE));
        $this->assertFalse($this->service->hasReachedDailyLimit($user, self::PHONE));
    }

    public function test_allows_when_total_is_90000_even_if_next_request_would_overshoot(): void
    {
        $user = $this->makeUser();
        $this->insertPayout($user->id, 40000, Payout::STATUS_SUCCESS, 'easypaisa');
        $this->insertPayout($user->id, 50000, Payout::STATUS_SUCCESS, 'jazzcash');

        $this->assertSame(90000.0, $this->service->getTodaySuccessfulPayoutTotal($user, self::PHONE));
        $this->assertFalse($this->service->hasReachedDailyLimit($user, self::PHONE));
    }

    public function test_blocks_when_total_reaches_exactly_100000(): void
    {
        $user = $this->makeUser();
        $this->insertPayout($user->id, 100000, Payout::STATUS_SUCCESS, 'easypaisa');

        $this->assertTrue($this->service->hasReachedDailyLimit($user, self::PHONE));
    }

    public function test_allows_when_total_is_99999(): void
    {
        $user = $this->makeUser();
        $this->insertPayout($user->id, 99999, Payout::STATUS_SUCCESS, 'jazzcash');

        $this->assertFalse($this->service->hasReachedDailyLimit($user, self::PHONE));
    }

    public function test_blocks_after_combined_total_exceeds_limit(): void
    {
        $user = $this->makeUser();
        $this->insertPayout($user->id, 90000, Payout::STATUS_SUCCESS, 'easypaisa');
        $this->insertPayout($user->id, 50000, Payout::STATUS_SUCCESS, 'jazzcash');

        $this->assertSame(140000.0, $this->service->getTodaySuccessfulPayoutTotal($user, self::PHONE));
        $this->assertTrue($this->service->hasReachedDailyLimit($user, self::PHONE));
    }

    public function test_combines_easypaisa_and_jazzcash_for_daily_total(): void
    {
        $user = $this->makeUser();
        $this->insertPayout($user->id, 60000, Payout::STATUS_SUCCESS, 'easypaisa');
        $this->insertPayout($user->id, 40000, Payout::STATUS_SUCCESS, 'jazzcash');

        $this->assertSame(100000.0, $this->service->getTodaySuccessfulPayoutTotal($user, self::PHONE));
        $this->assertTrue($this->service->hasReachedDailyLimit($user, self::PHONE));
    }

    public function test_ignores_failed_and_pending_payouts(): void
    {
        $user = $this->makeUser();
        $this->insertPayout($user->id, 60000, Payout::STATUS_SUCCESS, 'easypaisa');
        $this->insertPayout($user->id, 30000, Payout::STATUS_SUCCESS, 'jazzcash');
        $this->insertPayout($user->id, 50000, 'failed', 'easypaisa');
        $this->insertPayout($user->id, 50000, 'pending', 'jazzcash');

        $this->assertSame(90000.0, $this->service->getTodaySuccessfulPayoutTotal($user, self::PHONE));
        $this->assertFalse($this->service->hasReachedDailyLimit($user, self::PHONE));
    }

    public function test_user_totals_are_isolated(): void
    {
        $userA = $this->makeUser(['email' => 'a@example.com', 'payout_daily_limit' => 100000]);
        $userB = $this->makeUser(['email' => 'b@example.com', 'payout_daily_limit' => 100000]);

        $this->insertPayout($userA->id, 100000, Payout::STATUS_SUCCESS, 'easypaisa');
        $this->insertPayout($userB->id, 10000, Payout::STATUS_SUCCESS, 'jazzcash');

        $this->assertTrue($this->service->hasReachedDailyLimit($userA, self::PHONE));
        $this->assertFalse($this->service->hasReachedDailyLimit($userB, self::PHONE));
        $this->assertSame(10000.0, $this->service->getTodaySuccessfulPayoutTotal($userB, self::PHONE));
    }

    public function test_previous_pakistan_business_day_is_not_counted(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-08-09 10:00:00', 'Asia/Karachi'));

        $user = $this->makeUser();
        $this->insertPayout(
            $user->id,
            100000,
            Payout::STATUS_SUCCESS,
            'easypaisa',
            Carbon::parse('2026-08-08 23:59:59', 'Asia/Karachi')
        );
        $this->insertPayout(
            $user->id,
            10000,
            Payout::STATUS_SUCCESS,
            'jazzcash',
            Carbon::parse('2026-08-09 00:00:00', 'Asia/Karachi')
        );

        $this->assertSame(10000.0, $this->service->getTodaySuccessfulPayoutTotal($user, self::PHONE));
        $this->assertFalse($this->service->hasReachedDailyLimit($user, self::PHONE));
    }

    public function test_pakistan_timezone_day_boundary(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-08-09 00:30:00', 'Asia/Karachi'));

        $user = $this->makeUser();

        $this->insertPayout(
            $user->id,
            50000,
            Payout::STATUS_SUCCESS,
            'easypaisa',
            Carbon::parse('2026-08-08 23:59:59', 'Asia/Karachi')
        );
        $this->insertPayout(
            $user->id,
            40000,
            Payout::STATUS_SUCCESS,
            'jazzcash',
            Carbon::parse('2026-08-09 00:00:00', 'Asia/Karachi')
        );
        $this->insertPayout(
            $user->id,
            10000,
            Payout::STATUS_SUCCESS,
            'easypaisa',
            Carbon::parse('2026-08-09 23:59:59', 'Asia/Karachi')
        );

        $this->assertSame(50000.0, $this->service->getTodaySuccessfulPayoutTotal($user, self::PHONE));
    }

    public function test_per_user_daily_limit_overrides_config_default(): void
    {
        $user = $this->makeUser(['payout_daily_limit' => 50000]);
        $this->insertPayout($user->id, 50000, Payout::STATUS_SUCCESS, 'easypaisa');

        $this->assertSame(50000.0, $this->service->getDailyLimit($user));
        $this->assertTrue($this->service->hasReachedDailyLimit($user, self::PHONE));
    }

    public function test_null_user_limit_uses_config_default(): void
    {
        $user = $this->makeUser(['payout_daily_limit' => null]);

        $this->assertSame(100000.0, $this->service->getDailyLimit($user));
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

    private function insertPayout(
        int $userId,
        float $amount,
        string $status,
        string $transactionType,
        ?Carbon $createdAt = null
    ): void {
        $createdAt ??= Carbon::now('Asia/Karachi');

        DB::table('payouts')->insert([
            'user_id' => $userId,
            'amount' => $amount,
            'status' => $status,
            'phone' => self::PHONE,
            'transaction_type' => $transactionType,
            'created_at' => $createdAt->copy()->timezone(config('app.timezone'))->format('Y-m-d H:i:s'),
            'updated_at' => $createdAt->copy()->timezone(config('app.timezone'))->format('Y-m-d H:i:s'),
        ]);
    }
}
