# contao-PageThemeSniffer_bz

Sniffs the theme of a contao page.

Usage:

```php
$this->import('PageLayoutSniffer');
$output = $this->PageLayoutSniffer->findThemeData($varValue);

// Returns layout id $output->theme->id;
// Returns layout name $output->theme->name;
// Returns layout id $output->layout->id;
// Returns layout name $output->layout->name;

/* Output
stdClass Object
(
    [layout] => stdClass Object
        (
            [id] => 1
            [name] => HM Carport
        )

    [theme] => stdClass Object
        (
            [id] => 3
            [name] => Carports
        )

)
*/    
```

