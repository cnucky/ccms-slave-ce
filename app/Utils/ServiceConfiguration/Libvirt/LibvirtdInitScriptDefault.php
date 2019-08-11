<?php
/**
 * Created by PhpStorm.
 * Date: 19-1-30
 * Time: 下午6:26
 */

namespace App\Utils\ServiceConfiguration\Libvirt;


class LibvirtdInitScriptDefault
{
    private $startLibvirtd = true;

    private $libvirtdOptions = "";

    public function startLibvirtd($start)
    {
        $this->startLibvirtd = boolval($start);
        return $this;
    }

    public function libvirtdOptions($options)
    {
        $this->libvirtdOptions = $options;
        return $this;
    }

    public function make()
    {
        $startLibvirtd = $this->startLibvirtd ? "yes" : "no";

        return <<<EOF
# Defaults for libvirtd initscript (/etc/init.d/libvirtd)
# This is a POSIX shell fragment

# Start libvirtd to handle qemu/kvm:
start_libvirtd="${startLibvirtd}"

# options passed to libvirtd, add "-l" to listen on tcp
libvirtd_opts="$this->libvirtdOptions"

# pass in location of kerberos keytab
#export KRB5_KTNAME=/etc/libvirt/libvirt.keytab

# Whether to mount a systemd like cgroup layout (only
# useful when not running systemd)
#mount_cgroups=yes
# Which cgroups to mount
#cgroups="memory devices"
EOF
;
    }

    public function makeSysconf()
    {
        return <<<EOF
# Override the default config file
# NOTE: This setting is no longer honoured if using
# systemd. Set '--config /etc/libvirt/libvirtd.conf'
# in LIBVIRTD_ARGS instead.
#LIBVIRTD_CONFIG=/etc/libvirt/libvirtd.conf

# Listen for TCP/IP connections
# NB. must setup TLS/SSL keys prior to using this
LIBVIRTD_ARGS="$this->libvirtdOptions"

# Override Kerberos service keytab for SASL/GSSAPI
#KRB5_KTNAME=/etc/libvirt/krb5.tab

# Override the QEMU/SDL default audio driver probing when
# starting virtual machines using SDL graphics
#
# NB these have no effect for VMs using VNC, unless vnc_allow_host_audio
# is enabled in /etc/libvirt/qemu.conf
#QEMU_AUDIO_DRV=sdl
#
#SDL_AUDIODRIVER=pulse

# Override the maximum number of opened files.
# This only works with traditional init scripts.
# In the systemd world, the limit can only be changed by overriding
# LimitNOFILE for libvirtd.service. To do that, just create a *.conf
# file in /etc/systemd/system/libvirtd.service.d/ (for example
# /etc/systemd/system/libvirtd.service.d/openfiles.conf) and write
# the following two lines in it:
#   [Service]
#   LimitNOFILE=2048
#
#LIBVIRTD_NOFILES_LIMIT=2048
EOF;

    }

    public function makeThenWrite()
    {
        file_put_contents(Constants::INIT_SCRIPT_DEFAULT_FILE_PATH, $this->make());
        file_put_contents(Constants::SYSCONF_FILE_PATH, $this->makeSysconf());
    }
}