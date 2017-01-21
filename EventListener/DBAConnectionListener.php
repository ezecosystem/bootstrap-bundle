<?php
namespace xrow\bootstrapBundle\EventListener;


use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Doctrine\DBAL\Connection;
use Monolog\Logger;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Increase DB SESSION_TIMEOUT for migration executed from php (cli) app/console to prevent
 * "2006 MySQL server has gone away" error on connection timeouts
 */
class DBAConnectionListener
{
    use ContainerAwareTrait;

    protected $logger;

    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Modify DB settings on command call
     *
     * @param ConsoleCommandEvent $event            
     */
    public function onConsoleCommand(ConsoleCommandEvent $event)
    {
        $connections = $this->container->getParameter("doctrine.connections");
        if (PHP_SAPI === "cli") {
            foreach ($connections as $connection) {
                $this->setDbConnectionTimeout($this->container->get($connection));
            }
            
            $this->logger->info($event->getCommand()
                ->getName() . ': Modified DB SESSION_TIMEOUT');
        }
    }

    /**
     * Modify default DB connection and set session wait_timeout
     *
     * @param object $connection
     *            (description)
     */
    public function setDbConnectionTimeout(Connection $connection)
    {
        $connection->query('SET SESSION wait_timeout=86400;');
    }
}