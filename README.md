# emergence-skeleton-v2

## Development

1. Create a site with handle skeleton-v2 extending skeleton-v1.emr.ge (or your own skeleton-v1 instance)
2. Create [`php-config/Git.config.d/emergence-skeleton-v2.php`](https://github.com/JarvusInnovations/emergence-skeleton-v2/blob/master/php-config/Git.config.d/emergence-skeleton-v2.php)
3. Change `remote` to an SSH git URL you can write to (fork to your own user/organization if needed)
4. Visit <kbd>/site-admin/sources</kbd> and initialize the repository
5. Sync the git working tree to the VFS
6. Execute <kbd>/sass/compile</kbd>
7. Execute <kbd>/sencha-cmd/pages-build</kbd>
