<?php
/**
 * Created by PhpStorm.
 * Date: 19-1-30
 * Time: 下午9:21
 */

namespace App\Utils\ServiceConfiguration\Nginx;


use App\Utils\Certificates\Certificates;

class CCMSSlaveSiteConfiguration
{
    public function make()
    {
        $CACertificateFilePath = Certificates::CA_CERTIFICATE_FILE_PATH;
        $CRLFilePath = Certificates::CA_CRL_FILE_PATH;
        $serverPrivateKeyFilePath = Certificates::SERVER_PRIVATE_KEY_FILE_PATH;
        $serverFullChainCertificateFilePath = Certificates::SERVER_FULL_CHAIN_CERTIFICATE_FILE_PATH;

        $hostOnlySubnet = \YunInternet\CCMSCommon\Constants\Constants::DEFAULT_HOST_ONLY_IPV4_SUBNET;

        return <<<EOF
server {
        listen 2048 ssl http2 default_server;
        listen [::]:2048 ssl http2 default_server;

        ssl_certificate $serverFullChainCertificateFilePath;
        ssl_certificate_key $serverPrivateKeyFilePath;
        
        ssl_client_certificate $CACertificateFilePath;
        ssl_crl $CRLFilePath;
        ssl_verify_client on;

        root /var/www/ccms-slave/public;

        server_name _;

        index index.php;

        location / {
                try_files \$uri \$uri/ /index.php?\$query_string;
        }

        location ~ \.php$ {
                # regex to split \$uri to \$fastcgi_script_name and \$fastcgi_path
                fastcgi_split_path_info ^(.+\.php)(/.+)$;
                
                # Check that the PHP script exists before passing it
                try_files \$fastcgi_script_name /index.php?\$query_string;
                
                # Bypass the fact that try_files resets \$fastcgi_path_info
                # see: http://trac.nginx.org/nginx/ticket/321
                set \$path_info \$fastcgi_path_info;
                fastcgi_param PATH_INFO \$path_info;
                
                fastcgi_index index.php;
                include fastcgi.conf;

                fastcgi_param SSL_PROTOCOL          \$ssl_protocol;
                fastcgi_param SSL_CIPHER            \$ssl_cipher;
                fastcgi_param SSL_SESSION_ID        \$ssl_session_id;
                fastcgi_param SSL_CLIENT_VERIFY     \$ssl_client_verify;
                
                fastcgi_param SSL_CLIENT_CERT       \$ssl_client_cert;
                fastcgi_param SSL_CLIENT_RAW_CERT   \$ssl_client_raw_cert;
                fastcgi_param SSL_CLIENT_S_DN       \$ssl_client_s_dn;
                fastcgi_param SSL_CLIENT_I_DN       \$ssl_client_i_dn;
                fastcgi_param SSL_CLIENT_SERIAL     \$ssl_client_serial;

                fastcgi_pass unix:/var/run/php/ccms-slave.sock;
        }

        location ~ /\.ht {
                deny all;
        }

        location ~* \.(pem|csr|crt|cer|pfx|old|bak|backup|sql|gz|bz|bz2|tar|zip)$ {
                deny all;
        }
}

server {
        listen 127.0.0.1:2049 default_server;
        
        root /var/www/ccms-slave/public;
        
        server_name localhost 127.0.0.1;
        
        rewrite ^/api/localOnly/.* /index.php?\$query_string last;
        return 403;
                
        location ~ \.php$ {
                allow 127.0.0.0/8;
                deny all;
                
                # regex to split \$uri to \$fastcgi_script_name and \$fastcgi_path
                fastcgi_split_path_info ^(.+\.php)(/.+)$;
                
                # Check that the PHP script exists before passing it
                try_files \$fastcgi_script_name /index.php?\$query_string;
                
                # Bypass the fact that try_files resets \$fastcgi_path_info
                # see: http://trac.nginx.org/nginx/ticket/321
                set \$path_info \$fastcgi_path_info;
                fastcgi_param PATH_INFO \$path_info;
                
                fastcgi_index index.php;
                include fastcgi.conf;
                
                fastcgi_pass unix:/var/run/php/ccms-slave.sock;
        }

        location ~ /\.ht {
                deny all;
        }

        location ~* \.(pem|csr|crt|cer|pfx|old|bak|backup|sql|gz|bz|bz2|tar|zip)$ {
                deny all;
        }
}

limit_req_zone \$binary_remote_addr zone=CCMSSlaveGuestLimitReqZone:10m rate=15r/m;

server {
        listen 2050 default_server;
        
        root /var/www/ccms-slave-guest/;
        
        server_name _;
        
        limit_req zone=CCMSSlaveGuestLimitReqZone;
        
        rewrite ^/guest$ /index.php?\$query_string last;
        return 403;
                
        location ~ \.php$ {
                allow $hostOnlySubnet;
                deny all;
                
                # regex to split \$uri to \$fastcgi_script_name and \$fastcgi_path
                fastcgi_split_path_info ^(.+\.php)(/.+)$;
                
                # Check that the PHP script exists before passing it
                try_files \$fastcgi_script_name /index.php?\$query_string;
                
                # Bypass the fact that try_files resets \$fastcgi_path_info
                # see: http://trac.nginx.org/nginx/ticket/321
                set \$path_info \$fastcgi_path_info;
                fastcgi_param PATH_INFO \$path_info;
                
                fastcgi_index index.php;
                include fastcgi.conf;
                
                fastcgi_pass unix:/var/run/php/ccms-slave-guest.sock;
        }

        location ~ /\.ht {
                deny all;
        }

        location ~* \.(pem|csr|crt|cer|pfx|old|bak|backup|sql|gz|bz|bz2|tar|zip)$ {
                deny all;
        }
}
EOF
            ;
    }

    public function makeThenWrite()
    {
        file_put_contents(Constants::SITE_CONFIGURATION_FILE_PATH, $this->make());
    }
}