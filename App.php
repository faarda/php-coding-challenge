<?php

use Spatie\Async\Pool;

class App
{
    public function __construct()
    {
        $this->billUsers();
    }

    //handles the billing of users and measures how much time was taken
    public function billUsers()
    {
//        echo "Scroll to the bottom to see total time taken";
        $start_time = microtime(true);

        //lets create the list of data to be processed through a simple range

        $data = range(1, 10000);

        // we'll break down the list into chunks and process each chunk asynchroniusly
        $chunks = array_chunk($data, 100);

        $pool = Pool::create();

        foreach ($chunks as $chunk) {
            $pool[] = async(function () use ($chunk) {
                return $this->sendRequests($chunk);
            })->then(function ($output) use ($chunk){
                echo "Requests $chunk[0] to ".$chunk[count($chunk) -1]." completed\n";

                //save data to database here
            });
        }
        await($pool);

        $end_time = microtime(true);

        $execution_time = ($end_time - $start_time);

        echo "<h4> Took = ".($execution_time/60)." mins to complete </h4>\n";
        echo "Normally this process should take about ".((.6 * count($data))/60 )." minute(s) to complete, if we processed it serially";
        
    }

    //sends multiple requests asynchroniously
    public function sendRequests($items)
    {
        //create the multiple cURL handle
        $mh = curl_multi_init();

        $handles = [];
        foreach ($items as $item) {
            //get the handle for each item
            $curl_handle = $this->setupCurl();

            //add the handles
            curl_multi_add_handle($mh,$curl_handle);

            //push handle to the handles array so we can get the response and remove it later
            $handles[] = $curl_handle;
        }

        $status = true;
        do {
            try {

                $status = curl_multi_exec($mh, $active);
            }catch(\Exception $e){

                // here we'll handle request failures
            }

            if ($active) {
                curl_multi_select($mh);
            }
        } while ($active && $status == CURLM_OK);

        foreach ($handles as $handle) {
            $data = curl_multi_getcontent($handle);

            //persist to database based on response received
            $this->saveToDatabase($data);

            curl_multi_remove_handle($mh, $handle);
        }

        curl_multi_close($mh);

        //we could return some data for extra processing, but for now we'll just return the status which should be false
        return $status;
    }

    //prepares the curl request
    public function setupCurl()
    {
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL,"https://jsonplaceholder.typicode.com/posts");
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);

        return $ch;
    }

    public function saveToDatabase($data)
    {
        //IF WE HAD A DATABASE SETUP WE WOULD PERSIST THE DATA HERE
    }
}