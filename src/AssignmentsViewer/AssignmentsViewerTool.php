<?php

namespace GrotonSchool\AssignmentsViewer;

use ceLTIc\LTI\DataConnector\DataConnector;
use ceLTIc\LTI\Tool;
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
        if ($this->platform && $this->userResult) {
            /** @var UserFactory */
            $userFactory = $this->container->get(UserFactory::class);
            $_SESSION[USER_ID] = $this->userResult->ltiUserId;
            $_SESSION[CONSUMER_GUID] = $this->platform->consumerGuid;
            $_SESSION[IS_LEARNER] = $this->userResult->isLearner();
            $_SESSION[IS_STAFF] = $this->userResult->isStaff();
            $user = $userFactory->getByUserId($_SESSION[USER_ID], $_SESSION[CONSUMER_GUID]);
            if (!$user || !$user->refresh_token || strtotime($user->expires) < time() - 10) {
                $userFactory->create($_SESSION[USER_ID], $_SESSION[CONSUMER_GUID]);
                $this->redirectUrl = getenv('APP_URL') . '/auth/token';
            } else {
                $this->redirectUrl = getenv('APP_URL') . '/auth/refresh';
            }
        } else {
            $this->reason = 'Need both platform GUID and user id';
            $this->ok = false;
        }
    }
}
