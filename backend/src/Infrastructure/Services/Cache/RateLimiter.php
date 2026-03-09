<?php

namespace App\Infrastructure\Services\Cache;

class RateLimiter
{
    private array $attempts = [];
    private int $maxAttempts;
    private int $decayMinutes;

    public function __construct(int $maxAttempts = 5, int $decayMinutes = 15)
    {
        $this->maxAttempts = $maxAttempts;
        $this->decayMinutes = $decayMinutes;
    }

    public function isAllowed(string $key): bool
    {
        $this->clearOldAttempts($key);
        
        return $this->attempts[$key]['count'] ?? 0 < $this->maxAttempts;
    }

    public function attempt(string $key): void
    {
        $key = $this->sanitizeKey($key);
        
        if (!isset($this->attempts[$key])) {
            $this->attempts[$key] = [
                'count' => 0,
                'first_attempt_time' => time()
            ];
        }
        
        $this->attempts[$key]['count']++;
        $this->attempts[$key]['last_attempt_time'] = time();
    }

    public function getRemainingTime(string $key): int
    {
        $this->clearOldAttempts($key);
        
        if (!isset($this->attempts[$key])) {
            return 0;
        }
        
        $firstAttemptTime = $this->attempts[$key]['first_attempt_time'];
        $windowEnd = $firstAttemptTime + ($this->decayMinutes * 60);
        
        return max(0, $windowEnd - time());
    }

    private function clearOldAttempts(string $key): void
    {
        $key = $this->sanitizeKey($key);
        
        if (isset($this->attempts[$key])) {
            $firstAttemptTime = $this->attempts[$key]['first_attempt_time'];
            $windowEnd = $firstAttemptTime + ($this->decayMinutes * 60);
            
            if (time() > $windowEnd) {
                unset($this->attempts[$key]);
            }
        }
    }

    private function sanitizeKey(string $key): string
    {
        // Заменяем потенциально проблемные символы
        return preg_replace('/[^a-zA-Z0-9_:.-]/', '_', $key);
    }
}
