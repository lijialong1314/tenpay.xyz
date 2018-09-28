<?php
return [
    "context_option"=>[
        "ssl"=>[
            "local_cert"=>"/root/.acme.sh/baidu.com/baidu.com.cer",//你自己的https证书文件
            "local_pk"=>"/root/.acme.sh/baidu.com/baidu.com.key",//你自己的https证书私钥
             'verify_peer' => false,
        ]
    ]
];