<!DOCTYPE html>
<html>
<head>
    <title>Assignments Viewer</title>
    <?php

        use DI\Container;
        use GrotonSchool\AssignmentsViewer\UserFactory;
        use GrotonSchool\AssignmentsViewer\User;

        require_once __DIR__ . '/../bootstrap.php';
        /** @var Container $container */

        /** @var User */
        $user = $container->get(UserFactory::class)->getByUserId(
            $_SESSION['user_id'],
            $_SESSION['tool_consumer_instance_guid']
        );
        
        ?>
</head>
<body>

    <h1>Assignments Viewer</h1>

    <h3>User</h3>
    <pre><?= var_export($user, true) ?></pre>

    <h3>Session</h3>
    <pre><?= var_export($_SESSION, true) ?></pre>

</body>
</html>