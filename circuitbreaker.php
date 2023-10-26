<?php

class Service //servizio esterno
{
    public function doSomething() //genero un numero casuale e lancio eccezione se supera la soglia.
    {
        $randomNumber = floor(rand(0, 9));
        $isError = $randomNumber > 2;
        if ($isError) {
            throw new Exception("Something went wrong! Service unavailable!");
        }
        echo "Service invoked!" . PHP_EOL;
    }
}

class CircuitBreaker //gestione delle chiamate al servizio
{
    //tengo traccia dello stato del servizio
    private $serviceOn = true; //variabile che indica se il servizio è attivo o meno
    private $service; //istanza del servizio
    private $maxAttempts; //numero massimo di tentativi

    public function __construct($service, $maxAttempts = 3)
    {
        $this->service = $service;
        $this->maxAttempts = $maxAttempts;
    }

    public function handleRequest() //responsabile della gestione della chiamata la servizio
    {
        if (!$this->serviceOn) {
            echo "Server offline" . PHP_EOL;
            return;
        }

        for ($i = 1; $i <= $this->maxAttempts; $i++) {
            try {
                $this->service->doSomething();
                echo "Succeeded on the " . $i . "° try" . PHP_EOL;
                return;
            } catch (Exception $error) {
                echo "Attempt " . $i . " failed" . PHP_EOL;
            }
        }

        $this->serviceOn = false;
        echo "Server not responding" . PHP_EOL;
        sleep(2);
        $this->tryService();
    }

    public function tryService() //chiamato se il servizio è impostato come offline e provo a riattivare il servizio
    {
        while (!$this->serviceOn) {
            try {
                $this->service->doSomething();
                echo "Server Online" . PHP_EOL;
                $this->serviceOn = true;
            } catch (Exception $error) {
                echo "Checking" . PHP_EOL;
                sleep(2);
            }
        }
    }
}

class Client //utilizza il circuit breaker
{
    private $service;
    private $circuitBreaker; //invoco il servizio

    public function __construct($service)
    {
        $this->service = $service;
        $this->circuitBreaker = new CircuitBreaker($this->service, 3);
    }

    public function invokeService() //chiama il cb per gestire la chiamata al servizio
    {
        echo "Circuit breaker calling" . PHP_EOL;
        $this->circuitBreaker->handleRequest();
    }

    function sleep($seconds)
    {
        usleep($seconds * 1000000);
    }
}


$client = new Client(new Service());

$client->invokeService(); //chiamata al servizio
$client->invokeService();
$client->invokeService();
$client->invokeService();
