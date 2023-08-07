<?php

function getFiberFromStream($stream, $url): Fiber
{

    return new Fiber(function ($stream) use ($url): void {
        while (!feof($stream)) {
            echo "reading 100 bytes from $url" . PHP_EOL;
            $contents = fread($stream, 100);
            Fiber::suspend($contents);
        }
    });
}

function getContents(array $urls): array
{
    $contents = [];
    $fibers = [];

    // start them all up
    foreach ($urls as $key => $url) {

        $stream = fopen($url, 'r');
        stream_set_blocking($stream, false);
        $fiber = getFiberFromStream($stream, $url);
        $content = $fiber->start($stream);

        // save fiber context so we can process them later
        $fibers[$key] = [$fiber, $content, $stream];
    }

    // now poll
    $have_unterminated_fibers = true;
    while ($have_unterminated_fibers) {

        // first suppose we have no work to do
        $have_unterminated_fibers = false;

        // now loop over fibers to see if any is still working
        foreach ($fibers as $key => $item) {
            // fetch context
            $fiber = $item[0];
            $content = $item[1];
            $stream = $item[2];

            // don't do while till the end here,
            // just process next chunk
            if (!$fiber->isTerminated()) {
                // yep, mark we still have some work left
                $have_unterminated_fibers = true;

                // update content in the context
                $content .= $fiber->resume();
                $fibers[$key][1] = $content;
            } else {
                if ($stream) {
                    fclose($stream);

                    // save result for return
                    $contents[$urls[$key]] = $content;

                    // mark stream as closed in context
                    // so it don't close twice
                    $fibers[$key][2] = null;
                }
            }
        }
    }

    return $contents;
}

//function getContents(array $urls): array {
//
//    $contents = [];
//
//    foreach ($urls as $key => $url) {
//
//        $stream = fopen($url, 'r');
//        stream_set_blocking($stream, false);
//        $fiber = getFiberFromStream($stream, $url);
//        $content = $fiber->start($stream);
//
//        while (!$fiber->isTerminated()) {
//            $content .= $fiber->resume();
//        }
//        fclose($stream);
//
//        $contents[$urls[$key]] = $content;
//    }
//
//    return $contents;
//}


$urls = [
    'https://www.google.com/',
//    'https://www.twitter.com',
    'https://www.facebook.com'
];
var_dump(getContents($urls));