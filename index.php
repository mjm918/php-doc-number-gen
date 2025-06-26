<?php

class DocumentNumberFormatter {
    public static $MonthsMin = array('jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec');
    public static $Months = array('january', 'february', 'march', 'april', 'may', 'june', 'july', 'august', 'september', 'october', 'november', 'december');

    public static $DaysMin = array('su', 'mo', 'tu', 'we', 'th', 'fr', 'sa');
    public static $Days = array('sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat');
    public static $DaysMax = array('sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday');

    private static function getMonthIndex($month) {
        if (is_numeric($month)) {
            return intval($month) - 1;
        }
        if (is_string($month)) {
            $month = strtolower($month);
            $index = array_search($month, self::$Months);
            if ($index !== false) {
                return $index;
            }
            $index = array_search($month, self::$MonthsMin);
            if ($index !== false) {
                return $index;
            }
        }
        return 0; // Default to January
    }

    public static function getWeek($timestamp, $dowOffset = 0) {
        $dowOffset = is_numeric($dowOffset) ? $dowOffset : 0;

        $year = date('Y', $timestamp);
        $newYearTimestamp = strtotime($year . '-01-01');
        
        // The day of the week the year begins on.
        $day = date('w', $newYearTimestamp) - $dowOffset;
        $day = ($day >= 0 ? $day : $day + 7);

        $daynum = floor(($timestamp - $newYearTimestamp) / 86400) + 1;
        $weeknum = 0;

        // if the year starts before the middle of a week
        if ($day < 4) {
            $weeknum = floor(($daynum + $day - 1) / 7) + 1;
            if ($weeknum > 52) {
                $nextYearTimestamp = strtotime(($year + 1) . '-01-01');
                $nday = date('w', $nextYearTimestamp) - $dowOffset;
                $nday = $nday >= 0 ? $nday : $nday + 7;
                /* if the next year starts before the middle of
                   the week, it is week #1 of that year */
                $weeknum = $nday < 4 ? 1 : 53;
            }
        } else {
            $weeknum = floor(($daynum + $day - 1) / 7);
        }
        return $weeknum;
    }

    public static function formatDate($format, $month = null, $currentDate) {
        $timestamp = is_string($currentDate) ? strtotime($currentDate) : $currentDate;

        $currentMonth = intval(date('n', $timestamp)) - 1; // 0-11
        $currentWeek = self::getWeek($timestamp);
        
        switch ($format) {
            case 'D':
                return date('j', $timestamp);
            case 'DD':
                return date('d', $timestamp);
            case 'd':
            case 'dd':
                return strtoupper(self::$DaysMin[date('w', $timestamp)]);
            case 'ddd':
            case 'DDD':
                return strtoupper(self::$Days[date('w', $timestamp)]);
            case 'dddd':
            case 'DDDD':
                return strtoupper(self::$DaysMax[date('w', $timestamp)]);
            case 'W':
                return (string)$currentWeek;
            case 'WW':
                return sprintf('%02d', $currentWeek);
            case 'M':
                return date('n', $timestamp);
            case 'MM':
                return date('m', $timestamp);
            case 'MMM':
                return strtoupper(self::$MonthsMin[$currentMonth]);
            case 'MMMM':
                return strtoupper(self::$Months[$currentMonth]);
            default:
                if (strpos($format, 'Y') === 0) {
                    $formats = explode('=', str_replace(array('[', ']'), '', $format));
                    $currentYear = intval(date('Y', $timestamp));
                    $yearFormat = '';

                    if ($month !== null) {
                        $yearFormat = $format;
                    } else {
                        $yearFormat = $formats[0];
                        if (count($formats) > 1) {
                            $month = $formats[1];
                        }
                    }

                    if ($month !== null) {
                         $fiscalMonthIndex = self::getMonthIndex($month);
                         if ($currentMonth < $fiscalMonthIndex) {
                            $currentYear -= 1;
                         }
                    }
                    
                    $yearOnly = $yearFormat;
                    if (strpos($yearFormat, '+') !== false) {
                        $parts = explode('+', $yearFormat);
                        $currentYear += intval($parts[1]);
                        $yearOnly = $parts[0];
                    } elseif (strpos($yearFormat, '-') !== false) {
                        $parts = explode('-', $yearFormat);
                        $currentYear -= intval($parts[1]);
                        $yearOnly = $parts[0];
                    }

                    $currentYearString = (string)$currentYear;
                    return substr($currentYearString, -strlen($yearOnly));
                } else {
                    return $format;
                }
        }
    }

    private static function formatValue($format, $value, $size) {
        $valString = (string)$value;
        $formatSize = 0;

        if ($size > 0) {
            $formatSize = $size;
        } else {
            $formatSize = intval(str_replace(array('[', 'val:size:', 'v', ']'), '', $format));
        }

        if ($formatSize > 0) {
            return str_pad($valString, $formatSize, '0', STR_PAD_LEFT);
        }
        return $valString;
    }

    public static function formatDocumentNumber($format, $value = '0', $size = 0, $month = null, $currentDate = null) {
        $timestamp = null;
        if ($currentDate === null) {
            $timestamp = time();
        } else {
            $timestamp = is_string($currentDate) ? strtotime($currentDate) : $currentDate;
        }

        $documentNumber = '';
        $startIndex = 0;
        $formatLength = strlen($format);

        while ($startIndex < $formatLength) {
            $openBracIndex = strpos($format, '[', $startIndex);
            $documentNumber .= substr($format, $startIndex, ($openBracIndex !== false ? $openBracIndex - $startIndex : $formatLength));

            if ($openBracIndex === false) {
                break;
            }

            $closeBracIndex = strpos($format, ']', $openBracIndex);
            if ($closeBracIndex === false) {
                // Handle unclosed bracket by breaking
                $documentNumber .= substr($format, $openBracIndex);
                break;
            }

            $formatOnly = substr($format, $openBracIndex + 1, $closeBracIndex - $openBracIndex - 1);
            $colonIndex = strpos($formatOnly, ':');

            $firstChar = substr($formatOnly, 0, 1);
            $prefix = ($colonIndex !== false) ? substr($formatOnly, 0, $colonIndex) : '';

            if ($firstChar === 'v') {
                $documentNumber .= self::formatValue($formatOnly, $value, $size);
            } elseif ($prefix === 'date' || in_array($firstChar, array('Y', 'M', 'D', 'd', 'W'))) {
                $documentNumber .= self::formatDate($formatOnly, $month, $timestamp);
            } else {
                $documentNumber .= $formatOnly;
            }
            $startIndex = $closeBracIndex + 1;
        }
        return $documentNumber;
    }

    public static function fdnForDate($currentDate, $format, $value = '0', $size = 0, $month = null) {
        return self::formatDocumentNumber($format, $value, $size, $month, $currentDate);
    }
}


$today = '2025-06-26'; 
$invoiceNumber = 123;
$paddingSize = 5;

$format1 = 'DNF/[YY=apr]-[YY+1=apr]/[val:size:6]';
$result1 = DocumentNumberFormatter::formatDocumentNumber($format1, $invoiceNumber, $paddingSize, null, $today);
echo "Example 1: " . $result1 . "\n"; 


$format2 = 'PO/[DDDD]/[MMM]/[YY]';
$result2 = DocumentNumberFormatter::formatDocumentNumber($format2, null, 0, null, $today);
echo "Example 2: " . $result2 . "\n"; 


$fiscalFormat = 'FY-[YYYY=apr]-[v:size:4]';
$dateInFiscalYear = '2025-06-26';
$dateBeforeFiscalYear = '2025-02-15';

$result3a = DocumentNumberFormatter::formatDocumentNumber($fiscalFormat, 45, 0, null, $dateInFiscalYear);
echo "Example 3a (Date in fiscal year): " . $result3a . "\n";

$result3b = DocumentNumberFormatter::formatDocumentNumber($fiscalFormat, 46, 0, null, $dateBeforeFiscalYear);
echo "Example 3b (Date before fiscal year): " . $result3b . "\n";

// Example 4: Using the fdnForDate wrapper function
$format4 = 'RCPT-[YYYY+1]-[WW]';
$result4 = DocumentNumberFormatter::fdnForDate($today, $format4);
echo "Example 4 (Wrapper function): " . $result4 . "\n";

?>
