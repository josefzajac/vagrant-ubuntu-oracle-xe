<?php

namespace App\AdminModule\Presenters;

use App\Component\Form;
use App\Model\Entity\Participation;
use App\Model\Entity\Vote;
use App\Model\Repository\Ips;
use App\Model\Repository\Participations;
use Nette\Forms\Controls\SubmitButton;
use Nette\Utils\Image;

class ParticipantPresenter extends AdminFormPresenter
{
    const ENTITY_NAME = 'App\Model\Entity\Participation';
    const FORM_NAME   = 'App\Component\Form\ParticipationForm';

    /**
     * @var Participations
     */
    protected $repo;

    protected function startup()
    {
        parent::startup();
        $this->repo = $this->participations;
    }

    public function actionDefault($page = 0, $limit = 10, $competition_id = null, $deleted = false)
    {
        $orderBy = ['votes_int' => 'DESC', 'created' => 'ASC'];
        if(null !== $competition_id) {
            $competition = $this->competitions->repository()->findOneBy(['id'=>$competition_id]);
            $this->template->total = $total = $this->repo->countBy([
                    'competition'=>$competition,
                    'deleted'=>$deleted]);
            $this->template->all_items = $this->repo->repository()->findBy([
                'competition'=>$competition,
                'deleted'=>$deleted],
                $orderBy, $limit, ($page) * $limit
            );
        } else {
            $this->template->total = $total = $this->repo->countBy(['deleted'=>$deleted]);
            $this->template->all_items = $this->repo->repository()->findBy(['deleted'=>$deleted],
                $orderBy, $limit, ($page) * $limit);
        }
    }

    public function createComponentEditItemForm()
    {
        $formLabel = $this->getFormName();
        $form      = new $formLabel($this, 'editItemForm');
        $form->create();
        $form->addSubmit('send', 'Uložit')
            ->onClick[] = [$this, 'doItemForm'];
        $form->addSubmit('rotate_left', 'Otocit 90° doleva')
            ->onClick[] = [$this, 'rotate'];
        $form->addSubmit('rotate_right', 'Otocit 90° doprava')
            ->onClick[] = [$this, 'rotate'];

        return $form;
    }

    public function actionVotes($id)
    {
        $this->template->participant = $this->repo->repository()->findOneBy(['id'=>$id]);

        $this['hackForm']->setItem($this->template->participant);

        $history = $labels = [];
        for ($i = floor($this->template->participant->getVotes()->first()->formatTimestamp('U') / 86400);
            $i <= floor($this->template->participant->getVotes()->last()->formatTimestamp('U') / 86400);
            $i++) {
                $history[$i] = 0;
                $labels[]  = date('j.n', $i * 86400);
        }

        foreach ($this->template->participant->getVotes() as $v) {
            $history[floor($v->formatTimestamp('U') / 86400)] += $v->getIncrement();
        }

        $this->template->graphValues = $history;
        $this->template->graphLabels = $labels;
    }

    public function createComponentHackForm()
    {
        $form = new Form\VoteHackForm($this, 'hackForm');
        $form->create();
        $form->addSubmit('hack', 'Pridej hlasy')
            ->onClick[] = [$this, 'hackVote'];

        return $form;
    }

    public function hackVote(SubmitButton $button)
    {
        $data    = $button->getForm()->getValues();
        $participation    = $this->repo->repository()->findOneBy(['id' => $data['id']]);

        $vote = new Vote();
        $vote->setIncrement($data['value']);
        $vote->setCompetition($participation->getCompetition());
        $vote->setIp(null);
        $vote->setSpecial(true);
        $vote->setParticipation($participation);

        // Increase participation vote integer
        $participation->increaseVotes($vote->getIncrement());

        // Save vote
        try {
            $this->repo->entityManager()->persist($vote);
            $this->repo->entityManager()->persist($participation);
            $this->repo->entityManager()->flush();
        } catch (Exception $e) {
            throw $e;
        }

        $this->redirect('votes', ['id' => $participation->getId()]);
    }

    public function renderDecline($id)
    {
        $this->template->item = $this->repo->repository()->findOneBy(['id'=>$id]);

        $this['declineForm']->setItem($this->template->item);
    }

    public function createComponentDeclineForm()
    {
        $form = new Form\DeclineForm($this, 'declineForm');
        $form->create();
        $form->addSubmit('accept', 'Accept')
            ->onClick[] = [$this, 'acceptSubmit'];
        $form->addSubmit('decline', 'Decline')
            ->onClick[] = [$this, 'acceptSubmit'];
        $form->addSubmit('rotate_left', 'Otocit 90° doleva')
            ->onClick[] = [$this, 'rotate'];
        $form->addSubmit('rotate_right', 'Otocit 90° doprava')
            ->onClick[] = [$this, 'rotate'];

        return $form;
    }

    public function acceptSubmit(SubmitButton $button)
    {
        $data    = $button->getForm()->getValues();
        $item    = $this->repo->repository()->findOneBy(['id' => $data['id']]);
        if ($item instanceof ParticipationText) {
            $item->setName($data['name']);
            $item->setText($data['text']);
        }

        try {
            $item->approved = $button->getValue() === 'Accept';
            $item->new      = false;
            if (!$item->getApproved()) {
                $item->deleted_when = new \DateTime();
                $item->deleted_who  = $this->getUser()->id;
                $item->deleted = 1;
            }
            $this->repo->repository()->save($item);
            $this->flashMessage('tr.admin.item_updated');

            if (!$item->getApproved() && $data['message']) {
                $this->sendMail(
                    $item->getUser()->getEmail(),
                    'tr.mail.subject_decline',
                    ['name'    => $item->name,
                     'message' => $data['message']],
                    'declineMail'
                );
            }

        } catch (Exception $e) {
            $this->flashMessage($e->getMessage(), 'error');
        }
        $this->redirect('default');
    }

    public function rotate(SubmitButton $button)
    {
        $data    = $button->getForm()->getValues();
        $item    = $this->repo->repository()->findOneBy(['id' => $data['id']]);

        try {
            foreach ($item->getImages() as $image ){
                $src = $this->parameters['wwwDir'] . $image->namespace.'/'.$image->name;

                $im = Image::fromFile($src);
                $im->rotate($button->getName() === 'rotate_left' ? '90' : '270', 0);
                $im->save($src);

                $w = $image->width;
                $image->width  = $image->height;
                $image->height = $w;

                $this->repo->entityManager()->persist($image);
                $this->repo->entityManager()->flush();
            }
        } catch (Exception $e) {
            $this->flashMessage($e->getMessage(), 'error');
        }
        $this->redirect('this');
    }

    protected function getEntityName()
    {
        return self::ENTITY_NAME;
    }

    protected function getFormName()
    {
        return self::FORM_NAME;
    }
}
