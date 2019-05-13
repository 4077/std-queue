<?php namespace std\queue\controllers;

class Main extends \Controller
{
    public $instance;

    public function __create()
    {
        $this->instance = $this->_instance('default');
    }

    public function handle()
    {
        if ($process = $this->proc(':loop|')->pathLock($this->instance)->run()) {
            $this->d(':pid|', $process->getPid(), RR);
        }
    }

    public function loop()
    {
        $process = process();

        $jobBuilder = \std\queue\models\Job::where('instance', $this->instance)->where('running', false)->orderBy('priority', 'DESC')->orderBy('id', 'ASC');

        while (true) {
            if (true === $process->handleIteration(100)) {
                break;
            }

            if ($job = $jobBuilder->first()) {
                \std\queue\JobHandler::handle($job, $this);
            }
        }
    }

    private function openInstanceProcess()
    {
        $pid = $this->d(':pid|');

        return $this->app->processDispatcher->open($pid);
    }

    public function pause()
    {
        $this->openInstanceProcess()->pause();
    }

    public function resume()
    {
        $this->openInstanceProcess()->resume();
    }

    public function stop()
    {
        $this->openInstanceProcess()->break();
    }

    public function getInfo()
    {
        return $this->openInstanceProcess()->output();
    }

    public function add()
    {
        $ttl = $this->data('ttl');

        $jobData = [
            'instance'           => $this->instance,
            'priority'           => $this->data('priority') ?? 0,
            'mode'               => $this->data('mode') ?? 'sync',
            'proc_lock_type'     => $this->data('proc_lock_type'),
            'proc_lock_instance' => $this->data('proc_lock_instance'),
            'proc_input'         => j_($this->data('proc_input')),
            'call'               => j_($this->data('call')),
            'expires'            => $ttl ? time() + $ttl : 0
        ];

        \std\queue\models\Job::create($jobData);

        return $jobData;
    }
}
