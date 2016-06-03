<?php

while (true) {
    usleep(1000);
    $pendings = glob(__DIR__ . "/sessions/*.pending");
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
            1 => array("file", __DIR__ . '/sessions/' . $session_id . '.stdout', 'w'),
            2 => array("file", __DIR__ . '/sessions/' . $session_id . '.stderr', 'w'),
        );
        $cwd = $base_folder;
        $env = array();
        $proc = proc_open($command, $descriptorspec, $pipes, $cwd, $env);
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
                $error_message = "超過 {$limit} 被中斷";
                break;
            }
        }
        proc_close($proc);
        if ($error_message) {
            file_put_contents(__DIR__ . '/sessions/' . $session_id . '.stderr', "\n" . $error_message, FILE_APPEND);
        }

        touch(__DIR__ . '/sessions/' . $session_id . '.done');
        error_log("Running {$session_id} $command done...");
        exit;
    }
}
