<?php

namespace App\WebSockets\Handler;

use App\Models\DeliveryMan;
use Ratchet\ConnectionInterface;
use Illuminate\Support\Facades\DB;
use BeyondCode\LaravelWebSockets\Apps\App;
use Ratchet\RFC6455\Messaging\MessageInterface;
use Ratchet\WebSocket\MessageComponentInterface;
use BeyondCode\LaravelWebSockets\QueryParameters;
use BeyondCode\LaravelWebSockets\WebSockets\Exceptions\UnknownAppKey;


class DMLocationSocketHandler implements MessageComponentInterface
{

    function onMessage(ConnectionInterface $from, MessageInterface $msg)
    {
        $data = json_decode($msg->getPayload(), true);
        
        // Check if the message contains the necessary data for recording
        if (
            isset($data['token'], $data['longitude'], $data['latitude'], $data['location'])
        ) {
            $dm = DeliveryMan::where(['auth_token' => $data['token']])->first();
    
            if ($dm) {
                DB::table('delivery_histories')->insert([
                    'delivery_man_id' => $dm['id'],
                    'longitude' => $data['longitude'],
                    'latitude' => $data['latitude'],
                    'time' => now(),
                    'location' => $data['location'],
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                
                // Send a response back to the client indicating successful recording
                $from->send(json_encode(['message' => 'location recorded']));
            }
        }
    }
    

    function onOpen(ConnectionInterface $conn)
    {
        $this->verifyAppKey($conn)->generateSocketId($conn);

    }

    function onClose(ConnectionInterface $conn)
    {
        // TODO: Implement onClose() method.
    }

    function onError(ConnectionInterface $conn, \Exception $e)
    {
        // TODO: Implement onError() method.
    }

    protected function verifyAppKey(ConnectionInterface $connection)
    {

        $appKey = QueryParameters::create($connection->httpRequest)->get('appKey');
        if (! $app = App::findByKey($appKey)) {
            throw new UnknownAppKey($appKey);
        }
        $connection->app = $app;

        return $this;
    }

    protected function generateSocketId(ConnectionInterface $connection)
    {
        $socketId = sprintf('%d.%d', random_int(1, 1000000000), random_int(1, 1000000000));
        $connection->socketId = $socketId;

        return $this;
    }
}

