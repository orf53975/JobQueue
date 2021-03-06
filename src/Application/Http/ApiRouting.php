<?php

namespace JobQueue\Application\Http;

use function FastRoute\cachedDispatcher;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;

final class ApiRouting
{
    /**
     *
     * @return Dispatcher
     */
    public static function create(): Dispatcher
    {
        return cachedDispatcher(function (RouteCollector $r)
        {
            $r->get(  '/tasks',             ListTasks::class );
            $r->post( '/tasks',             AddTask::class   );
            $r->get(  '/task/{identifier}', ShowTask::class  );

        }, [
            'cacheFile' => self::getRoutingCachePath(),
            'cacheDisabled' => 'prod' === getenv('JOBQUEUE_ENV') ? false : true,
        ]);
    }

    /**
     *
     * @return string
     */
    private static function getRoutingCachePath(): string
    {
        // Get dir path from environment variables
        if (!$dir = (string) getenv('JOBQUEUE_CACHE_PATH')) {
            $dir = sys_get_temp_dir();
        }

        return sprintf('%s/jobqueue_routing.php', $dir);
    }
}
