<?php

    //Boot2.php

    //Ratchet WS/HTTP


    require __DIR__ . "/vendor/autoload.php";

    use Psr\Http\Message\RequestInterface;
    use Psr\Http\Message\ServerRequestInterface;
    use Ratchet\Http\HttpServerInterface;
    use Ratchet\MessageComponentInterface;
    use Ratchet\ConnectionInterface;
    use Ratchet\Server\IoServer;
    use Ratchet\Http\HttpServer;
    use Ratchet\WebSocket\WsServer;
    use Evenement\EventEmitter;
    use React\Http\Message\Response;


class WSChat implements MessageComponentInterface {
    protected $clients;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn) {
        // Store the new connection to send messages to later
        $this->clients->attach($conn);

        echo "New connection!\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        echo $msg . "\n";
        
    }

    public function onClose(ConnectionInterface $conn) {
        // The connection is closed, remove it, as we can no longer send it messages
        $this->clients->detach($conn);

        echo "Connection has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";

        $conn->close();
    }
}

#$loop = React\EventLoop\Factory::create();

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new WSChat()
        )
    ),
    8080
);


$socket = new React\Socket\SocketServer("0.0.0.0:80");

$http = new React\Http\HttpServer(function(ServerRequestInterface $request) {
    $path = $request->getUri()->getPath();
    $method = $request->getMethod();

    // Index
    if($path === "/" && $method === 'GET') {
        return new Response(200,['Content-Type'=>'text/html'],"Index");
    }
    
});

$http->listen($socket);

$server->run();

?>