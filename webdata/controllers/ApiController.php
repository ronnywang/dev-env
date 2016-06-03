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

    public function listfileAction()
    {
        $base_folder = $_REQUEST['base'];

        $file_base = realpath(__DIR__ . '/../../files/') . '/';
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

        $file_base = realpath(__DIR__ . '/../../files/') . '/';
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
}
