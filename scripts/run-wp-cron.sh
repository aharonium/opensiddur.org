#!/bin/bash
#
# WP-Cron Wrapper
#
# Executes all due WordPress cron events via WP-CLI.
# Written by Aharon Varady for the Open Siddur Project

PHP="/opt/cpanel/ea-php84/root/usr/bin/php" #your php
WP="/usr/local/bin/wp" #your wp-cli
SITE="/home/.../your_site_root" #your site root
LOG="/home/.../logs/debug.log" #your debug log wherever your wp-config.php says it should be (in your logs directory outside your site root for security)
LOCK="/home/.../tmp/wp-cron.lock" #your lock file (in your tmp directory seems a good place for it)

(
    # Prevent overlapping cron runs.
    if ! flock -n 9; then
        echo "===== $(date) =====" >> "$LOG"
        echo "Skipped: Previous cron run is still in progress." >> "$LOG"
        echo >> "$LOG"
        exit 0
    fi

    echo "===== $(date) =====" >> "$LOG"

    # Time the WP-CLI execution.
    SECONDS=0

    $PHP $WP \
        --path="$SITE" \
        cron event run --due-now \
        >> "$LOG" 2>&1

    STATUS=$?
    ELAPSED=$SECONDS

    # If the cron run succeeded, report whether any due events remain.
    if [ "$STATUS" -eq 0 ]; then

        echo "Result: SUCCESS (${ELAPSED}s)" >> "$LOG"

    else

        echo "Result: FAILURE (${ELAPSED}s, exit code $STATUS)" >> "$LOG"

    fi

    echo >> "$LOG"

) 9>"$LOCK"