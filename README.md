PHP-pkg
-------

Configuration module written on PHP and based on [documentation](https://confluence.teamc.io/pages/viewpage.action?pageId=4227704)

## Run

```bash
make build
```

```bash
make run
```

## Composer

Example hot it usage:

1) Add following section into `composer.json` file.

```json
"repositories": [
    {
      "type": "vcs",
      "url": "git@gitlab.teamc.io:tm-consulting/tmc24/hotels/configuration/php-pkg.git"
    }
]
```

2) Add library to `require` section:

```json
"require": {
    "tmconsulting/microservice-configuration": "^1.0"
}
```

3) Run command

```bash
composer install
```