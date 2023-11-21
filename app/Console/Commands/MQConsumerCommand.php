<?php

namespace App\Console\Commands;

use App\Services\DeliveryConsumer;
use Illuminate\Console\Command;

class MQConsumerCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mq:consume {consumerType}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'MQ Consumer run';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        $mqConsumer = new DeliveryConsumer();

        if($this->argument('consumerType') == 'create') 
        {
            $mqConsumer->consumeCreate();
        } 
        else if($this->argument('consumerType') == 'find_driver') 
        {
            $mqConsumer->consumeFindDriver();
        }
    }
}
