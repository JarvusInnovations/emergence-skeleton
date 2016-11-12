<?php
    namespace Emergence\Developer\Tools\Git;

use Site,Exception,Git,Benchmark,Emergence\Git\Repo;

class RepoRequestHandler extends \RequestHandler
{
    public static $userResponseModes = [
        'application/json' => 'json'
    ];

    public static $_currentRepo;

    public static function handleRequest($action)
    {
        if (static::$_currentRepo = Repo::getById($action)) {
            return static::handleRepositoryRequest();
        } else {
            return \Emergence\Developer\Tools\RequestHandler::throwNotFoundError();
        }
    }

    public static function handleRepositoryRequest()
    {
        /* This should have already been done but lets be paranoid */
        $GLOBALS['Session']->requireAccountLevel('Developer');

        switch ($action ? $action : $action = static::shiftPath()) {
            case 'commit':
                return static::handleCommitRequest();
            case 'init':
                return static::handleInitRequest();
            case 'pull':
                return static::handlePullRequest();
            case 'push':
                return static::handlePushRequest();
            case 'sync':
                return static::handleSyncRequest();
            case 'clean':
                return static::handleCleanRequest();
            case 'hard-reset-remote-head':
                return static::handleHardResetRemoteHeadRequest();
            case 'reset':
                return static::handleResetRequest();
            case 'stage':
                return static::handleStageRequest();
            case 'stage-multi':
                return static::handleStageMultiRequest();
            case 'key':
                return static::handleKeyRequest();
            case 'status':
            default:
                return static::handleStatusRequest();
        }
    }

    public static function respond($responseID, $responseData = [], $responseMode = false)
    {
        \Emergence\Developer\Tools\RequestHandler::respond($responseID, array_merge($responseData,[
            'Repo'  => static::$_currentRepo
        ]),$responseMode);
    }

    public static function handleCleanRequest()
    {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            return static::throwInvalidRequestError('Request must be POST');
        }

        try {
            $layer = static::$_currentRepo;
        } catch (Exception $e) {
            return static::throwInvalidRequestError($e->getMessage());
        }

        static::$responseMode = 'json';

        $command = 'clean';

        $args = ['-d','-f'];

        try {
            $output = $layer->getGitWrapper()->run($command, $args);

            return static::respond('clean',[
                'success' => true,
                'output' => $output,
                'command' => $command,
                'args' => $args
            ]);
        } catch (Exception $e) {
            return static::respond('clean', [
                'success' => false,
                'output' => $output,
                'command' => $command,
                'args' => $args,
                'error' => $e->getMessage()
            ]);
        }

        static::respond('repo/clean');
    }

    public static function handleResetRequest()
    {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            return static::throwInvalidRequestError('Request must be POST');
        }

        if (0===strpos($_SERVER['CONTENT_TYPE'],'application/json')) {
            $_REQUEST = \JSON::getRequestData();
        }

        try {
            $layer = static::$_currentRepo;
        } catch (Exception $e) {
            return static::throwInvalidRequestError($e->getMessage());
        }

        if (empty($_REQUEST['File'])) {
            return static::throwInvalidRequestError('A path is required.');
        }

        static::$responseMode = 'json';

        $file = escapeshellcmd($_REQUEST['File']);

        $command = 'checkout';

        $args = ['HEAD','--',$file];

        try {
            $output = $layer->getGitWrapper()->run($command, $args);

            return static::respond('reset',[
                'success' => true,
                'output' => $output,
                'command' => $command,
                'args' => $args
            ]);
        } catch (Exception $e) {
            return static::respond('reset', [
                'success' => false,
                'output' => $output,
                'command' => $command,
                'args' => $args,
                'error' => $e->getMessage()
            ]);
        }

        static::respond('repo/hard-reset');
    }

    public static function handleHardResetRemoteHeadRequest()
    {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            return static::throwInvalidRequestError('Request must be POST');
        }

        try {
            $layer = static::$_currentRepo;
        } catch (Exception $e) {
            return static::throwInvalidRequestError($e->getMessage());
        }

        static::$responseMode = 'json';

        $command = 'reset';

        $args = ['--hard','origin/master'];

        try {
            $output = $layer->getGitWrapper()->run($command, $args);

            return static::respond('hard-reset-remote-head',[
                'success' => true,
                'output' => $output,
                'command' => $command,
                'args' => $args
            ]);
        } catch (Exception $e) {
            return static::respond('hard-reset-remote-head', [
                'success' => false,
                'output' => $output,
                'command' => $command,
                'args' => $args,
                'error' => $e->getMessage()
            ]);
        }

        static::respond('repo/hard-reset');
    }

    public static function handleStatusRequest()
    {
        static::respond('repo/status', [
            'AdvancedStatus' =>   static::$_currentRepo->AdvancedStatus
        ]);
    }

    public static function handleSyncRequest()
    {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            return static::throwInvalidRequestError('Request must be POST');
        }

        static::$responseMode = 'json';

        // Disable diagnostics by default for this operation as a high volume of queries may be needed
        Site::$debug = !empty($_GET['debug']);


        /*
         *  This needs better error handling. Currently errors coming from php raw copy exit as server 500 because Site::$debug == false to disable caching.
         *  Instead errors should be caught and should bubble up to the JSON output.
         */
        switch ($action ? $action : $action = static::shiftPath()) {
            case 'to-disk':
                return static::handleSyncToDiskRequest();

            case 'from-disk':
                return static::handleSyncFromDiskRequest();
        }
    }

    public static function handleSyncToDiskRequest()
    {
        try {
            $repo = static::$_currentRepo;
            $results = $repo->syncToDisk();
        } catch (Exception $e) {
            return static::throwInvalidRequestError($e->getMessage());
        }

        return static::respond('syncedToDisk', [
            'results' => $results,
            'success' => true
        ]);
    }

    public static function handleSyncFromDiskRequest()
    {
        try {
            $repo = static::$_currentRepo;
            $results = $repo->syncFromDisk();
        } catch (Exception $e) {
            return static::throwInvalidRequestError($e->getMessage());
        }

        return static::respond('syncedFromDisk', [
            'results' => $results,
            'success' => true
        ]);
    }


    public static function handlePullRequest()
    {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            return static::throwInvalidRequestError('Request must be POST');
        }

        try {
            $layer = static::$_currentRepo;
        } catch (Exception $e) {
            return static::throwInvalidRequestError($e->getMessage());
        }

        static::$responseMode = 'json';

        $command = 'merge';
        $args = ['--ff-only', '@{upstream}'];

        try {
            $output = $layer->getGitWrapper()->run($command, $args);

            return static::respond('pull',[
                'success' => true,
                'output' => $output,
                'command' => $command,
                'args' => $args
            ]);
        } catch (Exception $e) {
            return static::respond('pull', [
                'success' => false,
                'output' => $output,
                'command' => $command,
                'args' => $args,
                'error' => $e->getMessage()
            ]);
        }
    }

    public static function handlePushRequest()
    {
        $layer = static::$_currentRepo;

        $repoName = $layer->ID;

        static::$responseMode = 'json';

        if (!array_key_exists($repoName, Git::$repositories)) {
            return static::throwInvalidRequestError("Repo '$repoName' is not defined in Git::\$repositories");
        }

        $repoCfg = Git::$repositories[$repoName];



        // start the process
        set_time_limit(0);
        //Benchmark::startLive();
        //Benchmark::mark("configured request: repoName=$repoName");


        // get paths
        $repoPath = "$_SERVER[SITE_ROOT]/site-data/git/$repoName";
        $keyPath = "$repoPath.key";
        $gitWrapperPath = "$repoPath.git.sh";
        putenv("GIT_SSH=$gitWrapperPath");


        // check if there is an existing repo
        if (!is_dir("$repoPath/.git")) {
            return static::throwInvalidRequestError("$repoPath does not contain .git");
        }


        // get repo
        chdir($repoPath);
        $repo = new \PHPGit_Repository($repoPath, !empty($_REQUEST['debug']));
        //Benchmark::mark("loaded git repo in $repoPath");


        // verify repo state
        if ($repo->getCurrentBranch() != $repoCfg['workingBranch']) {
            return static::throwInvalidRequestError("Current branch in $repoPath is not $repoCfg[workingBranch]; aborting.");
        }
        //Benchmark::mark("verified working branch");


        // push changes
        $command = "push origin $repoCfg[workingBranch]";
        try {
            $output = $repo->git($command);

            return static::respond('push',[
                'success' => true,
                'output' => $output,
                'command' => $command
            ]);
        } catch (Exception $e) {
            return static::respond('push', [
                'success' => false,
                'output' => $output,
                'command' => $command,
                'error' => $e->getMessage()
            ]);
        }


        Benchmark::mark("pushed to $repoCfg[workingBranch]");
    }

    public static function handleCommitRequest()
    {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            return static::throwInvalidRequestError('Request must be POST');
        }

        if (0===strpos($_SERVER['CONTENT_TYPE'],'application/json')) {
            $_REQUEST = \JSON::getRequestData();
        }

        try {
            $layer = static::$_currentRepo;
        } catch (Exception $e) {
            return static::throwInvalidRequestError($e->getMessage());
        }

        static::$responseMode = 'json';

        if (empty($_REQUEST['subject'])) {
            return static::throwInvalidRequestError('A subject is required.');
        }

        if (empty($_REQUEST['author'])) {
            return static::throwInvalidRequestError('An author is required.');
        }

        $command = 'commit';

        $subject = escapeshellcmd($_REQUEST['subject']);
        $description = escapeshellcmd($_REQUEST['description']);


        $author = $_REQUEST['author'];

        $args = [
            '-m',$subject
            ,'-m',$description
            ,'--author='.$author
        ];

        try {
            $layer->getGitWrapper()->run('config',['user.name',$GLOBALS['Session']->Person->FullName]);
            $layer->getGitWrapper()->run('config',['user.email',$GLOBALS['Session']->Person->Email]);

            $output = $layer->getGitWrapper()->run($command, $args);

            return static::respond('commit',[
                'success' => true,
                'output' => $output,
                'command' => $command,
                'args' => $args
            ]);
        } catch (Exception $e) {
            return static::respond('commit', [
                'success' => false,
                'output' => $output,
                'command' => $command,
                'args' => $args,
                'error' => $e->getMessage()
            ]);
        }

        static::respond('repo/clean');
    }



    public static function handleInitRequest()
    {
        try {
            $repo = static::$_currentRepo;
        } catch (Exception $e) {
            return static::throwInvalidRequestError($e->getMessage());
        }

        $repo->initializeRepository();

        Site::redirect('/.emr/git/'.$repo->ID);
    }

    public static function handleStageRequest()
    {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            return static::throwInvalidRequestError('Request must be POST');
        }

        if (0===strpos($_SERVER['CONTENT_TYPE'],'application/json')) {
            $_REQUEST = \JSON::getRequestData();
        }

        try {
            $layer = static::$_currentRepo;
        } catch (Exception $e) {
            return static::throwInvalidRequestError($e->getMessage());
        }

        static::$responseMode = 'json';

        $command = 'add';

        if (empty($_REQUEST['File'])) {
            return static::throwInvalidRequestError('A path is required.');
        }

        $file = escapeshellcmd($_REQUEST['File']);

        $args = [$file];

        try {
            $output = $layer->getGitWrapper()->run($command, $args);

            return static::respond('stage',[
                'success' => true,
                'output' => $output,
                'command' => $command,
                'args' => $args
            ]);
        } catch (Exception $e) {
            return static::respond('stage', [
                'success' => false,
                'output' => $output,
                'command' => $command,
                'args' => $args,
                'error' => $e->getMessage()
            ]);
        }

        static::respond('repo/hard-reset');
    }

    public static function handleStageMultiRequest()
    {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            return static::throwInvalidRequestError('Request must be POST');
        }

        if (0===strpos($_SERVER['CONTENT_TYPE'],'application/json')) {
            $_REQUEST = \JSON::getRequestData();
        }

        try {
            $layer = static::$_currentRepo;
        } catch (Exception $e) {
            return static::throwInvalidRequestError($e->getMessage());
        }

        static::$responseMode = 'json';

        $command = 'add';

        $success = true;

        if (is_array($_REQUEST['Files'])) {
            foreach ($_REQUEST['Files'] as $file) {
                $file = escapeshellcmd($file);
                $args = [$file];

                try {
                    $output = $layer->getGitWrapper()->run($command, $args);

                    $operations[] = [
                        'success' => true,
                        'output' => $output,
                        'command' => $command,
                        'args' => $args
                    ];
                } catch (Exception $e) {
                    $success = false;
                    $operations[] = [
                        'success' => false,
                        'output' => $output,
                        'command' => $command,
                        'args' => $args,
                        'error' => $e->getMessage()
                    ];
                }
            }
        }

        return static::respond('stage-multi', [
            'success' => $success,
            'operations' => $operations
        ]);
    }

    public static function handleExecuteCommandRequest(Layer $layer, $command, $args = [])
    {
        return static::respond('commandExecuted', [
            'layer' => $layer,
            'command' => $command,
            'args' => $args,
            'output' => $layer->getGitWrapper()->run($command, $args)
        ]);
    }

    public static function handleKeyRequest()
    {
        try {
            $repo = static::$_currentRepo;
        } catch (Exception $e) {
            return static::throwInvalidRequestError($e->getMessage());
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $repo->setKeys($_POST['privateKey'], $_POST['publicKey']);
        }

        $keyTmpPath = tempnam(sys_get_temp_dir(), 'git-key');
        unlink($keyTmpPath);
        $keyStatus = exec($command = "ssh-keygen -q -t rsa -N '' -C '".Site::getConfig('primary_hostname')."' -f '$keyTmpPath'; echo $?");

        if ($keyStatus != '0') {
            throw new Exception("Failed to execute command: $command\nExit output: $keyStatus");
        }

        $publicKey = file_get_contents("$keyTmpPath.pub");
        $privateKey = file_get_contents($keyTmpPath);

        unlink("$keyTmpPath.pub");
        unlink($keyTmpPath);

        return static::respond('repo/key', [
            'Repo' => $layer,
            'PublicKey' => $publicKey,
            'PrivateKey' => $privateKey
        ]);
    }
}