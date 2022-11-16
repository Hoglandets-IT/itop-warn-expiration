# iTop Warn Expiration
Trigger class to check if a date on a given object is approaching expiration.

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
