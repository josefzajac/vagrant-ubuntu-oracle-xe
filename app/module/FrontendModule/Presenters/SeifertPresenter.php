<?php

namespace App\FrontendModule\Presenters;

use App\Component\Form\FrontUploadForm;
use App\Component\Form\FrontUploadTextForm;
use App\Model\Entity\Author;
use App\Model\Entity\Competition;
use App\Model\Entity\Image;
use App\Model\Entity\IpLog;
use App\Model\Entity\Participation;
use App\Model\Entity\ParticipationText;
use App\Model\Entity\User;
use App\Model\Entity\Vote;
use App\Model\Repository\Ips;
use App\Model\Repository\IpLogs;
use App\Model\Repository\Users;
use App\Model\Repository\Participations;
use App\Model\Repository\Votes;
use Nette;
use Nette\Application\UI\Form;
use Nette\Http\FileUpload;

class SeifertPresenter extends CompetitionPresenter
{

    /** @persistent */
    public $competition_slug = 'seifertuv-zizkov';

    public function startup()
    {
        parent::startup();
    }

    /**
     * Common render method.
     */
    protected function beforeRender()
    {
        parent::beforeRender();

        if ($this->getUser()->isLoggedIn()) {
            $user = $this->users->repository()->findOneById($this->template->userData['id']);
            $approvedParticipants = 0;

            $this->template->userCanAddParticipant = count($approvedParticipants) < $this->competition->getParticipantsLimit();
        }

        if ($this->competition->getEndDate() < new \DateTime()) {
            $this->template->userCanAddParticipant = false;
        }

        if (null !== $this->competition->getUploadEndDate() && $this->competition->getUploadEndDate() < new \DateTime()) {
            $this->template->userCanAddParticipant = false;
        }
    }

    public function actionLanding()
    {
        $this->template->latest = array_filter($this->participations->getForFront($this->competition), function($x) {
            return get_class($x) == 'App\Model\Entity\Participation';
        });
        $this->template->boxesCount = count($this->participationTexts->getForFront($this->competition));
//        $this->flashMessage('testInfo');
//        $this->flashMessage('testError', 'danger');
    }

    public function actionRegister()
    {
        if ($this->getUser()->isLoggedIn()) {
            $this->redirect('landing');
        }
    }

    public function renderGallery($show_id = null)
    {
        if (new \DateTime() < $this->competition->getStartDate()) {
            $this->setView('countdown');
            $this->template->userCanAddParticipant = false;
            if ($this->getUser()->isInRole(User::ADMIN)) {
//                $this->setView($originalView);
            }
        }

        $boxes = $this->participationTexts->getForFront($this->competition);
        if ($this->competition->getEndDate() < new \DateTime()) {
            $this->setView('end');
            $this->template->winners = array_slice($boxes,0,  3);
            $this->template->boxes   = array_slice($boxes, 3);

            return;
        }

        $this->template->thisMorningAsU = $thisMorningAsU = (new \DateTime('@'.mktime('0','0')))->format('U');
        $this->template->now            = $now            = new \DateTime();

        $this->template->boxes = $boxes;
        $this->template->votes = $this->votes->getForFront($this->competition, $this->ips->getRemoteIp());
        foreach ($this->template->boxes as $participant) {
            foreach ($this->template->votes as $vote) {
                if (
                    ($vote->getParticipation() == $participant && $this->competition->isVotingType(Competition::VOTING_TYPE_MORE_PART_IN_PERIOD)
                        || $this->competition->isVotingType(Competition::VOTING_TYPE_ONE_PART_IN_PERIOD))
                    && ($futureVote = $vote->getTimestamp()->add(new \DateInterval($this->competition->getVotingPeriod()))) > $now) {

                    switch (true) {
                        case ($vote->getTimestamp()->format('U') - $thisMorningAsU) < (60*60*24):
                            $participant->voted = $this->translator->trans('tr.modal.next_today',
                                ['hours'=>$futureVote->format('H:i')]);
                            break;
                        case ($vote->getTimestamp()->format('U') - $thisMorningAsU) < (60*60*24*2):
                            $participant->voted = $this->translator->trans('tr.modal.next_tomorrow',
                                ['hours'=>$futureVote->format('H:i')]);
                            break;
                        default: $participant->voted = $this->translator->trans('tr.modal.next_next_day',
                            ['hours'=>$futureVote->format('d.n. H:i')]);
                            break;
                    }
                }
            }
        }

        $this->template->showPart = null;
        if ($show_id && $load = $this->participationTexts->repository()->findOneBy(['id' => $show_id]))
        {
            $this->template->showPart = $load;
        }
    }


    //-----------------------  NEW PARTICIPANTS TEXT FORM

    public function createComponentNewUploadTextForm()
    {
        $form = new FrontUploadTextForm();
        $form->create();
        $form->addSubmit('send', $this->translator->trans('tr.modal.add'));
        $form->onSuccess[] = [$this, 'uploadForm'];

        return $form;
    }

    public function uploadForm(Form $form, $values)
    {
        $part = new ParticipationText();
        $part->setCompetition($this->competition);
        $user = $this->users->repository()->findOneById($this->getUser()->getId());
        $part->setUser($user);
        try {
            $form->process($part);

            $this->participationTexts->entityManager()->persist($part);
            $this->participationTexts->entityManager()->flush();

            $image = new Image();
            if ($values->image->isOk()) {
                if ($values->image->isImage()) {
                    $image = $this->uploadImage($values->image, $image, $this->competition->getId() . '_' . $part->getId());

                    $size = $values->image->getImageSize();
                    $image->setWidth($size[0]);
                    $image->setHeight($size[1]);

                    $part->getImages()->add($image);
                } else {
                    $name = \Nette\Utils\Strings::webalize($values->image->name, '.', true);
                    $image->path = '/assets/uploads/';
                    $values->image->move($this->parameters['uploads_path'] . '/assets/uploads/'.$name);
                    $image->setName($name);
                }
                $image->setParticipation($part);
                $part->getImages()->add($image);
                $this->participationTexts->entityManager()->persist($image);
                $this->participationTexts->entityManager()->flush();
            }

            $this->sendMail(
                $this->parameters['admin_mail'],
                'Novy text ' . $values['name'],
                ['name' => $values['name'],
                    'id'   => $part->getId()
                ],
                'newParticipantTextMail'
            );

            $parentEmail = $user instanceof Author ? $user->getParentEmail() : $user->getEmail();
            // schvaleni rodicum
            $this->sendMail(
                $parentEmail,
                'Novy text ' . $values['name'],
                ['name' => $values['name'],
                    'id'   => $part->getId()
                ],
                'newParticipantTextParentMail'
                ,[$this->parameters['appDir'].'/../files/1617_SZ_souhlas_zakonneho_zastupce.pdf']
            );

            $this->flashMessage('tr.competition.text_saved');
        } catch (Exception $e) {
            $this->flashMessage('tr.competition.part_error', 'error');
        }

        $this->redirect('gallery');
    }

    //-----------------------  INJECTS

}
