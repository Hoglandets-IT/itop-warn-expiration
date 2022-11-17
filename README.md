# iTop Warn Expiration
Trigger class to check if a date on a given object is approaching expiration. Adds an additional Trigger to the Notification flow and runs with cron every few hours. Notifies once per object on the following conditions:

- An object of type T (iTop Class, setting per trigger) with the field E (Date or DateTime field, setting per trigger) passes N (Amount of days before date passes, setting per trigger)

- There has been no notification sent for this trigger between now and the date in field E minus N days

- The object is not marked as 'obsolete'

## Prerequisites
itop-structure/3.0.0 or newer

## Installation
Unzip the itop-warn-expiration folder into your extensions folder, re-run the setup to install the plugin

## Usage

## Notice
If running earlier versions of iTop 3.0.x, you might have to change the following line in action.class.inc.php:

```php
# Line 266, function FindRecipients
# Existing: 
protected function FindRecipients($sRecipAttCode, $aArgs)
{
    $sOQL = $this->Get($sRecipAttCode);
    if (strlen($sOQL) === 0) return '';
    // ......
}

# Change the following line
if (strlen($sOQL) == '') return '';

# To
if (strlen($sOQL) === 0) return '';
```
