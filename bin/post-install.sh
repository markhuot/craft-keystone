#!/bin/bash

./craft plugin/install pest
./craft plugin/install ai
php ./bin/create-default-fs.php > /dev/null 2>&1
