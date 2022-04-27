# Recipes (backend)

The backend of my recipes app.

## Installation

### Requirements
* [Docker](https://docs.docker.com/get-docker/)
* [DBeaver](https://dbeaver.io/download/) or any other database client to access the database

### Quick start
```bash
git clone https://github.com/y-garcia/recipes-backend.git
cd recipes-backend
# change passwords in docker-compose.yml and Config.php
# (see below for more info) and then run:
docker-compose up
# open http://localhost/recipes-dev/src/app/
```

### Detailed start
1. Install [Docker](https://docs.docker.com/get-docker/).
2. **Change** your database root and standard user **passwords** in the `docker-compose.yml` file.
3. Add the database user password to the file [src/config/Config.php](src/config/Config.php).
4. In order to use Google Sign-In to log into the recipes app, configure a 
   project in the Google Cloud Console and download the credentials json file to
   [src/config/credentials-dev.json](src/config/credentials-dev.json).
5. Add the following to [src/config/Config.php](src/config/Config.php):
   * CLIENT_ID: the client id  from the newly created project from step 4.
   * ROOT: The base url of the recipes website on your domain (e.g. https://example.com/recipes/src).
   * GOOGLE_REDIRECT_URL: The url to the "app" directory (e.g. https://example.com/recipes/src/app).
   * GOOGLE_CREDENTIALS: The local path to the json file containing the client id and secret that can be downloaded from
     downloaded in step 4 (e.g. credentials-dev.json).
6. Run `docker-compose up`. Afterwards you will have:
   * a MariaDB database running on port 3306
   * a PHP webserver running your code on port 80
7. You can **access your database** with any client (e.g. [DBeaver](https://dbeaver.io/download/)) using following credentials:
   * host: localhost
   * port: 3306
   * user: recipes_user (or root)
   * password: (the one you entered on step 2)
8. You can **access your php backend** by opening [http://localhost/recipes-dev/src/app/](http://localhost/recipes-dev/src/app/) on your browser. 
   This corresponds with the same folder structure in your code. 

## Usage

Coming soon...

## Contributing

Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change.

## Versioning

We use [SemVer](http://semver.org/) for versioning. For the versions available, see the [tags on this repository](https://github.com/y-garcia/tomate-timer/tags).

See the [CHANGELOG.md](CHANGELOG.md) file for details. 

## Authors

* **Yeray García Quintana** - Initial work - [y-garcia](https://github.com/y-garcia)

See also the list of [contributors](https://github.com/y-garcia/tomate-timer/contributors) who participated in this project.

## License

This project is licensed under the [MIT License](https://choosealicense.com/licenses/mit/). See the [LICENSE.md](LICENSE.md) file for details.

### External Licenses

This software uses the following external libraries:

* [Slim framework](http://www.slimframework.com/): Licensed under the [MIT License](https://choosealicense.com/licenses/mit/). See [LICENSE-Slim.md](LICENSE-Slim.md) for details.

