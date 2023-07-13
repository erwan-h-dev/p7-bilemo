# p7-bilemo

[![Codacy Badge](https://app.codacy.com/project/badge/Grade/9a9a184411e74abb81e40d2005becbd9)](https://app.codacy.com/gh/erwan-h-dev/p7-bilemo/dashboard?utm_source=gh&utm_medium=referral&utm_content=&utm_campaign=Badge_grade)


## DESCRIPTION

Bilemo est une API RESTful permettant de consulter des téléphones mobiles.
Celle-ci permet aux clients de consulter les téléphones ainsi que de créer, modifier et de les supprimer des utilisateurs.

Le projet a été développer dans un contexte pédagogique pour OpenClassrooms.
- [API Documentation](http://p7-bilemo.erwan-h.fr:48200/api/doc)

## REQUIREMENTS
* PHP 8.*
* MySQL 8.*
* Node 18.14.*
* Composer 2.5.*

## INSTALLATION

1. Clone the repository
```bash
git clone
```

2. Install dependencies
```bash
composer install
```

3. Create database
```bash
php bin/console doctrine:database:create
```

4. Create database schema
```bash
php bin/console doctrine:schema:create
```

5. Load fixtures
```bash
php bin/console doctrine:fixtures:load
```

- The default admin account is : 
    * username : admin
    * password : password

- The default client account is : 
    * username : client
    * password : password