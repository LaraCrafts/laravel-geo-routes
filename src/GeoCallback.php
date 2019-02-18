<?php

namespace LaraCrafts\GeoRoutes;

class GeoCallback
{
    #use Serializable;

    /**
     * The callback's name
     *
     * @var string
     */
    protected $name;

    /**
     * The callback's action
     *
     * @var callable
     */
    protected $action;

    /**
     * The callback arguments
     *
     * @var array
     */
    protected $arguments;

    /**
     * Create a new GeoCallback instance
     *
     * @param string $name
     * @param callable $action
     * @param array|null $arguments
     *
     * @return void
     */
	public function __construct(string $name, callable $action, array $arguments = null)
	{
        $this->name = $name;
        $this->action = $action;
        $this->arguments = $arguments;
    }

    /**
     * Call the callback
     *
     * @return void
     */
    public function __invoke()
    {
        return call_user_func_array($this->action, $this->arguments ?? []);
    }

    /**
     * Get the callback's name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the callback's name
     *
     * @param string $name
     *
     * @return $this
     */
    public function setName(string $name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get the callback's action
     *
     * @return callable
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Set the callback's action
     *
     * @param callable $action
     *
     * @return $this
     */
    public function setAction(callable $action)
    {
        $this->action = $action;

        return $this;
    }

    /**
     * Set the callback arguments
     *
     * @param array $arguments
     *
     * @return $this
     */
    public function setArguments(array $arguments)
    {
        $this->arguments = $arguments;

        return $this;
    }

}
