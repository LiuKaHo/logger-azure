<h1 align="center"> logger-azure </h1>

<p align="center"> send your log to azure account storeage.</p>


## Installing

```shell
$ composer require liukaho/logger-azure -vvv
```

## Usage
在`config/logging.php` 的 `channel` 中添加下面代码:
```php
'azure' => [
    'driver'  => 'monolog',
    'level' => 'debug',
    'handler' => Liukaho\LoggerAzure\AzureLogHandler::class,
    'handler_with' => [
        'default_endpoints_protocol' => env('DefaultEndpointsProtocol', 'https'),
        'account_name' => env('AccountName', 'AccountName'),
        'account_key' => env('AccountKey', 'AccountKey'),
        'queue_name' => env('Azure_Storage_QueueName', 'Azure_Storage_QueueName'),
        'queue_endpoint' => env('QueueEndpoint', ''),   //中国区 azure 需要传这个
    ],
],

```
修改`.env`中`LOG_CHANNEL = azure`即可使用






## License

MIT