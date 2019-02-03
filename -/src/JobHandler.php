<?php namespace std\queue;

class JobHandler
{
    public static function handle($job, \std\queue\controllers\Main $cMain)
    {
        $call = _j($job->call);

        $time = time();

        if (!$job->expires || $job->expires > $time) {
            $mode = $job->mode;

            $job->running = true;
            $job->save();

            if ($mode == 'sync') {
                $cMain->log($cMain->instance . ' SYNC: ' . j_($call));

                start_time('std_queue');

                $cMain->_call($call)->perform();

                $cMain->log('job processed in ' . end_time('std_queue', true) . ' ms');
            }

            if ($mode == 'async') {
                $cMain->log($cMain->instance . ' ASYNC: ' . j_($call));

                $cMain->_call($call)->async();
            }

            if ($mode == 'proc') {
                $cMain->log($cMain->instance . ' PROC: ' . j_($call));

                $proc = $cMain->proc($call[0], $call[1]);

                $procLockType = $job->proc_lock_type;

                if ($procLockType == 'global') {
                    $proc->lock($job->proc_lock_instance);
                }

                if ($procLockType == 'path') {
                    $proc->pathLock($job->proc_lock_instance);
                }

                $process = $proc->run(_j($job->proc_input));

                $cMain->log($cMain->instance . ' PROC WAIT');

                $output = $process->wait();

                $cMain->log($cMain->instance . ' PROC COMPLETE with output: ' . j_($output));
            }
        } else {
            $cMain->log($cMain->instance . ' EXPIRED CALL (' . ($job->expires - $time) . ' s): ' . j_($call));
        }

        $job->delete();
    }
}
