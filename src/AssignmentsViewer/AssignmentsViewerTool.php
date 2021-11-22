<?php

namespace GrotonSchool\AssignmentsViewer;

use ceLTIc\LTI\DataConnector\DataConnector;
use ceLTIc\LTI\Tool;
use ceLTIc\LTI\Util;
use DI\Container;

class AssignmentsViewerTool extends Tool
{
    /** Container */
    private $container;

    public function __construct(DataConnector $dataConnector, Container $container)
    {
        parent::__construct($dataConnector);
        $this->container = $container;
    }

    public function onLaunch()
    {
        if ($this->platform && ($this->userResult->isLearner() || $this->userResult->isStaff())) {
            /** @var UserFactory */
            $userFactory = $this->container->get(UserFactory::class);
            $_SESSION[USER_ID] = $this->userResult->ltiUserId;
            $_SESSION[CONSUMER_GUID] = $this->platform->consumerGuid;
            $_SESSION[IS_LEARNER] = $this->userResult->isLearner();
            $_SESSION[IS_STAFF] = $this->userResult->isStaff();
            $user = $userFactory->getByUserId($_SESSION[USER_ID], $_SESSION[CONSUMER_GUID]);
            if (!$user) {
                $user = $userFactory->create(['user_id' => $_SESSION[USER_ID], 'tool_consumer_instance_guid' => $_SESSION[CONSUMER_GUID]]);
            }
            if (!$user->refresh_token || strtotime($user->expires) < time() - 10) {
                $this->redirectUrl = getenv('APP_URL') . '/auth/token';
            } else {
                $this->redirectUrl = getenv('APP_URL') . '/auth/refresh';
            }
        } else {
            $this->reason = 'unauthorized';
            $this->ok = false;
        }
    }
}
