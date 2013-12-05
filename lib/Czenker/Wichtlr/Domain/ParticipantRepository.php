<?php


namespace Czenker\Wichtlr\Domain;


class ParticipantRepository {

    /**
     * @var array
     */
    protected $participants = array();

    /**
     * @param Participant $participant
     * @throws \InvalidArgumentException
     */
    public function addParticipant(Participant $participant) {
        $identifier = $participant->getIdentifier();
        if(array_key_exists($identifier, $this->participants) && $this->participants[$identifier] !== $participant) {
            throw new \InvalidArgumentException(sprintf('The repository already contains an entry with identifier %s.', $identifier));
        }
        $this->participants[$identifier] = $participant;
    }

    /**
     * @param $identifier
     * @return null|Participant
     */
    public function findOneByIdentifier($identifier) {
        return array_key_exists($identifier, $this->participants) ? $this->participants[$identifier] : NULL;
    }

}