<?php namespace std\queue\controllers;

class Test extends \Controller
{
    public function syncTest()
    {
        queue()->call($this->_abs(':loop'))->sync();
    }

    public function asyncTest()
    {
        queue()->call($this->_abs(':loop'))->async();
    }

    public function procTest()
    {
        queue()->call($this->_abs(':loop'))->proc();
    }

    public function procTest_()
    {
        $p = $this->proc(':loop')->run();

        $o = $p->wait();

        return $o;
    }

    public function loop()
    {
        $appProcess = $this->app->process;

        $iterations = 200;

        $n = 0;

        while ($n < $iterations) {
            if ($appProcess->handleIteration(250)) {
                break;
            }

            $this->log($n);

            $n++;

            $appProcess->output(['n' => $n]);
        }
    }
}
