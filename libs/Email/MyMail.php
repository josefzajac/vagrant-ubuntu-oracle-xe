<?php

namespace App\Email;

use App\Model\Entity\MailHistory;
use App\Model\Repository\MailHistoryRepo;
use Nette\Bridges\ApplicationLatte\UIMacros;
use Nette\Utils\Strings;

class MyMail extends \Nette\Object {

    /** @var \Nette\Mail\IMailer @inject */
    public $mailer;

    /** @var \Latte\Engine */
    private $latte;

    /** @var \Kdyby\Translation\Translator */
    private $translator;

    /** @var \App\Model\Repository\MailHistoryRepo */
    private $historyRepo;


    public function __construct(\Nette\Mail\IMailer $mailer, \Kdyby\Translation\Translator $translator, \App\Model\Repository\MailHistoryRepo $historyRepo) {
        $this->mailer       = $mailer;
        $this->translator   = $translator;
        $this->historyRepo  = $historyRepo;

        $this->latte        = new \Latte\Engine();
        $this->latte->addFilter('translate', $this->translator->trans);
        UIMacros::install($this->latte->getCompiler());
    }


    public function send($from, $to, $subject, $params, $templateConfig, $attachments = []) {
        $mail = new \Nette\Mail\Message;

        $mail->setFrom($from)
            ->addTo($to)
            ->setSubject($subject);

        if($template = $this->getTemplate($templateConfig)) {
            $mail->setHtmlBody($this->latte->renderToString($template, $params));
        }

        foreach ($attachments as $a) {
            $mail->addAttachment($a);
        }

        $h = new MailHistory();
        $h->sender   = $from;
        $h->receiver = $to;
        $h->subject  = $subject;
        $h->content  = $mail->getBody();
        $h->html     = $mail->getHtmlBody();

        $this->historyRepo->repository()->save($h);

        $h->sent = !!$this->mailer->send($mail);
        $this->historyRepo->repository()->save($h);
    }

    private function getTemplate($templateConfig)
    {
        $file = __DIR__ . '/../../app/module/' . ucfirst($templateConfig[0]) . 'Module/templates/_mailTemplate/' . $templateConfig[1] . '.latte' ;

        if (file_exists($file))
            return $file;

        return false;
    }
}
