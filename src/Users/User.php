<?php

namespace GrotonSchool\AssignmentsViewer\Users;

use Battis\PDOFactory\PDOObject;

/**
 * @method string getToolConsumerInstanceGuid()
 * @method User setToolConsumerInstanceGuid(string $guid)
 * @method string getUserId()
 * @method User setUserId($userId)
 * @method string getRefreshToken()
 * @method User setRefreshToken($token)
 * @method string getExpires()
 * @method User setExpires(string $timestamp)
 */
class User extends PDOObject
{
    /** @var string */
    public $tool_consumer_instance_guid;

    /** @var string */
    public $user_id;

    /** @var string */
    public $refresh_token;

    /** @var string */
    public $expires;
}
