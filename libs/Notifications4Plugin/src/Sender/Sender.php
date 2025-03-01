<?php

namespace srag\Plugins\UdfEditor\Libs\Notifications4Plugin\Sender;

use srag\Plugins\UdfEditor\Libs\Notifications4Plugin\Exception\Notifications4PluginException;

interface Sender
{
    /**
     * Reset internal state of object, e.g. clear all data (from, to, subject, message etc.)
     * @return $this
     */
    public function reset();


    /**
     * Send the notification
     * @throws Notifications4PluginException
     */
    public function send(): void;


    /**
     * @param array|string $bcc
     * @return $this
     */
    public function setBcc($bcc);


    /**
     * @param array|string $cc
     * @return $this
     */
    public function setCc($cc);


    /**
     * @param string $from
     * @return $this
     */
    public function setFrom($from);


    /**
     * Set the message to send
     * @param string $message
     * @return $this
     */
    public function setMessage($message);


    /**
     * Set the subject for the message
     * @param string $subject
     * @return $this
     */
    public function setSubject($subject);


    /**
     * @param array|string $to
     * @return $this
     */
    public function setTo($to);
}
