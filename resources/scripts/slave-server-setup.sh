#!/usr/bin/env bash
declare -i command_counter
command_counter=0

function create_parent_directory_on_not_exist() {
    parent_directory=$(dirname ${1})
    test -d ${parent_directory} || mkdir -v -p ${parent_directory}
}

# Function for using default value on user not provide value
function env_or_default() {
    name=${1}
    default=${2}

    if [ "$(eval echo \$${name})" == "" ];then
        eval ${name}="${default}"
    fi

    eval echo ${name}="\$${name}"
}

function hash() {
    echo -n ${@} | md5sum | awk '{print $1}'
}

function return_value_handler() {
    return_value=${?}
    if [ "${return_value}" != "0" ];then
        echo -e "Fatel error, command: \n-----\n${@}\n-----\nexit with non-zero: ${return_value}, script exit."
        exit ${return_value}
    fi
}

function raw_run_or_fail() {
    command=""
    for i in "${@}"; do
        command="${command} \"${i}\""
    done

    eval ${command}
    return_value_handler ${command}
}

# Exit on command return non-zero
function string_run_or_fail() {
    eval ${@}
    return_value_handler ${@}
}

# Alias for run_or_fail
function RUN() {
    if [ "${#}" == "1" ];then
        string_run_or_fail "${@}"
    else
        raw_run_or_fail "${@}"
    fi
}

# Use string as command
function SRUN() {
    string_run_or_fail "${@}"
}

# Stage command for run_or_fail
function RUNS() {
    command_counter+=1
    command_hash=$(hash "${command_counter} ${@}")
    echo "---Command ${command_counter}: ${command_hash}---"
    command_hash_record_file="${TMP_DIR}/${command_counter}-${command_hash}"
    if [ ! -f "${command_hash_record_file}" ];then
        if [ "${#}" == "1" ];then
            string_run_or_fail "${@}"
        else
            raw_run_or_fail "${@}"
        fi
        echo ${@} > ${command_hash_record_file}
    else
        echo -e "Skip command"
    fi
}

function install_composer() {
    EXPECTED_SIGNATURE="$(wget -q -O - https://composer.github.io/installer.sig)"
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
    ACTUAL_SIGNATURE="$(php -r "echo hash_file('sha384', 'composer-setup.php');")"

    if [ "$EXPECTED_SIGNATURE" != "$ACTUAL_SIGNATURE" ]
    then
        >&2 echo 'ERROR: Invalid installer signature'
        rm composer-setup.php
        return 1
    fi

    php composer-setup.php --quiet
    RESULT=$?
    rm composer-setup.php
    return $RESULT
}

env_or_default MAKE_JOBS $(cat /proc/cpuinfo | grep "processor" | wc -l || echo 4)
env_or_default PHP_VERSION "7.0"
env_or_default LIBVIRT_VERSION "5.2.0"
env_or_default TMP_DIR "/tmp/B3MGMG0DDXZLPVVVSH1WHFUFW5H313HN"
env_or_default FQDN "$(hostname -f)"

if [ "${FRESH_INSTALL}" != "" ] && [ "${TMP_DIR}" != "/" ] && [ "${TMP_DIR}" != "" ]; then
    rm -rf ${TMP_DIR}
fi

RUN umask 022

test -d ${TMP_DIR} || mkdir -p ${TMP_DIR}

# Change work directory to /usr/src
RUN pushd /usr/src

# Update index
RUN apt update

RUNS apt -y install dpkg-dev
RUNS apt-get -y build-dep libvirt-daemon

rm -rf libvirt-${LIBVIRT_VERSION}
RUN wget https://libvirt.org/sources/libvirt-${LIBVIRT_VERSION}.tar.xz -O- | tar -xJvf-
RUN pushd libvirt-${LIBVIRT_VERSION}
RUN ./configure --prefix=/usr --sysconfdir=/etc --localstatedir=/var
RUN make -j${MAKE_JOBS}
RUN make -j${MAKE_JOBS} install
RUN popd

RUNS apt -y install \
    dpkg-dev \
    qemu-kvm \
    ovmf \
    nginx-extras \
    curl \
    ca-certificates \
    php${PHP_VERSION}-fpm \
    php${PHP_VERSION}-curl \
    php${PHP_VERSION}-dev \
    php${PHP_VERSION}-json \
    php${PHP_VERSION}-mbstring \
    php${PHP_VERSION}-mysql \
    php${PHP_VERSION}-opcache \
    php${PHP_VERSION}-bcmath \
    php${PHP_VERSION}-readline \
    php${PHP_VERSION}-sqlite3 \
    php${PHP_VERSION}-xml \
    php${PHP_VERSION}-xmlrpc \
    php${PHP_VERSION}-zip \
    python-requests \
    python-websockify \
    git \
    rsync \
    unzip \
    libxml2-dev \
    xsltproc \
    php-imagick \
    supervisor \
    sudo
    # libsasl2-modules-db \
    # sasl2-bin

rm -rf libvirt-php
RUN git clone https://github.com/yzsme/libvirt-php.git
RUN pushd libvirt-php
RUN ./autogen.sh \
    --with-php-config=/usr/bin/php-config${PHP_VERSION} \
    --without-php-confdir

RUN make -j${MAKE_JOBS}
RUN make -j${MAKE_JOBS} install
SRUN "echo \"extension = libvirt-php.so\" > /etc/php/${PHP_VERSION}/mods-available/libvirt-php.ini"
RUN phpenmod -v ${PHP_VERSION} libvirt-php

# popd /usr/src
RUN popd

phpCommand="php${PHP_VERSION}"

RUN pushd /var/www
directoryName="ccms-slave"
if [ -d "${directoryName}" ];then
    pushd ${directoryName}
    RUN git pull
else
    RUN git clone https://github.com/yzsme/ccms-slave-ce.git ${directoryName}
    pushd ${directoryName}
fi

ln -sf "$(pwd -P)/storage/conf/libvirt/libvirtd.conf" /etc/libvirt/libvirtd.conf

RUN git config core.fileMode false
RUN touch database/database.sqlite

mkdir -p /etc/libvirt/hooks/
RUN cp -r resources/scripts/qemu-hook.sh /etc/libvirt/hooks/qemu
RUN chown root:root /etc/libvirt/hooks/qemu
RUN chmod 755 /etc/libvirt/hooks/qemu
#RUN cp -r resources/scripts/libvirtd /etc/init.d/libvirtd
#RUN cp -r resources/scripts/virtlogd /etc/init.d/virtlogd
#RUN cp -r resources/scripts/libvirt-guests /etc/init.d/libvirt-guests
#RUN chown root:root /etc/init.d/libvirtd /etc/init.d/virtlogd /etc/init.d/libvirt-guests
#RUN chmod 755 /etc/init.d/libvirtd /etc/init.d/virtlogd /etc/init.d/libvirt-guests
#RUN update-rc.d libvirtd defaults
#RUN update-rc.d virtlogd defaults
#RUN update-rc.d libvirt-guests defaults

RUNS install_composer
COMPOSER_PATH="$(pwd -P)/composer.phar"
if [ "${COMPOSER_CN}" != "" ];then
    echo "Use laravel-china packagist."
    ${phpCommand} ${COMPOSER_PATH} config -g repo.packagist composer https://packagist.laravel-china.org
fi

userName="ccms-slave"

groupadd libvirt
useradd -rms /bin/bash ${userName}
usermod -aG libvirt ${userName}

RUN chown -R ${userName}:${userName} .
RUN chmod -R 750 .
RUN sudo -u ${userName} ${phpCommand} ${COMPOSER_PATH} install

if [ ! -f .env ];then
    RUN cp .env.example .env
    RUN ${phpCommand} artisan key:generate
fi

if [ -d "noVNC" ];then
    pushd noVNC
    git pull
    popd
else
    git clone https://github.com/yzsme/ccms-noVNC.git noVNC
fi

RUN pushd noVNC
RUN git config core.fileMode false
RUN popd

RUN ${phpCommand} artisan ccms:init
RUN ${phpCommand} artisan migrate

RUN ${phpCommand} artisan ccms:slave:generate-uuid

RUN ${phpCommand} artisan cert:generate-ca
RUN ${phpCommand} artisan cert:generate-crl --no-auto-restart-services
RUN ${phpCommand} artisan cert:generate-server

RUN ${phpCommand} artisan libvirt:initial-conf
RUN ${phpCommand} artisan libvirt:write-conf
RUN ${phpCommand} artisan libvirt:write-isd

RUN ${phpCommand} artisan qemu:write-conf

RUN ${phpCommand} artisan php-fpm:write-pool-conf
RUN ${phpCommand} artisan php-fpm:write-isd
RUN ${phpCommand} artisan php-fpm:write-sc
RUN ${phpCommand} artisan nginx:write-site-conf

RUN ${phpCommand} artisan noVNC:write-supervisor-conf

RUN ${phpCommand} artisan ga:update

RUN ${phpCommand} artisan system:config:sudo
RUN ${phpCommand} artisan cron:set-job

RUN ${phpCommand} artisan perm:rst

# popd ${directoryName}
popd

systemctl daemon-reload
systemctl enable libvirtd
systemctl enable virtlogd
systemctl enable libvirt-guests
systemctl restart libvirtd
systemctl restart virtlogd
systemctl restart virtlogd.socket
/etc/init.d/php${PHP_VERSION}-fpm restart
/etc/init.d/nginx restart
/etc/init.d/supervisor restart

# popd /var/www
RUN popd
echo '#!/usr/bin/env' bash > /usr/bin/ccms-slave
echo -e "/usr/bin/sudo -u ccms-slave ${phpCommand} /var/www/${directoryName}/artisan \"\${@}\"" >> /usr/bin/ccms-slave
chmod 755 /usr/bin/ccms-slave

echo '#!/usr/bin/env' bash > /usr/bin/ccms-slave-root
echo -e "${phpCommand} /var/www/${directoryName}/artisan \"\${@}\"" >> /usr/bin/ccms-slave-root
chmod 750 /usr/bin/ccms-slave-root

ccms-slave-root pool:default
ccms-slave-root pimage:scan
ccms-slave-root piso:scan
ccms-slave-root pvfd:scan

ccms-slave network:define:default-host-only
ccms-slave network:define:local-private
ccms-slave network:define:public
ccms-slave network:activate:host-only
ccms-slave network:activate:local-private
ccms-slave network:activate:public
ccms-slave nwfilter:write
ccms-slave route:cache
ccms-slave-root cron:set-job

/etc/init.d/cron restart


echo "Set up finish."
