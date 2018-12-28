<?php
/**
 * Created by PhpStorm.
 * User: Liukaho
 * Date: 2018-12-28
 * Time: 10:27
 */

namespace Liukaho\LoggerAzure;


use MicrosoftAzure\Storage\Common\Exceptions\ServiceException;
use Monolog\Handler\AbstractHandler;
use Monolog\Logger;
use MicrosoftAzure\Storage\Queue\QueueRestProxy;

class AzureLogHandler extends AbstractHandler
{

    protected $queue_name;
    protected $my_formatter;
    protected $connection_string;
    protected $client = null;


    public function __construct(
        string $default_endpoints_protocol,
        string $account_name,
        string $account_key,
        string $queue_name,
        string $queue_endpoint = null,
        string $formatter = null,
        int $level = Logger::DEBUG,
        bool $bubble = true
    )
    {
        parent::__construct($level, $bubble);
        $this->queue_name = $queue_name;
        $array[] = "DefaultEndpointsProtocol=" . $default_endpoints_protocol;
        $array[] = "AccountName=" . $account_name;
        $array[] = "AccountKey=" . $account_key;
        !empty($queue_endpoint) && $array[] = 'QueueEndpoint=' . $queue_endpoint;
        $this->connection_string = implode(';', $array);
        //$this->client = QueueRestProxy::createQueueService($connection_string);
        $this->my_formatter = $formatter;

    }

    public function handle(array $record)
    {
        if (!$this->isHandling($record)) {
            return false;
        }
        $record = $this->processRecord($record);
        $record['formatted'] = $this->getFormatter()->format($record);
        $this->write($record);

        return true;
    }

    public function getClient()
    {
        return $this->client ?? $this->client = QueueRestProxy::createQueueService($this->connection_string);
    }


    protected function processRecord(array $record)
    {
        if ($this->processors) {
            foreach ($this->processors as $processor) {
                $record = call_user_func($processor, $record);
            }
        }

        return $record;
    }

    protected function write(array $record)
    {
        $line = $this->messageToLine($record);
        $this->sendMessageToQueue($line);
    }

    protected function messageToLine(array $record)
    {
        return $record['formatted'];
    }


    public function getFormatter()
    {
        return empty($this->my_formatter) ? parent::getFormatter() : new $this->my_formatter();
    }

    public function getDefaultFormatter()
    {
        return new Formatter();
    }

    private function sendMessageToQueue(string $message)
    {
        try {
            $this->getClient()->createMessage($this->queue_name, $message);
        }catch (ServiceException $exception){
            $code = $exception->getCode();
            $error_message = $exception->getMessage();
            $path = base_path('storage/logs/azure_logger.log');
            file_put_contents($path, $code.": ".$error_message.PHP_EOL);
        }
    }

}