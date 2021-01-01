# Developing Migrations

Within the development studio:

1. Create a new file under `php-migrations/`
2. Load modified working tree into runtime:

    ```bash
    update-site
    ```

3. Execute all migrations:

    ```bash
    console-run migrations:execute --all
    ```

4. (Re)Execute a specific migration:

    ```bash
    console-run migrations:execute --force "Emergence/People/20191209_system-user"
    ```
