<?php

namespace GrotonSchool\AssignmentsViewer;

use ceLTIc\LTI\Tool;
use DI\Container;

class AssignmentsViewerTool extends Tool
{
    public function onLaunch()
    {
        /** @var Container */
        global $container;

        if ($this->platform && $this->userResult) {
            /** @var UserFactory */
            $userFactory = $container->get(UserFactory::class);
            session_start();
            $_SESSION[USER_ID] = $this->userResult->ltiUserId;
            $_SESSION[CONSUMER_GUID] = $this->platform->consumerGuid;
            $_SESSION[IS_LEARNER] = $this->userResult->isLearner();
            $_SESSION[IS_STAFF] = $this->userResult->isStaff();
            $user = $userFactory->getByUserId($_SESSION[USER_ID], $_SESSION[CONSUMER_GUID]);
            if (!$user || !$user->refresh_token || strtotime($user->expires) < time() - 10) {
                $userFactory->create($_SESSION[USER_ID], $_SESSION[CONSUMER_GUID]);
                header('Location: auth/token');
                exit;
            } else {
                header('Location: auth/refresh');
                exit;
            }
        }
    }
}
