<?php

namespace Kumite\Pheasant;

class StorageAdapter implements \Kumite\Adapters\StorageAdapter
{
    public function createParticipant($testKey, $variantKey, $metadata)
    {
        $country = isset($metadata['country']) ? $metadata['country'] : null;
        $browser = isset($metadata['browser']) ? $metadata['browser'] : null;
        $operatingsystem = isset($metadata['operatingsystem']) ? $metadata['operatingsystem'] : null;
        unset($metadata['country']);
        unset($metadata['browser']);
        unset($metadata['operatingsystem']);

        $participant = KumiteParticipant::create(array(
            'testkey' => $testKey,
            'variantkey' => $variantKey,
            'country' => isset($metadata['country']) ? $metadata['country'] : null,
            'browser' => isset($metadata['browser']) ? $metadata['browser'] : null,
            'operatingsystem' => isset($metadata['operatingsystem']) ? $metadata['operatingsystem'] : null,
            'metadata' => json_encode($metadata)
        ));

        return $participant->id;
    }

    public function createEvent($testKey, $variantKey, $eventKey, $participantId, $metadata=null)
    {
        KumiteEvent::create(array(
            'testkey' => $testKey,
            'variantkey' => $variantKey,
            'eventkey' => $eventKey,
            'participantid' => $participantId,
            'metadata' => json_encode($metadata)
        ));
    }
}
