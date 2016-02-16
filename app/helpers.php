<?php

if (! function_exists('config_path')) {
    /**
     * Get the configuration path.
     *
     * @param  string $path
     * @return string
     */
    function config_path($path = '')
    {
        return app()->basePath() . '/config' . ($path ? '/' . $path : $path);
    }
}

if (! function_exists('return_message')) {
    /**
     *
     */
    function return_message($status, $message)
    {
        return compact('status','message');
    }
}

if (! function_exists('return_rest')) {
    /**
     *status 0 失败 1为成功
     */
    function return_rest($state = '1', $data ,$msg = '操作成功')
    {

        return array('json' => compact('state','data','msg'));
    }
}
