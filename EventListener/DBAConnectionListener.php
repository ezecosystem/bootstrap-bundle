<?php
namespace xrow\bootstrapBundle\EventListener;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;
use Exception;
use Monolog\Logger;

/**
 * Increase DB SESSION_TIMEOUT for migration executed from php (cli) app/console to prevent
 * "2006 MySQL server has gone away" when switching between two databases
 */
class DBAConnectionListener
{
    protected $container;
    protected $connection;
    protected $connection_cluster;
    protected $logger;

    public function __construct( ContainerInterface $container, Logger $logger )
    {
        $this->container            = $container;
        $this->connection           = $container->get(sprintf('doctrine.dbal.%s_connection', 'default'));
        $this->connection_cluster   = $container->get(sprintf('doctrine.dbal.%s_connection', 'cluster'));
        $this->logger = $logger;
    }

    /**
     * Modify DB settings on command call
     *
     * @param      ConsoleCommandEvent $event  (description)
     */
    public function onConsoleCommand( ConsoleCommandEvent $event )
    {
       $command = $event->getCommand();
       $name = $command->getName();
       if( $name == "kaliop:migration:migrate") {
            // Increase SESSION_TIMEOUT for "default" database
            $this->setDbConnectionTimeout( $this->connection );

            // Increase SESSION_TIMEOUT for  "cluster" database
            $this->setDbConnectionTimeout( $this->connection_cluster );

            // Save comment in log
            $this->logger->info($name.': Modified DB SESSION_TIMEOUT');
       }
    }

    /**
     * Log result on after command Terminate
     *
     * @param      ConsoleTerminateEvent $event  (description)
     */
    public function onConsoleTerminate( ConsoleTerminateEvent $event )
    {
        $command = $event->getCommand();
        $name = $command->getName();
        if( $name == "kaliop:migration:migrate") {
            // Save comment in log
            $this->logger->info( 'Migration terminated: ' . $name );
        }
    }

    /**
     * Modify default DB connection and set session wait_timeout
     *
     * @param      object  $connection  (description)
     */
    public function setDbConnectionTimeout( $connection )
    {
        $params = $connection->getParams();
        $params["driverOptions"][1002] = 'SET SESSION wait_timeout=86400;';
        // $this->logger->info(json_encode($params));
        $connection->__construct(
            $params, $connection->getDriver(), $connection->getConfiguration(),
            $connection->getEventManager()
        );
    }
}