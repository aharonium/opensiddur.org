#!/bin/bash
#
# Rotate the Wordpress Debug Log via system crom
# Written by Aharon Varady for the Open Siddur Project
# 

LOG="/home/.../debug.log"
MAXSIZE=$((20 * 1024 * 1024))

# Exit quietly if the log doesn't exist.
[ -f "$LOG" ] || exit 0

SIZE=$(stat -c%s "$LOG") || exit 1

if (( SIZE > MAXSIZE )); then
    : > "$LOG"
    printf '[%(%F %T)T] Log automatically cleared after exceeding %d MiB.\n' \
        -1 $((MAXSIZE / 1024 / 1024)) >> "$LOG"
fi