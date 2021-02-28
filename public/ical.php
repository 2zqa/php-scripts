<?php
$url = trim(file_get_contents("../calendar-url.txt"));
// Only allow from outlook for safety
if(substr($url, 0, 30) !== "https://outlook.office365.com/") {
	exit();
}

$firstpattern = "/\/(.+\/|.+?\s)/"; // from start until (inclusive)
$secondpattern = "/(?<=SUMMARY:).+?\//"; // Everything that appears after SUMMARY: until a backslash (inclusive)
$teacherpattern = "/ {.*/"; // Everything after a "{"-character until newline (inclusive)

function str_replace_and_return($pattern, $replacement, $subject, &$matches) {
	if (preg_match($pattern, $subject, $matches) !== false) {
		return preg_replace($pattern, $replacement, $subject);
	} else {
		return $subject;
	}
}

if(!isset($_GET["debug"])) {
	header('Content-type: text/calendar; charset=utf-8');
	header('Content-Disposition: inline; filename=calendar.ics');
} else {
	echo '<pre>DEBUGGING'."\n---------\n\n";
}

$handle = @fopen($url, "r");
if ($handle) {
	// Iterate over lines
	while (($line = fgets($handle, 4096)) !== false) {
		// Check if current line is a summary
		if(strpos($line, "SUMMARY") !== false) {
			// Skip if not a lesson. Simple check: must contain a "{"
			if(strpos($line, "{") === false) {
				echo $line;
				continue;
			}

			// Remove fluff
			// Examples:
			// 1. Removes "/21 " in "/21 Honours R. {DROK,FUME,HEBL,KAHH,MEEC,SWPI,VEGT}"
			// 2. Removes "/B " and then "SE SE3/" in "/B SE SE3/ADS hc {HEBL,ROTE}"
			$line = preg_replace($firstpattern, "", $line, 1);
			$line = preg_replace($secondpattern, "", $line, 1);

			// Remove {teachname} and related if found, saves to variable
			// Example:
			// 1. Removes " {HEBL,ROTE}\n" in "/B SE SE3/ADS hc {HEBL,ROTE}\n" (including space and newline)
			$matches = array();
			$line = str_replace_and_return($teacherpattern, "", $line, $matches);

			// substr: 2 places for " {" and -2 for "}\n"
			$docenten = substr(str_replace("\\,", ", ", $matches[0]), 2, -2);

			// Replace keywords hc and prac/pract
			$line = str_replace(" hc", " hoorcollege", $line);
			$line = preg_replace("/ pract| prac/", " practicum", $line);

			// Capitalize event names, use space and : for delimiters
			$line = ucwords($line," :");

			// If multiple teachers, use plural form
			if($pos = strpos($docenten, ',') !== false) {
				// Replace last comma with 'en' (=and)
				$portion = strrchr($docenten, ',');
				$docenten = str_replace($portion, (" en" . substr($portion, 1, -1)), $docenten);
				$line .= "DESCRIPTION:Docenten: ";
			} else {
				$line .= "DESCRIPTION:Docent: ";
			}

			// Add teacher list to description and add a new line for further ical items
			$line .= $docenten . "\n";
		}

		// Print line
		echo $line;
	}
	//TODO: handle errors (in general) in some way? e-mailing? dummy ical with appointment at current time saying there is an error?
	// if (!feof($handle)) {
	//     echo "Error: unexpected fgets() fail\n";
	// }
	fclose($handle);
}