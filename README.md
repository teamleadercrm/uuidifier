Embed incremental ids inside special uuids.

## Installation

Install using composer:

```bash
composer require teamleadercrm/uuidifier
```

## Console command
To do some quick encoding, we have a console command:
```
bin/console uuidifier:encode {prefix} {id}
```

e.g.
```
bin/console uuidifier:encode InvoiceId 1
```
will give you
```
Id 1 with prefix InvoiceId encodes as ac42e979-4cf9-0f3c-8616-6ff689592c91
```
