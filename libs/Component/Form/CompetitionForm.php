<?php

namespace App\Component\Form;

use App\Model\Entity\Competition;
use Nette\Application\UI\Form;

class CompetitionForm extends BaseForm
{
    /**
     * @var Competition
     */
    protected $item;

    protected function _create()
    {
        \Nella\Forms\DateTime\DateInput::register();
        \Nella\Forms\DateTime\DateTimeInput::register();

        $this->addText('name', 'Name');
        $this->addText('slug', 'Slug');
        $this->addText('fb_app_id', 'fb_app_id');
        $this->addText('fb_secret', 'fb_secret');
        $this->addCheckbox('enabled', 'Zapnuto');
        $this->addDateTime('start', 'Zacatek', 'j.n.Y', 'G:i');
        $this->addDateTime('vote_start', 'Zacatek hlasovani', 'j.n.Y', 'G:i');
        $this->addDateTime('upload_end', 'Konec nahravani', 'j.n.Y', 'G:i');
        $this->addDateTime('end', 'Konec', 'j.n.Y', 'G:i');
        $this->addSelect('voting_type', 'Typ hlasovani', Competition::$voting_types);
        $this->addSelect('voting_period', 'Interval hlasovani', Competition::$voting_periods);
        $this->addUpload('image', 'Hlavni banner');
        $this->addUpload('square', 'Ctvercovy (250x250)');

        $this->addText('participants_limit', 'Pocet fotografii pro uzivatele');
        $this->addText('short_description', 'Popisek - 1. veta v galerii');
        $this->addTextArea('landing', 'Uvodni stranka');
        $this->addTextArea('info', 'Informace');
        $this->addTextArea('rules', 'Pravidla');
        $this->addText('event_url', 'FB event url');
    }

    protected function _loadItem()
    {
        $data['name']      = $this->item->name;
        $data['slug']      = $this->item->slug;
        $data['fb_app_id'] = $this->item->fb_app_id;
        $data['fb_secret'] = $this->item->fb_secret;
        $data['start']     = $this->item->startDate;
        $data['end']       = $this->item->endDate;
        $data['enabled']   = $this->item->enabled;

        $data['voting_type']        = $this->item->getVotingType();
        $data['voting_period']      = $this->item->getVotingPeriod();
        $data['vote_start']         = $this->item->voteStartDate;
        $data['upload_end']         = $this->item->getUploadEndDate();
        $data['participants_limit'] = $this->item->participants_limit;
        $data['short_description']  = $this->item->short_description;
        $data['landing']            = $this->item->landing;
        $data['info']               = $this->item->info;
        $data['rules']              = $this->item->rules;
        $data['event_url']          = $this->item->event_url;

        parent::setValues($data);
    }

    protected function _process()
    {
        $this->item->name      = $this['name']->getValue();
        $this->item->slug      = $this['slug']->getValue();
        $this->item->fb_app_id = $this['fb_app_id']->getValue();
        $this->item->fb_secret = $this['fb_secret']->getValue();
        $this->item->startDate = $this['start']->getValue();
        $this->item->endDate   = $this['end']->getValue();
        $this->item->enabled   = $this['enabled']->getValue();

        $this->item->setVotingType($this['voting_type']->getValue());
        $this->item->setVotingPeriod($this['voting_period']->getValue());
        $this->item->voteStartDate      = $this['vote_start']->getValue();
        $this->item->uploadEndDate      = $this['upload_end']->getValue();
        $this->item->participants_limit = $this['participants_limit']->getValue();
        $this->item->short_description  = $this['short_description']->getValue();
        $this->item->landing            = $this['landing']->getValue();
        $this->item->info               = $this['info']->getValue();
        $this->item->rules              = $this['rules']->getValue();
        $this->item->event_url          = $this['event_url']->getValue();
    }
}
