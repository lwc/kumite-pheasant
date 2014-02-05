<?php

namespace Kumite\Pheasant;

use \Pheasant\DomainObject;
use \Pheasant\Types;

class Event extends DomainObject
{
    public static $timeFn = array('\Testable', 'time'); // Mockable source of time

    public function properties()
    {
        return array(
            'id' => new Types\Sequence(),
            'testkey' => new Types\String(),
            'variantkey' => new Types\String(),
            'eventkey' => new Types\String(),
            'participantid' => new Types\String(),
            'timecreated' => new Types\UnixTimestamp(),
            'metadata' => new Types\String(65534),
        );
    }

    protected function beforeCreate()
    {
        $time = is_callable(self::$timeFn)
            ? call_user_func(self::$timeFn)
            : new \DateTime();

        if (!is_object($time)) {
            $time = new \DateTime('@' . $time);
        }

        $this->timecreated = $time;
    }

    protected function tableName()
    {
        return 'kumiteevent';
    }


    public function getTotalForEvent($testKey, $variantKey, $eventKey)
    {
        $sql = <<<SQL
            SELECT COUNT(DISTINCT participantid)
            FROM kumiteevent
            WHERE testkey = ? AND variantkey = ? AND eventkey = ?
SQL;
        return self::connection()->execute(
            $sql,
            array($testKey, $variantKey, $eventKey)
        )->scalar();
    }

    public static function getTotalsForTest($testKey)
    {
        return self::connection()->execute(
            'SELECT eventkey, variantkey, COUNT(DISTINCT participantid) AS total
            FROM kumiteevent
            WHERE testkey = ?
            GROUP BY variantkey, eventkey',
            $testKey
        );
    }

    public static function getHistoryForTestEvent($testKey, $eventKey)
    {
        return self::connection()->execute(
            '
            SELECT
                g.variantkey,
                ke.eventkey,
                g.timegroup,
                g.participants,
                COUNT(DISTINCT participantid) events
            FROM (
                SELECT
                    variantkey,
                    timegroup,
                    COUNT(*) participants
                FROM (
                    SELECT
                        CEIL(timecreated / 21600) * 21600 as timegroup
                    FROM kumiteparticipant
                    GROUP BY timegroup
                ) tg
                LEFT JOIN kumiteparticipant kp ON (kp.timecreated <= timegroup)
                WHERE testkey = ?
                GROUP BY variantkey, timegroup
            ) g
            LEFT JOIN kumiteevent ke ON (g.variantkey = ke.variantkey AND ke.timecreated <= g.timegroup)
            WHERE ke.testkey = ? AND ke.eventkey = ?
            GROUP BY variantkey, timegroup', array($testKey, $testKey, $eventKey)
        );
    }
}
