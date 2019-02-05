<?php namespace std\queue\schemas;

class Job extends \Schema
{
    public $table = 'std_queue';

    public function blueprint()
    {
        return function (\Illuminate\Database\Schema\Blueprint $table) {
            $table->engine = 'InnoDB';

            $table->increments('id');
            $table->string('instance')->default('');
            $table->integer('priority')->default(0);
            $table->enum('mode', ['sync', 'async', 'proc'])->default('sync');
            $table->boolean('async')->default(false); // todo del
            $table->longText('call');
            $table->char('call_md5', 32)->default('');
            $table->longText('proc_input');
            $table->enum('proc_lock_type', ['none', 'global', 'path'])->nullable();
            $table->string('proc_lock_instance')->nullable();
            $table->integer('expires')->default(0);
            $table->boolean('running')->default(false);
        };
    }
}
