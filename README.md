# THM.Products

## Setup and Configuration


Create composer.json with following content:
```
{
	"name": "typo3/flow-base-distribution",
	"description" : "TYPO3 Flow Base Distribution",
	"license": "LGPL-3.0+",
	"config": {
		"vendor-dir": "Packages/Libraries",
		"bin-dir": "bin"
	},
	"repositories": [
		{
			"type": "vcs",
			"url": "https://github.com/sasschreiber/THM.Products.git"
		},
		{
			"type": "vcs",
			"url": "https://github.com/radmiraal/Radmiraal.CouchDB.git"
		}
	],

	"require": {
		"typo3/flow": "dev-master",
		"typo3/welcome": "dev-master",
		"typo3/fluid": "@dev",
		"typo3/eel": "@dev",
		"doctrine/migrations": "@dev",
		"thm/products":"dev-couchdb2"
	},
	"require-dev": {
		"typo3/kickstart": "dev-master",
		"typo3/buildessentials": "dev-master",
		"phpunit/phpunit": "4.5.*",
		"mikey179/vfsstream": "1.4.*",
		"doctrine/couchdb-odm": "dev-master",
		"doctrine/couchdb": "dev-master",
		"radmiraal/couchdb": "2.0.x-dev"
	},
	"suggest": {
		"ext-pdo_sqlite": "For running functional tests out-of-the-box this is required"
	},
	"scripts": {
		"post-update-cmd": "TYPO3\\Flow\\Composer\\InstallerScripts::postUpdateAndInstall",
		"post-install-cmd": "TYPO3\\Flow\\Composer\\InstallerScripts::postUpdateAndInstall",
		"post-package-update":"TYPO3\\Flow\\Composer\\InstallerScripts::postPackageUpdateAndInstall",
		"post-package-install":"TYPO3\\Flow\\Composer\\InstallerScripts::postPackageUpdateAndInstall"
	}
}
```

run ```composer update```

- Install couchdb on your server
- Create a couchdb admin user(see curl command below)

```
curl -X PUT http://127.0.0.1:5984/_config/admins/flow -d 'flow'
curl -X PUT http://flow:flow@127.0.0.1:5984/flow
```

- add following login data to Configuration/[CONTEXT]/Settings.yaml

```
Radmiraal:
  CouchDB:
    persistence:
      backendOptions:
        databaseName: 'radmiraal2'
        host: 'localhost'
        port: 5984
        username: flow
        password: flow
        ip: '127.0.0.1'
```

- run ```./flow migrate:designs``` 
- and you are done with setup
- now you can run ./bin/benchmark.sh to compare performance with ORM
