# Local Studio Container

This guide will walk you through launching a Docker-container local development studio and using it to test changes made within a local Git repository.

## Launch studio container

1. Install Chef Habitat:

    ```bash
    curl -s https://raw.githubusercontent.com/habitat-sh/habitat/master/components/hab/install.sh | sudo bash
    ```

1. Set up Chef Habitat, accepting defaults for all prompts:

    ```bash
    hab setup
    ```

1. Clone `{{ repository.name }}` repository and any submodules:

    ```bash
    git clone --recursive {{ repository.url }}
    ```

1. Change into cloned directory:

    ```bash
    cd ./{{ repository.name }}
    ```

1. Launch studio:

    Use the included [scripts-to-rules-them-all](https://github.com/github/scripts-to-rule-them-all) workflow script to configure and launch a studio session:

    ```bash
    script/studio
    ```

    Review the notes printed to your terminal at the end of the studio startup process for a list of all available studio commands.

## Bootstrap and develop backend

1. Start services:

    Use the studio command `start-all` to launch the http server (nginx), the application runtime (php-fpm), and a local mysql server:

    ```bash
    start-all
    ```

    At this point, you should be able to open [localhost:{{ studio.web_port }}](http://localhost:{{ studio.web_port }}) and see the error message `Page not found`.

1. Build site:

    To build the entire site and load it, use the studio command `update-site`:

    ```bash
    update-site
    ```

    At this point, [localhost:{{ studio.web_port }}](http://localhost:{{ studio.web_port }}) should display the current build of the site

1. Load fixture data into site database (optional):

    ```bash
    load-fixtures
    ```

    The standard fixture data includes the following users:

    | Username              | Password              | AccountLevel       | About            |
    |-----------------------|-----------------------|--------------------|------------------|
    {% for user in fixtures.users %}| `{{ user.username }}` | `{{ user.password }}` | `{{ user.level }}` | {{ user.about }} |
    {% endfor %}

1. Make and apply changes:

    After editing code in the working tree, you must rebuild and update the site:

    ```bash
    update-site
    ```

    A command to automatically rebuild and update the site as changes are made to the working tree is also available, but currently not that efficient or reliable:

    ```bash
    watch-site
    ```

## Enable user registration

To enable user registration on a site that comes with it disabled:

```bash
# write class configuring enabling registration
mkdir -p php-config/Emergence/People
echo '<?php Emergence\People\RegistrationRequestHandler::$enableRegistration = true;' > php-config/Emergence/People/RegistrationRequestHandler.config.php

# rebuild environment
update-site
```

After visiting [`/register`](http://localhost:{{ studio.web_port }}/register) and creating a new user account, you can use the studio command `promote-user` to upgrade the user account you just registered to the highest access level:

```bash
promote-user <myuser>
```

## Connect to local database

The studio container hosts a local MySQL instance that can be connected to at:

- **Host**: `localhost` (or LAN/WAN IP of machine hosting Docker engine)
- **Port**: `{{ studio.mysql_port }}`
- **Username**: `admin`
- **Password**: `admin`
