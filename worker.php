<?php

include(__DIR__ . '/init.inc.php');

while (true) {
    usleep(1000);
    $freeme = array_shift(glob(getenv("SESSION_PATH") . '/*.freeme'));
    if ($freeme) {
        unlink($freeme);
        $status = 0;
        pcntl_wait($status);
    }

    $pendings = glob(getenv("SESSION_PATH") . '/*.pending');
    if (!$pendings) {
        continue;
    }
    $pending = array_shift($pendings);

    $obj = json_decode(file_get_contents($pending));
    $session_id = $obj->session_id;
    $command = $obj->command;
    $base_folder = $obj->base_folder;

    $pid = pcntl_fork();
    if ($pid) { // parent
        if ($pid == -1) {
            error_log("fork 失敗");
            continue;
        }
        unlink($pending);
        continue;
    } else {
        error_log("Running {$session_id} $command...");
        $descriptorspec = array(
            0 => array("pipe", "r"),
            1 => array("file", getenv('SESSION_PATH') . "/{$session_id}.stdout", 'w'),
            2 => array("file", getenv('SESSION_PATH') . "/{$session_id}.stderr", 'w'),
        );
        $cwd = $base_folder;
        $proc = proc_open($command, $descriptorspec, $pipes, $cwd);
        $start = microtime(true);
        $limit = 300;
        $error_message = null;
        while (true) {
            usleep(1000);
            $status = proc_get_status($proc);
            if (!$status['running']) {
                break;
            }
            if (microtime(true) - $start > $limit) {
                $error_message = "超過 {$limit} 秒被中斷";
                break;
            }
        }
        proc_close($proc);
        if ($error_message) {
            file_put_contents(getenv('SESSION_PATH') . "/{$session_id}.stderr", "\n" . $error_message, FILE_APPEND);
        }

        touch(getenv('SESSION_PATH') . "/{$session_id}.done");
        touch(getenv('SESSION_PATH') . "/{$session_id}.freeme");
        error_log("Running {$session_id} $command done...");
        exit;
    }
}
