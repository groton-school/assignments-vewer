<?php

namespace GrotonSchool\AssignmentsViewer;

use ceLTIc\LTI\DataConnector\DataConnector;
use ceLTIc\LTI\Tool;
use DI\Container;
use GrotonSchool\AssignmentsViewer\Users\UserFactory;

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
            $_SESSION[USER_ID] = $this->userResult->ltiUserId;
            $_SESSION[CONSUMER_GUID] = $this->platform->consumerGuid;
            $_SESSION[IS_LEARNER] = $this->userResult->isLearner();
            $_SESSION[IS_STAFF] = $this->userResult->isStaff();
            $user = $this->container->get(UserFactory::class)
                ->getByInstance($_SESSION[CONSUMER_GUID], $_SESSION[USER_ID]);
            if (!$user ||
                !$user->getRefreshToken() ||
                !$user->getExpires() ||
                strtotime($user->getExpires()) < time() - 10
            ) {
                $this->redirectUrl = $_ENV['APP_URL'] . '/auth/token';
            } else {
                $this->redirectUrl = $_ENV['APP_URL'] . '/auth/refresh';
            }
        } else {
            $this->reason = 'unauthorized';
            $this->ok = false;
        }
    }
}
