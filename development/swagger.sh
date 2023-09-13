#!/bin/bash

# Output directory
directory="../public/swagger"

# Check if the directory exists
if [ ! -d "$directory" ]; then
    # Directory doesn't exist, create it
    mkdir -p "$directory"
    echo "Directory '$directory' created."
else
    # Directory already exists
    echo "Directory '$directory' already exists."
fi

php ../vendor/zircote/swagger-php/bin/openapi --bootstrap ./swagger-constants.php --output ../public/swagger ./swagger-v1.php ../app/Http/Controllers
