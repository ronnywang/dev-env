<?php

class ApiController extends Pix_Controller
{
    protected function error($str)
    {
        return $this->json(array(
            'error' => true,
            'message' => $str,
        ));
    }

    public function getfileAction()
    {
        $path = $_REQUEST['base'];

        $file_base = realpath(getenv('FILE_PATH')) . '/';
        $path = realpath($file_base . $path);
        if (strpos($path, $file_base) !== 0) {
            return $this->error(sprintf("資料夾錯誤: %s", $_REQUEST['base']));
        }
        if (!file_exists($path) or !is_file($path)) {
            return $this->error(sprintf("找不到檔案或者不是檔案:  %s", $_REQUEST['base']));
        }

        if ($_GET['type'] == 'download') {
            header(sprintf('Content-Disposition: attachment; filename="%s"', urlencode(basename($path))));
            readfile($path);
            return $this->noview();
        } elseif ($_GET['type'] == 'view') {
            header("Content-Type: text/plain");
            readfile($path);
            return $this->noview();
        } else {
            return $this->json(array(
                'error' => false,
                'body' => file_get_contents($path),
            ));
        }
    }

    public function deletefileAction()
    {
        $path = $_REQUEST['path'];
        if (trim($path, '/') == '') {
            return $this->error("不能刪除根目錄");
        }

        $file_base = realpath(getenv('FILE_PATH')) . '/';
        $path = realpath($file_base . $path);
        if (strpos($path, $file_base) !== 0) {
            return $this->error(sprintf("資料夾錯誤: %s", $_REQUEST['path']));
        }
        if (!file_exists($path)) {
            return $this->error(sprintf("找不到 %s", $_REQUEST['path']));
        }
        if (is_file($path)) {
            unlink($path);
        } else { 
            if (glob($path . '/*')) {
                return $this->error(sprintf("無法刪除裡面還有檔案的資料夾"));
            }
            rmdir($path);
        }

        return $this->json(array(
            'error' => false,
        ));
    }

    public function savefileAction()
    {
        $base_folder = $_REQUEST['base'];

        $file_base = realpath(getenv('FILE_PATH')) . '/';
        $base_folder = realpath($file_base . $base_folder);
        if (strpos($base_folder, $file_base) !== 0) {
            return $this->error(sprintf("資料夾錯誤: %s", $_REQUEST['base']));
        }
        if (!file_exists($base_folder) or !is_file($base_folder)) {
            return $this->error(sprintf("找不到資料夾或不是檔案: %s", $_REQUEST['base']));
        }
        file_put_contents($base_folder, $_REQUEST['body']);
        return $this->json(array(
            'error' => false,
        ));
    }

    public function listfileAction()
    {
        $base_folder = $_REQUEST['base'];

        $file_base = realpath(getenv('FILE_PATH')) . '/';
        $base_folder = realpath($file_base . $base_folder) . '/';
        if (strpos($base_folder, $file_base) !== 0) {
            return $this->error(sprintf("資料夾錯誤: %s", $_REQUEST['base']));
        }
        if (!file_exists($base_folder) or !is_dir($base_folder)) {
            return $this->error(sprintf("找不到資料夾或是不是資料夾: %s", $_REQUEST['base']));
        }
        $d = opendir($base_folder);
        $ret = array();
        while ($f = readdir($d)) {
            if (in_array($f, array('.', '..'))) {
                continue;
            }
            $ret[] = array(
                'type' => is_dir($base_folder . $f) ?'dir' : 'file',
                'name' => $f,
                'path' => substr($base_folder . $f, strlen($file_base) - 1),
                'size' => filesize($base_folder . $f),
                'mtime' => date('Y/m/d H:i:s', filemtime($base_folder . $f)),
            );
        }
        return $this->json(array(
            'error' => false,
            'files' => $ret,
        ));
    }

    public function addobjectAction()
    {
        $base_folder = $_REQUEST['base'];
        $name = $_REQUEST['name'];

        $file_base = realpath(getenv('FILE_PATH')) . '/';
        $base_folder = realpath($file_base . $base_folder) . '/';
        if (strpos($base_folder, $file_base) !== 0) {
            return $this->error(sprintf("資料夾錯誤: %s", $_REQUEST['base']));
        }

        if ('' == trim($name, '.') or strpos($name, '/') !== false) {
            return $this->error(sprintf("新資料夾名稱不正確: %s", $_REQUEST['name']));
        }

        if (file_exists($base_folder . $name)) {
            return $this->error(sprintf("檔案已存在 %s", $_REQUEST['name']));
        }

        if ('dir' == $_REQUEST['type']) {
            mkdir($base_folder . $name);
            $path = substr($base_folder . $name, strlen($file_base) - 1);
        } else {
            touch($base_folder . $name);
            $path = substr($base_folder, strlen($file_base) - 1);
        }
        return $this->json(array(
            'error' => false,
            'message' => sprintf("新增資料夾成功"),
            'path' => $path,
        ));
    }

    public function runcommandAction()
    {
        $base_folder = $_REQUEST['base'];
        $command = $_REQUEST['command'];

        $file_base = realpath(getenv('FILE_PATH')) . '/';
        $base_folder = realpath($file_base . $base_folder) . '/';
        if (strpos($base_folder, $file_base) !== 0 or !is_dir($base_folder)) {
            return $this->error(sprintf("資料夾錯誤: %s", $_REQUEST['base']));
        }

        $session_id = crc32(uniqid());
        touch(getenv('SESSION_PATH') . "/{$session_id}.stdout");
        file_put_contents(getenv('SESSION_PATH') . "/{$session_id}.pending", json_encode(array(
            'session_id' => $session_id,
            'command' => $command,
            'base_folder' => $base_folder,
        )));
        return $this->json(array(
            'error' => false,
            'session_id' => $session_id,
        ));
    }

    public function killsessionAction()
    {
        $session_id = intval($_GET['session_id']);
        $session_base = getenv('SESSION_PATH') . '/';

        if (!file_exists("{$session_base}{$session_id}.stdout")) {
            return $this->error(sprintf("找不到 session : %s", $session_id));
        }
        touch("{$session_base}{$session_id}.kill");
        return $this->json(array(
            'error' => false,
        ));
    }

    public function senddataAction()
    {
        $session_id = intval($_GET['session_id']);
        $session_base = getenv('SESSION_PATH') . '/';
        $data = $_POST['data'];

        if (!file_exists("{$session_base}{$session_id}.stdout")) {
            return $this->error(sprintf("找不到 session : %s", $session_id));
        }
        file_put_contents("{$session_base}{$session_id}.stdin", $data, FILE_APPEND);
        return $this->json(array(
            'error' => false,
        ));
    }

    public function getsessionAction()
    {
        $session_id = intval($_GET['session_id']);
        $session_base = getenv('SESSION_PATH') . '/';
        $stdout_offset = intval($_GET['stdout_offset']);
        $stderr_offset = intval($_GET['stderr_offset']);

        if (!file_exists("{$session_base}{$session_id}.stdout")) {
            return $this->error("找不到 Session: $session_id");
        }
        $stdout = file_get_contents("{$session_base}{$session_id}.stdout", false, null, $stdout_offset);
        $stderr = file_get_contents("{$session_base}{$session_id}.stderr", false, null, $stderr_offset);

        if (file_exists("{$session_base}{$session_id}.done")) {
            unlink("{$session_base}{$session_id}.done");
            unlink("{$session_base}{$session_id}.stderr");
            unlink("{$session_base}{$session_id}.stdout");
            error_log("clean {$session_id}");
            $done = true;
        } else {
            $done = false;
        }

        return $this->json(array(
            'stdout' => $stdout,
            'stderr' => $stderr,
            'stdout_offset' => $stdout_offset + strlen($stdout),
            'stderr_offset' => $stderr_offset + strlen($stderr),
            'done' => $done,
        ));
    }
}
