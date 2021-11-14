<?php

namespace GrotonSchool\AssignmentsViewer;

use ceLTIc\LTI\DataConnector\DataConnector;
use ceLTIc\LTI\Profile\Item;
use ceLTIc\LTI\Profile\Message;
use ceLTIc\LTI\Profile\ResourceHandler;
use ceLTIc\LTI\Profile\ServiceDefinition;
use ceLTIc\LTI\Tool;
use DI\Container;
use Error;

class AssignmentsViewerTool extends Tool
{
    public function __construct(DataConnector $dataConnector)
    {
        parent::__construct($dataConnector);

        $this->debugMode = true;
        
        $this->baseUrl = getenv('APP_URL');
        $this->vendor = new Item('groton-school', 'GrotonSchool', 'Groton School', 'https://groton.org');
        $this->product = new Item(
            '2df5edf1-daa6-4c48-acbc-e7c73554385c',
            'Assignments Viewer',
            'View assignments',
            'https://github.com/groton-school/assignments-viewer'
        );
        $this->resourceHandlers[] = new ResourceHandler(
            new Item('assignments-viewer', 'Assignments Viewer', 'View assignments'),
            null,
            [
                new Message('basic-lti-launch-request', 'launch.php', ['User.id', 'Membership.role'])
            ],
            []
        );
        $this->requiredServices[] = new ServiceDefinition(['application/vnd.ims.lti.v2.toolproxy+json'], ['POST']);
    }

    public function onLaunch()
    {
        /** @var Container */
        global $container;

        if ($this->platform && $this->userResult) {
            /** @var UserFactory */
            $userFactory = $container->get(UserFactory::class);
            $_SESSION[USER_ID] = $this->userResult->ltiUserId;
            $_SESSION[CONSUMER_GUID] = $this->platform->consumerGuid;
            $_SESSION[IS_LEARNER] = $this->userResult->isLearner();
            $_SESSION[IS_STAFF] = $this->userResult->isStaff();
            $user = $userFactory->getByUserId($_SESSION[USER_ID], $_SESSION[CONSUMER_GUID]);
            if (!$user || !$user->refresh_token || strtotime($user->expires) < time() - 10) {
                $userFactory->create($_SESSION[USER_ID], $_SESSION[CONSUMER_GUID]);
                $this->redirectUrl = getenv('auth/token');
            } else {
                $this->redirectUrl = getenv('auth/refresh');
            }
        } else {
            $this->reason = 'Need both platform GUID and user id';
            $this->ok = false;
        }
    }
}
