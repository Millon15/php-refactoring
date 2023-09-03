# php refactoring challenge

- There is some really ugly, but kinda working code
- The challenge is to refactor (rewrite, actually) this code and to cover it with unit tests

This should take up to 2-4 hours in most cases.

### The code

```php
<?php

foreach (explode("\n", file_get_contents($argv[1])) as $row) {

    if (empty($row)) break;
    $p = explode(",",$row);
    $p2 = explode(':', $p[0]);
    $value[0] = trim($p2[1], '"');
    $p2 = explode(':', $p[1]);
    $value[1] = trim($p2[1], '"');
    $p2 = explode(':', $p[2]);
    $value[2] = trim($p2[1], '"}');

    $binResults = file_get_contents('https://lookup.binlist.net/' .$value[0]);
    if (!$binResults)
        die('error!');
    $r = json_decode($binResults);
    $isEu = isEu($r->country->alpha2);

    $rate = @json_decode(file_get_contents('https://api.exchangeratesapi.io/latest'), true)['rates'][$value[2]];
    if ($value[2] == 'EUR' or $rate == 0) {
        $amntFixed = $value[1];
    }
    if ($value[2] != 'EUR' or $rate > 0) {
        $amntFixed = $value[1] / $rate;
    }

    echo $amntFixed * ($isEu == 'yes' ? 0.01 : 0.02);
    print "\n";
}

function isEu($c) {
    $result = false;
    switch($c) {
        case 'AT':
        case 'BE':
        case 'BG':
        case 'CY':
        case 'CZ':
        case 'DE':
        case 'DK':
        case 'EE':
        case 'ES':
        case 'FI':
        case 'FR':
        case 'GR':
        case 'HR':
        case 'HU':
        case 'IE':
        case 'IT':
        case 'LT':
        case 'LU':
        case 'LV':
        case 'MT':
        case 'NL':
        case 'PO':
        case 'PT':
        case 'RO':
        case 'SE':
        case 'SI':
        case 'SK':
            $result = 'yes';
            return $result;
        default:
            $result = 'no';
    }
    return $result;
}
```

**Note!** It's intentionally that ugly ;) Don't let this trick you into making semi-ugly solution yourself.

### Example `input.txt` file

```json
{"bin":"45717360","amount":"100.00","currency":"EUR"}
{"bin":"516793","amount":"50.00","currency":"USD"}
{"bin":"45417360","amount":"10000.00","currency":"JPY"}
{"bin":"41417360","amount":"130.00","currency":"USD"}
{"bin":"4745030","amount":"2000.00","currency":"GBP"}
```

### Running the code

Assuming PHP code is in `app.php`, you could run it by this command, output might be different due to dynamic data:

```
$ php app.php input.txt
1
0.46180844185832
1.6574127786525
2.4014038976632
43.714413735069
```

### Notes about the code

1. Idea is to calculate commissions for already made transactions
2. Transactions are provided each in it's own line in the input file, in JSON
3. BIN number represents first digits of credit card number. They can be used to resolve country where the card was
   issued
4. We apply different commission rates for EU-issued and non-EU-issued cards
5. We calculate all commissions in EUR currency

### Requirements for your code

1. You MUST cover your solutin with unit tests.
    - Unit tests must test the actual results and still pass even when the response from remote services change
      (this is quite normal, exchange rates change every day).
      This is best accomplished by using mocking.
2. As an improvement, add ceiling of commissions by cents. For example, `0.46180...` should become `0.47`.
3. It should give the same result as original code in case there are no failures, except for the additional ceiling.
4. Code should be extendible – we should not need to change existing, already tested functionality to accomplish the
   following:
    - Switch our currency rates provider
      (different URL, different response format and structure, possibly some authentication)
    - Switch our BIN provider
      (different URL, different response format and structure, possibly some authentication)
    - Just to note – no need to implement anything additional.
      Just structure your code so that we could implement that later on without braking our tests
5. It should look as you'd write it yourself in production – consistent, readable, structured.
   Anything we'll find in the code, we'll treat as if you'd write it yourself.
   Basically it's better to just look at the existing code and re-write it from scratch.
6. Use composer to install testing framework and any needed dependencies you'd like to use,
   also for enabling autoloading.
