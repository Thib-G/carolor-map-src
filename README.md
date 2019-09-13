# Carol'Or map, geolocated
https://carolor.org/map/

## Project setup
```
npm install
```

### Compiles and hot-reloads for development
```
npm run serve
```

### Compiles and minifies for production
```
npm run build
```

### Run your tests
```
npm run test
```

### Lints and fixes files
```
npm run lint
```

### Customize configuration
See [Configuration Reference](https://cli.vuejs.org/config/).

## Deploy

* Rename `/src/public/api/inc/conf.inc.php-dist` to `conf.inc.php` and add your credentials.
* Run `npm run build`.
* Upload the content of the `/dist` folder to your PHP-enabled server (by default to `/var/www/map/`).
