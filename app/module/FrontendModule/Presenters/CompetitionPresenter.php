<?php

namespace App\FrontendModule\Presenters;

use App\Component\Form\FrontUploadForm;
use App\Model\Entity\Competition;
use App\Model\Entity\Image;
use App\Model\Entity\IpLog;
use App\Model\Entity\Participation;
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

class CompetitionPresenter extends FrontendPresenter
{

    /** @persistent */
    public $competition_slug;

    /**
     * @var Competition
     */
    protected $competition;

    /**
     * @var Ips
     */
    protected $ips;

    /**
     * @var IpLogs
     */
    protected $ipLogs;

    /**
     * @var Votes
     */
    protected $votes;

    public function startup()
    {
        parent::startup();

        $this->competition_slug = $this->getParameter('competition_slug');
        $this->template->competition = $this->competition = $this->competitions->findOneBySlug($this->competition_slug);

        $this->template->userCanAddParticipant = true;
    }

    /**
     * Common render method.
     */
    protected function beforeRender()
    {
        parent::beforeRender();

        if ($this->getUser()->isLoggedIn()) {
            $user = $this->users->repository()->findOneById($this->template->userData['id']);
            $approvedParticipants = $this->participations->getUserParticipantApproved($this->competition, $user);

            $this->template->userCanAddParticipant = count($approvedParticipants) < $this->competition->getParticipantsLimit();
        }

        if ($this->competition->getEndDate() < new \DateTime()) {
            $this->template->userCanAddParticipant = false;
        }

        if (null !== $this->competition->getUploadEndDate() && $this->competition->getUploadEndDate() < new \DateTime()) {
            $this->template->userCanAddParticipant = false;
        }
    }

    public function renderAdd()
    {
        if (!$this->getUser()->isLoggedIn()) {
            $this->redirect('gallery');
        }
        if (!$this->template->userCanAddParticipant) {
            $this->flashMessage('tr.competition.photolimit');
            $this->redirect('gallery');
        }
    }

    public function renderGallery($show_id = null)
    {
        if (new \DateTime() < $this->competition->getStartDate()) {
            $this->setView('countdown');
            $this->template->userCanAddParticipant = false;
            if ($this->getUser()->isInRole(User::ADMIN)) {
//                $this->setCustomView($originalView);
            }
        }

        if ($this->competition->getEndDate() < new \DateTime()) {
            $this->setView('end');
            $boxes = $this->participations->getForFront($this->competition);
            $this->template->winners = array_slice($boxes,0,  3);
            $this->template->boxes   = array_slice($boxes, 3);

            return;
        }

        $this->template->thisMorningAsU = $thisMorningAsU = (new \DateTime('@'.mktime('0','0')))->format('U');
        $this->template->now            = $now            = new \DateTime();

        // dump(new \DateTime() == (new \DateTime('@'.time())));  // TRUE

        $this->template->boxes = $this->participations->getForFront($this->competition);
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
        if ($show_id && $load = $this->participations->repository()->findOneBy(['id' => $show_id]))
        {
            $this->template->showPart = $load;
        }
    }

    public function actionHash($q = null)
    {
        if (!$this->isAjax()) {
            //die('XHR-only');
        }

        $ip = $this->ips->getCurrent();
        if (!$ip) {
            $ip = $this->ips->create();
        }

        $ip->part = $q;
        $ip->hashTime = time();
        $hash = md5(serialize($ip));
        $ip->hash = $hash;
        $this->ips->entityManager()->persist($ip);
        $this->ips->entityManager()->flush();

        // Return success response
        $this->payload->status  = 'OK';
        $this->payload->m   = $hash;

        $this->sendPayload();
    }

    //-----------------------  VOTE

    /**
     * @param $participant_id
     */
    public function actionVote($participant_id)
    {
        if (!$this->isAjax()) {
//            die('XHR-only');
        }

        /** @var Participation $participation */
        if (! $participation = $this->participations->repository()->find($participant_id)) {
            $this->voteError('tr.vote.part_not_fount');

            return;
        }

        if ($this->competition->getVoteStartDate() > new \DateTime()) {
            $this->voteError('tr.vote.too_early', ['date' => $this->competition->getVoteStartDate()->format('j.n.Y G:i')]);

            return;
        }

        if ($this->competition->getEndDate() < new \DateTime()) {
            $this->voteError('tr.vote.too_late');

            return;
        }

        // Find or create current IP address record
        if (! $ip = $this->ips->getCurrent()) {
            $this->voteError('tr.vote.error1');

            return;
        }

        if($ip->hash !== $this->getHttpRequest()->getCookie('g') || $ip->part !== $participant_id) {
            $h = $this->getHttpRequest()->getHeaders();
            unset($h['cookie']);

            $log = new IpLog;
            $log->address = $ip->address;
            $log->part    = $ip->part;
            $log->hash    = $ip->hash;
            $log->cookie  = serialize($this->getHttpRequest()->getCookies());
            $log->headers = serialize($h);
            $this->ipLogs->entityManager()->persist($log);
            $this->ipLogs->entityManager()->flush();
            $this->voteError('tr.vote.error2');

            return;
        }

        // Get most recent vote for current competition and ip address
        if ($this->competition->isVotingType(Competition::VOTING_TYPE_MORE_PART_IN_PERIOD))
            $vote = $this->votes->findLast($participation, $ip);
        else
            // Get most recent vote for current participation and ip address
            $vote = $this->votes->findLast(null, $ip, $this->competition);

        if ($vote && $vote->isWithinPeriod()) {
            $this->voteError('tr.vote.24_hour', ['date' => $vote->getTimestamp()->add(
                new \DateInterval($this->competition->getVotingPeriod()))->format('j.n.Y G:i')]);

            return;
        }

        // Create vote
        $vote = new Vote();
        $vote->setCompetition($this->competition);
        $vote->setIp($ip);
        $vote->setParticipation($participation);
        $ip->hash = 'voted';

        // Increase participation vote integer
        $participation->increaseVotes($vote->getIncrement());

        // Save vote
        try {
            $this->votes->entityManager()->persist($ip);
            $this->votes->entityManager()->persist($vote);
            $this->votes->entityManager()->flush();
        } catch (Exception $e) {
            $this->voteError('tr.vote.error');

            return;
        }

        // Return success response
        $this->payload->status  = 'OK';
        $this->payload->modal   = 'modal-success';
        $this->payload->message = $this->translator->trans('tr.vote.success');

        $this->sendPayload();
    }

    /**
     * @param string $message
     */
    private function voteError($message = '', $params = [])
    {
        $this->payload->status  = 'ERROR';
        $this->payload->modal   = 'modal-danger';
        $this->payload->message = $this->translator->trans($message, $params);

        $this->sendPayload();
    }

    //-----------------------  NEW PARTICIPANTS FORM

    public function createComponentNewUploadForm()
    {
        $form = new FrontUploadForm();
        $form->create();
        $form->addSubmit('send', $this->translator->trans('tr.modal.add'));
        $form->onSuccess[] = [$this, 'uploadForm'];

        return $form;
    }

    public function uploadForm(Form $form, $values)
    {
        $part = new Participation();
        $part->setCompetition($this->competition);
        $part->setUser($this->users->repository()->findOneById($this->getUser()->getId()));

        try {
            $form->process($part);

            $this->participations->entityManager()->persist($part);
            $this->participations->entityManager()->flush();

            $image = new Image();
            if ($values->image->isOk()) {
                $image = $this->uploadImage($values->image, $image, $this->competition->getId() . '_' . $part->getId());

                $size = $values->image->getImageSize();
                $image->setWidth($size[0]);
                $image->setHeight($size[1]);

                $image->setParticipation($part);
                $part->getImages()->add($image);
            } else {
                throw new \Exception;
            }

            $this->participations->entityManager()->persist($image);
            $this->participations->entityManager()->flush();

            $this->sendMail(
                $this->parameters['admin_mail'],
                'Nova fotografie ' . $values['name'],
                ['name' => $values['name'],
                 'id'   => $part->getId()
                ],
                'newParticipantMail',
                [$this->getImageUrl($image)]
            );

            $this->flashMessage('tr.competition.part_saved');
        } catch (Exception $e) {
            $this->flashMessage('tr.competition.part_error', 'error');
        }

        $this->redirect('gallery');
    }

    //-----------------------  INJECTS

    /**
     * @param Ips $ips
     */
    public function injectIps(Ips $ips)
    {
        $this->ips = $ips;
    }

    /**
     * @param IpLogs $logs
     */
    public function injectIpLogs(IpLogs $logs)
    {
        $this->ipLogs = $logs;
    }

    /**
     * @param Votes $votes
     */
    public function injectVotes(Votes $votes)
    {
        $this->votes = $votes;
    }
}
