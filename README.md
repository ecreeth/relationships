# This package is not finished, it is still being developed.

### To test this package while it is being testing you must follow the following steps 

![image](https://user-images.githubusercontent.com/20761166/61153942-c22b4a00-a4ba-11e9-8823-15b896d02996.png)

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
