#!/bin/bash

git holo project emergence-skeleton \
    --ref=origin/releases/v1 \
    --commit-to=emergence/skeleton/v1

git holo project emergence-vfs-site \
    --ref=origin/releases/v1 \
    --commit-to=emergence/vfs-site/v1

git holo project emergence-vfs-skeleton \
    --ref=origin/releases/v1 \
    --commit-to=emergence/vfs-skeleton/v1
