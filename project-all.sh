#!/bin/bash

git holo project emergence-skeleton \
    --fetch \
    --ref=origin/releases/v1 \
    --commit-to=emergence/skeleton/v1

git holo project emergence-vfs-site \
    --fetch \
    --ref=origin/releases/v1 \
    --commit-to=emergence/vfs-site/v1
