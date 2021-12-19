<!DOCTYPE html>
<html>
    <head>
        <title>Assignments Viewer</title>
    </head>
    <link rel="stylesheet" href="css/calendar.css">
    <link rel="stylesheet" href="css/loading.css">
    <body>
        <div id="content">
            <span class="loading big">⬤</span>
        </div>
        <?php

        use DI\Container;
        use GrotonSchool\AssignmentsViewer\Users\User;
        use GrotonSchool\AssignmentsViewer\Users\UserFactory;
        use GrotonSchool\OAuth2\Client\Provider\BlackbaudSKY;

        require_once __DIR__ . '/../bootstrap.php';
        /** @var Container $container */

        /** @var User */
        $user = $container->get(UserFactory::class)-> getByInstance($_SESSION[CONSUMER_GUID], $_SESSION[USER_ID]);

        $today = new DateTime();
        $year = intval($today->format('Y'));
        if (intval($today->format('n')) < 6) {
            $year -= 1;
        }

        /** @var BlackbaudSKY */
        $sky = $container->get(BlackbaudSKY::class);
        $academics = $sky->endpoint('school/v1/academics');
        $enrollments = $academics->get("enrollments/{$user->getUserId()}?school_year=$year-" . ($year + 1));

        $data = [];
        foreach ($enrollments['value'] as $enrollment) {
            if (isset($enrollment['id'])) {
                $assignments = $academics->get("sections/{$enrollment['id']}/assignments");
                foreach ($assignments['value'] as $assignment) {
                    if (!empty($assignment['due_date'])) {
                        $due = strtotime($assignment['due_date']);
                        $data[date('Y', $due)][date('n', $due)][date('j', $due)][] = $assignment;
                    } else {
                        $data['undated'][] = $assignment;
                    }
                }
            }
        }

        ?>
        <script src="js/calendar.js"></script>
        <script>
            const data = <?= json_encode($data) ?>;
            calendar(data);
        </script>
    </body>
</html>