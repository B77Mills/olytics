<?php
namespace Cygnus\OlyticsBundle\Event;

use Symfony\Component\HttpFoundation\ParameterBag;

class Request implements RequestInterface
{
    /**
     * The session data for this request
     *
     * @var ParameterBag
     */
    protected $event;

    /**
     * The vertical this request belongs to
     * This is used for determining entity persistence
     *
     * @var string
     */
    protected $vertical;

    /**
     * The product this request belongs to
     * This is used for determining event persistence
     *
     * @var string
     */
    protected $product;

    /**
     * Gets the event data
     *
     * @return ParameterBag
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * Sets the event data
     *
     * @param  array $eventData
     * @return self
     */
    public function setEvent(array $eventData)
    {
        $this->event = new ParameterBag($eventData);
        return $this;
    }

    /**
     * Gets the vertical
     *
     * @return string
     */
    public function getVertical()
    {
        return $this->vertical;
    }

    /**
     * Gets the product
     *
     * @return string
     */
    public function getProduct()
    {
        return $this->product;
    }
}