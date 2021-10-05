<?php

namespace GrotonSchool\MajorAssignments;

use Battis\PDOFactory\PDOObject;
use DateTimeImmutable;

class User extends PDOObject
{
    /** @var integer */
    public $id;

    /** @var string */
    public $user_id;

    /** @var string */
    public $tool_consumer_instance_guid;

    /** @var string */
    public $refresh_token;

    /** @var string */
    public $expires;
}
