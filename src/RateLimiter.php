<?php

namespace Permafrost;

class RateLimiter
{

    protected $counter = 0;

    protected $perSecondLimit = 1;

    protected $throttleDelayMs = 2000; //1 second

    protected $previousThrottleAt = false;

    public function __construct($perSecondLimit = 1, $throttleDelayMs = 1000)
    {
        $this->perSecondLimit = $perSecondLimit;
        $this->throttleDelayMs = $throttleDelayMs;
        $this->resetCounter();
    }


    protected function updateThrottledTimestamp()
    {
        $previous = $this->previousThrottleAt;
        $this->previousThrottleAt = microtime(true);

        return $previous;
    }

    public function getThrottleDelay($format = 'milliseconds')
    {
        switch(strtolower($format)) {
            case 'seconds':
                $result = $this->throttleDelayMs / 1000;
                break;
            case 'microseconds':
                $result = $this->throttleDelayMs * 1000;
                break;

            case 'milliseconds':
            default:
                $result = $this->throttleDelayMs;
                break;
        }

        return $result;
    }

    public function shouldThrottle()
    {
        echo "[debug] ".__METHOD__."\n";

        if (!$this->previousThrottleAt) {
            $this->previousThrottleAt = microtime(true);
        }

        $now = microtime(true);

        $secs = $this->previousThrottleAt - $now;
        if ($secs == 0)
            $secs = 1;

        $rate = round(($secs / $this->counter), 2);

        echo "[debug] \$rate = $rate\n";

        return ($rate < $this->perSecondLimit);
    }

    public function throttle()
    {
        if (!$this->shouldThrottle())
            return false;

        $this->updateThrottledTimestamp();
        $this->resetCounter();

        $delay = $this->getThrottleDelay('microseconds');

        echo "[debug] delay = $delay\n";

        usleep($delay);
        return true;
    }

    public function increaseCounter($amount = 1)
    {
        $this->counter = $this->counter + $amount;
    }

    public function resetCounter()
    {
        $this->counter = 0;
    }
}
