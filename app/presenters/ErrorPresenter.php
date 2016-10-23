<?php

namespace App\Presenters;

use Nette;
use Tracy\ILogger;

class ErrorPresenter extends BasePresenter
{
    /** @var ILogger */
    protected $logger;

    public function __construct(ILogger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param  \Exception
     */
    public function renderDefault($exception)
    {
        $this->setLayout('layout');

        if ($exception instanceof Nette\Application\BadRequestException) {
            $code = $exception->getCode();
            $this->setView(in_array($code, [403, 404, 405, 410, 500]) ? $code : '4xx');
        } else {
            $this->setView('500');
            $this->logger->mailer = array($this, 'errorMailer');
            $this->logger->log($exception, ILogger::EXCEPTION);
        }

        if ($this->isAjax()) {
            $this->payload->error = true;
            $this->terminate();
        }
    }

    public function errorMailer($message, $email)
    {
        $this->sendMail($email, 'ERROR 500 - P3 FB', ['text'=>$this->formatMessage($message)], ['Frontend','500Mail']);
    }

    protected function formatMessage($message)
    {
        if ($message instanceof \Exception || $message instanceof \Throwable) {
            while ($message) {
                $tmp[] = ($message instanceof \ErrorException
                        ? 'Fatal error: ' . $message->getMessage()
                        : get_class($message) . ': ' . $message->getMessage()
                    ) . ' in ' . $message->getFile() . ':' . $message->getLine();
                $message = $message->getPrevious();
            }
            $message = implode($tmp, "\ncaused by ");

        } elseif (!is_string($message)) {
            $message = \Tracy\Dumper::toText($message);
        }

        return trim($message);
    }
}
