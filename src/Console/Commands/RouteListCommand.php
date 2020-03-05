<?php

namespace LaraCrafts\GeoRoutes\Console\Commands;

use Illuminate\Foundation\Console\RouteListCommand as BaseCommand;
use Illuminate\Routing\Route;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\Console\Input\InputOption;

class RouteListCommand extends BaseCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'route:list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all registered routes';

    /**
     * The router instance.
     *
     * @var \Illuminate\Routing\Router
     */
    protected $router;

    /**
     * The table geo headers for the command.
     *
     * @var array
     */
    protected $geoHeaders = ['Countries', 'Strategy', 'Callback'];

    /**
     * The columns to display when using the "compact" flag.
     *
     * @var array
     */
    protected $compactColumns = ['method', 'uri', 'action'];

    /**
     * Get the route information for a given route.
     *
     * @param  \Illuminate\Routing\Route  $route
     * @return array
     */
    protected function getRouteInformation(Route $route)
    {
        return $this->filterRoute([
            'domain' => $route->domain(),
            'method' => implode('|', $route->methods()),
            'uri'    => $route->uri(),
            'name'   => $route->getName(),
            'action' => ltrim($route->getActionName(), '\\'),
            'middleware' => $this->getMiddleware($route),
            'is_geo' => $this->isGeoRoute($route),
            'countries' => $this->getCountries($route),
            'strategy' => $this->getStrategy($route),
            'callback' => $this->getCallback($route),
        ]);
    }

    /**
     * Display the route information on the console.
     *
     * @param  array  $routes
     * @return void
     */
    protected function displayRoutes(array $routes)
    {
        if ($this->option('json')) {
            $this->line(json_encode(array_values($routes)));

            return;
        }
        
        $this->table($this->getHeaders(), $this->formatRoutes($routes));
    }

    /**
     * Format the routes output.
     *
     * @param array $routes
     *
     * @return array
     */
    protected function formatRoutes(array $routes)
    {
        foreach ($routes as $key => $route) {
            $routes[$key]['countries'] = $this->formatCountries($route['countries']);
            $routes[$key]['strategy'] = $this->formatStrategy($route['strategy']);
            $routes[$key]['callback'] = $this->formatCallback($route['callback']);
        }

        return $routes;
    }

    /**
     * Filter the route by the coutnry, strategy,
     * URI or/and name
     *
     * @param  array  $route
     *
     * @return array|null
     */
    protected function filterRoute(array $route)
    {
        if (($this->option('country') && !in_array(strtoupper($this->option('country')), $route['countries'] ?? []))
            || $this->option('strategy') && $route['strategy'] != strtolower($this->option('strategy'))
            || $this->option('geo-only') && !$route['is_geo']) {
            return;
        }

        return parent::filterRoute($route);
    }

    /**
     * Get the table headers for the visible columns.
     *
     * @return array
     */
    protected function getHeaders()
    {
        return Arr::only(
            $this->hasGeoOption() ? array_merge($this->headers, $this->geoHeaders) : $this->headers,
            array_keys($this->getColumns())
        );
    }

    /**
     * Get the column names to show (lowercase table headers).
     *
     * @return array
     */
    protected function getColumns()
    {
        $availableColumns = array_map(
            'strtolower',
            $this->hasGeoOption() ? array_merge($this->headers, $this->geoHeaders) : $this->headers
        );

        if ($this->option('compact')) {
            return array_intersect($availableColumns, $this->compactColumns);
        }

        if ($columns = $this->option('columns')) {
            return array_intersect($availableColumns, $this->parseColumns($columns));
        }

        return $availableColumns;
    }

    /**
     * Determine if the given route is a georoute.
     *
     * @param \Illuminate\Routing\Route $route
     *
     * @return boolean
     */
    protected function isGeoRoute(Route $route)
    {
        $validator = Validator::make($route->getAction('geo') ?? [], [
            'countries' => 'required|array|min:1',
            'countries.*' => 'string|min:2|max:2',
            'strategy' => 'required|in:allow,deny',
        ]);

        return $validator->passes();
    }

    /**
     * Get the list of countries covered by
     * the geo-constraint for the given route.
     *
     * @param \Illuminate\Routing\Route $route
     *
     * @return array|null
     */
    protected function getCountries(Route $route)
    {
        return $route->getAction('geo')['countries'];
    }

    /**
     * Get the route geo strategy.
     *
     * @param \Illuminate\Routing\Route $route
     *
     * @return string|null
     */
    protected function getStrategy(Route $route)
    {
        return $route->getAction('geo')['strategy'];
    }

    /**
     * Get the route geo callback.
     *
     * @param \Illuminate\Routing\Route $route
     *
     * @return string|null
     */
    protected function getCallback(Route $route)
    {
        return $route->getAction('geo')['callback'][0];
    }
    
    /**
     * Format the route strategy output.
     *
     * @param string|null $strategy
     *
     * @return string
     */
    protected function formatStrategy($strategy)
    {
        $strategy = strtolower($strategy);

        if ($strategy == 'allow') {
            return '<fg=green;options=bold>Allow</>';
        }

        if ($strategy == 'deny') {
            return '<fg=red;options=bold>Deny</>';
        }

        return '<fg=yellow>None</>';
    }

    /**
     * Format the route countries output.
     *
     * @param array|null $countries
     *
     * @return string
     */
    protected function formatCountries($countries)
    {
        return $countries ? implode(', ', $countries) : '<fg=yellow>None</>';
    }

    /**
     * Format the route callback output.
     *
     * @param string|null $callback
     *
     * @return string
     */
    protected function formatCallback($callback)
    {
        return !is_null($callback) ? str_replace('::', '@', $callback) : '<fg=yellow>None</>';
    }

    /**
     * Determine if any geo option exists.
     *
     * @return boolean
     */
    protected function hasGeoOption()
    {
        return $this->option('geo') || $this->option('geo-only')
            || $this->option('country') || $this->option('strategy');
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return array_merge(parent::getOptions(), [
            ['geo', 'g', InputOption::VALUE_NONE, 'Show the routes geo specifications'],
            ['geo-only', null, InputOption::VALUE_NONE, 'Display GeoRoutes only'],
            ['strategy', null, InputOption::VALUE_REQUIRED, 'Display only the routes that have a given strategy'],
            ['country', null, InputOption::VALUE_REQUIRED, 'Display only the routes that have a given country'],
        ]);
    }
}
