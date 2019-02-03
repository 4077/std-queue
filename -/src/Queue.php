<?php namespace std\queue;

class Queue
{
    public static $instances = [];

    /**
     * @return \std\queue\Queue
     */
    public static function getInstance($instance)
    {
        if (!isset(static::$instances[$instance])) {
            $server = new self($instance);

            static::$instances[$instance] = $server;
        }

        return static::$instances[$instance];
    }

    private $instance;

    public function __construct($instance)
    {
        $this->instance = $instance;
    }

    private $call;

    public function call($call)
    {
        $this->call = $call;

        return $this;
    }

    private $callOnce = false;

    private $callOnceExceptRunning = false;

    public function callOnce($call)
    {
        $this->call = $call;

        $this->callOnce = true;
        $this->callOnceExceptRunning = false;

        return $this;
    }

    public function callOnceExceptRunning($call)
    {
        $this->call = $call;

        $this->callOnce = true;
        $this->callOnceExceptRunning = true;

        return $this;
    }

    private $ttl;

    public function ttl($ttl)
    {
        $this->ttl = $ttl;

        return $this;
    }

    private $priority = 0;

    public function priority($priority)
    {
        $this->priority = $priority;
    }

    private $procLockType;

    private $procLockInstance;

    public function lock($instance = '')
    {
        $this->procLockType = 'global';
        $this->procLockInstance = $instance;

        return $this;
    }

    public function pathLock($instance = '')
    {
        $this->procLockType = 'path';
        $this->procLockInstance = $instance;

        return $this;
    }

    private $mode;

    public function sync()
    {
        $this->mode = 'sync';

        $this->add();
    }

    public function async()
    {
        $this->mode = 'async';

        $this->add();
    }

    private $procInput = [];

    public function proc($procInput = [])
    {
        $this->mode = 'proc';
        $this->procInput = $procInput;

        $this->add();
    }

    private function add()
    {
        $callJson = j_($this->call);
        $callMd5 = md5($callJson);

        $add = true;

        if ($this->callOnce) {
            $jobBuilder = \std\queue\models\Job::where('call_md5', $callMd5);

            if ($this->callOnceExceptRunning) {
                $jobBuilder->where('running', false);
            }

            $job = $jobBuilder->first();

            if ($job) {
                $add = false;
            }
        }

        if ($add) {
            $jobData = [
                'instance'           => $this->instance,
                'priority'           => $this->priority,
                'mode'               => $this->mode,
                'proc_lock_type'     => $this->procLockType,
                'proc_lock_instance' => $this->procLockInstance,
                'proc_input'         => j_($this->procInput),
                'call'               => $callJson,
                'call_md5'           => $callMd5,
                'expires'            => $this->ttl ? time() + $this->ttl : 0
            ];

            \std\queue\models\Job::create($jobData);
        }
    }
}
