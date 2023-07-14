<?php

class Application_Task_TaskConfig
{
    const SCHEDULE_DEFAULT = "*/1 * * * *";

    /** @var string */
    protected $name;

    /** @var string */
    protected $class;

    /** @var bool */
    protected $enabled;

    /** @var string */
    protected $schedule;

    /** @var bool */
    protected $preventOverlapping;

    /** @var array */
    protected $options;

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @param string $class
     */
    public function setClass($class)
    {
        $this->class = $class;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * @param bool $enabled
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
    }

    /**
     * @return string
     */
    public function getSchedule()
    {
        return $this->schedule;
    }

    /**
     * @param string $schedule
     */
    public function setSchedule($schedule)
    {
        $this->schedule = $schedule;
    }

    /**
     * @return bool
     */
    public function isPreventOverlapping()
    {
        return $this->preventOverlapping;
    }

    /**
     * @param bool $preventOverlapping
     */
    public function setPreventOverlapping($preventOverlapping)
    {
        $this->preventOverlapping = $preventOverlapping;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param array $options
     */
    public function setOptions($options)
    {
        $this->options = $options;
    }
}
