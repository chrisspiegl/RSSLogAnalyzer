#!/bin/bash

# https://gist.github.com/3783146

RSS_LOG_FILE="PATH_AND_FILENAME_OF_THE_FILE_WHERE_THE_SCRIPT_SHOULD_LOG_INTO"

# --- Required variables ---
RSS_URI="/feed"
MAIL_TO_SPECIAL=0 # 1=on / 0=off
MAIL_TO_SPECIAL_DOMAIN=(    "YOURDOMAIN1.com"   "YOURDOMAIN2.com"   )   # To send an email per domain (for spezific domains)
MAIL_TO_SPECIAL_MAIL=(      "YOUR@EMAIL.com"    "YOUR@EMAIL.com"    )   # Add the domain in the upper row and at the same place the email
MAIL_TO="YOUR_EMAIL_ADRESS"
LOG_FILE="PATH_TO_APACHE_LOG_FILE"
LOG_DATE_FORMAT="%d/%b/%Y"

# --- Optional customization ---

MAIL_SUBJECT="RSS feed subscribers"

# Date expression for yesterday
DATE="-1 day"

# Locale for printf number formatting (e.g. "10000" => "10,000")
LANG=en_US

# Date format for display in emails
HUMAN_FDATE=`date -d "$DATE" '+%F'`

# --- The actual log parsing ---

LOG_FDATE=`date -d "$DATE" "+${LOG_DATE_FORMAT}"`
DAY_BEFORE_FDATE=`date -d "$DATE -1 day" "+${LOG_DATE_FORMAT}"`

REPORT=""
LOGROW="$HUMAN_FDATE|"

FORDOMAINS=$(egrep "($LOG_FDATE|$DAY_BEFORE_FDATE)" "$LOG_FILE" | fgrep " $RSS_URI" | cut -d':' -f 1 | sort | uniq)

echo $FORDOMAINS

for entry in $FORDOMAINS; do
    # Unique IPs requesting RSS, except those reporting "subscribers":
    IPSUBS=`egrep "($LOG_FDATE|$DAY_BEFORE_FDATE)" "$LOG_FILE" | fgrep "$entry" | fgrep " $RSS_URI" | egrep -v '[0-9]+ subscribers' | cut -d' ' -f 2 | sort | uniq | wc -l`
    if [ -z "$IPSUBS" ]; then IPSUBS=`echo 0`; fi;

    # # Google Reader subscribers and other user-agents reporting "subscribers" and using the "feed-id" parameter for uniqueness:
    GRSUBS=`egrep "($LOG_FDATE|$DAY_BEFORE_FDATE)" "$LOG_FILE" | fgrep "$entry" | fgrep " $RSS_URI" | egrep -o '[0-9]+ subscribers; feed-id=[0-9]+' | sort -t= -k2 -s | tac | uniq -f2 | awk '{s+=$1} END {print s}'`
    if [ -z "$GRSUBS" ]; then GRSUBS=`echo 0`; fi;

    # # Other user-agents reporting "subscribers", for which we'll use the entire user-agent string for uniqueness:
    OTHERSUBS=`egrep "($LOG_FDATE|$DAY_BEFORE_FDATE)" "$LOG_FILE" | fgrep "$entry" | fgrep " $RSS_URI" | fgrep -v 'subscribers; feed-id=' | egrep '[0-9]+ subscribers' | egrep -o '"[^"]+"$' | sort -t\( -k2 -sr | awk '!x[$1]++' | egrep -o '[0-9]+ subscribers' | awk '{s+=$1} END {print s}'`
    if [ -z "$OTHERSUBS" ]; then OTHERSUBS=`echo 0`; fi;

    LOGROW+=$(
        printf "$entry/%d/%d/%d/%d|" $GRSUBS $OTHERSUBS $IPSUBS `expr $GRSUBS + $OTHERSUBS + $IPSUBS`
    )
    REPORT_SINGLE=$(
        printf "\n\n\nFeed stats for $entry $HUMAN_FDATE:\n\n"
        printf "%'8d Google Reader subscribers\n" $GRSUBS
        printf "%'8d subscribers from other aggregators\n" $OTHERSUBS
        printf "%'8d direct subscribers\n" $IPSUBS
        echo   "--------"
        printf "%'8d total subscribers\n\n==============================" `expr $GRSUBS + $OTHERSUBS + $IPSUBS`
    )
    REPORT+=$REPORT_SINGLE
    if [ $MAIL_TO_SPECIAL -eq 1 ]; then
        for((i=0;i<${#MAIL_TO_SPECIAL_DOMAIN[@]}; i++)); do
            if [ $entry = ${MAIL_TO_SPECIAL_DOMAIN[${i}]} ]; then
                #echo "MAIL AN: " $entry " WITH " ${MAIL_TO_SPECIAL_MAIL[${i}]} " THIS IS WHAT I SAY: " $REPORT_SINGLE
                echo "$REPORT_SINGLE " | mail -s "[$HUMAN_FDATE] $MAIL_SUBJECT" ${MAIL_TO_SPECIAL_MAIL[${i}]}
                EMAILADDR=${MAIL_TO_SPECIAL_MAIL[${i}]}
                printf "Email sent to: $EMAILADDR\n"
            fi
        done;
    fi
done;


echo "$REPORT"
echo ""
echo "Also emailed to $MAIL_TO."

# Save the rss stats into a log file to later calc averages or echoh
echo "$LOGROW" >> $RSS_LOG_FILE
echo "$REPORT " | mail -s "[$HUMAN_FDATE] $MAIL_SUBJECT" $MAIL_TO