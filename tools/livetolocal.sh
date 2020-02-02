#!/bin/bash

ssh goprep "
  ./console/backup_sql.sh
  exit
"

scp forge@157.230.216.93:~/backup_sql.sql $1

mysql -u root -e "drop database goprep";
mysql -u root -e "create database goprep";
mysql -u root goprep < $1

# Set the passwords of all accounts to 'secret'
mysql -u root -e "UPDATE goprep.users set password='\$2y\$10\$2/2kIqiN7bobISEc.mIQwe.2qWdHUvzdkg8.xWqPRzlk88tZCz/Wa'";

php artisan migrate
