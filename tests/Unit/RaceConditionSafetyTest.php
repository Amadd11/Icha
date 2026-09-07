<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class RaceConditionSafetyTest extends TestCase
{
    /**
     * Test atomic cache lock acquisition and release.
     */
    public function test_cache_atomic_locks_can_be_acquired_and_released(): void
    {
        $lockName = 'unit_test_race_lock_' . time();
        $lock = Cache::lock($lockName, 10);

        $this->assertTrue($lock->get(), 'Failed to acquire atomic cache lock.');

        // Second attempt to acquire without releasing should fail
        $secondLock = Cache::lock($lockName, 10);
        $this->assertFalse($secondLock->get(), 'Second lock should fail to acquire before first is released.');

        // Release first lock
        $lock->release();

        // Now third attempt should succeed
        $thirdLock = Cache::lock($lockName, 10);
        $this->assertTrue($thirdLock->get(), 'Should acquire lock after release.');
        $thirdLock->release();
    }

    /**
     * Test sequential code generator simulation under atomic lock.
     */
    public function test_sequential_code_generator_under_atomic_lock(): void
    {
        $codes = [];

        for ($i = 0; $i < 5; $i++) {
            $code = Cache::lock('generate_test_code_lock', 5)->block(3, function () use (&$codes) {
                $next = count($codes) + 1;
                $generated = 'TEST-' . str_pad($next, 3, '0', STR_PAD_LEFT);
                $codes[] = $generated;
                return $generated;
            });

            $this->assertNotEmpty($code);
        }

        $this->assertCount(5, array_unique($codes), 'All generated codes under lock must be distinct and sequential.');
        $this->assertEquals(['TEST-001', 'TEST-002', 'TEST-003', 'TEST-004', 'TEST-005'], $codes);
    }

    /**
     * Test sequential code generator format.
     */
    public function test_clean_sequential_code_generator(): void
    {
        $mockExisting = [
            'ABS-001',
            'ABS-002',
            'ABS-003',
        ];

        $extractedNumbers = array_map(function ($c) {
            $parts = explode('-', $c);
            return (int) end($parts);
        }, $mockExisting);

        $max = !empty($extractedNumbers) ? max($extractedNumbers) : 0;
        $next = $max + 1;
        $newCode = 'ABS-' . str_pad($next, 3, '0', STR_PAD_LEFT);
        $this->assertEquals('ABS-004', $newCode);
    }

    /**
     * Test CodeGenerator helper class generates valid prefix and sequential numbers.
     */
    public function test_code_generator_helper_generates_valid_format(): void
    {
        $code = \App\Helpers\CodeGenerator::next(\App\Models\AbstractSubmission::class, 'abstract_code', 'ABS');
        $this->assertStringStartsWith('ABS-', $code);
        $this->assertMatchesRegularExpression('/^ABS-\d{3,}$/', $code);
    }
}
