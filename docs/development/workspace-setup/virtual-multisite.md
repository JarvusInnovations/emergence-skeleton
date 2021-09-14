# Virtual Multi-site Container

## Launch virtual multisite container

```bash
docker run \
    --name emergence \
    -v emergence:/emergence \
    -p 80:80 \
    -p 3306:3306 \
    -p 9083:9083 \
    jarvus/emergence
```
