#!/bin/bash
read -p 'Enter digirooster ical url (beginnend met "https://outlook.office365.com/"): ' ICAL_URL

echo $ICAL_URL > calendar-url.txt
echo Written info
