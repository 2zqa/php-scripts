#!/bin/bash
read -p 'Enter digirooster ical url (starting with "https://outlook.office365.com/"): ' ICAL_URL

echo $ICAL_URL > calendar-url.txt
echo Written info
