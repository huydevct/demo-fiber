<?php

$fiber = new Fiber(function (): void {

    echo "Welcome to Fiber!\n";

    Fiber::suspend();

    echo "Welcome back to Fiber!\n";

});


echo "Starting a Fiber!\n";

$fiber->start();

echo "Fiber has been suspended\n";

echo "Resuming the Fiber!\n";

$fiber->resume();

echo "Fiber has been completed!";