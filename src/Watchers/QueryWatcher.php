<?php

namespace Metrichawk\MetrichawkLaravel\Watchers;

use Illuminate\Database\Events\QueryExecuted;
use Metrichawk\MetrichawkLaravel\MetrichawkLaravel;

class QueryWatcher extends Watcher
{
    /**
     * @param \Illuminate\Contracts\Foundation\Application $app
     */
    public function register($app)
    {
        $app['events']->listen(QueryExecuted::class, [$this, 'recordQuery']);
    }

    /**
     * @param QueryExecuted $event
     */
    public function recordQuery(QueryExecuted $event)
    {
        $duration = $event->time;

        MetrichawkLaravel::recordQuery([
            'connection_name' => $event->connectionName,
            'bindings' => json_encode($event->bindings),
            //'sql' => $this->replaceBindings($event),
            'sql' => $event->sql,
            'duration' => $duration,
//            'slow' => isset($this->options['slow']) && $time >= $this->options['slow'],
            'hash' => $this->familyHash($event),
        ]);
    }

    /**
     * Get the tags for the query.
     *
     * @param \Illuminate\Database\Events\QueryExecuted $event
     * @return array
     */
    protected function tags($event)
    {
        return isset($this->options['slow']) && $event->time >= $this->options['slow'] ? ['slow'] : [];
    }

    /**
     * Calculate the family look-up hash for the query event.
     *
     * @param \Illuminate\Database\Events\QueryExecuted $event
     * @return string
     */
    public function familyHash($event)
    {
        // xxHash is an extremely fast hashing algorithm that is not designed for cryptographic purposes
        return hash('xxh128', $event->sql);
    }

    /**
     * Format the given bindings to strings.
     *
     * @param \Illuminate\Database\Events\QueryExecuted $event
     * @return array
     */
    protected function formatBindings($event)
    {
        return $event->connection->prepareBindings($event->bindings);
    }

    /**
     * Replace the placeholders with the actual bindings.
     *
     * @param \Illuminate\Database\Events\QueryExecuted $event
     * @return string
     */
    public function replaceBindings($event)
    {
        $sql = $event->sql;

        foreach ($this->formatBindings($event) as $key => $binding) {
            $regex = is_numeric($key)
                ? "/\?(?=(?:[^'\\\']*'[^'\\\']*')*[^'\\\']*$)/"
                : "/:{$key}(?=(?:[^'\\\']*'[^'\\\']*')*[^'\\\']*$)/";

            if ($binding === null) {
                $binding = 'null';
            } elseif (!is_int($binding) && !is_float($binding)) {
                $binding = $event->connection->getPdo()->quote($binding);
            }

            $sql = preg_replace($regex, $binding, $sql, 1);
        }

        return $sql;
    }
}
