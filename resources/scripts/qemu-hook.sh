#!/usr/bin/env bash
guest_name=${1}
action=${2}
status=${3}

case "${action}" in
    "started")
        ccms-slave ci:event:started ${guest_name} &
    ;;
    "stopped")
        ccms-slave ci:event:stopped ${guest_name} &
    ;;
esac

exit 0
