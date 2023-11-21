<?php

namespace App\Services;
use App\Models\Delivery;
use App\Services\Facades\DeliveryEvent;
use PhpAmqpLib\Connection\AMQPStreamConnection;

class DeliveryConsumer
{
    public function consumeCreate()
    {
        $connection = new AMQPStreamConnection(env('MQ_HOST'), env('MQ_PORT'), env('MQ_USER'), env('MQ_PASSWORD'), env('MQ_VHOST'));

        $channel = $connection->channel();

        $callback = function ($msg) {
            echo ' [x] Received ', $msg->body, "\n";
            $data = json_decode(json_decode($msg->body, true));
            Delivery::create(["order_id" => $data->order_id, "driver_id" => null, "email" => $data->email]);
            echo ' [x] Done', "\n";
        };

        $channel->queue_declare('order_new', false, true, false, false);
        $channel->basic_consume('order_new', '<delivery-service>', false, true, false, false, $callback);
        echo 'Waiting for new message on order_new', " \n";

        while ($channel->is_consuming()) {
            $channel->wait();
        }

        $channel->close();
        $connection->close();
    }

    public function consumeFindDriver()
    {
        $connection = new AMQPStreamConnection(env('MQ_HOST'), env('MQ_PORT'), env('MQ_USER'), env('MQ_PASSWORD'), env('MQ_VHOST'));

        $channel = $connection->channel();

        $callback = function ($msg) {
            echo ' [x] Received ', $msg->body, "\n";
            $data = json_decode(json_decode($msg->body, true));
            $delivery = Delivery::where("order_id", $data->order_id)->first();
            $driver_id = random_int(1, 10);
            $delivery->driver_id = $driver_id;
            $delivery->save();

            // update order table with status picked up
            DeliveryEvent::publish($delivery->toJson(), 'StatusExchange', 'direct', 'status_pickup', 'status.pickup');
            DeliveryEvent::publish($delivery->toJson(), 'NotifyExchange', 'fanout', 'notify_pickup');

            echo ' [x] Done', "\n";
        };

        $channel->queue_declare('status_confirm', false, true, false, false);
        $channel->basic_consume('status_confirm', '<delivery-service>', false, true, false, false, $callback);
        echo 'Waiting for new message on status_confirm', " \n";

        while ($channel->is_consuming()) {
            $channel->wait();
        }

        $channel->close();
        $connection->close();
    }

}