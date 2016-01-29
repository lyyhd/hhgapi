<?php

return [
    'EASEMOB_DOMAIN' => env('easemob_domain', 'a1.easemob.com'),          //域名
    'ORG_NAME'       => env('easemob_org_name', 'chongxiangtec'),        //公司名称
    'APP_NAME'       => env('easemob_app_name', 'hhg'),        //应用名称
    'CLIENT_ID'       => env('easemob_client_id', 'YXA6c7oYMLkMEeWKweFXTHTUDg'),
    'CLIENT_SECRET'   => env('easemob_client_secret', 'YXA6ca8jVmr4W07_AiXNrRzehMfZbsM'),
    'TOKEN_PATH'     => env('token_path','easemob.token'),               //token储存的位置，默认不用修改
];
