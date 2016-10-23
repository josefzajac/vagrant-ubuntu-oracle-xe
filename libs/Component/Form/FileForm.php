<?php

namespace App\Component\Form;

use Nette\Application\UI\Form;
use Nette\Http\FileUpload;

class FileForm extends Form
{

    public function __construct()
    {
        parent::__construct();

        $this
            ->addUpload('file', 'File:');
        $this->addSubmit('submit', 'Send');

        $this->onSuccess[] = callback($this, 'success');
    }


    public function success(Form $form)
    {
        $values = $form->getValues();

        /** @var FileUpload $file */
        $file = $values['file'];
        var_dump($file);
        var_dump($file->isImage());
        var_dump($file->isOk());
        die;
    }
}
