# This package is not finished, it is still being developed.

### To test this package while it is being testing you must follow the following steps 

- update the `"require-dev"` prop in your **composer.json** file with `"ecreeth/relationships": "dev-master"`

- Add the next text to your **composer.json** file
```json
  "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/ecreeth/relationships"
        }
    ],
```

#### Run the next command in your terminal
```bash
composer update
```

#### And finally run
```bash
php artisan make:relationship
```
