# PHP Document Number Formatter

A flexible, zero-dependency PHP utility for generating formatted document numbers, such as invoice numbers, purchase orders, or receipt IDs. It uses a simple template-based system that can embed dynamic date/time components, fiscal year calculations, and zero-padded sequential numbers.

This script is a direct port of a useful TypeScript/JavaScript utility and is designed to be fully compatible with **PHP 5** and later versions.

## Features

- **Template-Based Formatting**: Define a string format like `INV-[YYYY][MM]-[v]` and let the script generate the output.
- **Rich Date/Time Placeholders**: Supports a wide variety of date formats for year, month, day, and week number.
- **Fiscal Year Support**: Automatically adjust the year based on a specified fiscal start month (e.g., if the fiscal year starts in April, a date in February will correctly map to the previous year).
- **Value Padding**: Automatically pads sequential numbers with leading zeros to a specified length (e.g., `123` becomes `00123`).
- **Pure PHP**: No external libraries or dependencies are required.
- **PHP 5+ Compatible**: Written to work in legacy environments as well as modern ones.

## Installation

Simply include the `DocumentNumberFormatter.php` file in your project and you can start using its static methods.

```php
require_once 'path/to/DocumentNumberFormatter.php';
```

## Usage

The primary method is `DocumentNumberFormatter::formatDocumentNumber()`. You provide a format string, a value, and optional parameters for padding and date.

```php
public static function formatDocumentNumber(
    $format,
    $value = '0',
    $size = 0,
    $month = null,
    $currentDate = null
)
```

### Basic Example

```php
$invoiceNumber = 123;
$format = 'INV-[YYYY]-[MM]-[v]';

// Generate the document number with 5-digit padding for the value
$result = DocumentNumberFormatter::formatDocumentNumber($format, $invoiceNumber, 5);

echo $result;
// Output for the year 2025 and month June: INV-2025-06-00123
```

### Fiscal Year Example

Suppose your fiscal year starts in April (`apr`). For any date before April, the year will be considered the previous one.

```php
$fiscalFormat = 'FY-[YYYY=apr]/[v:size:4]';
$value = 45;

// A date within the '2025' fiscal year
$date1 = '2025-06-26';
$result1 = DocumentNumberFormatter::formatDocumentNumber($fiscalFormat, $value, 0, null, $date1);
echo $result1; // Output: FY-2025/0045

// A date in the previous fiscal year ('2024')
$date2 = '2025-02-15';
$result2 = DocumentNumberFormatter::formatDocumentNumber($fiscalFormat, $value, 0, null, $date2);
echo $result2; // Output: FY-2024/0045
```

## Formatting Tokens

Use the following tokens inside square brackets `[...]` in your format string.

### Value / Number Tokens

| Token | Description | Example | Output |
| :--- | :--- | :--- | :--- |
| `[v]` | The placeholder for the value. Use the `$size` parameter in the function to set padding. | `formatDocumentNumber('..', 12, 5)` | `00012` |
| `[v:size:N]`| The placeholder for the value, with padding defined directly in the format string. `N` is the total length. | `formatDocumentNumber('[v:size:4]', 12)` | `0012` |

### Year Tokens

| Token | Description | Example Date | Output |
| :--- | :--- | :--- | :--- |
| `[YY]` | Two-digit year. | 2025-06-26 | `25` |
| `[YYYY]` | Four-digit year. | 2025-06-26 | `2025` |
| `[YYYY=mon]` | Four-digit fiscal year. `mon` is the three-letter lowercase start month. | 2025-02-15 with `[YYYY=apr]` | `2024` |
| `[YYYY+N]` | The current year plus `N` years. | 2025-06-26 with `[YYYY+1]` | `2026` |
| `[YYYY-N]` | The current year minus `N` years. | 2025-06-26 with `[YYYY-2]` | `2023` |

### Month Tokens

| Token | Description | Example Date | Output |
| :--- | :--- | :--- | :--- |
| `[M]` | The month number, without leading zeros. | 2025-06-26 | `6` |
| `[MM]` | The month number, with leading zeros. | 2025-06-26 | `06` |
| `[MMM]` | The three-letter month name, uppercase. | 2025-06-26 | `JUN` |
| `[MMMM]`| The full month name, uppercase. | 2025-06-26 | `JUNE` |

### Day Tokens

| Token | Description | Example Date | Output |
| :--- | :--- | :--- | :--- |
| `[D]` | The day of the month, without leading zeros. | 2025-06-05 | `5` |
| `[DD]` | The day of the month, with leading zeros. | 2025-06-05 | `05` |
| `[d]` or `[dd]`| The two-letter day of the week, uppercase. | 2025-06-26 (Thursday) | `TH` |
| `[ddd]` | The three-letter day of the week, uppercase. | 2025-06-26 (Thursday) | `THU` |
| `[dddd]`| The full day of the week, uppercase. | 2025-06-26 (Thursday) | `THURSDAY` |

### Week Tokens

| Token | Description | Example Date | Output |
| :--- | :--- | :--- | :--- |
| `[W]` | The week number of the year, without leading zeros. | 2025-06-26 | `26` |
| `[WW]` | The week number of the year, with leading zeros. | 2025-01-10 | `02` |


## Public Methods

A quick reference to the available static methods.

### `formatDocumentNumber()`

The main formatting function.

```php
string DocumentNumberFormatter::formatDocumentNumber(string $format, [string|int $value], [int $size], [string|int $month], [string|int $currentDate])
```

### `fdnForDate()`

A wrapper for `formatDocumentNumber` with a different argument order, where the date comes first.

```php
string DocumentNumberFormatter::fdnForDate(string|int $currentDate, string $format, [string|int $value], [int $size], [string|int $month])
```

## Credits

This PHP script is a port of the original JavaScript code from https://github.com/hariprasath-yadav/document-number-formatter
