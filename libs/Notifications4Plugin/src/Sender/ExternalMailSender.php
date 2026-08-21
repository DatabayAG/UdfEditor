<?php

namespace ILIAS\Plugin\UdfEditor\Libs\Notifications4Plugin\Sender;

use ILIAS\DI\Container;
use ilMimeMail;
use ILIAS\Plugin\UdfEditor\Libs\Notifications4Plugin\Exception\Notifications4PluginException;
use ILIAS\Plugin\UdfEditor\Libs\Notifications4Plugin\Utils\Notifications4PluginTrait;

class ExternalMailSender implements Sender
{
    use Notifications4PluginTrait;

    /**
     * @var array
     */
    protected $attachments = [];
    /**
     * @var string|array
     */
    protected $bcc = [];
    /**
     * @var string|array
     */
    protected $cc = [];
    /**
     * @var string
     */
    protected $from = "";
    /**
     * @var ilMimeMail
     */
    protected $mailer;
    /**
     * @var string
     */
    protected $message = "";
    /**
     * @var string
     */
    protected $subject = "";

    private Container $dic;


    /**
     * @param string $from E-Mail from address. If omitted, the ILIAS setting "external noreply address" is used
     * @param string|array $to E-Mail address or array of addresses
     */
    public function __construct(string $from = "", protected $to = "")
    {
        global $DIC;
        $this->dic = $DIC;
        $this->from = $from;
        $this->mailer = new ilMimeMail();
    }


    /**
     * Add an attachment
     * @param string $file Full path of the file to attach
     * @return $this
     */
    public function addAttachment($file)
    {
        if (is_file($file)) {
            $this->attachments[] = $file;
        }

        return $this;
    }


    /**
     * @return array|string
     */
    public function getBcc()
    {
        return $this->bcc;
    }


    public function setBcc($bcc)
    {
        $this->bcc = $bcc;
    }


    /**
     * @return array|string
     */
    public function getCc()
    {
        return $this->cc;
    }


    public function setCc($cc)
    {
        $this->cc = $cc;

        return $this;
    }


    /**
     * @return string
     */
    public function getFrom()
    {
        return $this->from;
    }


    public function setFrom($from)
    {
        $this->from = $from;

        return $this;
    }


    /**
     * @return array|string
     */
    public function getTo()
    {
        return $this->to;
    }


    public function setTo($to)
    {
        $this->to = $to;

        return $this;
    }


    public function reset()
    {
        $this->from = "";
        $this->to = "";
        $this->subject = "";
        $this->message = "";
        $this->attachments = [];
        $this->cc = [];
        $this->bcc = [];
        $this->mailer = new ilMimeMail();

        return $this;
    }


    public function send(): void
    {
        $from = $this->from ?: $this->dic->ilias()->getSetting("mail_external_sender_noreply");
        $this->mailer->From($this->dic->mailMimeSenderFactory()->userByEmailAddress($from));

        $this->mailer->To($this->to);

        $this->mailer->Cc($this->cc);
        $this->mailer->Bcc($this->bcc);

        $this->mailer->Subject($this->subject);

        $this->mailer->Body($this->message);

        foreach ($this->attachments as $attachment) {
            $this->mailer->Attach($attachment);
        }

        $sent = $this->mailer->Send();

        if (!$sent) {
            throw new Notifications4PluginException("Mailer not returns true");
        }
    }


    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }


    public function setSubject($subject)
    {
        $this->subject = $subject;

        return $this;
    }
}
