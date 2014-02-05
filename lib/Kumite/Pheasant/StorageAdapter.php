<?php

namespace Kumite\Pheasant;

class StorageAdapter implements \Kumite\Adapters\StorageAdapter
{

    public function onCreateParticipant($callback)
    {
        Participant::schema()
            ->events()
            ->register('beforeCreate', $callback);
        return $this;
    }

    public function onCreateEvent($callback)
    {
        Event::schema()
            ->events()
            ->register('beforeCreate', $callback);
        return $this;
    }

    public function createParticipant($testKey, $variantKey, $metadata=null)
    {
        $participant = Participant::create(array(
            'testkey' => $testKey,
            'variantkey' => $variantKey,
            'metadata' => json_encode($metadata)
        ));

        return $participant->id;
    }

    public function createEvent($testKey, $variantKey, $eventKey, $participantId, $metadata=null)
    {
        Event::create(array(
            'testkey' => $testKey,
            'variantkey' => $variantKey,
            'eventkey' => $eventKey,
            'participantid' => $participantId,
            'metadata' => json_encode($metadata)
        ));
    }

    public function countParticipants($testKey, $variantKey)
    {
        return Participant::getTotalForVariant($testKey, $variantKey);
    }

    public function countEvents($testKey, $variantKey, $eventKey)
    {
        return Event::getTotalForEvent($testKey, $variantKey, $eventKey);
    }
}
