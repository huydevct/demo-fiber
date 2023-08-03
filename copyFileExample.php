<?php

function writeToLog(string $message): void
{
    echo $message . "\n";
}
$files = [
    './foo.png' => './foo-copy.png',
    './bar.png' => './bar-copy.png',
    './baz.png' => './baz-copy.png',
];

$fiber = new Fiber(function (array $files): void {
    foreach ($files as $source => $destination) {
        copy($source, $destination);
        Fiber::suspend([$source, $destination]);
    }
});

// Pass the files list into Fiber.
$copied = $fiber->start($files);
$copied_count = 1;
$total_count = count($files);

while (!$fiber->isTerminated()) {
    $percentage = round($copied_count / $total_count, 2) * 100;
    writeToLog("[{$percentage}%]: Copied '{$copied[0]}' to '{$copied[1]}'");
    $copied = $fiber->resume();
    ++$copied_count;
}

writeToLog('Completed');