**PHP Developer coding challenge**

###### **The problem**
We have an external API that bills users and takes about 1.6secs to complete one request, but we need to bill 10000 users under an hour.

###### **Analysing the problem**
Naturally if we send each requests one after the other, it'll take a total time of `n * t //where n is the total number of requests and t is the time for each request to complete`, in this case it should take about 4.5 hours to complete.

Basically to solve this problem we need to find a way to send the requests to the api endpoint asynchronously, i.e in a parallel order. 

Considering the above, we could just send all the requests at once, say 10000, and it would possibly take less than 20secs to complete, but doing it this way would mean we would overload the servers processors, which could make things run really slowly and possibly crash the server, plus it really isn't scalable.

Another problem is that PHP is a single threaded language, and it executes programs in a blocking manner i.e serially, naturally request 2 would have to wait for request 1 to complete before running.

###### **My solution**
Although, PHP doesn't really have a native way of running asynchronously, we could send multiple requests to the server at once using `multi_curl`.

Also @spatie created a package that allows us run code asynchronously it's here https://github.com/spatie/async, the package allows us to spin up multiple PHP child processes and runs specified code on each of them, helping us execute them in a non blocking order.

My solution involves a mix of the above stated, Here's my process

1. Get a list of all the requests and split them into chunks (in this solution i used a chunk of 100)
2. Create an async pool using treating each chunk as a single item
3. Create a multi curl handle for each chunk and call the number of requests in that chunk using `curl_multi_exec`

Run the `index.php` file to test the process (most of the code for the solution is in `App.php`), you'll need to run `composer install` to install the dependencies.

I did'nt wan't to overload the curl process, so each handle runs only 100 processes while our pool also spins up 100 processes, this seemed to be a very optimized solution as it took only about 10 minutes to complete the 10000 requests as supposed to the 100 minutes running it serially.

I used https://jsonplaceholder.typicode.com/posts to send dummy requests, this request takes an average of about .6secs to complete, you can run the `test.php` file to test it.

###### **Scaling the solution**
In a production environment where we need to run about 100k requests, Our little PHP script wouldn't scale well.

To scale our solution, there has to be a mix of devops.

We could serialize and send the requests to a queues managed by say, Redis or Amazon SQS.
Foe example we could create 10 queues which handles 10k requests each, to be more effective we could use Load balancing to run the queues on their own individual servers, this solution should also scale well even to millions of users. 



